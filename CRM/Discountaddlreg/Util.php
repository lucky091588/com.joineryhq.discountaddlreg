<?php

/**
 * Utility methods for discountaddlreg
 *
 * @author as
 */
class CRM_Discountaddlreg_Util {

  public static function getAvailableDiscountConfig($amounts, $participantPositionId = NULL) {
    $availableDiscounts = [];
    foreach ($amounts as $amountId => $amount) {
      foreach (CRM_Utils_Array::value('options', $amount, []) as $optionId => $option) {
        $optionConfig = self::getConfig($optionId);
        if (!empty($optionConfig)) {
          if (!$participantPositionId || $participantPositionId >= ($optionConfig['min_person'] ?? 0)) {
            $availableDiscounts[$amountId][$optionId] = $optionConfig;
          }
        }
      }
    }
    return $availableDiscounts;
  }

  public static function getSelectedDiscounts($availableDiscounts, $primaryParticipantParams) {
    $selectedDiscounts = [];
    foreach ($availableDiscounts as $priceSetId => $optionConfigs) {
      $priceSetKey = "price_{$priceSetId}";
      if (isset($primaryParticipantParams[$priceSetKey])) {
        foreach ($optionConfigs as $priceFieldValueId => $optionConfig) {
          if (
            is_array($primaryParticipantParams[$priceSetKey]) && isset($primaryParticipantParams[$priceSetKey][$priceFieldValueId])
            || $primaryParticipantParams[$priceSetKey] == $priceFieldValueId
          ) {
            $selectedDiscounts[$priceSetId][$priceFieldValueId] = $optionConfig;
          }
        }
      }
    }
    return $selectedDiscounts;
  }

  public static function getConfig($priceFieldValueId) {
    $config = $priceFieldValueDiscount = \Civi\Api4\PriceFieldValueDiscount::get()
      ->addWhere('price_field_value_id', '=', $priceFieldValueId)
      ->setCheckPermissions(FALSE)
      ->execute()
      ->first();
    if (empty($config)) {
      $config = [];
    }
    return $config;
  }

  public static function calculateParticipantDiscounts($amounts, $selectedDiscounts, $submitValues, $countedParticipantPositionId) {
    // Caculate total price based on submitted values.
    $emptyArray = [];
    CRM_Price_BAO_PriceSet::processAmount($amounts, $submitValues, $emptyArray);
    $undiscountedAmount = CRM_Utils_Array::value('amount', $submitValues, 0);

    // Calculate total available discounts
    $selectedDiscountTotals = [];
    foreach ($selectedDiscounts as $priceSetId => $priceSetSelectedDiscounts) {
      foreach ($priceSetSelectedDiscounts as $priceFieldId => $priceFieldDiscount) {
        $discountFieldId = $priceFieldDiscount['discount_field_id'];
        if (empty($selectedDiscountTotals[$discountFieldId])) {
          $selectedDiscountTotals[$discountFieldId] = 0;
        }
        // Use this discount only if $participantPositionId is within the max_persons limit.
        $minPerson = CRM_Utils_Array::value('min_person', $priceFieldDiscount, 0);
        $maxPerson = $minPerson + CRM_Utils_Array::value('max_persons', $priceFieldDiscount, 0) - 1;
        if (
          $countedParticipantPositionId >= $minPerson
          && $countedParticipantPositionId <= $maxPerson
        ) {
          $maxDiscountEach = CRM_Utils_Array::value('max_discount_each', $priceFieldDiscount, 0);
          if (($undiscountedAmount - $maxDiscountEach) >= 0) {
            $selectedDiscountTotals[$discountFieldId] += $maxDiscountEach;
            $undiscountedAmount -= $maxDiscountEach;
          }
          else {
            $selectedDiscountTotals[$discountFieldId] += $undiscountedAmount;
            $undiscountedAmount = 0;
            break 2;
          }
        }
      }
    }
    return $selectedDiscountTotals;
  }

  public static function hideDiscountFields(&$amounts) {
    $availableDiscounts = CRM_Discountaddlreg_Util::getAvailableDiscountConfig($amounts);
    foreach ($availableDiscounts as $priceSetId => $optionConfigs) {
      foreach ($optionConfigs as $priceFieldValueId => $optionConfig) {
        // Hide the discount field with CSS.
        // Rationale and concerns:
        //  - This is definitely better than hiding with JavaScript.
        //  - Simply removing the item in hook_buildAmount is best, but we must be
        //    certain only to remove it upon form display, and never during submitted
        //    form proessing (as doing so would prevent the discount from being counted).
        //    Given civicrm's execution flow, that hook is unable to distinguish
        //    between a) form being submitted properly and b) form being submitted with
        //    validation errors (which causes the form to be reloaded). I.e., that
        //    hook can't distinguish between a case in which the field should be
        //    removed and one in which it should not.
        //  - Hiding the field with CSS does well for all use cases, as far as normal
        //    UX goes; the user should never see these fields, and that criteria
        //    is met with this method.
        //  - However, merely hiding the field with css leaves open the possibility
        //    of tampering, because the field is still submitted and processed. A
        //    malicious user could manipulate the value of this discount field,
        //    bypassing the field's intent.  We aim to mitigate this in hook_ubildAmount
        //    by forcing the value to 0 in all cases, and only then applying the
        //    correct value where appropriate.
        //
        CRM_Core_Resources::singleton()->addStyle("div.crm-section.{$amounts[$optionConfig['discount_field_id']]['name']}-section{display:none;}");
      }
    }
  }

  public static function stripSubmittedDiscountFieldValues($amounts, &$submitValues) {
    $availableDiscounts = CRM_Discountaddlreg_Util::getAvailableDiscountConfig($amounts);
    foreach ($availableDiscounts as $priceSetId => $optionConfigs) {
      foreach ($optionConfigs as $priceFieldValueId => $optionConfig) {
        unset($submitValues["price_{$optionConfig['discount_field_id']}"]);
      }
    }
  }

  public static function devalueDiscountFields(&$amounts) {
    $availableDiscounts = CRM_Discountaddlreg_Util::getAvailableDiscountConfig($amounts);
    foreach ($availableDiscounts as $priceSetId => $optionConfigs) {
      foreach ($optionConfigs as $priceFieldValueId => $optionConfig) {
        foreach ($amounts[$optionConfig['discount_field_id']]['options'] as &$option) {
          $option['amount'] = 0;
        }
      }
    }
  }

  static function adjustCountedParticipantPosition($participantPositionId, $primaryParticipantParams, $eventId) {
    // Start with the actual position.
    $countedParticipantPositionId = $participantPositionId;
    // Determine if primary is attending; start by checking the params.
    $isRegisteringSelf = $primaryParticipantParams['isRegisteringSelf'] ?? NULL;
    // If it's not in the params, and if groupreg is even installed, then it's determined
    // on a per-event basis from event config. Get it from there.
    if ($isRegisteringSelf === NULL && method_exists('CRM_Groupreg_Util', 'getEventSettings')) {
      $groupregEventSettings = CRM_Groupreg_Util::getEventSettings($eventId);

      $isPrimaryAttending = $groupregEventSettings['is_primary_attending'] ?? CRM_Groupreg_Util::primaryIsAteendeeYes;
      if ($isPrimaryAttending == CRM_Groupreg_Util::primaryIsAteendeeYes) {
        $isRegisteringSelf = 1;
      }
    }
    // If primary is not attending, decrement the counted position.
    if (!$isRegisteringSelf) {
      $countedParticipantPositionId--;
    }
    return $countedParticipantPositionId;
  }
}
