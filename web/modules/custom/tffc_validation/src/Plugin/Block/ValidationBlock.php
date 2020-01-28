<?php

namespace Drupal\tffc_validation\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'Validation' Block.
 *
 * @Block(
 *   id = "validation_block",
 *   admin_label = @Translation("Validation block"),
 *   category = @Translation("TFFC"),
 * )
 */
class ValidationBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#theme' => 'validation_options',
    ];
  }

}
