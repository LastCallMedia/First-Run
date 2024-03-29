<?php

namespace Drupal\welcome_page\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;

/**
 * Class WelcomePageConfigForm.
 */
class WelcomePageConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'welcome_page.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'welcome_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('welcome_page.settings');

    $form['support_agency_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Support Agency Name'),
      '#default_value' => $config->get('support_agency_name'),
    ];
    $form['support_agency_information'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Support Agency Information'),
      '#description' => $this->t('Contact info and other support agency information.'),
      '#default_value' => $config->get('support_agency_information.value'),
      '#format' => $config->get('support_agency_information.format'),
      '#allowed_formats' => ['basic_html'],
    ];
    $form['knowledgebase_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Knowledgebase URL'),
      '#description' => $this->t('Provide a link to a informational resource.'),
      '#default_value' => $config->get('knowledgebase_url'),
    ];
    $form['request_support_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Request Support URL'),
      '#description' => $this->t('Provide a support agency link.'),
      '#default_value' => $config->get('request_support_url'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $this->config('welcome_page.settings')
      ->set('support_agency_name', $values['support_agency_name'])
      ->set('support_agency_information.value', $values['support_agency_information']['value'])
      ->set('support_agency_information.format', $values['support_agency_information']['format'])
      ->set('knowledgebase_url', $values['knowledgebase_url'])
      ->set('request_support_url', $values['request_support_url'])
      ->save();

    parent::submitForm($form, $form_state);
    $form_state->setRedirect('welcome_page.welcome_home');
  }

}
