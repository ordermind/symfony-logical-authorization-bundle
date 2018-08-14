<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\Fixtures\BypassAccessChecker;

use Ordermind\LogicalPermissions\BypassAccessCheckerInterface;

class AlwaysAllow implements BypassAccessCheckerInterface {
  public function checkBypassAccess($context) {
    return TRUE;
  }
}
