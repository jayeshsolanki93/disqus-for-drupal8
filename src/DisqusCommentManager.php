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

}
