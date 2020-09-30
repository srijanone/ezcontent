<?php

namespace Drupal\ezcontent\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;

/**
 * Create setting form for ezcontent.
 *
 * @package Drupal\ezcontent\Form
 */
class SettingsForm extends ConfigFormBase {
  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;
  /**
   * Bundle info.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $bundleInfo;

  /**
   * Constructs a \Drupal\ezcontent\SettingsForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $bundleInfo
   *   Bundle info.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entityTypeManager, EntityTypeBundleInfoInterface $bundleInfo) {
    parent::__construct($config_factory);
    $this->entityTypeManager = $entityTypeManager;
    $this->bundleInfo = $bundleInfo;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'ezcontent.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ezcontent_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('ezcontent.settings');
    $form['hide_field'] = [
      '#type'  => 'details',
      '#title' => $this->t('Cleanup fields in Entity add / edit form'),
      '#description' => $this->t('Hide fields, which cannot be hidden via manage form display section.'),
      '#open'  => TRUE,
    ];
    $entityTypes = $this->entityTypeManager->getDefinitions();
    foreach ($entityTypes as $entityId => $entityType) {
      if ($entityId == 'media') {
        $form['hide_field'][$entityId] = [
          '#type'  => 'details',
          '#title' => $entityType->getLabel(),
          '#description' => $this->t('Please select bundle.'),
          '#open'  => FALSE,
        ];
        foreach ($this->bundleInfo->getBundleInfo($entityId) as $bundleId => $bundle) {
          $form['hide_field'][$entityId][$bundleId] = [
            '#type' => 'checkbox',
            '#title' => $bundle['label'],
            '#default_value' => $config->get($bundleId),
            '#open'  => FALSE,
          ];
        }
      }
    }
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $config = $this->config('ezcontent.settings');
    foreach ($form_state->cleanValues()->getValues() as $key => $value) {
      $config->set($key, $value);
    }
    $config->save();
  }

}
