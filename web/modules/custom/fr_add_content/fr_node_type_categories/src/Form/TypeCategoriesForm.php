<?php

namespace Drupal\fr_node_type_categories\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @file
 * Contains \Drupal\fr_node_type_categories\Form\TypeCategoriesForm.
 */

/**
 * Configure Site Wide Notification.
 */
class TypeCategoriesForm extends ConfigFormBase {

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
    return 'type_categories_list_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $form['#tree'] = TRUE;

    $config = $this->config('fr_node_type_categories.settings');

    $form['category_list'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Category List Builder'),
      '#description' => $this->t('Use this field to generate the list of categories you wish to use to categorize your content types. Please format the list as "key|value" pairs with a vertical pipe between the key and value.'),
      '#default_value' => $config->get('category_list'),
    ];

    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('fr_node_type_categories.settings');
    $config->set('category_list', $form_state->getValue('category_list'));
    $config->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'fr_node_type_categories.settings',
    ];
  }

}
