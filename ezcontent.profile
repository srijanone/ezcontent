<?php

/**
 * @file
 * Enables modules and site configuration for a standard site installation.
 */

use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements hook_install_tasks().
 */
function ezcontent_install_tasks(&$install_state) {
  $tasks = [];
  /*
  If (empty($install_state['config_install_path'])) {
  $tasks['ezcontent_module_configure_form'] = [
  'display_name' => t('Configure additional modules'),
  'type' => 'form',
  'function' => 'Drupal\ezcontent\Installer\Form\ModuleConfigureForm',
  ];
  $tasks['ezcontent_module_install'] = [
  'display_name' => t('Install additional modules'),
  'type' => 'batch',
  ];
  }
   */
  return $tasks;
}

/**
 * Installs the ezcontent modules in a batch.
 *
 * @param array $install_state
 *   The install state.
 *
 * @return array
 *   A batch array to execute.
 */
function ezcontent_module_install(array &$install_state) {
  $modules = $install_state['ezcontent_additional_modules'];
  $batch = [];
  if ($modules) {
    $operations = [];
    foreach ($modules as $module) {
      $operations[] = [
        '_ezcontent_install_module_batch',
        [[$module], $module, $install_state['form_state_values']],
      ];
    }

    $batch = [
      'operations' => $operations,
      'title' => t('Installing additional modules'),
      'error_message' => t('The installation has encountered an error.'),
    ];
  }

  return $batch;
}

/**
 * Implements callback_batch_operation().
 *
 * Performs batch installation of modules.
 */
function _ezcontent_install_module_batch($module, $module_name, $form_values, &$context) {
  set_time_limit(0);
  try {
    \Drupal::service('module_installer')->install($module, TRUE);
  }
  catch (\Exception $e) {
    \Drupal::logger('ezcontent')->error($e->getMessage());
  }
  $context['results'][] = $module;
  $context['message'] = t('Installed %module_name modules.', ['%module_name' => $module_name]);
}

/**
 * Implements hook_themes_installed().
 */
function ezcontent_themes_installed($theme_list) {
  if (in_array('ezcontent_amp', $theme_list)) {
    // Install AMP module.
    \Drupal::service('module_installer')->install(['amp'], TRUE);

    // Use EZContent AMP theme as the theme in AMP settings.
    $amp_theme_config = \Drupal::configFactory()->getEditable('amp.theme');
    $amp_theme = $amp_theme_config->get('amptheme');
    if (empty($amp_theme) || $amp_theme !== 'ezcontent_amp') {
      $amp_theme_config->set('amptheme', 'ezcontent_amp')
        ->save(TRUE);
    }
  }
}

/**
 * Implements hook_form_alter().
 */
function ezcontent_form_alter(array &$form, FormStateInterface $form_state, $form_id) {
  if ($form_state->getFormObject() instanceof EntityFormInterface) {
    $entityType = $form_state->getFormObject()->getEntity()->bundle();
    $entityHideObj = \Drupal::config('ezcontent.settings')->getRawData();
    if (isset($entityHideObj[$entityType]) && $entityHideObj[$entityType]) {
      $form['revision_information']['#access'] = FALSE;
    }
  }
}
