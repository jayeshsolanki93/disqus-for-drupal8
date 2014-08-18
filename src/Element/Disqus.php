<?php

/**
 * @file
 * Contains \Drupal\disqus\Element\Disqus.
 */

namespace Drupal\disqus\Element;

use Drupal\Core\Render\Element\RenderElement;

/**
 * Provides disqus module's render element properties
 *
 * @RenderElement("disqus")
 */
class Disqus extends RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    return array(
      '#disqus' => array(),
      '#theme_wrappers' => array('disqus_noscript', 'container'),
      '#attributes' => array('id' => 'disqus_thread'),
    );
  }

  /**
   * Post render function of the Disqus element to inject the Disqus JavaScript.
   */
  public static function disqus_element_post_render_cache(array $element, array $context) {
    // Construct the settings to be passed in for Disqus.
    $entity = $context['entity'];
    $disqus = array(
      'domain' => \Drupal::config('disqus.settings')->get('disqus_domain'),
      'url' => $entity->url('canonical',array('absolute' => TRUE)),
      'title' => $entity->label(),
      'identifier' => "{$entity->getEntityTypeId()}/{$entity->id()}",
    );
    $disqus['category_id'] = $context['category_id'];
    $disqus['disable_mobile'] = \Drupal::config('disqus.settings')->get('behavior.disqus_disable_mobile');

    // If the user is logged in, we can inject the username and email for Disqus.
    $account = \Drupal::currentUser();

    if (\Drupal::config('disqus.settings')->get('behavior.disqus_inherit_login') && !$account->isAnonymous()) {
      $disqus['name'] = $account->getUsername();
      $disqus['email'] = $account->getEmail();
    }

    // Provide alternate language support if desired.
    if (\Drupal::config('disqus.settings')->get('behavior.disqus_localization')) {
      $language = \Drupal::languageManager()->getCurrentLanguage();
      $disqus['language'] = $language->id;
    }

    // Check if we are to provide Single Sign-On access.
    if (\Drupal::config('disqus.settings')->get('advanced.sso.disqus_sso')) {
      $disqus += \Drupal::service('disqus.manager')->disqus_sso_disqus_settings();
    }

    /**
     * Pass callbacks on if needed. Callbacks array is two dimensional array
     * with callback type as key on first level and array of JS callbacks on the
     * second level.
     *
     * Example:
     * @code
     * $element['#disqus']['callbacks'] = array(
     *   'onNewComment' => array(
     *     'myCallbackThatFiresOnCommentPost',
     *     'Drupal.mymodule.anotherCallbInsideDrupalObj',
     *   ),
     * );
     * @endcode
     */
    if (!empty($element['#disqus']['callbacks'])) {
      $disqus['callbacks'] = $element['#disqus']['callbacks'];
    }
    // Add the disqus.js and all the settings to process the JavaScript and load Disqus.
    $element['#attached']['js'][] = drupal_get_path('module', 'disqus') . '/disqus.js';
    $element['#attached']['js'][] = array(
      'type' => 'setting',
      'data' => array(
        'disqus' => $disqus,
      ),
    );
    return $element;
  }

}
