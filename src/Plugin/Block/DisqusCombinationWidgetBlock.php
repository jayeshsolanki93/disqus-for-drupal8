<?php

namespace Drupal\disqus\Plugin\Block;

use Drupal\block\Annotation\Block;
use Drupal\Core\Annotation\Translation;

/**
 *
 * @Block(
 *   id = "disqus_combination_widget",
 *   admin_label = @Translation("Disqus: Combination Widget"),
 *   module = "disqus"
 * )
 */
class DisqusCombinationWidgetBlock extends DisqusBaseBlock {
  protected $id = 'disqus_combination_widget';

  /**
   * {@inheritdoc}
   */
  public function build() {
    return array(
      '#title' => t('Comments'),
      $this->render('combination_widget')
    );
  }
}
