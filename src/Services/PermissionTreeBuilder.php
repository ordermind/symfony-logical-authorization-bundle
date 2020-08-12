<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Services;

use Psr\Cache\CacheItemPoolInterface;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

use Ordermind\LogicalAuthorizationBundle\Services\LogicalPermissionsProxyInterface;
use Ordermind\LogicalAuthorizationBundle\Services\PermissionTreeBuilderInterface;
use Ordermind\LogicalAuthorizationBundle\Event\AddPermissionsEvent;

/**
 * {@inheritdoc}
 */
class PermissionTreeBuilder implements PermissionTreeBuilderInterface
{
    protected $permissionKeys;
    protected $dispatcher;
    protected $cache;
    protected $tree;

  /**
   * @internal
   *
   * @param Ordermind\LogicalAuthorizationBundle\Services\LogicalPermissionsProxyInterface $lpProxy    LogicalPermissionsProxy service
   * @param Symfony\Component\EventDispatcher\EventDispatcherInterface                     $dispatcher Event dispatcher service
   * @param Psr\Cache\CacheItemPoolInterface                                               $cache      Caching service
   */
    public function __construct(LogicalPermissionsProxyInterface $lpProxy, EventDispatcherInterface $dispatcher, CacheItemPoolInterface $cache)
    {
        $this->dispatcher = $dispatcher;
        $this->permissionKeys = $lpProxy->getValidPermissionKeys();
        $this->cache = $cache;
    }

  /**
   * {@inheritdoc}
   */
    public function getTree(bool $reset = false, bool $debug = false): array
    {
        if (!$reset && !is_null($this->tree)) {
            $tree = $this->tree;
            if ($debug) {
                $tree['fetch'] = 'static_cache';
            }

            return $tree;
        }

        if (!$reset && !is_null($tree = $this->loadTreeFromCache())) {
            $this->tree = $tree;
            if ($debug) {
                $tree['fetch'] = 'cache';
            }

            return $tree;
        }

        $tree = $this->loadTreeFromEvent();
        ksort($tree);
        $this->saveTreeToCache($tree);
        $this->tree = $tree;

        if ($debug) {
            $tree['fetch'] = 'no_cache';
        }

        return $tree;
    }

  /**
   * @internal
   *
   * @return ?array
   */
    protected function loadTreeFromCache(): ?array
    {
        $cachedTree = $this->cache->getItem('ordermind.logauth.permissions');
        if ($cachedTree->isHit()) {
            return $cachedTree->get();
        }

        return null;
    }

  /**
   * @internal
   *
   * @param array $tree
   */
    protected function saveTreeToCache(array $tree)
    {
        $cachedTree = $this->cache->getItem('ordermind.logauth.permissions');
        $cachedTree->set($tree);
        $this->cache->save($cachedTree);
    }

  /**
   * @internal
   *
   * @return array
   */
    protected function loadTreeFromEvent(): array
    {
        $event = new AddPermissionsEvent($this->permissionKeys);
        $this->dispatcher->dispatch($event, 'logauth.add_permissions');

        return $event->getTree();
    }
}
