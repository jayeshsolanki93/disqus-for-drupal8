<?php

namespace Drupal\disqus\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\Component\Annotation\PluginID;
use Drupal\views\ResultRow;

/**
 * Field handler to present the number of Disqus comments on a node.
 *
 * @ingroup views_field_handlers
 *
 * @PluginID("disqus_comment_count")
 */
class DisqusCommentCount extends FieldPluginBase {
  /**
   * {@inheritdoc}
   */
  function render(ResultRow $values) {
    // Ensure Disqus comments are available on the node user has access to edit this node.
    $node = node_load($values->nid);

    if (!\Drupal::currentUser()->hasPermission('view disqus comments') || isset($node->disqus)) {
      return;
    }

    // Extract the Disqus values.
    $disqus = $node->disqus;

    // Build a renderable array for the link.
    $content = array(
      '#theme' => 'link',
      '#text' => t('Comments'),
      '#path' => $disqus['identifier'],
      '#options' => array(
        'fragment' => 'disqus_thread',
        'attributes' => array(
          // Identify the node for Disqus with the unique identifier:
          // http://docs.disqus.com/developers/universal/#comment-count
          'data-disqus-identifier' => $disqus['identifier'],
        ),
        'html' => FALSE,
      ),
    );

    /**
     * This attaches disqus.js, which will look for the DOM variable
     * disqusComments which is set below. When found, the disqus javascript
     * api replaces the html element with the attribute:
     * "data-disqus-identifier" and replaces the element with the number of
     * comments on the node.
     */
    $content['#attached'] = array(
      'js' => array(
        array('data' => drupal_get_path('module', 'disqus') . '/disqus.js'),
        array(
          'data' => array('disqusComments' => $disqus['domain']),
          'type' => 'setting',
        ),
      ),
    );

    return drupal_render($content);
  }
}
