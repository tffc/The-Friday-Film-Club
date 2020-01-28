<?php

namespace Drupal\tffc_validation\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\Node;

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
    $id = $this->get_film_id();
    if($id) {
      return [
        '#theme' => 'validation_options',
        '#film_id' => $id,
        '#cache' => [
          'max-age' => 0,
        ],
      ];
    }
  }

  /**
   * Update the status
   *
   * @return bool|mixed|null
   */
  private function get_film_id() {
    $nid = \Drupal::routeMatch()->getParameter('nid');
    $node = Node::load($nid);
    if ($node && $node->bundle() === "film") {
      return $nid;
    }
    return FALSE;
  }

}
