<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\EventListener;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Ordermind\LogicalAuthorizationBundle\Services\PermissionTreeBuilderInterface;

/**
 * Generates a permission tree during cache warm-up
 */
class PermissionsCacheWarmer implements CacheWarmerInterface
{
    /**
     * @var Ordermind\LogicalAuthorizationBundle\Services\PermissionTreeBuilderInterface
     */
    protected $treeBuilder;

    /**
     * @internal
     *
     * @param Ordermind\LogicalAuthorizationBundle\Services\PermissionTreeBuilderInterface $treeBuilder
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
