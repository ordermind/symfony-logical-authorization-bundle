<?php

namespace Ordermind\LogicalAuthorizationBundle\Services;

use Symfony\Component\Routing\RouterInterface;

use Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorizationInterface;
use Ordermind\LogicalAuthorizationBundle\Services\PermissionTreeBuilderInterface;
use Ordermind\LogicalAuthorizationBundle\Services\HelperInterface;
use Ordermind\LogicalAuthorizationBundle\DataCollector\CollectorInterface;

class LogicalAuthorizationRoute implements LogicalAuthorizationRouteInterface {

  protected $la;
  protected $treeBuilder;
  protected $router;
  protected $helper;
  protected $debugCollector;

  public function __construct(LogicalAuthorizationInterface $la, PermissionTreeBuilderInterface $treeBuilder, RouterInterface $router, HelperInterface $helper, CollectorInterface $debugCollector = null) {
    $this->la = $la;
    $this->treeBuilder = $treeBuilder;
    $this->router = $router;
    $this->helper = $helper;
    $this->debugCollector = $debugCollector;
  }

  public function getAvailableRoutes($user = null) {
    if(is_null($user)) {
      $user = $this->helper->getCurrentUser();
    }

    $routes = [];
    foreach($this->router->getRouteCollection()->getIterator() as $route_name => $route) {
      if(!$this->checkRouteAccess($route_name, $user)) continue;

      if(!isset($routes['routes'])) $routes['routes'] = [];
      $routes['routes'][$route->getPath()] = $route->getPath();
    }

    $tree = $this->treeBuilder->getTree();
    if(!empty($tree['route_patterns'])) {
      foreach($tree['route_patterns'] as $pattern => $permissions) {
        if(!$this->la->checkAccess($permissions, ['user' => $user])) continue;

        if(!isset($routes['route_patterns'])) $routes['route_patterns'] = [];
        $routes['route_patterns'][$pattern] = $pattern;
      }
    }

    return $routes;
  }

  public function checkRouteAccess($route_name, $user = null) {
    if(is_null($user)) {
      $user = $this->helper->getCurrentUser();
      if(is_null($user)) return true;
    }

    if(!is_null($this->debugCollector)) {
      $this->debugCollector->addPermissionCheckAttempt('route', $route_name, $user);
    }

    if(!is_string($route_name)) {
      $this->helper->handleError('Error checking route access: the route_name parameter must be a string.', ['route' => $route_name, 'user' => $user]);
      return false;
    }
    if(!$route_name) {
      $this->helper->handleError('Error checking route access: the route_name parameter cannot be empty.', ['route' => $route_name, 'user' => $user]);
      return false;
    }
    if(!is_string($user) && !is_object($user)) {
      $this->helper->handleError('Error checking route access: the user parameter must be either a string or an object.', ['route' => $route_name, 'user' => $user]);
      return false;
    }

    $route = $this->router->getRouteCollection()->get($route_name);
    if(is_null($route)) {
      $this->helper->handleError('Error checking route access: the route could not be found.', ['route' => $route_name, 'user' => $user]);
      return false;
    }

    $permissions = $this->getRoutePermissions($route_name);

    if(!is_null($this->debugCollector)) {
      $this->debugCollector->addPermissionCheck('route', $route_name, $user, $permissions);
    }

    $context = ['route' => $route_name, 'user' => $user];

    return $this->la->checkAccess($permissions, $context);
  }

  protected function getRoutePermissions($route_name) {
    //If permissions are defined for an individual route, pattern permissions are completely ignored for that route.
    $tree = $this->treeBuilder->getTree();

    //Check individual route permissions
    if(!empty($tree['routes']) && array_key_exists($route_name, $tree['routes'])) {
      return $tree['routes'][$route_name];
    }

    //Check pattern permissions
    if(!empty($tree['route_patterns'])) {
      $route = $this->router->getRouteCollection()->get($route_name);
      if($route) {
        $route_path = $route->getPath();
        foreach($tree['route_patterns'] as $pattern => $permissions) {
          if(preg_match("@$pattern@", $route_path)) {
            return $permissions;
          }
        }
      }
    }

    return [];
  }
}
