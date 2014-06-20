<?php

/**
 * @file
 * Contains \Drupal\disqus\Plugin\Field\FieldWidget\DisqusWidget.
 */

namespace Drupal\disqus\Plugin\Field\FieldWidget;

use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
class DisqusWidget extends WidgetBase implements ContainerFactoryPluginInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, AccountInterface $current_user) {
   parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, array());
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, array &$form_state) {

    $element['status'] = array(
      '#type' => 'checkbox',
      '#title' => t('Disqus Comments'),
      '#description' => t('Users can post comments using <a href="@disqus">Disqus</a>.', array('@disqus' => 'http://disqus.com')),
      '#default_value' => isset($items->status) ? $items->status : FALSE,
      '#access' => $this->currentUser->hasPermission('toggle disqus comments'),
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
