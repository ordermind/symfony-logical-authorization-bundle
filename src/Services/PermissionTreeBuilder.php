<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Services;

use Ordermind\LogicalAuthorizationBundle\Event\AddPermissionsEvent;
use Ordermind\LogicalPermissions\PermissionCheckerLocatorInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * {@inheritDoc}
 */
class PermissionTreeBuilder implements PermissionTreeBuilderInterface
{
    /**
     * @var array|string[]
     */
    protected $permissionKeys;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var CacheItemPoolInterface
     */
    protected $cache;

    /**
     * @var array|null
     */
    protected $tree;

    public function __construct(
        PermissionCheckerLocatorInterface $locator,
        EventDispatcherInterface $dispatcher,
        CacheItemPoolInterface $cache
    ) {
        $this->dispatcher = $dispatcher;
        $this->permissionKeys = $locator->getValidPermissionTreeKeys();
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
