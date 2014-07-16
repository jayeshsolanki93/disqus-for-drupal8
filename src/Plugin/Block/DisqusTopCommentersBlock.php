<?php

/**
 * @file
 * Contains \Drupal\disqus\Plugin\Block\DisqusTopCommentersBlock.
 */

namespace Drupal\disqus\Plugin\Block;

use Drupal\block\Annotation\Block;
use Drupal\Core\Annotation\Translation;

/**
 *
 * @Block(
 *   id = "disqus_top_commenters",
 *   admin_label = @Translation("Disqus: Top Commenters"),
 *   module = "disqus"
 * )
 */
class DisqusTopCommentersBlock extends DisqusBaseBlock {
  protected $id = 'disqus_top_commenters';

  /**
   * {@inheritdoc}
   */
  public function build() {
    return array(
      '#title' => t('Top Commenters'),
      $this->render('top_commenters_widget')
    );
  }
}
