<?php

namespace Drupal\welcome\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Block\BlockManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class WelcomeController.
 */
class WelcomeController extends ControllerBase {

  /**
   * The block manager.
   *
   * @var \Drupal\Core\Block\BlockManagerInterface
   */
  protected $blockManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(BlockManagerInterface $block_manager) {
    $this->blockManager = $block_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.block')
    );
  }

  /**
   * Welcome content.
   *
   * @return array
   *   Returns array of content to be passed to the welcome template.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function content() {
    $whats_new_block = $this->blockManager->createInstance('whats_new_block', ['whats_new_number' => 2])->build();
    $welcome_settings = $this->config('welcome.settings')->get();

    return [
      '#theme' => 'welcome',
      '#welcome_content' => $this->t('Welcome to welcome!'),
      '#whats_new' => $whats_new_block,
      '#recently_added_content' => views_embed_view('recently_added_content', 'block_1'),
      '#support' => [
        'name' => $welcome_settings['support_agency_name'],
        'text' => check_markup($welcome_settings['support_agency_information']['value']),
        'knowledge_link' => $welcome_settings['knowledgebase_url'],
        'support_link' => $welcome_settings['request_support_url'],
      ]
    ];
  }

}
