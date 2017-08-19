<?php

namespace Ordermind\LogicalAuthorizationBundle\Twig;

use Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorizationModelInterface;
use Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorizationRouteInterface;

class LogicalAuthorizationExtension extends \Twig_Extension {
  protected $laRoute;
  protected $laModel;

  public function __construct(LogicalAuthorizationRouteInterface $laRoute, LogicalAuthorizationModelInterface $laModel) {
    $this->laRoute = $laRoute;
    $this->laModel = $laModel;
  }

  public function getFunctions() {
    return array(
      new \Twig_Function('logauth.check_route_access', array($this, 'checkRouteAccess')),
      new \Twig_Function('logauth.check_model_access', array($this, 'checkModelAccess')),
      new \Twig_Function('logauth.check_field_access', array($this, 'checkFieldAccess')),
    );
  }

  public function checkRouteAccess($route_name, $user = null) {
    return $this->laRoute->checkRouteAccess($route_name, $user);
  }

  public function checkModelAccess($model, $action, $user = null) {
    return $this->laModel->checkModelAccess($model, $action, $user);
  }

  public function checkFieldAccess($model, $field_name, $action, $user = null) {
    return $this->laModel->checkFieldAccess($model, $field_name, $action, $user);
  }
}
