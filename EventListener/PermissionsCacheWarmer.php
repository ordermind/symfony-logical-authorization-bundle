<?php

namespace Ordermind\LogicalAuthorizationBundle\EventListener;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Ordermind\LogicalAuthorizationBundle\Services\PermissionTreeBuilderInterface;

class PermissionsCacheWarmer implements CacheWarmerInterface {
  protected $treeBuilder;

  public function __construct(PermissionTreeBuilderInterface $treeBuilder) {
    $this->treeBuilder = $treeBuilder;
  }

  public function warmUp($cacheDir) {
    $this->treeBuilder->getTree(TRUE);
  }

  public function isOptional() {
    return true;
  }
}
