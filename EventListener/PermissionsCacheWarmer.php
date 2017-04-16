<?php

namespace Ordermind\LogicalAuthorizationBundle\EventListener;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Ordermind\LogicalAuthorizationBundle\Services\PermissionTreeManagerInterface;

class PermissionsCacheWarmer implements CacheWarmerInterface {
  protected $treeManager;

  public function __construct(PermissionTreeManagerInterface $treeManager) {
    $this->treeManager = $treeManager;
  }

  public function warmUp($cacheDir) {
    $this->treeManager->getTree();
  }

  public function isOptional() {
    return true;
  }
}
