<?php

namespace Ordermind\LogicalAuthorizationBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class AddPermissionsEvent extends Event implements AddPermissionsEventInterface {
  protected $tree = [];
  protected $permissionKeys;

  public function __construct($permissionKeys) {
    $this->permissionKeys = $permissionKeys;
  }

  public function getTree() {
    return $this->tree;
  }

  public function insertTree($tree) {
    if(!is_array($tree)) {
      throw new \InvalidArgumentException('Error inserting tree: The tree parameter must be an array. Current type: ' . gettype($tree));
    }

    $this->setTree($this->mergeTrees([$this->getTree(), $tree]));
  }

  protected function setTree($tree) {
    $this->tree = $tree;
  }

  protected function mergeTrees($trees) {
    if(count($trees) == 0) return [];

    $tree1 = array_shift($trees);
    while(count($trees)) {
      $tree2 = array_shift($trees);
      foreach($tree2 as $key => $value) {
        if(in_array($key, $this->permissionKeys)) {
          $tree1 = $tree2;
          break;
        }
        if(isset($tree1[$key]) && is_array($value)) {
          $tree1[$key] = $this->mergeTrees([$tree1[$key], $tree2[$key]]);
          continue;
        }
        $tree1[$key] = $value;
      }
    }

    return $tree1;
  }
}
