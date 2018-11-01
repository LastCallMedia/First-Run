<?php

namespace Drupal\welcome\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class WelcomeController.
 */
class WelcomeController extends ControllerBase {

  /**
   * Welcome content.
   *
   * @return array
   *   Return Welcome array.
   */
  public function content() {
    return [
      '#theme' => 'welcome',
      '#welcome_content' => $this->t('Welcome to welcome!'),
    ];
  }

}
