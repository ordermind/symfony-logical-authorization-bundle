<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\Misc\Functional\Services;

use Ordermind\LogicalAuthorizationBundle\PermissionTypes\Flag\Flags\BypassAccess as BypassAccessFlag;
use Ordermind\LogicalAuthorizationBundle\Tests\Misc\Fixtures\Entity\User\ErroneousUser;

class LogicalAuthorizationExceptionsTest extends LogicalAuthorizationMiscBase {

  /*------------ Flags ---------------*/

  /**
    * @expectedException InvalidArgumentException
    */
  public function testFlagBypassAccessWrongContextType() {
    $bypassAccess = new BypassAccessFlag();
    $bypassAccess->checkFlag(null);
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testFlagBypassAccessMissingUser() {
    $bypassAccess = new BypassAccessFlag();
    $bypassAccess->checkFlag([]);
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testFlagBypassAccessWrongUserType() {
    $bypassAccess = new BypassAccessFlag();
    $bypassAccess->checkFlag(['user' => []]);
  }

  /**
    * @expectedException UnexpectedValueException
    */
  public function testFlagBypassAccessWrongReturnType() {
    $user = new ErroneousUser();
    $bypassAccess = new BypassAccessFlag();
    $bypassAccess->checkFlag(['user' => $user]);
  }



  /*------------ LogicalAuthorization -------------*/

  /**
    * @expectedException Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException
    * @expectedExceptionMessageRegExp /^An exception was caught while checking access bypass: /
    */
  public function testCheckBypassAccess() {
    $this->la->checkBypassAccess(null);
  }


}
