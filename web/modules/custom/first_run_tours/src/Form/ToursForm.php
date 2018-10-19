<?php

namespace Drupal\first_run_tours\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\NodeType;
use Drupal\tour\Entity\Tour;

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
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, NodeType $node_type = NULL) {
    $type = $node_type->get('type');

    $form = parent::buildForm($form, $form_state);

    $form['first_run_tour']['tour_enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable tour'),
      '#description' => $this->t('Check here to enable tour for this Content type'),
      '#default_value' => $node_type->getThirdPartySetting('first_run_tours', $type),
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
   *
   * @todo: Don't use [0] for $ct_machine_name, find method to get type.
   * @todo: Use dependency injection instead of NodeType::load.
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

    // Add tips and tour based on this content type.
    $field_tips = $this->createTips($ct_machine_name);
    $this->createTour($ct_name, $tour_id, $field_tips);
    $form_state->setRedirect('entity.tour.edit_form', ['tour' => $tour_id]);

    return;
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
    $values = \Drupal::entityQuery('tour')->condition('id', $tour_id)->execute();

    if (empty($values)) {
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
   * @param string $ct_machine_name
   *   Parameter is CT type, e.g., 'article'.
   *
   * @return array
   *   Returns tip array.
   */
  public function createTips($ct_machine_name) {
    $field_tips = [];

    $fields = \Drupal::service('entity.manager')->getFieldDefinitions('node', $ct_machine_name);

    foreach ($fields as $field) {
      // Try to filter out base fields.
      if (!method_exists($field, 'isBaseField') && !method_exists($field, 'getBaseFieldDefinition')) {

        $field_name = $field->label();
        $field_machine_name = $field->getName();
        $field_tip_id = $field_machine_name . '_tip';

        $field_tips[$field_tip_id] = [
          'id' => $field_tip_id,
          'plugin' => 'text',
          'label' => $field_name . ' tip',
          'body' => '',
          'weight' => '100'
        ];
      }
    }

    return $field_tips;
  }

}
