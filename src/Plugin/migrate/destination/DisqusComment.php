<?php

/**
 * @file
 * Contains \Drupal\disqus\Plugin\migrate\destination\DisqusComment.
 */

namespace Drupal\disqus\Plugin\migrate\destination;

use Drupal\migrate\Plugin\migrate\destination\DestinationBase;

/**
 * Disqus comment destination.
 *
 * @MigrateDestination(
 *   id = "disqus_comment_destination"
 * )
 */

class DisqusComment extends DestinationBase {

  /**
   * {@inheritdoc}
   */
  public function fields(MigrationInterface $migration = NULL) {
    return array(
      'message' => $this->t('The comment body.'),
      'parent' => $this->t('Parent comment ID. If set to null, this comment is not a reply to an existing comment.'),
      'entity_id' => $this->t('The entity to which this comment is a reply.'),
      'entity_type' => $this->t('The entity-type of the entity on which this comment is a reply.'),
      'author_email' => $this->t("The comments author's email."),
      'author_name' => $this->t("The comments author's name."),
      'author_url' => $this->t("The comments author's url."),
      'date' => $this->t('The time that the comment was created as a Unix timestamp.'),
      'ip_address' => $this->t("The IP address that the comment was posted from."),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['message']['type'] = 'string';
		return $ids;
  }

  /**
   * {@inheritdoc}
   */
  public function import(Row $row, array $old_destination_id_values = array()) {
    $entity_type = $row->getDestinationProperty('entity_type');
    $entity_id = $row->getDestinationProperty('entity_id');
    $config = \Drupal::config('disqus_settings');
    $disqus = disqus_api();
    if ($disqus) {
      try {
        $thread = $disqus->threads->details(array('forum' => $config->get('disqus_domain'), 'thread:ident' => "{$entity_type}/{$entity_id}", 'thread' => '1'));
      }
      catch (Exception $exception) {
        \Drupal::logger('disqus')->error('Error loading thread details for entity : !identifier. Check your API keys.', array('!identifier' => "{$entity_type}/{$entity_id}"));
        $thread = null;
      }
      $entity = entity_load($entity_type, $entity_id);
      if(!isset($thread->id)) {
        try {
          $thread = $disqus->threads->create(array('forum' => $config->get('disqus_domain'), 'access_token' => $config->get('advanced.disqus_useraccesstoken'), 'title' => $entity->label(), 'url' => $entity->url('canonical',array('absolute' => TRUE)), 'identifier' => "{$entity_type}/{$entity_id}"));
        }
        catch (Exception $exception) {
          \Drupal::logger('disqus')->error('Error creating thread for entity : !identifier. Check your user access token.', array('!identifier' => "{$entity_type}/{$entity_id}"));
        }
      }
      try {
// cannot create posts as anonymous user, needs 'api_key' (api_key is not the public key)
        $disqus->posts->create(array('message' => $row->getDestinationProperty('message'), 'thread' => $thread->id, 'author_name' => $row->getDestinationProperty('author_name'), 'author_email' => $row->getDestinationProperty('author_email'), 'author_url' => $row->getDestinationProperty('author_url'), 'date' => $row->getDestinationProperty('date'), 'ip_address' => $row->getDestinationProperty('ip_address')));
        return TRUE;
      }
      catch (Exception $exception) {
        \Drupal::logger('disqus')->error('Error creating post on thread !thread.', array('!thread' => $thread->id));
      }
      return FALSE;
    }
  }

  /**
   * Creates an instance of the Disqus PHP API.
   *
   * @return
   *   The instance of the Disqus API.
   */
  public function disqus_api() {
    try {
      $disqus = new DisqusAPI(\Drupal::config('disqus.settings')->get('advanced.disqus_secretkey'));
    }
    catch (Exception $exception) {
      drupal_set_message(t('There was an error loading the Disqus PHP API. Please check your API keys and try again.'), 'error');
      \Drupal::logger('disqus')->error('Error loading the Disqus PHP API. Check your API keys.', array());
      return FALSE;
    }
    return $disqus;
  }
}

