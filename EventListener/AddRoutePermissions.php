<?php

namespace Ordermind\LogicalAuthorizationBundle\EventListener;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Config\Loader\FileLoader;

use Ordermind\LogicalAuthorizationBundle\Event\AddPermissionsEvent;
use Ordermind\LogicalAuthorizationBundle\Routing\RouteInterface;

class AddRoutePermissions {
  protected $router;

  public function __construct(RouterInterface $router) {
    $this->router = $router;
  }

  public function onAddPermissions(AddPermissionsEvent $event) {
    $permissionTree = ['routes' => []];
    foreach($this->router->getRouteCollection()->getIterator() as $name => $route) {
      if(!($route instanceof RouteInterface)) continue;

      $logauth = $route->getLogAuth();
      if(empty($logauth)) continue;

      $permissionTree['routes'][$name] = $logauth;
    }
    $event->insertTree($permissionTree);
  }
}

