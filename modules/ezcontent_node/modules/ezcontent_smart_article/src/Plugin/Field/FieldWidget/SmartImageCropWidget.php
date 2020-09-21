<?php

namespace Drupal\ezcontent_smart_article\Plugin\Field\FieldWidget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\image_widget_crop\Plugin\Field\FieldWidget\ImageCropWidget;
use Drupal\file\Entity\File;

/**
 * Plugin implementation of the 'smart_image_widget_crop' widget.
 *
 * @FieldWidget(
 *   id = "smart_image_widget_crop",
 *   label = @Translation("SmartImageWidget crop"),
 *   field_types = {
 *     "image"
 *   }
 * )
 */
class SmartImageCropWidget extends ImageCropWidget {

  /**
   * Form API callback: Processes a crop_image field element.
   *
   * Expands the image_image type to include the alt and title fields.
   *
   * This method is assigned as a #process callback in formElement() method.
   *
   * @return array
   *   The elements with parents fields.
   */
  public static function process($element, FormStateInterface $form_state, $form) {
    if ($element['#files']) {
      foreach ($element['#files'] as $file) {
        $element['image_crop'] = [
          '#type' => 'image_crop',
          '#file' => $file,
          '#crop_type_list' => $element['#crop_list'],
          '#crop_preview_image_style' => $element['#crop_preview_image_style'],
          '#show_default_crop' => $element['#show_default_crop'],
          '#show_crop_area' => $element['#show_crop_area'],
          '#warn_multiple_usages' => $element['#warn_multiple_usages'],
          '#crop_types_required' => $element['#crop_types_required'],
        ];
      }
    }
    $element = parent::process($element, $form_state, $form);
    // Generating automatic caption.
    $fid = $element['fids']['#value'];
    if (!empty($fid[0])) {
      // @todo: get file object directly form_state.
      $file = File::load($fid[0]);
      $imageCaptioningManager = \Drupal::service('plugin.manager.image_captioning');
      $serviceType = \Drupal::config('ezcontent_smart_article.settings')
        ->get('image_captioning_service');
      $plugin = $imageCaptioningManager->createInstance($serviceType);
      $caption = $plugin->getImageCaption($file);
      if ($caption) {
        $element['alt']['#default_value'] = $caption;
      }
    }
    return $element;
  }

}
