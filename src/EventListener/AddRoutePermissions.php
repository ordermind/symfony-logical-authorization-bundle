<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\EventListener;

use Ordermind\LogicalAuthorizationBundle\Event\AddPermissionsEventInterface;
use Ordermind\LogicalAuthorizationBundle\Routing\RouteInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Adds permissions from routes.
 */
class AddRoutePermissions
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @internal
     *
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * Event listener callback for adding permissions to the tree.
     *
     * @param AddPermissionsEventInterface $event
     */
    public function onAddPermissions(AddPermissionsEventInterface $event)
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
        $event->insertTree($permissionTree);
    }
}
