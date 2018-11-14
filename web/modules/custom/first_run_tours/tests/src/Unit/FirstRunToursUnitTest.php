<?php

namespace Drupal\Tests\first_run_tours\Unit;

use Drupal\first_run_tours\Form\ToursForm;
use Drupal\Tests\UnitTestCase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;

/**
 * First Run Tours Unit tests.
 */
class FirstRunToursUnitTest extends UnitTestCase {

  protected $toursForm;
  protected $container;
  protected $entityManager;
  protected $entityTypeManager;
  protected $entityFieldManager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->container = $this->prophesize(ConfigFactoryInterface::class);
    $this->entityManager = $this->prophesize(EntityManager::class);
    $this->entityTypeManager = $this->prophesize(EntityTypeManagerInterface::class);
    $this->entityFieldManager = $this->prophesize(EntityFieldManagerInterface::class);

    $this->toursForm = new ToursForm(
      $this->container->reveal(),
      $this->entityManager->reveal(),
      $this->entityTypeManager->reveal(),
      $this->entityFieldManager->reveal()
    );
  }

  /**
   * TestHyphenate.
   */
  public function testHyphenate() {
    $actual = 'a_test_string';
    $expected = 'a-test-string';

    $result = $this->toursForm->hyphenate($actual);
    $this->assertEquals($expected, $result);
  }

  /**
   * TestCreateWelcomeTip.
   */
  public function testCreateWelcomeTip() {
    $result = $this->toursForm->createWelcomeTip('name', 'machine_name');
    $this->assertArrayHasKey('machine-name-welcome', $result);
  }

  /**
   * TestCreateTips.
   */
  public function testCreateTips() {
    $fields = [
      'node-add-body' => [
        'label' => 'Body',
        'tip_id' => 'node-add-body',
        'data_id' => 'body',
        'description' => 'The body field.',
      ],
      'node-add-field-image' => [
        'label' => 'Image',
        'tip_id' => 'node-add-image',
        'data_id' => 'image',
        'description' => 'The image field.',
      ],
    ];
    $result = $this->toursForm->createTips($fields);
    $this->assertArrayHasKey('node-add-body', $result);
    $this->assertArrayHasKey('node-add-image', $result);
    $this->assertEquals($result['node-add-body']['attributes']['data-id'], 'edit-body-wrapper');
    $this->assertEquals($result['node-add-image']['attributes']['data-id'], 'edit-image-wrapper');
  }

}
