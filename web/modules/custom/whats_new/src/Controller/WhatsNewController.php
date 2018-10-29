<?php

namespace Drupal\whats_new\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\Yaml\Yaml;

/**
 * Class WhatsNewController.
 */
class WhatsNewController extends ControllerBase {

  /**
   * What's New page content.
   *
   * @return array
   *   Return markup array.
   */
  public function content() {
    return [
      '#theme' => 'whats_new_page',
      '#data' => $this->getChangelogData(),
    ];
  }

  /**
   * Retrieve data from changelog.yml file.
   *
   * @param null $quantity
   *   Integer tov specify how many results are returned.
   *
   * @return array|mixed
   *   Return array of data, sorted and limited.
   */
  public function getChangelogData($quantity = NULL) {
    $file_path = __DIR__ . '/../../changelog.yml';
    $file_contents = file_get_contents($file_path);
    $data = Yaml::parse($file_contents);

    if (!$data) {
      return [];
    }

    // Sort by date
    krsort($data);

    // Limit results.
    if ($quantity) {
      $data = array_slice($data, 0, $quantity, TRUE);
    }

    return $data;
  }

}
