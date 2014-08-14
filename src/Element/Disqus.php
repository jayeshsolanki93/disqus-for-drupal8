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

}
