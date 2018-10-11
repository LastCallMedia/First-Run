<?php

namespace Drupal\fr_add_content\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @file
 * Contains \Drupal\fr_add_content\Form\AddContentForm.
 */

/**
 * Configure Site Wide Notification.
 */
class AddContentForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'add_content_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $form['#tree'] = TRUE;

    $config = $this->config('fr_add_content.settings');

    $form['intro_text'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Intro Text Title'),
      '#base_type' => 'textarea',
      '#format' => 'full_html',
      '#required' => FALSE,
      '#description' => $this->t('Add custom overview content to the top of the node/add page'),
      '#default_value' => $config->get('intro_text'),
    ];

    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('fr_add_content.settings');
    $config->set('intro_text', $form_state->getValue('intro_text'));
    $config->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'fr_add_content.settings',
    ];
  }

}
