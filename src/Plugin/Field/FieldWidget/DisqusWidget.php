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
      if (!isset($element['comment_settings'])) {
        $element['comment_settings'] = array(
          '#type' => 'fieldset',
          '#access' => \Drupal::currentUser()->hasPermission('toggle disqus comments'),
          '#title' => t('Comment settings'),
          '#collapsible' => TRUE,
          '#collapsed' => TRUE,
          '#group' => 'additional_settings',
          '#weight' => 30,
        );
      }
      $element['comment_settings']['disqus_status'] = array(
        '#type' => 'checkbox',
        '#title' => t('Disqus comments'),
        '#description' => t('Users can post comments using <a href="@disqus">Disqus</a>.', array('@disqus' => 'http://disqus.com')),
        // @TODO: Check default value
        '#default_value' => $items[$delta]->status,
        '#access' => \Drupal::currentUser()->hasPermission('toggle disqus comments'),
      );

   return $element;
 }

}
