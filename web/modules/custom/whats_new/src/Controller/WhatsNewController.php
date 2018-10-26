<?php

namespace Drupal\whats_new\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class WhatsNewController.
 */
class WhatsNewController extends ControllerBase {

  /**
   * Hello.
   *
   * @return array
   *   Return markup array.
   */
  public function content() {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Hello')
    ];
  }

}
