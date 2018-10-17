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
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, NodeType $node_type = NULL) {
    $type = $node_type->get('type');

    $form = parent::buildForm($form, $form_state);

    $form['tour_enabled'] = [
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
   * @todo: Don't use [0], find method to get type.
   * @todo: Use dependency injection instead of NodeType::load.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $ct_machine_name = $form_state->getBuildInfo()['args'][0]->get('type');
    $ct_name = $form_state->getBuildInfo()['args'][0]->get('name');
    $tour_id = 'node-edit-' . $ct_machine_name;
    $node_type = NodeType::load($ct_machine_name);
    $tour_enabled_value = $form_state->getValue('tour_enabled');
    $node_type->setThirdPartySetting('first_run_tours', $ct_machine_name, $tour_enabled_value);
    $node_type->save();

    // Add tour based on this content type.
    if ($tour_enabled_value === 1) {
      $this->createTour($ct_name, $tour_id);
      $form_state->setRedirect('entity.tour.edit_form', ['tour' => $tour_id]);
    }

    return;
  }

  /**
   * Create tour.
   *
   * @param string $ct_name
   *   Human readable CT name.
   * @param string $tour_id
   *   ID of the tour.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createTour($ct_name, $tour_id) {
    $values = \Drupal::entityQuery('tour')->condition('id', $tour_id)->execute();

    if (empty($values)) {
      $tour = Tour::create([
        'id' => $tour_id,
        'label' => 'Node Edit ' . $ct_name,
        'module' => 'first_run_tours',
        'routes' => [],
      ]);
      $tour->save();
    }
  }

}
