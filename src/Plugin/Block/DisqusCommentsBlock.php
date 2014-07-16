<?php

/**
 * @file
 * Contains \Drupal\disqus\Plugin\Block\DisqusCommentsBlock.
 */

namespace Drupal\disqus\Plugin\Block;

use Drupal\block\Annotation\Block;
use Drupal\Core\Annotation\Translation;

/**
 *
 * @Block(
 *   id = "disqus_comments",
 *   admin_label = @Translation("Disqus: Comments"),
 *   module = "disqus"
 * )
 */
class DisqusCommentsBlock extends DisqusBaseBlock {
  protected $id = 'disqus_comments';

  /**
   * Overrides DisqusBaseBlock::blockForm().
   */
  public function blockForm($form, &$form_state) {
    $form['disqus'] = array(
      '#type' => 'fieldset',
      '#title' => t('Disqus settings'),
      '#tree' => TRUE,
    );

    $form['disqus']['#description'] = t('This block will be used to display the comments from Disqus when comments are applied to the given page. Visit the <a href="@disqussettings">Disqus settings</a> to configure when this is visible.', array('@disqussettings' => url('admin/config/services/disqus')));

    return $form;
  }

  /**
   * Overrides DisqusBaseBlock::blockSubmit().
   */
  public function blockSubmit($form, &$form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $disqus_config = \Drupal::config('disqus.settings');
    if ($this->currentUser->hasPermission('view disqus comments')) {
      $keys = $this->routeMatch->getParameters();
      foreach($keys as $key => $value) {
        if(!(is_a($value,'Drupal\Core\Entity\ContentEntityInterface'))) {
          continue;
        }
        // Display if the Disqus field is enabled for the entity.
        $entity = $this->routeMatch->getParameter($key);
        $field = $this->disqusManager->getFields($key);
        if($entity->hasField(key($field))) {
          if ($entity->get(key($field))->status) {
            return array(
              'disqus' => array(
                '#type' => 'disqus',
                '#post_render_cache' => array(
                  'disqus_element_post_render_cache' => array(
                    array('entity' => $entity),
                  ),
                ),
                '#cache' => array(
                  'bin' => 'render',
                  'keys' => array('disqus', 'disqus_comments', "{$entity->getEntityTypeId()}", $entity->id()),
                  'tags' => array('content' => TRUE),
                ),
              ),
            );
          }
        }
      }
    }
  }
}
