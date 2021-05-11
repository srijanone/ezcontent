<?php

namespace Drupal\ezcontent_smart_article\Plugin\Ezcontent\SpeechToText;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystem;
use Drupal\ezcontent_smart_article\EzcontentSpeechToTextInterface;
use Drupal\ezcontent_smart_article\EzcontentSpeechToTextPluginBase;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Process\Process;

/**
 * Plugin implementation of 'google_speech_to_text'.
 *
 * @EzcontentSpeechToText(
 *   id = "google_speech_to_text",
 *   label = @Translation("Google Speech to Text"),
 *   description = @Translation("Converts speech-to-text using Google Cloud Speech-to-Text API."),
 * )
 */
class GoogleSpeechToText extends EzcontentSpeechToTextPluginBase implements EzcontentSpeechToTextInterface {

  /**
   * The base url of the Google Cloud Speech to Text API.
   */
  const API_ENDPOINT = 'https://speech.googleapis.com/v1p1beta1/speech:recognize';

  /**
   * The plugin_id.
   *
   * @var string
   */
  protected $pluginId;

  /**
   * The plugin implementation definition.
   *
   * @var array
   */
  protected $pluginDefinition;

  /**
   * Configuration information passed into the plugin.
   *
   * When using an interface like
   * \Drupal\Component\Plugin\ConfigurableInterface, this is where the
   * configuration should be stored.
   *
   * Plugin configuration is optional, so plugin implementations must provide
   * their own setters and getters.
   *
   * @var array
   */
  protected $configuration;

  /**
   * The Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * A File system service object.
   *
   * @var \Drupal\Core\File\FileSystem;
   */
  protected $fileSystem;

  /**
   * GuzzleHttp.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * Constructs a \Drupal\Component\Plugin\PluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory object.
   * @param \Drupal\Core\File\FileSystem $file_system
   *   The File System.
   * @param \GuzzleHttp\Client $httpClient
   *   The guzzelhttp client object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager, ConfigFactoryInterface $configFactory, FileSystem $file_system, Client $httpClient) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entityTypeManager, $configFactory);
    $this->configFactory = $configFactory;
    $this->fileSystem = $file_system;
    $this->httpClient = $httpClient;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('config.factory'),
      $container->get('file_system'),
      $container->get('http_client')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function convertSpeechToText(string $inputPath) {
    $pathInfo = pathinfo($inputPath);
    $config = $this->configFactory->get('ezcontent_smart_article.settings');
    $executable_path = $config->get('ffmpeg_executable_path');
    $outputPath = $this->fileSystem->realPath('public://' . $pathInfo['filename'] . '.flac');
    $output = $this->executeCommand($executable_path, '-i "' . $inputPath . '" "' . $outputPath. '" -y', $error);
    if (!$output) {
      $resultText = $this->getSpeechToText($outputPath);
    }
    //@todo When there's an error code. Log the error.
    return $resultText ?? "";
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
  function executeCommand($command, $arguments, &$error = NULL) {
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
    return $return_code;
  }

  /**
   * This Function is used to make an Google Speech-To-Text API.
   *
   * @param string $inputFilePath
   *   Given input file path.
   *
   * @return string
   *   Returns response text from Speech-To-Text API.
   */
  function getSpeechToText($inputFilePath) {
    $config = $this->configFactory->get('ezcontent_smart_article.settings');
    $baseRequestUrl = self::API_ENDPOINT . '?key=' . $config->get('gcp_speech_to_text_key');
    $inputData = base64_encode(file_get_contents($inputFilePath));
    $formParams = [
      "config" => [
        "enableAutomaticPunctuation" => true,
        "encoding" => "FLAC",
        "languageCode" => "en-US"
      ],
      "audio" => [
        "content" => $inputData
      ]
    ];
    $response = $this->httpClient->post($baseRequestUrl, [
      'verify' => true,
      'body' => json_encode($formParams),
        'headers' => [
          'Content-type' => 'application/json',
          'Accept' => 'application/json'
        ],
    ])->getBody()->getContents();
    $responseArray = json_decode($response);
    //@todo Google Speech-To-Text provides different alternative. Can we extract more ?
    return $responseArray->results[0]->alternatives[0]->transcript ?? '';
  }

}
