<?php

namespace Drupal\ezcontent_listing;

use Drupal\views\Views;

/**
 * Class ContentListingHelperBlock.
 *
 * @package Drupal\ezcontent_listing
 */
class ContentListingHelperBlock {

  /**
   * Preprocess the Content Listing Block.
   *
   * @param object $block
   *   Block entity to preprocess.
   *
   * @return array
   *   Preprocessed data for the given block.
   */
  public function getContentListingBlock($block) {
    $data = [];
    $author_name = [];
    $tag_name = [];
    if ($block->hasField('field_author') && $block->field_author->entity) {
      $authorEntities = $block->field_author->referencedEntities();
      foreach ($authorEntities as $authorkey => $authorEntity) {
        $author_name[$authorkey] = $authorEntity->id();
      }
    }
    if ($block->hasField('field_tags') && $block->field_tags->entity) {
      $tagsEntities = $block->field_tags->referencedEntities();
      foreach ($tagsEntities as $tagkey => $tagsEntity) {
        $tag_name[$tagkey] = $tagsEntity->id();
      }
    }

    $view = Views::getView('article_content_listing');

    if (is_object($view)) {
      $view->setDisplay('block_1');
      $filters = $view->display_handler->getOption('filters');
      if ($author_name['0']) {
        $filters['field_author_target_id']['value']['value'] = $author_name['0'];
      }
      if ($tag_name) {
        $filters['field_tags_target_id']['value'] = $tag_name;
      }
      $view->display_handler->overrideOption('filters', $filters);
      $view->execute();
      $data['rows'] = $view->buildRenderable('block_1');
    }

    return $data;
  }

}
