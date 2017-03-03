<?php

namespace Ordermind\LogicalAuthorizationBundle\EventListener;

use Ordermind\LogicalAuthorizationBundle\Event\AddPermissionsEvent;
use Symfony\Component\Routing\RouterInterface;

class AddRoutePermissions {
  protected $router;

  public function __construct(RouterInterface $router) {
    $this->router = $router;
  }

  public function onAddPermissions(AddPermissionsEvent $event) {
    // Specific route permissions
    $permissions = ['routes' => []];
    foreach($this->router->getRouteCollection()->getIterator() as $name => $route) {
      $options = $route->getOptions();
      if(!empty($options['logical_authorization'])) {
        $permissions['routes'][$name] = $options['logical_authorization'];
      }
    }
    $event->setTree($event->mergePermissions([$event->getTree(), $permissions]));
  }
}

