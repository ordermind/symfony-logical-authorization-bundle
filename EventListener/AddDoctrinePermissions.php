<?php

namespace Ordermind\LogicalAuthorizationBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Common\Persistence\ObjectManager;

use Ordermind\LogicalAuthorizationBundle\Event\AddPermissionsEvent;
use Ordermind\LogicalAuthorizationBundle\Doctrine\Annotation\LogicalAuthorization;

class AddDoctrinePermissions implements EventSubscriberInterface {
  protected $doctrine;
  protected $annotationDriverClass;
  protected $xmlDriverClass;
  protected $ymlDriverClass;

  public function __construct($doctrine = null, $annotationDriverClass = null, $xmlDriverClass = null, $ymlDriverClass = null) {
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

    $entities = array();
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
        $propertyName = $property->getName();
        $propertyAnnotations = $annotationReader->getPropertyAnnotations($property);
        foreach ($propertyAnnotations as $annotation) {
          if ($annotation instanceof LogicalAuthorization) {
            if(!isset($permissions['models'])) $permissions['models'] = [];
            if(!isset($permissions['models'][$entityName])) $permissions['models'][$entityName] = ['fields' => []];
            $permissions['models'][$entityName]['fields'][$propertyName] = $annotation->getPermissions();
          }
        }
      }
    }
    $event->setTree($event->mergePermissions([$event->getTree(), $permissions]));
  }

  protected function addXMLPermissions(AddPermissionsEvent $event, MappingDriver $driver) {
    $classes = $driver->getAllClassNames();
    foreach($classes as $class) {
      $element = $driver->getElement($class);
      // Parse XML structure in $element
      //-----
//       print_r($element);
    }
  }

  protected function addYMLPermissions(AddPermissionsEvent $event, MappingDriver $driver) {

  }
}

