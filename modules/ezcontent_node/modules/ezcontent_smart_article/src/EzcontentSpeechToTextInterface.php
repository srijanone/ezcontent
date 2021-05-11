<?php

namespace Drupal\ezcontent_smart_article;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * An interface implementation for speech-to-text conversion plugin.
 */
interface EzcontentSpeechToTextInterface extends PluginInspectionInterface, ContainerFactoryPluginInterface {

  /**
   * Converts text to speech/audio.
   *
   * @param string $text
   *   Text string that needs to be converted.
   *
   * @return array
   *   Base64 decoded binary string of the speech.
   */
  public function convertSpeechToText(string $text);

  /**
   * Saves text to an entity.
   *
   * @param int $entity_type_id
   *   Given entity type id.
   * @param int $entity_id
   *   Given entity id.
   * @param string $field_name
   *   Given field name.
   * @param string $speech
   *   Response text returned from an entity.
   */
  public function saveTextToEntity($entity_type_id, $entity_id, $field_name, $speech);

}
