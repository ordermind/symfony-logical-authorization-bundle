<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\EventListener;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Config\Loader\FileLoader;

use Ordermind\LogicalAuthorizationBundle\Event\AddPermissionsEventInterface;
use Ordermind\LogicalAuthorizationBundle\Routing\RouteInterface;

/**
 * Adds permissions from routes
 */
class AddRoutePermissions
{
    /**
     * @var Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @internal
     *
     * @param Symfony\Component\Routing\RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * Event listener callback for adding permissions to the tree
     *
     * @param Ordermind\LogicalAuthorizationBundle\Event\AddPermissionsEventInterface $event
     */
    public function onAddPermissions(AddPermissionsEventInterface $event)
    {
        $permissionTree = ['routes' => []];
        foreach ($this->router->getRouteCollection()->getIterator() as $name => $route) {
            if (!($route instanceof RouteInterface)) {
                continue;
            }

            $permissions = $route->getPermissions();
            if (is_null($permissions)) {
                continue;
            }

            $permissionTree['routes'][$name] = $permissions;
        }
        $event->insertTree($permissionTree);
    }
}
