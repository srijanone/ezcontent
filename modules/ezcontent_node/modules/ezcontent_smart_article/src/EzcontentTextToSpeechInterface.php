<?php

namespace Drupal\ezcontent_smart_article;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * An interface implementation for text-to-speech conversion plugin.
 */
interface EzcontentTextToSpeechInterface extends PluginInspectionInterface, ContainerFactoryPluginInterface {

  /**
   * Converts text to speech/audio.
   *
   * @param string $text
   *   Text string that needs to be converted.
   *
   * @return array
   *   Base64 decoded binary string of the speech.
   */
  public function convertTextToSpeech($text);

}
