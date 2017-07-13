<?php

namespace Ordermind\LogicalAuthorizationBundle\EventListener;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Config\Loader\FileLoader;

use Ordermind\LogicalAuthorizationBundle\Event\AddPermissionsEvent;

class AddRoutePermissions {
  protected $router;

  public function __construct(RouterInterface $router) {
    $this->router = $router;
  }

  public function onAddPermissions(AddPermissionsEvent $event) {
    $permissionTree = ['routes' => []];
    foreach($this->router->getRouteCollection()->getIterator() as $name => $route) {
      $options = $route->getOptions();
      if(!empty($options['logauth_permissions'])) {
        if(is_string($options['logauth_permissions'])) { //Support for json in routing.xml
          $options['logauth_permissions'] = json_decode($options['logauth_permissions'], true);
        }
        if(is_null($options['logauth_permissions'])) {
          $options['logauth_permissions'] = FALSE;
        }

        $permissionTree['routes'][$name] = $options['logauth_permissions'];
      }
    }
    $event->insertTree($permissionTree);
  }
}

