<?php

namespace Drupal\whats_new\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\whats_new\Controller\WhatsNewController;
use Drupal\Core\Form\FormStateInterface;

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
    $whats_new = new WhatsNewController();
    $data = $whats_new->getChangelogData($this->configuration['whats_new_number']);

    return [
      '#theme' => 'whats_new_block',
      '#data' => $data,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['whats_new_number'] = [
      '#type' => 'number',
      '#title' => $this->t('Number of entries'),
      '#description' => $this->t('Sets the number or Whats New entries by date.'),
      '#default_value' => isset($this->configuration['whats_new_number']) ? $this->configuration['whats_new_number'] : 5,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['whats_new_number'] = $form_state->getValue('whats_new_number');
  }

}
