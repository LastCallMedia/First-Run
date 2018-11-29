<?php

namespace Drupal\fr_node_type_categories\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a block for searching/filtering content types on the node add page.
 *
 * @Block(
 *   id = "add_content_search",
 *   admin_label = @Translation("Add Content Search"),
 *   category = @Translation("Content type categories"),
 * )
 */
class AddContentSearch extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Make category terms into links.
    $links = [];
    $categories = !empty(_get_used_categories()) ? _get_used_categories() : NULL;

    foreach ($categories as $key => $value) {
      $links[] = [
        '#markup' => $this->t('<a href="#:key">@value</a>', [
          ':key' => $key,
          '@value' => $value,
        ]),
      ];
    }

    return [
      [
        '#theme' => 'item_list',
        '#list_type' => 'ul',
        '#title' => 'Jump to a category',
        '#items' => $links,
        '#attributes' => ['class' => 'category-jump-links'],
        '#wrapper_attributes' => ['class' => 'container'],
      ],
    ];
  }

}
