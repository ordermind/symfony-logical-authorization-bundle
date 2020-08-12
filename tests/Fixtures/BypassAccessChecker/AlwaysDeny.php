<?php

namespace Ordermind\LogicalAuthorizationBundle\Test\Fixtures\BypassAccessChecker;

use Ordermind\LogicalPermissions\BypassAccessCheckerInterface;

class AlwaysDeny implements BypassAccessCheckerInterface {
  public function checkBypassAccess($context) {
    return FALSE;
  }
}
