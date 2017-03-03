<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\ORM\Functional\Services;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\BrowserKit\Cookie;

use Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorization;

class LogicalAuthorizationRoute extends WebTestCase
{

  /**
   * @expectedException Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException
   */
  public function testCheckRouteAccessWrongRoutenameType() {
    $admin = self::$admin_user;
    $this->laRoute->checkRouteAccess(true, $admin);
  }

}
