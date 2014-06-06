<?php

/**
 * @file
 * Contains \Drupal\disqus\Plugin\Field\FieldFormatter\DisqusFormatter.
 */

namespace Drupal\disqus\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Provides a default disqus comment formatter.
 *
 * @FieldFormatter(
 *   id = "disqus_comment",
 *   label = @Translation("Default"),
 *   field_types = {
 *     "disqus_comment"
 *   }
 * )
 */
class DisqusFormatter extends FormatterBase {
  
  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items) {
    $element = array();
    foreach ($items as $delta => $item) {
      $element[$delta] = array(
        '#type' => 'disqus',
        '#disqus' => $item,
      );
    }
    return $element;
  }
}
