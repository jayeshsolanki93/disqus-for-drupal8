<?php

/**
 *  @file
 *  Contains \Drupal\comment\DisqusCommentManager.
 */

namespace Drupal\disqus;

use Drupal\Core\Entity\EntityManagerInterface;

/**
 * Disqus comment manager contains common functions to manage disqus_comment fields.
 */
class DisqusCommentManager implements DisqusCommentManagerInterface {
  
  /**
   * The entity manager service.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;
  
  /**
   * Constructs the DisqusCommentManager object.
   * 
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager service.
   */
  public function __construct(EntityManagerInterface $entity_manager) {
    $this->entityManager = $entity_manager;
  }
  
  /**
   * {@inheritdoc}
   */
  public function getFields($entity_type_id) {
    $entity_type = $this->entityManager->getDefinition($entity_type_id);
    if (!$entity_type->isSubclassOf('\Drupal\Core\Entity\ContentEntityInterface')) {
      return array();
    }
    
    $map = $this->getAllFields();
    return isset($map[$entity_type_id]) ? $map[$entity_type_id] : array();
  }
  
  /**
   * {@inheritdoc}
   */
  public function getAllFields() {
    $map = $this->entityManager->getFieldMap();
    // Build a list of disqus comment fields only.
    $disqus_comment_fields = array();
    foreach ($map as $entity_type => $data) {
      foreach ($data as $field_name => $field_info) {
        if ($field_info['type'] == 'disqus_comment') {
          $disqus_comment_fields[$entity_type][$field_name] = $field_info;
        } 
      } 
    }
    return $disqus_comment_fields;
  }

  /**
   * {@inheritdoc}
   */
  public function disqus_sso_disqus_settings() {

    $disqus['sso'] = array(
      'name' => \Drupal::config('system.site')->get('name'),
      // The login window must be closed once the user logs in.
      'url' => url('user/login', array('query' => array('destination' => 'disqus/closewindow'))),
      // The logout link must redirect back to the original page.
      'logout' => url('user/logout', array('query' => array('destination' => current_path()))),
      'width' => 800,
      'height' => 600,
    );

    $managed_logo = \Drupal::config('disqus.settings')->get('advanced.sso.disqus_logo');
    $use_site_logo = \Drupal::config('disqus.settings')->get('advanced.sso.disqus_use_site_logo');
    if (!$use_site_logo && $managed_logo !== FALSE) {
      $disqus['sso']['button'] = file_create_url(file_load($managed_logo['0'])->getFileUri());
    }
    elseif ($logo = theme_get_setting('logo')) {
      $disqus['sso']['button'] = $logo['url'];
    }
    else {
      $disqus['sso']['button'] = url('misc/druplicon.png', array('absolute' => TRUE));
    }
    if ($favicon = theme_get_setting('favicon')) {
      $disqus['sso']['icon'] = $favicon['url'];
    }

    // Stick the authentication requirements and data in the settings.
    $disqus['api_key'] = \Drupal::config('disqus.settings')->get('advanced.disqus_publickey');
    $disqus['remote_auth_s3'] = $this->disqus_sso_key_encode($this->disqus_sso_user_data());

    return $disqus;
  }

  /**
   * {@inheritdoc}
   */
  public function disqus_sso_key_encode($data) {
    // Encode the data to be sent off to Disqus.
    $message = base64_encode(json_encode($data));
    $timestamp = time();
    $hmac = hash_hmac('sha1', "$message $timestamp", \Drupal::config('disqus.settings')->get('advanced.disqus_secretkey'));

    return "$message $hmac $timestamp";
  }

  /**
   * {@inheritdoc}
   */
  public function disqus_sso_user_data() {

    $account = \Drupal::currentUser();

    $data = array();
    if (!$account->isAnonymous()) {
      $data['id'] = $account->id();
      $data['username'] = $account->getUsername();
      $data['email'] = $account->getEmail();
      $data['url'] = url('user/' . $account->id(), array('absolute' => TRUE));

      // Load the user's avatar.
      $user_picture_default = \Drupal::config('field.instance.user.user.user_picture')->get('user_picture_default');

      $user = user_load($account->id());
      if (isset($user->user_picture->target_id) && !empty($user->user_picture->target_id) && $file = file_load($user->user_picture->target_id)) {
        $data['avatar'] = !empty($file->getFileUri()) ? $file->getFileUri() : NULL;
      }
      elseif (!empty($user_picture_default)) {
        $data['avatar'] = $user_picture_default;
      }
      if (isset($data['avatar'])) {
        $data['avatar'] = file_create_url($data['avatar']);
      }
    }
    drupal_alter('disqus_user_data', $data);

    return $data;
  }

}
