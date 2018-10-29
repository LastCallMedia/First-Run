<?php

namespace Drupal\whats_new\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\Yaml\Yaml;

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
    $file_path = __DIR__ . '/../../changelog.yml';
    $file_contents = file_get_contents($file_path);
    $data = Yaml::parse($file_contents);
    krsort($data);

    return [
      '#theme' => 'whats_new',
      '#data' => $data,
    ];
  }

}
