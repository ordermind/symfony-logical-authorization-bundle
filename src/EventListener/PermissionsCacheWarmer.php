<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\EventListener;

use Ordermind\LogicalAuthorizationBundle\Services\PermissionTreeBuilderInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * Generates a permission tree during cache warm-up.
 */
class PermissionsCacheWarmer implements CacheWarmerInterface
{
    /**
     * @var PermissionTreeBuilderInterface
     */
    protected $treeBuilder;

    /**
     * @internal
     *
     * @param PermissionTreeBuilderInterface $treeBuilder
     */
    public function __construct(PermissionTreeBuilderInterface $treeBuilder)
    {
        $this->treeBuilder = $treeBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir)
    {
        $this->treeBuilder->getTree(true);
    }

    /**
     * {@inheritdoc}
     */
    public function isOptional(): bool
    {
        return true;
    }
}
