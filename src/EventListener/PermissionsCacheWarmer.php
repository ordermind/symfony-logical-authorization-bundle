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

    public function __construct(PermissionTreeBuilderInterface $treeBuilder)
    {
        $this->treeBuilder = $treeBuilder;
    }

    /**
     * {@inheritDoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function warmUp(string $cacheDir): array
    {
        $this->treeBuilder->getTree(true);

        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function isOptional(): bool
    {
        return true;
    }
}
