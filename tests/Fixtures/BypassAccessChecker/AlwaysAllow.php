<?php

namespace Ordermind\LogicalAuthorizationBundle\Test\Fixtures\BypassAccessChecker;

use Ordermind\LogicalPermissions\BypassAccessCheckerInterface;

class AlwaysAllow implements BypassAccessCheckerInterface {
  public function checkBypassAccess($context) {
    return TRUE;
  }
}
