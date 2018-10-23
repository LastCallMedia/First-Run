<?php

namespace Drupal\first_run_tours\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\NodeType;
use Drupal\tour\Entity\Tour;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityManager;
use Drupal\Core\Entity\EntityFieldManagerInterface;

/**
 * Class ToursForm.
 */
class ToursForm extends ConfigFormBase {

  const TOUR_ID_PREFIX = 'node-add-';

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'first_run_tours.tours',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'tours_form';
  }

  /**
   * Return constant.
   *
   * @return string
   *   Returns a constant string.
   */
  public function returnTourIdPrefix() {
    return self::TOUR_ID_PREFIX;
  }

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * {@inheritdoc}
   */
  public function __construct($config_factory, EntityManager $entity_manager, EntityTypeManagerInterface $entity_type_manager, EntityFieldManagerInterface $entity_field_manager) {
    parent::__construct($config_factory);
    $this->entityManager = $entity_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFieldManager = $entity_field_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity.manager'),
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, NodeType $node_type = NULL) {
    $type = $node_type->get('type');
    $form = parent::buildForm($form, $form_state);
    $ct_machine_name = $form_state->getBuildInfo()['args'][0]->get('type');

    // Load the Tour tips, convert hyphenated tour ids to field underscore id.
    $tour_id = $this->returnTourIdPrefix() . $ct_machine_name;
    $tour_tips = Tour::load($tour_id) ? Tour::load($tour_id)->getTips() : NULL;
    $tips = [];
    if (isset($tour_tips)) {
      foreach ($tour_tips as $tip) {
        $tips[] = str_replace([ToursForm::TOUR_ID_PREFIX, '-'], ['', '_'], $tip->id());
      }
    }

    $form['first_run_tour']['tour_enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable tour'),
      '#description' => $this->t('Check here to enable tour for this Content type'),
      '#default_value' => $node_type->getThirdPartySetting('first_run_tours', $type),
    ];
    $form['first_run_tour']['field_select'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Tour Tip fields'),
      '#options' => $this->getConfigFieldNames($ct_machine_name),
      '#empty_option' => $this->t('- Select -'),
      '#multiple' => TRUE,
      '#default_value' => $tips,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $ct_machine_name = $form_state->getBuildInfo()['args'][0]->get('type');
    $ct_name = $form_state->getBuildInfo()['args'][0]->get('name');
    $tour_id = $this->returnTourIdPrefix() . $ct_machine_name;

    /* @var $node_type \Drupal\node\Entity\NodeType */
    $node_type = NodeType::load($ct_machine_name);
    $tour_enabled_value = $form_state->getValue('tour_enabled');
    $node_type->setThirdPartySetting('first_run_tours', $ct_machine_name, $tour_enabled_value);
    $node_type->save();

    if ($tour_enabled_value !== 1) {
      return;
    }

    $tour = $this->entityTypeManager->getStorage('tour')->getQuery()->condition('id', $tour_id)->execute();
    // Get fields and create tour if one doesn't exist yet for this CT.
    if (empty($tour)) {

      // Create an array of fields with label and hyphenated machine name in order to create tour tips.
      $field_names = [];
      $fields = $this->entityFieldManager->getFieldDefinitions('node', $ct_machine_name);
      $selected_values = $form_state->getValues()['field_select'];
      foreach ($selected_values as $value) {
        $label = $fields[$value] ? $fields[$value]->getLabel() : $value;
        $field_names[] = [
          'label' => $label,
          'data_id' => str_replace('_', '-', $value),
          'tip_id' => ToursForm::TOUR_ID_PREFIX . str_replace('_', '-', $value),
        ];
      }

      // Add tips and tour based on this content type.
      $field_tips = $this->createTips($field_names);
      $this->createTour($ct_name, $tour_id, $field_tips);
    }

    $form_state->setRedirect('entity.tour.edit_form', ['tour' => $tour_id]);
  }

  /**
   * Create tour.
   *
   * @param string $ct_name
   *   Human readable CT name.
   * @param string $tour_id
   *   ID of the tour.
   * @param array $field_tips
   *   Array of tour tips.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createTour($ct_name, $tour_id, array $field_tips) {
    $existing_tour = $this->entityTypeManager->getStorage('tour')->getQuery()->condition('id', $tour_id)->execute();

    if (empty($existing_tour)) {
      $tour = Tour::create([
        'id' => $tour_id,
        'label' => 'Node Edit ' . $ct_name,
        'module' => 'first_run_tours',
        'routes' => [],
        'tips' => $field_tips,
      ]);
      $tour->save();
    }
  }

  /**
   * Returns an array of tips based on CT fields.
   *
   * @param array $field_names
   *   Parameter is array of field information.
   *
   * @return array
   *   Returns tip array.
   */
  public function createTips(array $field_names) {
    $field_tips = [];

    foreach ($field_names as $field) {
      $field_tips[$field['tip_id']] = [
        'id' => $field['tip_id'],
        'plugin' => 'text',
        'label' => $field['label'],
        'body' => '',
        'weight' => '100',
        'location' => 'top',
        'attributes' => [
          'data-id' => 'edit-' . $field['data_id'] . '-wrapper',
        ],
      ];
    }

    return $field_tips;
  }

  /**
   * Get non-base fields from CT.
   *
   * @param string $ct_machine_name
   *   String of CT machine name.
   *
   * @return array
   *   Returns an array of non-base field names.
   */
  public function getConfigFieldNames($ct_machine_name) {
    $fields = [];
    $node_fields = $this->entityManager->getFieldDefinitions('node', $ct_machine_name);
    foreach ($node_fields as $field) {
      // Filter out base fields like node nid.
      if (method_exists($field, 'getEntityTypeId') && $field->getEntityTypeId() == 'field_config') {
        $fields[$field->getName()] = $field->getLabel();
      }
    }
    return $fields;
  }

}
