<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\PermissionProviders;

use Ordermind\LogicalAuthorizationBundle\Routing\RouteInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Provides permissions from route definitions.
 */
class RoutePermissionProvider implements PermissionProviderInterface
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @internal
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritDoc}
     */
    public function getPermissionTree(): array
    {
        $permissionTree = ['routes' => []];
        foreach ($this->router->getRouteCollection()->getIterator() as $name => $route) {
            if (!($route instanceof RouteInterface)) {
                continue;
            }

            /**
             * @var RouteInterface $route
             */
            $rawPermissionTree = $route->getRawPermissionTree();
            if (is_null($rawPermissionTree)) {
                continue;
            }

            $permissionTree['routes'][$name] = $rawPermissionTree;
        }

        return $permissionTree;
    }
}
