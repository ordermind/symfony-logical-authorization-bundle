<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\EventListener;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Ordermind\LogicalAuthorizationBundle\Services\PermissionTreeBuilderInterface;

class PermissionsCacheWarmer implements CacheWarmerInterface {
  protected $treeBuilder;

  public function __construct(PermissionTreeBuilderInterface $treeBuilder) {
    $this->treeBuilder = $treeBuilder;
  }

  /**
   * {@inheritdoc}
   */
  public function warmUp($cacheDir) {
    $this->treeBuilder->getTree(TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function isOptional(): bool {
    return true;
  }
}
