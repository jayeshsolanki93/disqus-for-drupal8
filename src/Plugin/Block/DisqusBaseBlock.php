<?php

/**
 * @file
 * Contains \Drupal\disqus\Plugin\Block\DisqusBaseBlock.
 */

namespace Drupal\disqus\Plugin\Block;

use Drupal\block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\disqus\DisqusCommentManager;
use Drupal\Core\Routing\RouteMatchInterface;

abstract class DisqusBaseBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Disqus comment manager object.
   *
   * @var \Drupal\disqus\DisqusCommentManager
   */
  protected $disqusManager;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Constructs a new DisqusBaseBlock.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   * @param \Drupal\disqus\DisqusCommentManager $disqusManager
   *   The disqus comment manager object.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The account for which view access should be checked.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match, DisqusCommentManager $disqus_manager, AccountInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentUser = $current_user;
    $this->disqusManager = $disqus_manager;
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('disqus.manager'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'cache' => array(
        'max_age' => 3600,
        'contexts' => array(),
      ),
    );
  }

  /**
   * Helper method to get configuration value.
   *
   * @param  string $key
   * @param  mixed  $default_value
   * @return mixed
   */
  protected function configuration($key, $default_value = NULL) {
    if (isset($this->configuration[$key])) {
      return $this->configuration[$key];
    }

    if (!is_null($default_value)) {
      return $default_value;
    }

    throw new UnexpectedValueException('Missing default value for ' . $key);
  }

  /**
   * Overrides \Drupal\block\BlockBase::blockForm().
   */
  public function blockForm($form, &$form_state) {
    return $this->_blockForm($form, $form_state, $this->id);
  }

  /**
   * Helper for blockForm() method.
   */
  public function _blockForm($form, &$form_state, $delta) {
    $form['disqus'] = array(
      '#type' => 'fieldset',
      '#title' => t('Disqus settings'),
      '#tree' => TRUE,
    );

    $form['disqus'][$delta . '_items'] = array(
      '#type' => 'select',
      '#title' => t('Number of items to show'),
      '#options' => array(1 => 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20),
      '#default_value' => $this->configuration($delta .'_items', 5),
      '#access' => TRUE,
    );
    $form['disqus'][$delta . '_showavatars'] = array(
      '#type' => 'select',
      '#title' => t('Show avatars'),
      '#options' => array(FALSE => t('No'), TRUE => t('Yes')),
      '#default_value' => $this->configuration($delta .'_showavatars', TRUE),
      '#access' => ($delta == 'disqus_recent_comments') || ($delta == 'disqus_top_commenters'),
    );
    $form['disqus'][$delta . '_avatarsize'] = array(
      '#type' => 'select',
      '#title' => t('Avatar size'),
      '#options' => array(
        24 => t('X-Small (24px)'),
        32 => t('Small (32px)'),
        48 => t('Medium (48px)'),
        92 => t('Large (92px)'),
        128 => t('X-Large (128px)'),
      ),
      '#default_value' => $this->configuration($delta .'_avatarsize', 32),
      '#access' => ($delta == 'disqus_recent_comments') || ($delta == 'disqus_top_commenters'),
    );
    $form['disqus'][$delta . '_colortheme'] = array(
      '#type' => 'select',
      '#title' => t('Color Theme'),
      '#options' => array(
        'blue' => t('Blue'),
        'grey' => t('Grey'),
        'green' => t('Green'),
        'red' => t('Red'),
        'orange' => t('Orange'),
      ),
      '#default_value' => $this->configuration($delta .'_colortheme', 'blue'),
      '#access' => $delta == 'disqus_combination_widget',
    );
    $form['disqus'][$delta . '_defaulttabview'] = array(
      '#type' => 'select',
      '#title' => t('Default Tab View'),
      '#options' => array(
        'people' => t('People'),
        'recent' => t('Recent'),
        'popular' => t('Popular'),
      ),
      '#default_value' => $this->configuration($delta .'_defaulttabview', 'people'),
      '#access' => $delta == 'disqus_combination_widget',
    );
    $form['disqus'][$delta . '_excerpt_length'] = array(
      '#type' => 'textfield',
      '#title' => t('Comment Except Length'),
      '#default_value' => $this->configuration($delta .'_excerpt_length', '200'),
      '#access' => ($delta == 'disqus_recent_comments') || ($delta == 'disqus_combination_widget'),
      '#size' => 4,
    );
    $form['disqus'][$delta . '_hide_mods'] = array(
      '#type' => 'checkbox',
      '#title' => t('Hide moderators in ranking'),
      '#default_value' => $this->configuration($delta .'_hide_mods', FALSE),
      '#access' => ($delta == 'disqus_top_commenters') || ($delta == 'disqus_combination_widget'),
    );
    return $form;
  }

  /**
   * Overrides \Drupal\block\BlockBase::blockSubmit().
   */
  public function blockSubmit($form, &$form_state) {
    foreach ($form_state['values']['disqus'] as $k => $v) {
      if ($form['settings']['disqus'][$k]['#access']) {
        $this->configuration[$k] = $v;
      }
    }
  }

  protected function getOptions() {
    $disqus_config = \Drupal::config('disqus.settings');

    return array(
      'num_items' => $this->configuration($this->id . '_items', 5),
      'avatars' => $this->configuration($this->id . '_showavatars', TRUE) ? array('avatar_size' => $this->configuration($this->id . '_avatarsize', 32)) : array('hide_avatars=1'),
      'color' => $this->configuration($this->id . '_colortheme', 'blue'),
      'default_tab' => $this->configuration($this->id . '_defaulttabview', 'people'),
      'excerpt_length' => $this->configuration($this->id . '_excerpt_length', '200'),
      'hide_mods' => $this->configuration($this->id . '_hide_mods', FALSE) ? '1' : '0',
      'domain' => $disqus_config->get('disqus_domain'),
    );
  }

  /**
   * Helper function for disqus widget blocks content.
   *
   * @param $function
   *   Name of the function (widget) that needs to be returned. Same as widget
   *   API call name (w/o .json suffix).
   * @param $options
   *   Options array (query variables, domain, ...).
   * @return
   *   Render array that can be directly used for block content.
   */
  function render($function) {
    $options = $this->getOptions();

    $configuration = array(
      'recent_comments_widget' => array(
        'id' => 'dsq-recentcomments',
        'query_items' => array('num_items', 'excerpt_length', 'avatars'),
      ),
      'popular_threads_widget' => array(
        'id' => 'dsq-popthreads',
        'query_items' => array('num_items'),
      ),
      'top_commenters_widget' => array(
        'id' => 'dsq-topcommenters',
        'query_items' => array('num_items', 'hide_mods', 'avatars'),
      ),
      'combination_widget' => array(
        'id' => 'dsq-combinationwidget',
        'query_items' => array('num_items', 'hide_mods', 'excerpt_length', 'color', 'default_tab'),
      ),
    );

    if (empty($configuration[$function])) {
      return FALSE;
    }

    $query = array();
    foreach ($configuration[$function]['query_items'] as $query_item) {
      if ($query_item === 'avatars') {
        $query += $options[$query_item];
      }
      else {
        $query[$query_item] = $options[$query_item];
      }
    }

    $url = url("//disqus.com/forums/${options['domain']}/$function.js", array('external' => TRUE, 'query' => $query));

    return array(
      'widget' => array(
        '#prefix' => '<script type="text/javascript" src="'. $url .'">',
        '#suffix' => '</script>',
        '#value' => '',
      ),
      '#theme_wrappers' => array('container'),
      '#attributes' => array(
        'id' => $configuration[$function]['id'],
        'class' => array('dsq-widget'),
      ),
    );
  }
}
