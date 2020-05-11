<?php

/**
 * Utility methods for discountaddlreg
 *
 * @author as
 */
class CRM_Discountaddlreg_Util {

  public static function getAvailableDiscountConfig($amounts) {
    $availableDiscounts = [];
    foreach ($amounts as $amountId => $amount) {
      foreach (CRM_Utils_Array::value('options', $amount, []) as $optionId => $option) {
        $optionConfig = self::getConfig($optionId);
        if (!empty($optionConfig)) {
          $availableDiscounts[$amountId][$optionId] = $optionConfig;
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
          if ($primaryParticipantParams[$priceSetKey][$priceFieldValueId]) {
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
      ->execute()
      ->first();
    if (empty($config)) {
      $config = [];
    }
    return $config;
  }

  public static function calculateParticipantDiscounts($amounts, $selectedDiscounts, $submitValues, $participantPositionId) {
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
        if ($participantPositionId <= CRM_Utils_Array::value('max_persons', $priceFieldDiscount, 0)) {
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
        unset($amounts[$optionConfig['discount_field_id']]);
      }
    }
  }

}
