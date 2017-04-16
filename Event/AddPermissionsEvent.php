<?php

namespace Ordermind\LogicalAuthorizationBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class AddPermissionsEvent extends Event {
  protected $tree = [];
  protected $permissionKeys;

  public function __construct(array $permissionKeys) {
    $this->permissionKeys = $permissionKeys;
  }

  public function setTree($tree) {
    $this->tree = $tree;
  }

  public function getTree() {
    return $this->tree;
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
