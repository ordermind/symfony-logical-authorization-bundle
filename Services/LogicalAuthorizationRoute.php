<?php

namespace Ordermind\LogicalAuthorizationBundle\Services;

use Symfony\Component\Routing\RouterInterface;

use Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorizationInterface;
use Ordermind\LogicalAuthorizationBundle\Services\PermissionTreeManagerInterface;
use Ordermind\LogicalAuthorizationBundle\Services\UserHelperInterface;

class LogicalAuthorizationRoute implements LogicalAuthorizationRouteInterface {

  protected $la;
  protected $treeManager;
  protected $router;
  protected $userHelper;

  public function __construct(LogicalAuthorizationInterface $la, PermissionTreeManagerInterface $treeManager, RouterInterface $router, UserHelperInterface $userHelper) {
    $this->la = $la;
    $this->treeManager = $treeManager;
    $this->router = $router;
    $this->userHelper = $userHelper;
  }

  public function checkRouteAccess($routeName, $user = null) {
    if(is_null($user)) {
      $user = $this->userHelper->getCurrentUser();
      if(is_null($user)) return true;
    }
    $user = $this->la->getRidOfManager($user);

    if(!is_string($routeName)) {
      $this->la->handleError('Error checking route access: the routeName parameter must be a string.', ['route' => $routeName, 'user' => $user]);
      return false;
    }
    if(!is_string($user) && !is_object($user)) {
      $this->la->handleError('Error checking model access: the user parameter must be either a string or an object.', ['route' => $routeName, 'user' => $user]);
      return false;
    }

    $permissions = $this->getRoutePermissions($routeName);
    if($permissions) {
      $context = ['route' => $routeName, 'user' => $user];
      return $this->la->checkAccess($permissions, $context);
    }
    return true;
  }

  protected function getRoutePermissions($routeName) {
    //If permissions are defined for an individual route, pattern permissions are completely ignored for that route.
    $tree = $this->treeManager->getTree();
    //Check individual route permissions
    if(!empty($tree['routes'][$routeName])) {
      return $tree['routes'][$routeName];
    }
    //Check pattern permissions
    if(!empty($tree['route_patterns'])) {
      $route = $this->router->getRouteCollection()->get($routeName);
      if($route) {
        $routePath = $route->getPath();
        foreach($tree['route_patterns'] as $pattern => $permissions) {
          if(preg_match("#$pattern#", $routePath)) {
            return $permissions;
          }
        }
      }
    }
    return [];
  }
}
