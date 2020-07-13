<?php

namespace Drupal\ezcontent_smart_article\Plugin\Ezcontent\ImageTagging;

use Drupal\ezcontent_smart_article\EzcontentImageTaggingInterface;
use Drupal\ezcontent_smart_article\EzcontentImageTaggingPluginBase;
use Drupal\file\Entity\File;

/**
 * Plugin implementation of the 'srijan_image_tagging' image tagging.
 *
 * @EzcontentImageTagging(
 *   id = "srijan_image_tagging",
 *   label = @Translation("Srijan Image Tagging"),
 *   description = @Translation("Provide image Tagging feature using srijan
 *   AI tool."),
 * )
 */
class SrijanEzcontentImageTagging extends EzcontentImageTaggingPluginBase implements EzcontentImageTaggingInterface {

  /**
   * {@inheritdoc}
   */
  public function getImageTags(File $file) {
    $tags = [];
    if ($file) {
      $imageFile = file_get_contents(\Drupal::service('file_system')
        ->realpath($file->getFileUri()));
      $url = \Drupal::config('smart_article.settings')
        ->get('image_generate_tags_api_url');
      $response = \Drupal::service('http_client')->request('POST', $url, [
        'headers' => [
          'content-type' => $file->getMimeType(),
        ],
        'body' => $imageFile,
      ]);
      if ($response->getStatusCode() == 200) {
        $body = \Drupal::service('serialization.json')
          ->decode($response->getBody()->getContents());
        $tags = $body['data']['objects'];
      }
    }
    return $tags;
  }

}
