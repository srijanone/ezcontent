<?php

namespace Drupal\ezcontent_smart_article\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystem;
use GuzzleHttp\ClientInterface;
use Drupal\Core\Messenger\Messenger;
use Drupal\ezcontent_smart_article\EzcontentImageCaptioningManager;
use Drupal\ezcontent_smart_article\EzcontentImageTaggingManager;

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
   * File storage object.
   *
   * @var \Drupal\file\FileStorage
   */
  protected $fileStorage;

  /**
   * File system object.
   *
   * @var \Drupal\Core\File\FileSystem
   */
  protected $fileSystem;

  /**
   * An http client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * A messenger object.
   *
   * @var \Drupal\Core\Messenger\Messenger
   */
  protected $messenger;

  /**
   * A image captioning Manager object.
   *
   * @var \Drupal\ezcontent_smart_article\EzcontentImageCaptioningManager
   */
  protected $imageCaptioningManager;

  /**
   * A image tagging Manager object.
   *
   * @var \Drupal\ezcontent_smart_article\EzcontentImageTaggingManager
   */
  protected $imageTaggingManager;

  /**
   * Constructs a \Drupal\fvm\Form\FvmSettingsForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager.
   * @param \Drupal\Core\File\FileSystem $fileSystem
   *   File system object.
   * @param \GuzzleHttp\ClientInterface $httpClient
   *   An http client.
   * @param \Drupal\Core\Messenger\Messenger $messenger
   *   A messenger object.
   * @param \Drupal\ezcontent_smart_article\EzcontentImageCaptioningManager $image_captioning_manager
   *    A image captioning Manager object.
   * @param \Drupal\ezcontent_smart_article\EzcontentImageTaggingManager $image_tagging_manager
   *    A image tagging Manager object.
   */
  public function __construct(ConfigFactoryInterface $config_factory,
  EntityTypeManagerInterface $entityTypeManager,
                              FileSystem $fileSystem,
  ClientInterface $httpClient,
  Messenger $messenger,
                              EzcontentImageCaptioningManager $image_captioning_manager,
  EzcontentImageTaggingManager $image_tagging_manager) {
    parent::__construct($config_factory);
    $this->fileStorage = $entityTypeManager->getStorage('file');
    $this->fileSystem = $fileSystem;
    $this->httpClient = $httpClient;
    $this->messenger = $messenger;
    $this->imageCaptioningManager = $image_captioning_manager;
    $this->imageTaggingManager = $image_tagging_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('file_system'),
      $container->get('http_client'),
      $container->get('messenger'),
      $container->get('plugin.manager.image_captioning'),
      $container->get('plugin.manager.image_tagging')
    );
  }

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
    $plugin_definitions_image_captioning = $this->imageCaptioningManager->getDefinitions();
    $plugin_definitions_image_tagging = $this->imageTaggingManager->getDefinitions();
    // Prepare options for image captioning service types.
    $image_captioning_options = [];
    foreach ($plugin_definitions_image_captioning as $plugin_definition) {
      $image_captioning_options[$plugin_definition['id']] = $plugin_definition['label'];
    }
    // Prepare options for image tagging service types.
    $image_tagging_options = [];
    foreach ($plugin_definitions_image_tagging as $plugin_definition) {
      $image_tagging_options[$plugin_definition['id']] = $plugin_definition['label'];
    }
    $form['summary_generator_api_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Summary Generator API URL'),
      '#description' => $this->t('Provide the API URL.'),
      '#default_value' => $config->get('summary_generator_api_url'),
    ];
    $form['summary_generator_data_file'] = [
      '#type' => 'managed_file',
      '#name' => 'data_file',
      '#title' => $this->t('Data file'),
      '#size' => 20,
      '#description' => $this->t('File to be uploaded to API. Only upload Excel file.'),
      '#upload_validators' => [
        'file_validate_extensions' => ['xls xlsx'],
      ],
      '#upload_location' => 'public://companies-data/',
      '#default_value' => $config->get('summary_generator_data_file'),
    ];
    // Select plugin type for image captioning.
    $form['image_captioning_service'] = [
      '#title' => t('Image Captioning Service'),
      '#type' => 'select',
      '#description' => $this->t('Please choose image captioning service type.'),
      '#options' => $image_captioning_options,
      '#default_value' => $config->get('image_captioning_service'),
    ];
    $form['image_captioning_api_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Srijan Image Captioning API URL'),
      '#description' => $this->t('Provide the API URL to generate image caption.'),
      '#default_value' => $config->get('image_captioning_api_url'),
      '#states' => [
        'visible' => ['select[name="image_captioning_service"]' => ['value' => 'srijan_image_captioning']],
      ],
    ];
    // Select plugin type for image tagging.
    $form['image_tagging_service'] = [
      '#title' => t('Image Tagging Service'),
      '#type' => 'select',
      '#description' => $this->t('Please choose image tagging service type.'),
      '#options' => $image_tagging_options,
      '#default_value' => $config->get('image_tagging_service'),
    ];
    $form['image_generate_tags_api_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Srijan Image Generate Tags API URL'),
      '#description' => $this->t('Provide the API URL to generate image tags.'),
      '#default_value' => $config->get('image_generate_tags_api_url'),
      '#states' => [
        'visible' => ['select[name="image_tagging_service"]' => ['value' => 'srijan_image_tagging']],
      ],
    ];
    $form['abstractive_summary_api_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Abstractive Summary API URL'),
      '#description' => $this->t('Provide the API URL.'),
      '#default_value' => $config->get('abstractive_summary_api_url'),
    ];
    $form['extractive_summary_api_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Extractive Summary API URL'),
      '#description' => $this->t('Provide the API URL.'),
      '#default_value' => $config->get('extractive_summary_api_url'),
    ];
    $form['smart_tags_api_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Smart Tags API URL'),
      '#description' => $this->t('Provide the API URL.'),
      '#default_value' => $config->get('smart_tags_api_url'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $config = $this->config(static::SETTINGS);
    foreach ($form_state->cleanValues()->getValues() as $key => $value) {
      $config->set($key, $value);
    }
    $config->save();

    // Upload file on api endpoint.
    $fid = $form_state->getValue('summary_generator_data_file');
    if (!empty($fid) && !empty($form_state->getValue('summary_generator_api_url'))) {
      $file = $this->fileStorage->load($fid[0]);
      if ($this->uploadServer($file) == 200) {
        $this->messenger->addStatus("File successfully uploaded");
      }
    }
  }

  /**
   * Upload file to api server.
   */
  public function uploadServer($file) {
    $fileRealPath = $this->fileSystem->realpath($file->getFileUri());
    $url = $this->config(static::SETTINGS)
      ->get('summary_generator_api_url') . '/upload';

    $response = $this->httpClient->request('POST', $url, [
      'headers' => [
        'content-type' => 'application/pdf',
      ],
      'body' => file_get_contents($fileRealPath),
    ]);

    return $response->getStatusCode();
  }

}
