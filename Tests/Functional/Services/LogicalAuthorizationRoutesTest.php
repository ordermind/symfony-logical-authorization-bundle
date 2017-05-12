<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\Functional\Services;

class LogicalAuthorizationRoutesTest extends LogicalAuthorizationBase {
  public function testRouteRoleAllow() {
    $this->sendRequestAs('GET', '/test/route-role', [], static::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
  }

  public function testRouteRoleDisallow() {
    $this->sendRequestAs('GET', '/test/route-role', [], static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(403, $response->getStatusCode());
  }

  public function testRouteBypassActionAllow() {
    $this->sendRequestAs('GET', '/test/route-role', [], static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
  }

  public function testRouteBypassActionDisallow() {
    $this->sendRequestAs('GET', '/test/route-no-bypass', [], static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(403, $response->getStatusCode());
  }

  public function testRouteHasAccountAllow() {
    $this->sendRequestAs('GET', '/test/route-has-account', [], static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
  }

  public function testRouteHasAccountDisallow() {
    $this->sendRequestAs('GET', '/test/route-has-account', []);
    $response = $this->client->getResponse();
    $this->assertEquals(403, $response->getStatusCode());
  }

  public function testYmlRouteAllow() {
    $this->sendRequestAs('GET', '/test/route-yml', [], static::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
  }

  public function testYmlRouteDisallow() {
    $this->sendRequestAs('GET', '/test/route-yml', [], static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(403, $response->getStatusCode());
  }

  public function testXmlRouteAllow() {
    $this->sendRequestAs('GET', '/test/route-xml', [], static::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
  }

  public function testXmlRouteDisallow() {
    $this->sendRequestAs('GET', '/test/route-xml', [], static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(403, $response->getStatusCode());
  }

  public function testRoutePatternAllow() {
    $this->sendRequestAs('GET', '/test/pattern-allowed', []);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
  }

  public function testRoutePatternDeny() {
    $this->sendRequestAs('GET', '/test/route-forbidden', [], static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(403, $response->getStatusCode());
  }

  public function testRoutePatternOverriddenAllow() {
    $this->sendRequestAs('GET', '/test/route-allowed', []);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
  }

  public function testRoutePatternOverriddenDeny() {
    $this->sendRequestAs('GET', '/test/pattern-forbidden', [], static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(403, $response->getStatusCode());
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