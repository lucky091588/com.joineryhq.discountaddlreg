<?php

require_once 'discountaddlreg.civix.php';
use CRM_Discountaddlreg_ExtensionUtil as E;

/**
 * Implements hook_civicrm_buildForm().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_buildForm/
 */
function discountaddlreg_civicrm_buildForm($formName, &$form) {
  // TODO: complete this section.
  return;

  if (empty($form->_submitValues)) {
    // Fixme: this is probably better done in buildForm, because  the if-conditions
    // here are funny when we use continue and back buttons.
    //
    // This form has not been submitted, so this is the time to insert our notice
    // about discounts taken at final, if any discounts were selected.
    $availableDiscounts = CRM_Discountaddlreg_Util::getAvailableDiscountConfig($amounts);
    $params = $form->getVar('_params');
    $selectedDiscounts = CRM_Discountaddlreg_Util::getSelectedDiscounts($availableDiscounts, $params[0]);
    if (!empty($selectedDiscounts)) {
    }
  }
}

/**
 * Implements hook_civicrm_buildAmount().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_buildAmount/
 */
function discountaddlreg_civicrm_buildAmount($pageType, &$form, &$amounts) {
  $formName = $form->getVar('_name');
  if (
    (substr($formName, 0, 12) == 'Participant_')
    && preg_match('/^Participant_([0-9]+)$/', $formName, $matches)
  ) {
    $participantPositionId = $matches[1];
    $actionName = $form->controller->_actionName[1];
    if ($actionName == 'upload') {
      // If action is upload, it means this participant form is submitted,
      // so we can know the selected values, and therefore discount only those.
      // This way the discounts don't appear until the very end, which is really
      // the only way to do it, since we must apply discounts AFTER the price
      // selections are submitted.
      $params = $form->getVar('_params');
      $submitValues = $form->getVar('_submitValues');
      $primaryFormKey = $params[0]['qfKey'];
      $availableDiscounts = CRM_Discountaddlreg_Util::getAvailableDiscountConfig($amounts);
      $selectedDiscounts = CRM_Discountaddlreg_Util::getSelectedDiscounts($availableDiscounts, $params[0]);

      $discountFieldId = CRM_Utils_Array::value('discount_field_id', reset(reset($selectedDiscounts)));

      $participantDiscount = CRM_Discountaddlreg_Util::calculateParticipantDiscount($amounts, $selectedDiscounts, $submitValues, $participantPositionId);
      if ($participantDiscount) {
        // If any discount is to be applied, add the value of the 'discount price field'
        // to reflect that amount in the negative.
        if ($discountFieldId) {
          $submitValues["price_{$discountFieldId}"] = (-1 * $participantDiscount);
          $form->setVar('_submitValues', $submitValues);
        }
      }
      else {
        unset($amounts[$discountFieldId]);
      }
    }
  }
}

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function discountaddlreg_civicrm_config(&$config) {
  _discountaddlreg_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_xmlMenu
 */
function discountaddlreg_civicrm_xmlMenu(&$files) {
  _discountaddlreg_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function discountaddlreg_civicrm_install() {
  _discountaddlreg_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function discountaddlreg_civicrm_postInstall() {
  _discountaddlreg_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function discountaddlreg_civicrm_uninstall() {
  _discountaddlreg_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function discountaddlreg_civicrm_enable() {
  _discountaddlreg_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function discountaddlreg_civicrm_disable() {
  _discountaddlreg_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function discountaddlreg_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _discountaddlreg_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
 */
function discountaddlreg_civicrm_managed(&$entities) {
  _discountaddlreg_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_caseTypes
 */
function discountaddlreg_civicrm_caseTypes(&$caseTypes) {
  _discountaddlreg_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_angularModules
 */
function discountaddlreg_civicrm_angularModules(&$angularModules) {
  _discountaddlreg_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_alterSettingsFolders
 */
function discountaddlreg_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _discountaddlreg_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function discountaddlreg_civicrm_entityTypes(&$entityTypes) {
  _discountaddlreg_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Implements hook_civicrm_thems().
 */
function discountaddlreg_civicrm_themes(&$themes) {
  _discountaddlreg_civix_civicrm_themes($themes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 *
function discountaddlreg_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 *
function discountaddlreg_civicrm_navigationMenu(&$menu) {
  _discountaddlreg_civix_insert_navigation_menu($menu, 'Mailings', array(
    'label' => E::ts('New subliminal message'),
    'name' => 'mailing_subliminal_message',
    'url' => 'civicrm/mailing/subliminal',
    'permission' => 'access CiviMail',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _discountaddlreg_civix_navigationMenu($menu);
} // */
