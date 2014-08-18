<?php

/**
 * @file
 * Contains \Drupal\disqus\Plugin\Field\FieldType\DisqusItem.
 */

namespace Drupal\disqus\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'disqus' field type.
 *
 * @FieldType(
 *   id = "disqus_comment",
 *   label = @Translation("Disqus comment"),
 *   description = @Translation("Disqus comment widget"),
 *   default_widget = "disqus_comment",
 *   default_formatter = "disqus_comment"
 * )
 */
class DisqusItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'category_id' => NULL,
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return array(
      'columns' => array(
        'status' => array(
          'type' => 'int',
          'not null' => TRUE,
          'default' => 1,
        ),
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['status'] = DataDefinition::create('integer')->setLabel(t('Disqus status value'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function __get($name) {
    if ($name == 'status' && !isset($this->values[$name])) {
      // Get default value from field instance when no data saved in entity.
      $field_default_values = $this->getFieldDefinition()->getDefaultValue($this->getEntity());
      return $field_default_values[0]['status'];
    }
    else {
      return parent::__get($name);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('status')->getValue();
    return $value === NULL || $value === '';
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    $element = array();
    $settings = $this->getFieldDefinition()->getFieldStorageDefinition()->getSettings();
    $disqus = disqus_api();
    if ($disqus && empty($settings['category_id'])) {
      try {
        $category = $disqus->categories->create(array('forum' => \Drupal::config('disqus.settings')->get('disqus_domain'), 'title' => $this->getEntity()->bundle(), 'access_token' => \Drupal::config('disqus.settings')->get('advanced.disqus_useraccesstoken')));
        $settings['category_id'] = $category->id;
      }
      catch (Exception $exception) {
        \Drupal::logger('disqus')->error('Error creating category. Check your API keys.');
      }
    }
    $element['category_id'] = array(
      '#type' => 'value',
      '#value' => $settings['category_id'],
    );
    return $element;
  }

}
