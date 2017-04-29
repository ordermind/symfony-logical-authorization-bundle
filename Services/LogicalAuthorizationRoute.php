<?php

namespace Ordermind\LogicalAuthorizationBundle\Services;

use Symfony\Component\Routing\RouterInterface;

use Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorizationInterface;
use Ordermind\LogicalAuthorizationBundle\Services\PermissionTreeManagerInterface;
use Ordermind\LogicalAuthorizationBundle\Services\HelperInterface;

class LogicalAuthorizationRoute implements LogicalAuthorizationRouteInterface {

  protected $la;
  protected $treeManager;
  protected $router;
  protected $helper;

  public function __construct(LogicalAuthorizationInterface $la, PermissionTreeManagerInterface $treeManager, RouterInterface $router, HelperInterface $helper) {
    $this->la = $la;
    $this->treeManager = $treeManager;
    $this->router = $router;
    $this->helper = $helper;
  }

  public function getAvailableRoutes($user = null) {
    if(is_null($user)) {
      $user = $this->helper->getCurrentUser();
    }
    $user = $this->helper->getRidOfManager($user);
    $routes = [];
    foreach($this->router->getRouteCollection()->getIterator() as $name => $route) {
      if($this->checkRouteAccess($name, $user)) {
        if(!isset($routes['routes'])) $routes['routes'] = [];
        $routes['routes'][$name] = $route;
      }
    }

    $tree = $this->treeManager->getTree();
    if(!empty($tree['route_patterns'])) {
      foreach($tree['route_patterns'] as $pattern => $permissions) {
        if($this->la->checkAccess($permissions, ['route' => $pattern, 'user' => $user])) {
          if(!isset($routes['route_patterns'])) $routes['route_patterns'] = [];
          $routes['route_patterns'][$pattern] = true;
        }
      }
    }

    return $routes;
  }

  public function checkRouteAccess($route_name, $user = null) {
    if(is_null($user)) {
      $user = $this->helper->getCurrentUser();
      if(is_null($user)) return true;
    }
    $user = $this->helper->getRidOfManager($user);

    if(!is_string($route_name)) {
      $this->helper->handleError('Error checking route access: the route_name parameter must be a string.', ['route' => $route_name, 'user' => $user]);
      return false;
    }
    if(!is_string($user) && !is_object($user)) {
      $this->helper->handleError('Error checking route access: the user parameter must be either a string or an object.', ['route' => $route_name, 'user' => $user]);
      return false;
    }

    $permissions = $this->getRoutePermissions($route_name);
    $context = ['route' => $route_name, 'user' => $user];
    return $this->la->checkAccess($permissions, $context);
  }

  protected function getRoutePermissions($route_name) {
    //If permissions are defined for an individual route, pattern permissions are completely ignored for that route.
    $tree = $this->treeManager->getTree();
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
