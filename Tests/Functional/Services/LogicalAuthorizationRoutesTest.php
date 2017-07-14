<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\Functional\Services;

class LogicalAuthorizationRoutesTest extends LogicalAuthorizationBase {
  public function testRouteRoleAllow() {
    $this->sendRequestAs('GET', '/test/route-role', [], static::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
  }

  /**
    * @expectedException Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
    */
  public function testRouteRoleDisallow() {
    $this->sendRequestAs('GET', '/test/route-role', [], static::$authenticated_user);
  }

  public function testRouteBypassActionAllow() {
    $this->sendRequestAs('GET', '/test/route-role', [], static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
  }

  /**
    * @expectedException Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
    */
  public function testRouteBypassActionDisallow() {
    $this->sendRequestAs('GET', '/test/route-no-bypass', [], static::$superadmin_user);
  }

  public function testRouteHasAccountAllow() {
    $this->sendRequestAs('GET', '/test/route-has-account', [], static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
  }

  /**
    * @expectedException Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
    */
  public function testRouteHasAccountDisallow() {
    $this->sendRequestAs('GET', '/test/route-has-account', []);
  }

  public function testYmlRouteAllow() {
    $this->sendRequestAs('GET', '/test/route-yml', [], static::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
  }

  /**
    * @expectedException Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
    */
  public function testYmlRouteDisallow() {
    $this->sendRequestAs('GET', '/test/route-yml', [], static::$authenticated_user);
  }

  public function testYmlRouteBoolAllow() {
    $this->sendRequestAs('GET', '/test/route-yml-allowed', []);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
  }

  /**
    * @expectedException Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
    */
  public function testYmlRouteBoolDisallow() {
    $this->sendRequestAs('GET', '/test/route-yml-denied', [], static::$admin_user);
  }

  public function testXmlRouteAllow() {
    $this->sendRequestAs('GET', '/test/route-xml', [], static::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
  }

  /**
    * @expectedException Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
    */
  public function testXmlRouteDisallow() {
    $this->sendRequestAs('GET', '/test/route-xml', [], static::$authenticated_user);
  }

  public function testXmlRouteBoolAllow() {
    $this->sendRequestAs('GET', '/test/route-xml-allowed', []);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
  }

  /**
    * @expectedException Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
    */
  public function testXmlRouteBoolDisallow() {
    $this->sendRequestAs('GET', '/test/route-xml-denied', [], static::$admin_user);
  }

  public function testRouteBoolAllow() {
    $this->sendRequestAs('GET', '/test/pattern-allowed', []);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
  }

  /**
    * @expectedException Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
    */
  public function testRouteBoolDeny() {
    $this->sendRequestAs('GET', '/test/route-denied', [], static::$admin_user);
  }

  /**
    * @expectedException Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
    */
  public function testRoutePatternDenyAll() {
    $this->sendRequestAs('GET', '/test/route-forbidden', [], static::$superadmin_user);
  }

  public function testRoutePatternOverriddenAllow() {
    $this->sendRequestAs('GET', '/test/route-allowed', []);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
  }

  /**
    * @expectedException Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
    */
  public function testRoutePatternOverriddenDeny() {
    $this->sendRequestAs('GET', '/test/pattern-forbidden', [], static::$superadmin_user);
  }

  public function testAvailableRoutesAnonymous() {
    $this->sendRequestAs('GET', '/test/count-available-routes', []);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $routes_count = $response->getContent();
    $this->assertGreaterThan(3, $routes_count);
  }

  public function testAvailableRoutesAuthenticated() {
    $this->sendRequestAs('GET', '/test/count-available-routes', [], static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $routes_count = $response->getContent();
    $this->assertGreaterThan(4, $routes_count);
  }

  public function testAvailableRoutesAdmin() {
    $this->sendRequestAs('GET', '/test/count-available-routes', [], static::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $routes_count = $response->getContent();
    $this->assertGreaterThan(5, $routes_count);
  }

  public function testAvailableRoutesSuperadmin() {
    $this->sendRequestAs('GET', '/test/count-available-routes', [], static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $routes_count = $response->getContent();
    $this->assertGreaterThan(5, $routes_count);
  }

  public function testAvailableRoutePatternsAnonymous() {
    $this->sendRequestAs('GET', '/test/count-available-route-patterns', []);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $routes_count = $response->getContent();
    $this->assertEquals(1, $routes_count);
  }

  public function testAvailableRoutePatternsAuthenticated() {
    $this->sendRequestAs('GET', '/test/count-available-route-patterns', [], static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $routes_count = $response->getContent();
    $this->assertEquals(1, $routes_count);
  }

  public function testAvailableRoutePatternsAdmin() {
    $this->sendRequestAs('GET', '/test/count-available-route-patterns', [], static::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $routes_count = $response->getContent();
    $this->assertEquals(1, $routes_count);
  }

  public function testAvailableRoutePatternsSuperadmin() {
    $this->sendRequestAs('GET', '/test/count-available-route-patterns', [], static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $routes_count = $response->getContent();
    $this->assertEquals(1, $routes_count);
  }
}