<?php

namespace Drupal\ezcontent_preview\Controller;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing
 */
class PreviewListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Label');
    $header['url'] = $this->t('URL');
    $header['id'] = $this->t('Machine name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label;
    $row['url'] = $entity->url;
    $row['id'] = $entity->id;

    // You probably want a few more properties here...

    return $row + parent::buildRow($entity);
  }

}
