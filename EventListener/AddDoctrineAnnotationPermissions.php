<?php

namespace Ordermind\LogicalAuthorizationBundle\EventListener;

use Ordermind\LogicalAuthorizationBundle\Event\AddPermissionsEvent;
use Ordermind\LogicalAuthorizationBundle\Annotation\Doctrine\LogicalAuthorization;

class AddDoctrineAnnotationPermissions {
  protected $doctrine;
  protected $annotationReader;

  public function __construct($doctrine = null, $annotationReader = null) {
    $this->doctrine = $doctrine;
    $this->annotationReader = $annotationReader;
  }

  public function onAddPermissions(AddPermissionsEvent $event) {
    if(!is_null($this->doctrine) && !is_null($this->annotationReader)) {
      $entities = array();
      $em = $this->doctrine->getManager();
      $meta = $em->getMetadataFactory()->getAllMetadata();
      $permissions = [];
      foreach ($meta as $entity) {
        $entityName = $entity->getName();
        $reflectionClass = new \ReflectionClass($entityName);
        $classAnnotations = $this->annotationReader->getClassAnnotations($reflectionClass);
        foreach ($classAnnotations as $annot) {
          if ($annot instanceof LogicalAuthorization) {
            if(!isset($permissions['models'])) $permissions['models'] = [];
            $permissions['models'][$entityName] = $annot->getPermissions();
          }
        }
        foreach($reflectionClass->getProperties() as $property) {
          $propertyName = $property->getName();
          $propertyAnnotations = $this->annotationReader->getPropertyAnnotations($property);
          foreach ($propertyAnnotations as $annot) {
            if ($annot instanceof LogicalAuthorization) {
              if(!isset($permissions['models'])) $permissions['models'] = [];
              if(!isset($permissions['models'][$entityName])) $permissions['models'][$entityName] = ['fields' => []];
              $permissions['models'][$entityName]['fields'][$propertyName] = $annot->getPermissions();
            }
          }
        }
      }
      $event->setTree($event->mergePermissions([$event->getTree(), $permissions]));
    }
  }
}

