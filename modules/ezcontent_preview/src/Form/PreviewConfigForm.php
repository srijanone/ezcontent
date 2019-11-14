<?php

namespace Drupal\ezcontent_preview\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\taxonomy\Entity\Term;

/**
 * Class that configures module settings.
 */
class PreviewConfigForm extends ConfigFormBase {

  /** 
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'ezcontent_preview.settings';

   /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ezcontent_preview_config';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);
    $form['ezcontent_preview_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Preview URL'),
      '#description' => $this->t('Provide the URL actual user interface, eg: http://example.com.'),
      '#default_value' => $config->get('ezcontent_preview_url'),
    ];
    $form['ezcontent_preview_token_expire_time'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Token Expire Time'),
      '#description' => $this->t('Provide the expire time in seconds.'),
      '#default_value' => $config->get('ezcontent_preview_token_expire_time') ?? 300,
    ];
    $form['ezcontent_preview_new_window'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Preview on new window'),
      '#description' => $this->t('Check this checkbox if you want to see preview in new browser tab. Default is <strong>embeded</strong> preview in Drupal.'),
      '#default_value' => $config->get('ezcontent_preview_new_window'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $this->config(static::SETTINGS)
      ->set('ezcontent_preview_url', $form_state->getValue('ezcontent_preview_url'))
      ->set('ezcontent_preview_token_expire_time', $form_state->getValue('ezcontent_preview_token_expire_time'))
      ->set('ezcontent_preview_new_window', $form_state->getValue('ezcontent_preview_new_window'))
      ->save();
  }

}
