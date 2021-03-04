<?php

namespace Drupal\ezcontent_listing;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\views\ViewExecutableFactory;

/**
 * Helper class for ContentListingBlock.
 *
 * @package Drupal\ezcontent_listing
 */
class ContentListingHelperBlock {

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * The Executable view.
   *
   * @var \Drupal\views\ViewExecutable
   */
  protected $viewExecutable;

  /**
   * Content Listing constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   Entity Type manager.
   * @param \Drupal\views\ViewExecutableFactory $viewExecutable
   *   A view executable instance, from the loaded entity.
   */
  public function __construct(EntityTypeManager $entityTypeManager, ViewExecutableFactory $viewExecutable) {
    $this->entityTypeManager = $entityTypeManager;
    $this->viewExecutable = $viewExecutable;
  }

  /**
   * Preprocess the Content Listing Block.
   *
   * @param object $block
   *   Block entity to preprocess.
   * @param string $type
   *   Type of response to be returned.
   * @param int $page
   *   The page number.
   *
   * @return array
   *   Preprocessed data for the given block.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getContentListingBlock($block, $type = '', $page = 0) {
    $data = $arguments = [];
    if ($block->hasField('field_tags') && $block->field_tags->entity) {
      $tagsEntities = $block->field_tags->getString();
      $arguments[] = str_replace(', ', '+', $tagsEntities);
    }
    else {
      $arguments[] = 'all';
    }
    if ($block->hasField('field_author') && $block->field_author->entity) {
      $authorEntities = $block->field_author->getString();
      $arguments[] = str_replace(', ', '+', $authorEntities);
    }
    else {
      $arguments[] = 'all';
    }

    $viewObject = $this->entityTypeManager->getStorage('view')->load('article_content_listing');
    $view = $this->viewExecutable->get($viewObject);

    if (is_object($view)) {
      $view->setDisplay('block_1');
      $view->setArguments($arguments);
      if ($type === 'result') {
        $view->setCurrentPage($page);
        $view->execute();
        // Handle pager in case of JSON:API response.
        $data['rows'] = $view->result;
        $data['total_rows'] = $view->total_rows;
        $data['item_per_page'] = $view->getItemsPerPage();
        return $data;
      }
      $view->execute();
      $data['rows'] = $view->buildRenderable('block_1');

    }

    return $data;
  }

}
