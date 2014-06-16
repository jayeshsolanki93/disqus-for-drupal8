<?php

/**
 * @file
 * Contains \Drupal\disqus\Plugin\Field\FieldWidget\DisqusWidget.
 */

namespace Drupal\disqus\Plugin\Field\FieldWidget;

use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'disqus' widget.
 *
 * @FieldWidget(
 *   id = "disqus_comment",
 *   label = @Translation("Default"),
 *   field_types = {
 *     "disqus_comment"
 *   }
 * )
 */
class DisqusWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
 public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, array &$form_state) {
    $status = $items->status;

    $element['status'] = array(
      '#type' => 'checkbox',
      '#title' => t('Disqus Comments'),
      '#description' => t('Users can post comments using <a href="@disqus">Disqus</a>.', array('@disqus' => 'http://disqus.com')),
      // @TODO: Check default value
      '#default_value' => $status,
      '#access' => \Drupal::currentUser()->hasPermission('toggle disqus comments'),
    );
    // If the advanced settings tabs-set is available (normally rendered in the
    // second column on wide-resolutions), place the field as a details element
    // in this tab-set.
    if (isset($form['advanced'])) {
      $element += array(
        '#type' => 'details',
        '#group' => 'advanced',
      );
    }
   return $element;
 }

}
