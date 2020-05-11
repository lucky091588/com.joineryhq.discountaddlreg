<?php
use CRM_Discountaddlreg_ExtensionUtil as E;

class CRM_Discountaddlreg_BAO_priceFieldValueDiscount extends CRM_Discountaddlreg_DAO_PriceFieldValueDiscount {

  /**
   * Create a new PriceFieldValueDiscount based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Discountaddlreg_DAO_PriceFieldValueDiscount|NULL
   *
  public static function create($params) {
    $className = 'CRM_Discountaddlreg_DAO_PriceFieldValueDiscount';
    $entityName = 'PriceFieldValueDiscount';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  } */

}
