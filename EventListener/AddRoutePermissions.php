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
      if(!empty($options['logauth'])) {
        if(is_string($options['logauth'])) { //Support for json in routing.xml
          $options['logauth'] = json_decode($options['logauth'], true);
        }
        if(is_null($options['logauth'])) {
          $options['logauth'] = FALSE;
        }

        $permissionTree['routes'][$name] = $options['logauth'];
      }
    }
    $event->insertTree($permissionTree);
  }
}

