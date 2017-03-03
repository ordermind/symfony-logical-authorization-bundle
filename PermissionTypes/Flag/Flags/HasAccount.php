<?php

namespace Ordermind\LogicalAuthorizationBundle\PermissionTypes\Flag\Flags;

use Ordermind\LogicalAuthorizationBundle\PermissionTypes\Flag\FlagInterface;

class HasAccount implements FlagInterface {
  public function getName() {
    return 'has_account';
  }

  public function checkFlag($context) {
    $user = $context['user'];
    if(is_string($user)) { //Anonymous user
      return false;
    }
    return true;
  }
}
