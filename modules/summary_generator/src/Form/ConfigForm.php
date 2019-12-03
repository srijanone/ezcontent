<?php

namespace Drupal\summary_generator\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\file\Entity\File;

/**
 * Class that configures forms module settings.
 */
class ConfigForm extends ConfigFormBase {

  /** 
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'summary_generator.settings';

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
    return 'summary_generator_config';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);
    $form['summary_generator_text'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Text for company'),
      '#description' => $this->t('Text for company.'),
      '#default_value' => $config->get('summary_generator_text'),
    ];
    $form['summary_generator_dynamic'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Dynamic Companies'),
      '#description' => $this->t('Check this checkbox if you want to pull dynamic companies.'),
      '#default_value' => $config->get('summary_generator_dynamic'),
    ];
    $form['summary_generator_api_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API URL'),
      '#description' => $this->t('Provide the API URL.'),
      '#default_value' => $config->get('summary_generator_api_url'),
    ];
    //dpr($config->get('summary_generator_data_file'));exit;
    $form['summary_generator_data_file'] = [
      '#type' => 'managed_file',
      '#name' => 'data_file',
      '#title' => t('Data file'),
      '#size' => 20,
      '#description' => t('Excel file only'),
      '#upload_validators' => [
        'file_validate_extensions' => ['xls xlsx'],
      ],
      '#upload_location' => 'public://companies-data/',
      '#default_value' => $config->get('summary_generator_data_file'),
    ];
    /*$fid = $config->get('summary_generator_data_file');
    if (!empty($fid)) {
      $file = File::load($fid[0]);
      dpr();
      dpr(\Drupal::service('file_system')->realpath($file->getFileUri()));
      exit;
    }*/
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $this->config(static::SETTINGS)
      ->set('summary_generator_text', $form_state->getValue('summary_generator_text'))
      ->set('summary_generator_dynamic', $form_state->getValue('summary_generator_dynamic'))
      ->set('summary_generator_api_url', $form_state->getValue('summary_generator_api_url'))
      ->set('summary_generator_data_file', $form_state->getValue('summary_generator_data_file'))
      ->save();

    // upload file on api endpoint
    $fid = $form_state->getValue('summary_generator_data_file');
    if (!empty($fid)) {
      $file = File::load($fid[0]);
      $this->uploadServer($file);
    }
  }

  /**
   * Upload file to api server
   */
  public function uploadServer($file) {
    $fileRealPath = \Drupal::service('file_system')->realpath($file->getFileUri());
    $url = \Drupal::config('summary_generator.settings')->get('summary_generator_api_url') . '/upload';
    $client = \Drupal::httpClient();
    $response = $client->request('POST', $url, [
        'headers' => [
          'content-type' => 'application/pdf'
        ],
        'body' => file_get_contents($fileRealPath)
    ]);
    $body = $response->getBody()->getContents();
    $status = $response->getStatusCode();
  }

}
