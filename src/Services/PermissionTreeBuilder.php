<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Services;

use Psr\Cache\CacheItemPoolInterface;

/**
 * {@inheritDoc}
 */
class PermissionTreeBuilder implements PermissionTreeBuilderInterface
{
    protected PermissionCollector $permissionCollector;

    protected CacheItemPoolInterface $cache;

    protected ?array $tree = null;

    public function __construct(
        PermissionCollector $permissionCollector,
        CacheItemPoolInterface $cache
    ) {
        $this->permissionCollector = $permissionCollector;
        $this->cache = $cache;
    }

    /**
     * {@inheritDoc}
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

        $tree = $this->permissionCollector->getPermissionTree();
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
     */
    protected function saveTreeToCache(array $tree): void
    {
        $cachedTree = $this->cache->getItem('ordermind.logauth.permissions');
        $cachedTree->set($tree);
        $this->cache->save($cachedTree);
    }
}
