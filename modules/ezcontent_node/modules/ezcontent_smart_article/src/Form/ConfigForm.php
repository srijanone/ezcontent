<?php

namespace Drupal\ezcontent_smart_article\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystem;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Messenger\Messenger;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Url;
use Drupal\ezcontent_smart_article\EzcontentImageCaptioningManager;
use Drupal\ezcontent_smart_article\EzcontentImageTaggingManager;
use Drupal\ezcontent_smart_article\EzcontentSpeechToTextManager;
use Drupal\ezcontent_smart_article\EzcontentTextTaggingManager;
use Drupal\ezcontent_smart_article\EzcontentTextToSpeechManager;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Process\Process;

/**
 * Class that configures forms module settings.
 */
class ConfigForm extends ConfigFormBase {

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'ezcontent_smart_article.settings';

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
   * A image tagging Manager object.
   *
   * @var \Drupal\ezcontent_smart_article\EzcontentTextTaggingManager
   */
  protected $textTaggingManager;

  /**
   * Ezcontent Text to Speech Manager object.
   *
   * @var \Drupal\ezcontent_smart_article\EzcontentTextToSpeechManager
   */
  protected $textToSpeechManager;

  /**
   * A image tagging Manager object.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * Ezcontent Speech to Text Manager object.
   *
   * @var \Drupal\ezcontent_smart_article\EzcontentSpeechToTextManager
   */
  protected $speechToTextManager;

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
   * @param \Drupal\ezcontent_smart_article\EzcontentImageCaptioningManager $imageCaptioningManager
   *   A image captioning Manager object.
   * @param \Drupal\ezcontent_smart_article\EzcontentImageTaggingManager $imageTaggingManager
   *   A image tagging Manager object.
   * @param \Drupal\ezcontent_smart_article\EzcontentTextTaggingManager $textTaggingManager
   *   A text tagging Manager object.
   * @param \Drupal\ezcontent_smart_article\EzcontentTextToSpeechManager $textToSpeechManager
   *   Ezcontent text to speech manager object.
   * @param \Drupal\ezcontent_smart_article\EzcontentSpeechToTextManager $speechToTextManager
   *   Ezcontent speech to text manager object.
   * @param \Drupal\Core\Render\Renderer $renderer
   *   An rendere object.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entityTypeManager, FileSystem $fileSystem, ClientInterface $httpClient, Messenger $messenger, EzcontentImageCaptioningManager $imageCaptioningManager, EzcontentImageTaggingManager $imageTaggingManager, EzcontentTextTaggingManager $textTaggingManager, EzcontentTextToSpeechManager $textToSpeechManager, EzcontentSpeechToTextManager $speechToTextManager, Renderer $renderer) {
    parent::__construct($config_factory);
    $this->fileStorage = $entityTypeManager->getStorage('file');
    $this->fileSystem = $fileSystem;
    $this->httpClient = $httpClient;
    $this->messenger = $messenger;
    $this->imageCaptioningManager = $imageCaptioningManager;
    $this->imageTaggingManager = $imageTaggingManager;
    $this->textTaggingManager = $textTaggingManager;
    $this->textToSpeechManager = $textToSpeechManager;
    $this->speechToTextManager = $speechToTextManager;
    $this->renderer = $renderer;
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
      $container->get('plugin.manager.image_tagging'),
      $container->get('plugin.manager.text_tagging'),
      $container->get('plugin.manager.ezcontent_text_to_speech'),
      $container->get('plugin.manager.ezcontent_speech_to_text'),
      $container->get('renderer')
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
    return 'smart_article_config';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);
    $pluginDefinitionsImageCaptioning = $this->imageCaptioningManager->getDefinitions();
    $pluginDefinitionsImageTagging = $this->imageTaggingManager->getDefinitions();
    $pluginDefinitionsTextTagging = $this->textTaggingManager->getDefinitions();
    $pluginDefinitionsTextToSpeech = $this->textToSpeechManager->getDefinitions();
    $pluginDefinitionsSpeechToText = $this->speechToTextManager->getDefinitions();
    // Prepare options for image captioning service types.
    $imageCaptioningOptions = [];
    foreach ($pluginDefinitionsImageCaptioning as $pluginDefinition) {
      $imageCaptioningOptions[$pluginDefinition['id']] = $pluginDefinition['label'];
    }
    // Prepare options for image tagging service types.
    $imageTaggingOptions = [];
    foreach ($pluginDefinitionsImageTagging as $pluginDefinition) {
      $imageTaggingOptions[$pluginDefinition['id']] = $pluginDefinition['label'];
    }
    // Prepare options for text tagging service types.
    $textTaggingOptions = [];
    foreach ($pluginDefinitionsTextTagging as $pluginDefinition) {
      $textTaggingOptions[$pluginDefinition['id']] = $pluginDefinition['label'];
    }
    // Prepare options for text to speech service types.
    $textToSpeechOptions = [];
    foreach ($pluginDefinitionsTextToSpeech as $pluginDefinition) {
      $textToSpeechOptions[$pluginDefinition['id']] = $pluginDefinition['label'];
    }
    // Prepare options for text to speech service types.
    $speechToTextOptions = [];
    foreach ($pluginDefinitionsSpeechToText as $pluginDefinition) {
      $speechToTextOptions[$pluginDefinition['id']] = $pluginDefinition['label'];
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
      '#title' => $this->t('Image Captioning Service'),
      '#type' => 'select',
      '#description' => $this->t('Please choose image captioning service type.'),
      '#options' => $imageCaptioningOptions,
      '#default_value' => $config->get('image_captioning_service'),
    ];
    $form['image_captioning_api_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('EZContent Smart Image Captioning API URL'),
      '#description' => $this->t('Provide the API URL to generate image caption.'),
      '#default_value' => $config->get('image_captioning_api_url'),
      '#states' => [
        'visible' => ['select[name="image_captioning_service"]' => ['value' => 'srijan_image_captioning']],
      ],
    ];
    // Select plugin type for image tagging.
    $form['image_tagging_service'] = [
      '#title' => $this->t('Image Tagging Service'),
      '#type' => 'select',
      '#description' => $this->t('Please choose image tagging service type.'),
      '#options' => $imageTaggingOptions,
      '#default_value' => $config->get('image_tagging_service'),
    ];
    $form['image_tagging_action_type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Image Tagging Action Type'),
      '#description' => $this->t('Select an action type.'),
      '#options' => [
        'auto' => $this->t('Generate image tags, automatically.'),
        'button_click' => $this->t('Generate image tags, manually, by clicking a button.'),
      ],
      '#default_value' => $config->get('image_tagging_action_type') ? $config->get('image_tagging_action_type') : 'button_click',
    ];
    $form['image_generate_tags_api_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('EZContent Smart Image Generate Tags API URL'),
      '#description' => $this->t('Provide the API URL to generate image tags.'),
      '#default_value' => $config->get('image_generate_tags_api_url'),
      '#states' => [
        'visible' => ['select[name="image_tagging_service"]' => ['value' => 'srijan_image_tagging']],
      ],
    ];
    $form['gcm_secret_key_image_tags'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Enter Secret Key'),
      '#description' => $this->t('Provide the google vision api secret key to generate image tags.'),
      '#default_value' => $config->get('gcm_secret_key_image_tags'),
      '#states' => [
        'visible' => ['select[name="image_tagging_service"]' => ['value' => 'google_image_tagging']],
      ],
    ];
    $form['gcm_max_count_image_tags'] = [
      '#type' => 'number',
      '#title' => $this->t('Number of tags'),
      '#description' => $this->t('Provide the maximum number of tag to be generated default is 12.'),
      '#min' => 6,
      '#default_value' => $config->get('gcm_max_count_image_tags'),
      '#states' => [
        'visible' => ['select[name="image_tagging_service"]' => ['value' => 'google_image_tagging']],
      ],
    ];
    $form['aws_access_key_image_tags'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Enter Aws Access Key'),
      '#description' => $this->t('Provide the aws access key to generate image tags.'),
      '#default_value' => $config->get('aws_access_key_image_tags'),
      '#states' => [
        'visible' => ['select[name="image_tagging_service"]' => ['value' => 'aws_image_tagging']],
      ],
    ];
    $form['aws_secret_key_image_tags'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Enter Aws Secret Key'),
      '#description' => $this->t('Provide the aws secret key to generate image tags.'),
      '#default_value' => $config->get('aws_secret_key_image_tags'),
      '#states' => [
        'visible' => ['select[name="image_tagging_service"]' => ['value' => 'aws_image_tagging']],
      ],
    ];
    $form['aws_max_count_image_tags'] = [
      '#type' => 'number',
      '#title' => $this->t('Number of tags'),
      '#description' => $this->t('Provide the maximum number of tag to be generated default is 12.'),
      '#min' => 6,
      '#default_value' => $config->get('aws_max_count_image_tags'),
      '#states' => [
        'visible' => ['select[name="image_tagging_service"]' => ['value' => 'aws_image_tagging']],
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
    // Select plugin type for text tagging.
    $form['text_tagging_service'] = [
      '#title' => $this->t('Text Tagging Service'),
      '#type' => 'select',
      '#description' => $this->t('Please choose text tagging service type.'),
      '#options' => $textTaggingOptions,
      '#default_value' => $config->get('text_tagging_service'),
    ];
    $form['smart_tags_api_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Smart Tags API URL'),
      '#description' => $this->t('Provide the API URL.'),
      '#default_value' => $config->get('smart_tags_api_url'),
      '#states' => [
        'visible' => ['select[name="text_tagging_service"]' => ['value' => 'srijan_text_tagging']],
      ],
    ];
    $form['gcm_text_tag_api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('GCM API Key For Tag Extraction'),
      '#description' => $this->t('Provide the API Key.'),
      '#default_value' => $config->get('gcm_text_tag_api_key'),
      '#states' => [
        'visible' => ['select[name="text_tagging_service"]' => ['value' => 'google_text_tagging']],
      ],
    ];
    // Select plugin type for text to speech.
    $form['text_to_speech_service'] = [
      '#title' => $this->t('Text To Speech Service'),
      '#type' => 'select',
      '#description' => $this->t('Please choose text to speech service type.'),
      '#options' => $textToSpeechOptions,
      '#default_value' => $config->get('text_to_speech_service'),
    ];
    $form['gcp_text_to_speech_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('GCP API Key For Text to Speech'),
      '#description' => $this->t('Provide the API key.'),
      '#default_value' => $config->get('gcp_text_to_speech_key'),
      '#states' => [
        'visible' => ['select[name="text_to_speech_service"]' => ['value' => 'google_text_to_speech']],
      ],
    ];
    // Select plugin type for speech to text.
    $form['speech_to_text_service'] = [
      '#title' => $this->t('Speech To Text Service'),
      '#type' => 'select',
      '#description' => $this->t('Please choose speech to text service type.'),
      '#options' => $speechToTextOptions,
      '#default_value' => $config->get('speech_to_text_service'),
    ];
    $form['gcp_speech_to_text_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('GCP API Key For Speech to Text'),
      '#description' => $this->t('Provide the API key.'),
      '#default_value' => $config->get('gcp_speech_to_text_key'),
      '#states' => [
        'visible' => ['select[name="speech_to_text_service"]' => ['value' => 'google_speech_to_text']],
      ],
    ];
    $form['ffmpeg_executable_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('FFMPEG Executable Path'),
      '#description' => $this->t('Provide ffmpeg executable path, <b>including</b> the trailing slash/backslash. For example: /usr/bin/ffmpeg or C:\ffmpeg\bin\ffmpeg.exe'),
      '#default_value' => $config->get('ffmpeg_executable_path'),
    ];
    $executablePath = $config->get('ffmpeg_executable_path');
    if ($executablePath) {
      $output = $this->execute($executablePath, '-version');
      if ($output) {
        $form['ffmpeg_version'] = array(
          '#type' => 'details',
          '#title' => $this->t('FFMPEG Version Information'),
          '#description' => "<pre>" . $output . "</pre>",
          '#open' => FALSE,
        );
      }
    }
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $imageTaggingService = $form_state->getValue('image_tagging_service');
    // Validate gcm secret key field.
    if ($imageTaggingService === 'google_image_tagging') {
      $gcmSecretKey = $form_state->getValue('gcm_secret_key_image_tags');
      if (empty($gcmSecretKey)) {
        $form_state->setError($form['gcm_secret_key_image_tags'],
          $this->t("Field @field_title is required.",
            ['@field_title' => $form['gcm_secret_key_image_tags']['#title']]
          ));
      }
    }
    // Check if aws sdk exist.
    elseif ($imageTaggingService === 'aws_image_tagging') {
      $awsAcessKey = $form_state->getValue('aws_access_key_image_tags');
      $awsSecretKey = $form_state->getValue('aws_secret_key_image_tags');
      if (!class_exists('\Aws\Rekognition\RekognitionClient')) {
        $link = Link::fromTextAndUrl('here', Url::fromUri('https://github.com/aws/aws-sdk-php'));
        $link = $link->toRenderable();
        $link = $this->renderer->render($link);
        $form_state->setError($form['image_tagging_service'],
          $this->t("Aws sdk library is missing, please download it from @link",
            ['@link' => $link]
          ));
      }
      elseif (empty($awsAcessKey)) {
        $form_state->setError($form['aws_access_key_image_tags'],
          $this->t("Field @field_title is required.",
            ['@field_title' => $form['aws_access_key_image_tags']['#title']]
          ));
      }
      elseif (empty($awsSecretKey)) {
        $form_state->setError($form['aws_secret_key_image_tags'],
          $this->t("Field @field_title is required.",
            ['@field_title' => $form['aws_secret_key_image_tags']['#title']]
          ));
      }
    }
    // Validate if api key is added for selected text to speech service.
    $textToSpeechService = $form_state->getValue('text_to_speech_service');
    switch ($textToSpeechService) {
      case 'google_text_to_speech':
        if (empty($form_state->getValue('gcp_text_to_speech_key'))) {
          $form_state->setError($form['gcp_text_to_speech_key'],
            $this->t("@field is required.",
              ['@field' => $form['gcp_text_to_speech_key']['#title']]
            ));
        }
        break;
    }
    // Validate if api key is added for selected speech to text service.
    $speechToTextService = $form_state->getValue('speech_to_text_service');
    switch ($speechToTextService) {
      case 'google_speech_to_text':
        if (empty($form_state->getValue('gcp_speech_to_text_key'))) {
          $form_state->setError($form['gcp_speech_to_text_key'],
            $this->t("@field is required.",
              ['@field' => $form['gcp_speech_to_text_key']['#title']]
            ));
        }
        break;
    }
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

  /**
   * This function is used to execute command on terminal.
   *
   * @param string $command
   *   Given command.
   * @param string $arguments
   *   Given arguments to pass in command.
   * @param string $error
   *   Given response message.
   *
   * @return integer
   *   Returns error code.
   */
  public function execute($command, $arguments, &$error = NULL) {
    $command_line = $command . ' ' . $arguments;
    $process = new Process($command_line);
    $process->setTimeout(60);
    try {
      $process->run();
      $output = utf8_encode($process->getOutput());
      $error = utf8_encode($process->getErrorOutput());
      $return_code = $process->getExitCode();
    }
    catch (\Exception $e) {
      $error = $e->getMessage();
      $return_code = $process->getExitCode() ? $process->getExitCode() : 1;
    }
    return $output;
  }

}
