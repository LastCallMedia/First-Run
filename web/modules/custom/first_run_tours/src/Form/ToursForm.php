<?php

namespace Drupal\first_run_tours\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeTypeInterface;

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
  public function buildForm(array $form, FormStateInterface $form_state, NodeTypeInterface $node_type = NULL) {
    $config = $this->config('first_run_tours.tours');

    kint($node_type);

    $form = parent::buildForm($form, $form_state);

    $form['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable tours'),
      '#description' => $this->t('Check here'),
    ];


    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state, NodeTypeInterface $node_type = NULL) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state, NodeTypeInterface $node_type = NULL) {
    parent::submitForm($form, $form_state);

    $this->config('first_run_tours.tours')
      ->save();
  }

}
