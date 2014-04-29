<?php

namespace Drupal\disqus\Plugin\Block;

use Drupal\block\Annotation\Block;
use Drupal\Core\Annotation\Translation;

/**
 *
 * @Block(
 *   id = "disqus_recent_comments",
 *   admin_label = @Translation("Disqus: Recent Comments"),
 *   module = "disqus"
 * )
 */
class DisqusRecentCommentBlock extends DisqusBaseBlock {
  protected $id = 'disqus_recent_comments';

  /**
   * {@inheritdoc}
   */
  public function build() {
    return array(
      '#title' => t('Recent Comments'),
      $this->render('recent_comments_widget'),
    );
  }
}
