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
    if ($priceFieldValueId == 297) {
      return [
        'max_discount_each' => 600,
        'max_persons' => 2,
        'discount_field_id' => 224,
      ];
    }
    else {
      return NULL;
    }
  }

  public static function calculateParticipantDiscount($amounts, $selectedDiscounts, $submitValues, $participantPositionId) {
    // Calculate total available discounts
    $selectedDiscountTotal = 0;
    foreach ($selectedDiscounts as $priceSetId => $priceSetSelectedDiscounts) {
      foreach ($priceSetSelectedDiscounts as $priceFieldId => $priceFieldDiscount) {
        // Use this discount only if $participantPositionId is within the max_persons limit.
        if ($participantPositionId <= CRM_Utils_Array::value('max_persons', $priceFieldDiscount, 0)) {
          $selectedDiscountTotal += CRM_Utils_Array::value('max_discount_each', $priceFieldDiscount, 0);
        }
      }
    }
    if (!$selectedDiscountTotal) {
      return 0;
    }

    // Caculate total price based on submitted values.
    $emptyArray = [];
    CRM_Price_BAO_PriceSet::processAmount($amounts, $submitValues, $emptyArray);
    $amount = CRM_Utils_Array::value('amount', $submitValues, 0);

    return min($amount, $selectedDiscountTotal);
  }

  public static function hideDiscountField(&$amounts) {
    $availableDiscounts = CRM_Discountaddlreg_Util::getAvailableDiscountConfig($amounts);
    $discountFieldId = CRM_Utils_Array::value('discount_field_id', reset(reset($availableDiscounts)));;
    unset($amounts[$discountFieldId]);
  }
}
