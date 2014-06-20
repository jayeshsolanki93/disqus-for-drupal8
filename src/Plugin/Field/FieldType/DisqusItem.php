<?php

/**
 * @file
 * Contains \Drupal\disqus\Plugin\Field\FieldType\DisqusItem.
 */

namespace Drupal\disqus\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\TypedData\DataDefinition;

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
  public function isEmpty() {
    $value = $this->get('status')->getValue();
    return $value === NULL || $value === '';
  }

}
