<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Test\Functional\Services;

use Ordermind\LogicalAuthorizationBundle\Test\Fixtures\Model\TestModelRoleAuthor;
use Twig\TwigFunction;

class LogicalAuthorizationTwigTest extends LogicalAuthorizationBase
{
    public function testTwigCheckRouteAccess()
    {
        $function = $this->twig->getFunction('logauth_check_route_access');
        $this->assertTrue($function instanceof TwigFunction);
        $callable = $function->getCallable();
        $this->assertTrue($callable('route_role', static::$userAdmin));
        $this->assertFalse($callable('route_role', static::$userAuthenticated));
    }

    public function testTwigCheckModelAccess()
    {
        $function = $this->twig->getFunction('logauth_check_model_access');
        $this->assertTrue($function instanceof TwigFunction);
        $callable = $function->getCallable();
        $model = new TestModelRoleAuthor();
        $this->assertTrue($callable(get_class($model), 'create', static::$userAdmin));
        $this->assertTrue($callable(get_class($model), 'read', static::$userAdmin));
        $this->assertTrue($callable(get_class($model), 'update', static::$userAdmin));
        $this->assertTrue($callable(get_class($model), 'delete', static::$userAdmin));
        $this->assertFalse($callable(get_class($model), 'create', static::$userAuthenticated));
        $this->assertFalse($callable(get_class($model), 'read', static::$userAuthenticated));
        $this->assertFalse($callable(get_class($model), 'update', static::$userAuthenticated));
        $this->assertFalse($callable(get_class($model), 'delete', static::$userAuthenticated));
    }

    public function testTwigCheckFieldAccess()
    {
        $function = $this->twig->getFunction('logauth_check_field_access');
        $this->assertTrue($function instanceof TwigFunction);
        $callable = $function->getCallable();
        $model = new TestModelRoleAuthor();
        $this->assertTrue($callable(get_class($model), 'field1', 'set', static::$userAdmin));
        $this->assertTrue($callable(get_class($model), 'field1', 'get', static::$userAdmin));
        $this->assertFalse($callable(get_class($model), 'field1', 'set', static::$userAuthenticated));
        $this->assertFalse($callable(get_class($model), 'field1', 'get', static::$userAuthenticated));
    }
}
