<?php

/**
 * @file
 * Contains \Drupal\disqus\Plugin\migrate\destination\DisqusComment.
 */

namespace Drupal\disqus\Plugin\migrate\destination;

use Drupal\migrate\Plugin\migrate\destination\DestinationBase;
use Drupal\migrate\Entity\MigrationInterface;
use Psr\Log\LoggerInterface;
use Drupal\Core\Entity\EntityManager;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Disqus comment destination.
 *
 * @MigrateDestination(
 *   id = "disqus_destination"
 * )
 */
class DisqusComment extends DestinationBase {

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The entity manager service.
   *
   * @var \Drupal\Core\Entity\EntityManager
   */
  protected $entityManager;

  /**
   * The disqus.settings configuration.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * Constructs Disqus comments destination plugin.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implemetation definition.
   * @param \Drupal\migrate\Entity\MigrationInterface $migration
   *   The migration.
   * @param \Drupal\Core\Entity\EntityManager $entity_manager
   *   The entity manager service.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, EntityManager $entity_manager, LoggerInterface $logger, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);
    $this->entityManager = $entity_manager;
    $this->logger = $logger;
    $this->config = $config_factory->get('disqus.settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('logger.factory')->get('disqus'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function fields(MigrationInterface $migration = NULL) {
    return array(
      'message' => $this->t('The comment body.'),
      'parent' => $this->t('Parent comment ID. If set to null, this comment is not a reply to an existing comment.'),
      'entity_id' => $this->t('The entity to which this comment belongs.'),
      'entity_type' => $this->t('The entity-type of the entity on which this comment belongs.'),
      'author_email' => $this->t("The comments author's email."),
      'author_name' => $this->t("The comments author's name."),
      'author_url' => $this->t("The comments author's url."),
      'date' => $this->t('The time that the comment was posted as a Unix timestamp.'),
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
    $disqus = disqus_api();
    if ($disqus) {
      try {
        $thread = $disqus->threads->details(array('forum' => $this->config->get('disqus_domain'), 'thread:ident' => "{$entity_type}/{$entity_id}", 'thread' => '1'));
      }
      catch (Exception $exception) {
        $this->logger->error('Error loading thread details for entity : !identifier. Check your API keys.', array('!identifier' => "{$entity_type}/{$entity_id}"));
        $thread = null;
      }
      $entity = $this->entityManager->getStorage($entity_type)->load($entity_id);

      if(!isset($thread->id)) {
        try {
        }
        catch (Exception $exception) {
          $this->logger->error('Error creating thread for entity : !identifier. Check your user access token.', array('!identifier' => "{$entity_type}/{$entity_id}"));
        }
      }
      try {
        //cannot create posts as anonymous user, needs 'api_key' (api_key is not the public key)
        $disqus->posts->create(array('message' => $row->getDestinationProperty('message'), 'thread' => $thread->id, 'author_name' => $row->getDestinationProperty('author_name'), 'author_email' => $row->getDestinationProperty('author_email'), 'author_url' => $row->getDestinationProperty('author_url'), 'date' => $row->getDestinationProperty('date'), 'ip_address' => $row->getDestinationProperty('ip_address')));
        return TRUE;
      }
      catch (Exception $exception) {
        $this->logger->error('Error creating post on thread !thread.', array('!thread' => $thread->id));
      }
      return FALSE;
    }
  }

}

