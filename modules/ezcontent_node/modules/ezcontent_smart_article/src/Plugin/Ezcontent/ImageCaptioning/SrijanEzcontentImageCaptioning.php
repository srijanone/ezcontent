<?php

namespace Drupal\ezcontent_smart_article\Plugin\Ezcontent\ImageCaptioning;

use Drupal\ezcontent_smart_article\EzcontentImageCaptioningInterface;
use Drupal\ezcontent_smart_article\EzcontentImageCaptioningPluginBase;
use Drupal\file\Entity\File;

/**
 * Plugin implementation of the 'srijan_image_captioning' image captioning.
 *
 * @EzcontentImageCaptioning(
 *   id = "srijan_image_captioning",
 *   label = @Translation("Srijan Image Captioning"),
 *   description = @Translation("Provide image cationing feature using srijan
 *   AI tool."),
 * )
 */
class SrijanEzcontentImageCaptioning extends EzcontentImageCaptioningPluginBase implements EzcontentImageCaptioningInterface {

  /**
   * {@inheritdoc}
   */
  public function getImageCaption(File $file) {
    $caption = '';
    if ($file) {
      $imageFile = file_get_contents(\Drupal::service('file_system')
        ->realpath($file->getFileUri()));
      $url = \Drupal::config('summary_generator.settings')
        ->get('image_captioning_api_url');
      $response = \Drupal::service('http_client')->request('POST', $url, [
        'headers' => [
          'content-type' => $file->getMimeType(),
        ],
        'body' => $imageFile,
      ]);
      if ($response->getStatusCode() == 200) {
        $body = \Drupal::service('serialization.json')
          ->decode($response->getBody()->getContents());
        $caption = $body['data']['caption'];
      }
    }
    return $caption;
  }

}
