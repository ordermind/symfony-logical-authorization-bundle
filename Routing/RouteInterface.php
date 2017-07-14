<?php

namespace Ordermind\LogicalAuthorizationBundle\Routing;

interface RouteInterface {
  public function setLogAuth($logauth);
  public function getLogAuth();
}
