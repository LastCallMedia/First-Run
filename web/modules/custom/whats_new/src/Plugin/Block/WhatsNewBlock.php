<?php

namespace Drupal\whats_new\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\whats_new\Controller\WhatsNewController;

/**
 * Provides a 'WhatsNewBlock' block.
 *
 * @Block(
 *  id = "whats_new_block",
 *  admin_label = @Translation("Whats new block"),
 * )
 */
class WhatsNewBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['whats_new_block']['#markup'] = 'Implement WhatsNewBlock.';

    return $build;
  }

}
