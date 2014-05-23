<?php

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
   * Overrides DisqusBaseBlock::settings().
   */
  public function settings() {
    return array(
      'cache' => DRUPAL_CACHE_CUSTOM,
    );
  }

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

    if ($disqus_config->get('visibility.disqus_location') === 'block' && \Drupal::currentUser()->hasPermission('view disqus comments')) {
      if ($object = \Drupal::request()->attributes->get('node')) {
        return $this->buildForNodeEntity($object);
      }

      if ($object = \Drupal::request()->attributes->get('user')) {
        return  $this->buildForUserEntity($object);
      }
    }
  }

  /**
   * Build the disqus comment block for node entity.
   */
  protected function buildForNodeEntity($object) {
      // For nodes, display if the Disqus object is enabled.
      if (isset($object->disqus) && $object->disqus['status']) {
        return array(
          'disqus' => array(
            '#type' => 'disqus',
            '#disqus' => $object->disqus,
          ),
        );
      }
  }

  /**
   * Build the disqus comment block for user entity.
   */
  protected function buildForUserEntity($object) {
    if (isset($object->disqus)) {
      return array(
        'disqus' => array(
          '#type' => 'disqus',
          '#disqus' => $object->disqus,
        ),
      );
    }
  }
}
