<?php

namespace Drupal\content_type_tour\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\NodeType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityManager;
use Drupal\Core\Entity\EntityFieldManagerInterface;

/**
 * Class ContentTypeTourForm.
 */
class ContentTypeTourForm extends ConfigFormBase {

  const TOUR_ID_PREFIX = 'node-add-';

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'content_type_tour.tour',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'content_type_tour_form';
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

    // Load the Tour tips.
    $tour_id = $this->returnTourIdPrefix() . $this->hyphenate($ct_machine_name);
    $tour = $this->entityManager->getStorage('tour')->load($tour_id);
    $tour_tips = $tour ? $tour->getTips() : NULL;

    // Convert hyphenated tip ids to field id with underscore.
    $tips = [];
    if (isset($tour_tips)) {
      foreach ($tour_tips as $tip) {
        $tips[] = str_replace([ContentTypeTourForm::TOUR_ID_PREFIX, '-'], ['', '_'], $tip->id());
      }
    }

    $form['tour_enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable tour'),
      '#description' => $this->t('Check here to enable tour for this Content type'),
      '#default_value' => $node_type->getThirdPartySetting('content_type_tour', $type),
    ];
    $form['field_select'] = [
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
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $ct_machine_name = $form_state->getBuildInfo()['args'][0]->get('type');
    $ct_name = $form_state->getBuildInfo()['args'][0]->get('name');
    $tour_id = $this->returnTourIdPrefix() . $this->hyphenate($ct_machine_name);

    /* @var $node_type \Drupal\node\Entity\NodeType */
    // phpcs:disable
    $node_type = NodeType::load($ct_machine_name);
    // phpcs:enable
    $tour_enabled_value = $form_state->getValue('tour_enabled');
    $node_type->setThirdPartySetting('content_type_tour', $ct_machine_name, $tour_enabled_value);
    $node_type->save();

    if ($tour_enabled_value !== 1) {
      return;
    }

    // Create an array of fields with label and hyphenated machine name in order
    // to create tour tips.
    $selected_field_info = [];
    $fields = $this->entityFieldManager->getFieldDefinitions('node', $ct_machine_name);
    $selected_values = $form_state->getValues()['field_select'];
    foreach ($selected_values as $value) {
      $label = $fields[$value] ? $fields[$value]->getLabel() : $value;
      $selected_field_info[] = [
        'label' => $label,
        'data_id' => $this->hyphenate($value),
        'tip_id' => ContentTypeTourForm::TOUR_ID_PREFIX . $this->hyphenate($value),
        'description' => $fields[$value]->getDescription(),
      ];
    }

    $tour_id_results = $this->entityTypeManager->getStorage('tour')->getQuery()->condition('id', $tour_id)->execute();

    // If a tour for this CT doesn't exist, create one, else add newly
    // selected tips.
    if (empty($tour_id_results)) {
      // Get fields and create tour if one doesn't exist yet for this CT.
      $field_tips = $this->createTips($selected_field_info);
      $welcome_tip = $this->createWelcomeTip($ct_name, $ct_machine_name);
      $this->createTour($ct_name, $tour_id, array_merge($field_tips, $welcome_tip));
    }
    else {
      $tour = $this->entityManager->getStorage('tour')->load($tour_id);
      $new_tips = [];

      // For each selected field, if it isn't currently a tip, add to array.
      foreach ($selected_field_info as $field) {
        if (!$tour->get('tipsCollection')->has($field['tip_id'])) {
          $new_tips[$field['tip_id']] = $field;
        }
      }

      // Transform selected fields to tip arrays.
      $new_tips = $this->createTips($new_tips);

      // Save existing tips to array.
      $existing_tip_list = $tour->getTips();
      $existing_tips = [];
      if (!empty($existing_tip_list)) {
        foreach ($existing_tip_list as $tip) {
          $existing_tips[$tip->id()] = $tip->getConfiguration();
        }
      }

      // Merge existing and new tips, set, and save.
      $tour->set('tips', array_merge($new_tips, $existing_tips));
      $tour->save();
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
      $tour = $this->entityManager->getStorage('tour')->create([
        'id' => $tour_id,
        'label' => 'Node Edit ' . $ct_name,
        'module' => 'content_type_tour',
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
        'body' => $field['description'],
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

  /**
   * Creates a welcome tip for a specific CT.
   *
   * @param string $ct_name
   *   String of CT name.
   * @param string $ct_machine_name
   *   String of CT machine name.
   *
   * @return array
   *   Returns a welcome tip.
   */
  public function createWelcomeTip($ct_name, $ct_machine_name) {
    $welcome_tip_id = $this->hyphenate($ct_machine_name) . '-welcome';
    $tip = [
      $welcome_tip_id => [
        'id' => $welcome_tip_id,
        'plugin' => 'text',
        'label' => 'Welcome',
        'body' => 'Welcome to the ' . $ct_name . ' tour. Here are some tips on the fields you will be using.',
        'weight' => '-100',
      ],
    ];
    return $tip;
  }

  /**
   * Converts underscores to hyphens.
   *
   * @param string $string
   *   Takes an underscore string.
   *
   * @return string
   *   Returns hyphenated string.
   */
  public function hyphenate($string) {
    return str_replace('_', '-', $string);
  }

}
