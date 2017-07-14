<?php

namespace Ordermind\LogicalAuthorizationBundle\Routing;

interface RouteInterface {
  public function setPermissions($permissions);
  public function getPermissions();
}
