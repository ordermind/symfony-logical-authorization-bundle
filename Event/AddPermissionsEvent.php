<?php

namespace Ordermind\LogicalAuthorizationBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class AddPermissionsEvent extends Event {
  protected $tree = [];
  protected $treeManager;

  public function __construct($treeManager) {
    $this->treeManager = $treeManager;
  }

  public function setTree($tree) {
    $this->tree = $tree;
  }

  public function getTree() {
    return $this->tree;
  }

  public function mergePermissions($arrays = []) {
    return $this->treeManager->mergePermissions($arrays);
  }
}
