<?php

/**
 *  @file
 *  Contains \Drupal\comment\DisqusCommentManager.
 */

namespace Drupal\disqus;

use Drupal\Core\Entity\EntityManagerInterface;

class DisqusCommentManager {
  
  protected $entityManager;
  
  /**
   * Constructs the DisqusCommentManager object.
   * 
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   */
  public function __construct(EntityManagerInterface $entity_manager) {
    $this->entityManager = $entity_manager;
  }
  
  public function getFields($entity_type_id) {
    $entity_type = $this->entityManager->getDefinition($entity_type_id);
    if (!$entity_type->isSubclassOf('\Drupal\Core\Entity\ContentEntityInterface')) {
      return array();
    }
    
    $map = $this->getAllFields();
    return isset($map[$entity_type_id]) ? $map[$entity_type_id] : array();
  }
  
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
?>
