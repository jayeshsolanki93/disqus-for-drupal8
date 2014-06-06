<?php

/**
 * @file
 * Contains \Drupal\disqus\Plugin\Field\FieldType\DisqusItem.
 */

namespace Drupal\disqus\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;

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
        'value' => array(
          'type' => 'int',
          'not null' => FALSE,
        ),
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = parent::propertyDefinitions($field_definition);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function update() {

  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    return FALSE;
  }

}
