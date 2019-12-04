<?php
/**
 * @file
 * Add custom theme settings to Acquia Claro.
 */
/**
 * Implements hook_form_FORM_ID_alter().
 *
 * @param $form
 *   The form.
 * @param $form_state
 *   The form state.
 */
function ezcontent_admin_form_system_theme_settings_alter(&$form, $form_state) {
  $form['theme_ui_options'] = array(
    '#type' => 'details',
    '#title' => t('UI Options'),
    '#open' => 'false',
  );
  $form['theme_ui_options']['ezcontent_admin_logo'] = array(
    '#type' => 'checkbox',
    '#title' => t('Display logo'),
    '#default_value' => theme_get_setting('ezcontent_admin_logo'),
  );
  $form['theme_ui_options']['ezcontent_admin_site_name'] = array(
    '#type' => 'checkbox',
    '#title' => t('Display site name'),
    '#default_value' => theme_get_setting('ezcontent_admin_site_name'),
  );
}
