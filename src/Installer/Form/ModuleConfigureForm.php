<?php

namespace Drupal\ezcontent\Installer\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the site configuration form.
 */
class ModuleConfigureForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ezcontent_module_configure_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['description'] = [
      '#type' => 'item',
      '#markup' => $this->t('Please select modules that you would like to install'),
    ];
    $form['install_modules'] = [
      '#type' => 'container',
    ];
    // List of optional modules.
    $modules = [
      [
        'id' => 'ezcontent_demo',
        'label' => $this->t('EZContent Demo'),
        'description' => $this->t('Installs content which allows you to explore features.'),
      ],
      [
        'id' => 'ezcontent_smart_article',
        'label' => $this->t('Smart Article Feature (Experimental)'),
        'description' => $this->t('A content type integrated with AI/ML tools to fasten publication.'),
      ],
      [
        'id' => 'ezcontent_api',
        'label' => $this->t('EZContent API'),
        'description' => $this->t('Improves Drupal core JSONAPI experience'),
      ],
    ];
    static::sortByWeights($modules);
    foreach ($modules as $module) {
      $form['install_modules_' . $module['id']] = [
        '#type' => 'checkbox',
        '#title' => $module['label'],
        '#description' => isset($module['description']) ? $module['description'] : '',
        '#default_value' => 0,
      ];
    }
    $form['#title'] = $this->t('Install & configure modules');
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['save'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save and continue'),
      '#button_type' => 'primary',
      '#submit' => ['::submitForm'],
    ];
    return $form;
  }

  /**
   * Returns a sorting function to sort an array by weights.
   *
   * If an array element doesn't provide a weight, it will be set to 0.
   * If two elements have the same weight, they are sorted by label.
   *
   * @param array $array
   *   The array to be sorted.
   */
  private static function sortByWeights(array &$array) {
    uasort($array, function ($a, $b) {
      $a_weight = isset($a['weight']) ? $a['weight'] : 0;
      $b_weight = isset($b['weight']) ? $b['weight'] : 0;
      if ($a_weight == $b_weight) {
        return ($a['label'] > $b['label']) ? 1 : -1;
      }
      return ($a_weight > $b_weight) ? 1 : -1;
    });
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $installModules = [];
    foreach ($form_state->getValues() as $key => $value) {
      if (strpos($key, 'install_modules') !== FALSE && $value) {
        preg_match('/install_modules_(?P<name>\w+)/', $key, $values);
        $installModules[] = $values['name'];
      }
    }
    $buildInfo = $form_state->getBuildInfo();
    $install_state = $buildInfo['args'];
    $install_state[0]['ezcontent_additional_modules'] = $installModules;
    $install_state[0]['form_state_values'] = $form_state->getValues();
    $buildInfo['args'] = $install_state;
    $form_state->setBuildInfo($buildInfo);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [];
  }

}
