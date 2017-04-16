<?php

namespace Ordermind\LogicalAuthorizationBundle\Services;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

use Ordermind\LogicalAuthorizationBundle\Services\LogicalPermissionsManagerInterface;
use Ordermind\LogicalAuthorizationBundle\Services\PermissionTreeManagerInterface;
use Ordermind\LogicalAuthorizationBundle\Event\AddPermissionsEvent;

class PermissionTreeManager implements PermissionTreeManagerInterface {
  protected $tree;
  protected $appDir;
  protected $permissionKeys;
  protected $dispatcher;
  const FILENAME = 'logical_authorization.yml';

  public function __construct($appDir, LogicalPermissionsManagerInterface $lpManager, EventDispatcherInterface $dispatcher) {
    $this->appDir = $appDir;
    $this->dispatcher = $dispatcher;
    $this->permissionKeys = $lpManager->getValidPermissionKeys();
  }

  public function getTree() {
    if(is_null($this->tree)) {
      $this->generateTree();
    }
    return $this->tree;
  }

  public function generateTree() {
    $this->tree = $this->findPermissions();
  }

  protected function findPermissions() {
    $event = new AddPermissionsEvent($this);
    $this->dispatcher->dispatch('logical_authorization.add_permissions', $event);

    return $event->getTree();
  }

  public function mergePermissions($arrays = []) {
    if(count($arrays)) {
      $arr1 = array_shift($arrays);
      while(count($arrays)) {
        $arr2 = array_shift($arrays);
        foreach($arr2 as $key => $value) {
          if(in_array($key, $this->permissionKeys)) {
            $arr1 = $arr2;
            break;
          }
          if(isset($arr1[$key]) && is_array($value)) {
            $arr1[$key] = $this->mergePermissions([$arr1[$key], $arr2[$key]]);
            continue;
          }
          $arr1[$key] = $value;
        }
      }
      return $arr1;
    }
    return [];
  }
}
