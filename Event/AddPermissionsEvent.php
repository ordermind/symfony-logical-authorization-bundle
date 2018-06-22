<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class AddPermissionsEvent extends Event implements AddPermissionsEventInterface {
  protected $tree = [];
  protected $permissionKeys;

  /**
   * @internal
   *
   * @param array $permissionKeys array of valid permission keys
   */
  public function __construct($permissionKeys) {
    $this->permissionKeys = $permissionKeys;
  }

  /**
   * {@inheritdoc}
   */
  public function getTree(): array {
    return $this->tree;
  }

  /**
   * {@inheritdoc}
   */
  public function insertTree(array $tree) {
    $this->setTree($this->mergeTrees([$this->getTree(), $tree]));
  }

  protected function setTree(array $tree) {
    $this->tree = $tree;
  }

  protected function mergeTrees(array $trees): array {
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
