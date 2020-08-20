<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Test\Functional\Services;

class LogicalAuthorizationRouteTest extends LogicalAuthorizationBase
{
    public function testRouteNoPermissionsAllow()
    {
        $this->sendRequestAs('GET', '/test/no-permissions');
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRouteRoleAllow()
    {
        $this->sendRequestAs('GET', '/test/route-role', [], static::$userAdmin);
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRouteRoleMultipleAllow()
    {
        $this->sendRequestAs('GET', '/test/route-role-multiple', [], static::$userAdmin);
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRouteRoleDisallow()
    {
        $this->sendRequestAs('GET', '/test/route-role', [], static::$userAuthenticated);
        $response = $this->client->getResponse();
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testRouteBypassActionAllow()
    {
        $this->sendRequestAs('GET', '/test/route-role', [], static::$userSuperadmin);
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRouteBypassActionDisallow()
    {
        $this->sendRequestAs('GET', '/test/route-no-bypass', [], static::$userSuperadmin);
        $response = $this->client->getResponse();
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testRouteHostAllow()
    {
        $this->client->setServerParameters(['HTTP_HOST' => 'test.com']);
        $headers = [
            'PHP_AUTH_USER' => static::$userAuthenticated->getUsername(),
            'PHP_AUTH_PW'   => $this->userCredentials[static::$userAuthenticated->getUsername()],
        ];
        $this->client->request('GET', '/test/route-host', [], [], $headers);
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRouteHostDisallow()
    {
        $this->client->setServerParameters(['HTTP_HOST' => 'test.se']);
        $headers = [
            'PHP_AUTH_USER' => static::$userAuthenticated->getUsername(),
            'PHP_AUTH_PW'   => $this->userCredentials[static::$userAuthenticated->getUsername()],
        ];
        $this->client->request('GET', '/test/route-host', [], [], $headers);
        $response = $this->client->getResponse();
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testRouteMethodAllow()
    {
        $this->sendRequestAs('GET', '/test/route-method', [], static::$userAdmin);
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRouteMethodLowercaseAllow()
    {
        $this->sendRequestAs('GET', '/test/route-method-lowercase', [], static::$userAdmin);
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRouteMethodDisallow()
    {
        $this->sendRequestAs('PUSH', '/test/route-method', [], static::$userAuthenticated);
        $response = $this->client->getResponse();
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testRouteIpAllow()
    {
        $this->client->setServerParameters(['REMOTE_ADDR' => '127.0.0.1']);
        $headers = [
            'PHP_AUTH_USER' => static::$userAuthenticated->getUsername(),
            'PHP_AUTH_PW'   => $this->userCredentials[static::$userAuthenticated->getUsername()],
        ];
        $this->client->request('GET', '/test/route-ip', [], [], $headers);
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRouteIpDisallow()
    {
        $this->client->setServerParameters(['REMOTE_ADDR' => '127.0.0.55']);
        $headers = [
            'PHP_AUTH_USER' => static::$userAuthenticated->getUsername(),
            'PHP_AUTH_PW'   => $this->userCredentials[static::$userAuthenticated->getUsername()],
        ];
        $this->client->request('GET', '/test/route-ip', [], [], $headers);
        $response = $this->client->getResponse();
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testRouteHasAccountAllow()
    {
        $this->sendRequestAs('GET', '/test/route-has-account', [], static::$userAuthenticated);
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRouteHasAccountDisallow()
    {
        $this->sendRequestAs('GET', '/test/route-has-account', []);
        $response = $this->client->getResponse();
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testMultipleRoute1Allow()
    {
        $this->sendRequestAs('GET', '/test/multiple-route-1', [], static::$userAdmin);
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testMultipleRoute2Allow()
    {
        $this->sendRequestAs('GET', '/test/multiple-route-2', [], static::$userAdmin);
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testMultipleRoute1Disallow()
    {
        $this->sendRequestAs('GET', '/test/multiple-route-1', [], static::$userAuthenticated);
        $response = $this->client->getResponse();
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testMultipleRoute2Disallow()
    {
        $this->sendRequestAs('GET', '/test/multiple-route-2', [], static::$userAuthenticated);
        $response = $this->client->getResponse();
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testYmlRouteNoPermissionsAllow()
    {
        $this->sendRequestAs('GET', '/test/yml-no-permissions');
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testYmlRouteRoleAllow()
    {
        $this->sendRequestAs('GET', '/test/route-yml-role', [], static::$userAdmin);
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testYmlRouteRoleDisallow()
    {
        $this->sendRequestAs('GET', '/test/route-yml-role', [], static::$userAuthenticated);
        $response = $this->client->getResponse();
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testYmlRouteBoolAllow()
    {
        $this->sendRequestAs('GET', '/test/route-yml-allowed', []);
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testYmlRouteBoolDisallow()
    {
        $this->sendRequestAs('GET', '/test/route-yml-denied', [], static::$userAdmin);
        $response = $this->client->getResponse();
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testXmlRouteNoPermissionsAllow()
    {
        $this->sendRequestAs('GET', '/test/xml-no-permissions');
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testXmlRouteRoleAllow()
    {
        $this->sendRequestAs('GET', '/test/route-xml-role', [], static::$userAdmin);
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testXmlRouteRoleDisallow()
    {
        $this->sendRequestAs('GET', '/test/route-xml-role', [], static::$userAuthenticated);
        $response = $this->client->getResponse();
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testXmlRouteBoolAllow()
    {
        $this->sendRequestAs('GET', '/test/route-xml-allowed', []);
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testXmlRouteBoolDisallow()
    {
        $this->sendRequestAs('GET', '/test/route-xml-denied', [], static::$userAdmin);
        $response = $this->client->getResponse();
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testRouteBoolAllow()
    {
        $this->sendRequestAs('GET', '/test/pattern-allowed', []);
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRouteBoolDeny()
    {
        $this->sendRequestAs('GET', '/test/route-denied', [], static::$userAdmin);
        $response = $this->client->getResponse();
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testRouteComplexAllow()
    {
        $this->sendRequestAs('GET', '/test/route-complex', [], static::$userAdmin);
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRouteComplexDeny()
    {
        $this->sendRequestAs('GET', '/test/route-complex', [], static::$userAuthenticated);
        $response = $this->client->getResponse();
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testRoutePatternDenyAll()
    {
        $this->sendRequestAs('GET', '/test/route-forbidden', [], static::$userSuperadmin);
        $response = $this->client->getResponse();
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testRoutePatternOverriddenAllow()
    {
        $this->sendRequestAs('GET', '/test/route-allowed', []);
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRoutePatternOverriddenDeny()
    {
        $this->sendRequestAs('GET', '/test/pattern-forbidden', [], static::$userSuperadmin);
        $response = $this->client->getResponse();
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testAvailableRoutesAnonymous()
    {
        $this->sendRequestAs('GET', '/test/count-available-routes', []);
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $routeCount = $response->getContent();
        $this->assertGreaterThan(3, $routeCount);
    }

    public function testAvailableRoutesAuthenticated()
    {
        $this->sendRequestAs('GET', '/test/count-available-routes', [], static::$userAuthenticated);
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $routeCount = $response->getContent();
        $this->assertGreaterThan(4, $routeCount);
    }

    public function testAvailableRoutesAdmin()
    {
        $this->sendRequestAs('GET', '/test/count-available-routes', [], static::$userAdmin);
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $routeCount = $response->getContent();
        $this->assertGreaterThan(5, $routeCount);
    }

    public function testAvailableRoutesSuperadmin()
    {
        $this->sendRequestAs('GET', '/test/count-available-routes', [], static::$userSuperadmin);
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $routeCount = $response->getContent();
        $this->assertGreaterThan(5, $routeCount);
    }

    public function testAvailableRoutePatternsAnonymous()
    {
        $this->sendRequestAs('GET', '/test/count-available-route-patterns', []);
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $routeCount = $response->getContent();
        $this->assertEquals(1, $routeCount);
    }

    public function testAvailableRoutePatternsAuthenticated()
    {
        $this->sendRequestAs('GET', '/test/count-available-route-patterns', [], static::$userAuthenticated);
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $routeCount = $response->getContent();
        $this->assertEquals(1, $routeCount);
    }

    public function testAvailableRoutePatternsAdmin()
    {
        $this->sendRequestAs('GET', '/test/count-available-route-patterns', [], static::$userAdmin);
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $routeCount = $response->getContent();
        $this->assertEquals(1, $routeCount);
    }

    public function testAvailableRoutePatternsSuperadmin()
    {
        $this->sendRequestAs('GET', '/test/count-available-route-patterns', [], static::$userSuperadmin);
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $routeCount = $response->getContent();
        $this->assertEquals(1, $routeCount);
    }
}
