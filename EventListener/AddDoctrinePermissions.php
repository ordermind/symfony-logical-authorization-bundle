<?php

namespace Ordermind\LogicalAuthorizationBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;

use Ordermind\LogicalAuthorizationBundle\Event\AddPermissionsEvent;
use Ordermind\LogicalAuthorizationBundle\Doctrine\Annotation\LogicalAuthorization;

class AddDoctrinePermissions implements EventSubscriberInterface {
  protected $doctrine;
  protected $annotationDriverClass;
  protected $xmlDriverClass;
  protected $ymlDriverClass;

  public function __construct(ManagerRegistry $doctrine = null, $annotationDriverClass = null, $xmlDriverClass = null, $ymlDriverClass = null) {
    $this->doctrine = $doctrine;
    $this->annotationDriverClass = $annotationDriverClass;
    $this->xmlDriverClass = $xmlDriverClass;
    $this->ymlDriverClass = $ymlDriverClass;
  }

  public static function getSubscribedEvents() {
    return array(
      'logical_authorization.add_permissions' => array(
        array(
          'addPermissions',
        ),
      ),
    );
  }

  public function addPermissions(AddPermissionsEvent $event) {
    if(is_null($this->doctrine)) return;

    $object_managers = $this->doctrine->getManagers();
    foreach($object_managers as $om) {
      $metadataDriverImplementation = $om->getConfiguration()->getMetadataDriverImpl();
      $drivers = $metadataDriverImplementation->getDrivers();
      foreach($drivers as $driver) {
        $driver_class = get_class($driver);
        if($driver_class === $this->annotationDriverClass) {
          $this->addAnnotationPermissions($event, $driver, $om);
        }
        elseif($driver_class === $this->xmlDriverClass) {
          $this->addXMLPermissions($event, $driver);
        }
        elseif($driver_class === $this->ymlDriverClass) {
          $this->addYMLPermissions($event, $driver);
        }
      }
    }
  }

  protected function addAnnotationPermissions(AddPermissionsEvent $event, MappingDriver $driver, ObjectManager $om) {
    $classes = $driver->getAllClassNames();
    $annotationReader = $driver->getReader();
    $permissions = [];
    foreach($classes as $class) {
      $metadata = $om->getClassMetadata($class);
      $entityName = $metadata->getName();
      $reflectionClass = new \ReflectionClass($entityName);
      $classAnnotations = $annotationReader->getClassAnnotations($reflectionClass);
      foreach ($classAnnotations as $annotation) {
        if ($annotation instanceof LogicalAuthorization) {
          if(!isset($permissions['models'])) $permissions['models'] = [];
          $permissions['models'][$entityName] = $annotation->getPermissions();
        }
      }
      foreach($reflectionClass->getProperties() as $property) {
        $fieldName = $property->getName();
        $propertyAnnotations = $annotationReader->getPropertyAnnotations($property);
        foreach ($propertyAnnotations as $annotation) {
          if ($annotation instanceof LogicalAuthorization) {
            if(!isset($permissions['models'])) $permissions['models'] = [];
            if(!isset($permissions['models'][$entityName])) $permissions['models'][$entityName] = ['fields' => []];
            $permissions['models'][$entityName]['fields'][$fieldName] = $annotation->getPermissions();
          }
        }
      }
    }
    $event->setTree($event->mergePermissions([$event->getTree(), $permissions]));
  }

  protected function addXMLPermissions(AddPermissionsEvent $event, MappingDriver $driver) {
    $classes = $driver->getAllClassNames();
    $permissions = [];
    foreach($classes as $class) {
      $xmlRoot = $driver->getElement($class);
      $entityName = (string) $xmlRoot['name'];
      // Parse XML structure in $element
      if(isset($xmlRoot->{'logical-authorization'})) {
        if(!isset($permissions['models'])) $permissions['models'] = [];
        $permissions['models'][$entityName] = $this->parseXMLPermissionsElementRecursive($xmlRoot->{'logical-authorization'});
      }
      if(isset($xmlRoot->field)) {
        foreach($xmlRoot->field as $field) {
          $fieldName = (string) $field['name'];
          if(isset($field->{'logical-authorization'})) {
            if(!isset($permissions['models'])) $permissions['models'] = [];
            if(!isset($permissions['models'][$entityName])) $permissions['models'][$entityName] = ['fields' => []];
            $permissions['models'][$entityName]['fields'][$fieldName] = $this->parseXMLPermissionsElementRecursive($field->{'logical-authorization'});
          }
        }
      }
    }
    $event->setTree($event->mergePermissions([$event->getTree(), $permissions]));
  }
  protected function parseXMLPermissionsElementRecursive($element) {
    $permissions = [];
    $children = $element->children();
    foreach($children as $key => $child) {
      if($subchildren = $child->children()) {
        $parsed_child = $this->parseXMLPermissionsElementRecursive($child);
      }
      else {
        $str_child = (string) $child;
        $lowercase_child = strtolower($str_child);
        if($lowercase_child === 'true') {
          $parsed_child = TRUE;
        }
        elseif($lowercase_child === 'false') {
          $parsed_child = FALSE;
        }
        else {
          $parsed_child = $str_child;
        }
      }
      if($key === 'value') {
        $permissions[] = $parsed_child;
      }
      else {
        $permissions[$key] = $parsed_child;
      }
    }

    return $permissions;
  }

  protected function addYMLPermissions(AddPermissionsEvent $event, MappingDriver $driver) {

  }
}

