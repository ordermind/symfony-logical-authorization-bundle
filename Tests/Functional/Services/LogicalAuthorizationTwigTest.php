<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Tests\Functional\Services;

use Ordermind\LogicalAuthorizationBundle\Tests\Fixtures\Model\TestModelRoleAuthor;

class LogicalAuthorizationTwigTest extends LogicalAuthorizationBase
{
    public function testTwigCheckRouteAccess()
    {
        $function = $this->twig->getFunction('logauth_check_route_access');
        $this->assertTrue($function instanceof \Twig_SimpleFunction);
        $callable = $function->getCallable();
        $this->assertTrue($callable('route_role', static::$admin_user));
        $this->assertFalse($callable('route_role', static::$authenticated_user));
    }

    public function testTwigCheckModelAccess()
    {
        $function = $this->twig->getFunction('logauth_check_model_access');
        $this->assertTrue($function instanceof \Twig_SimpleFunction);
        $callable = $function->getCallable();
        $model = new TestModelRoleAuthor();
        $this->assertTrue($callable(get_class($model), 'create', static::$admin_user));
        $this->assertTrue($callable(get_class($model), 'read', static::$admin_user));
        $this->assertTrue($callable(get_class($model), 'update', static::$admin_user));
        $this->assertTrue($callable(get_class($model), 'delete', static::$admin_user));
        $this->assertFalse($callable(get_class($model), 'create', static::$authenticated_user));
        $this->assertFalse($callable(get_class($model), 'read', static::$authenticated_user));
        $this->assertFalse($callable(get_class($model), 'update', static::$authenticated_user));
        $this->assertFalse($callable(get_class($model), 'delete', static::$authenticated_user));
    }

    public function testTwigCheckFieldAccess()
    {
        $function = $this->twig->getFunction('logauth_check_field_access');
        $this->assertTrue($function instanceof \Twig_SimpleFunction);
        $callable = $function->getCallable();
        $model = new TestModelRoleAuthor();
        $this->assertTrue($callable(get_class($model), 'field1', 'set', static::$admin_user));
        $this->assertTrue($callable(get_class($model), 'field1', 'get', static::$admin_user));
        $this->assertFalse($callable(get_class($model), 'field1', 'set', static::$authenticated_user));
        $this->assertFalse($callable(get_class($model), 'field1', 'get', static::$authenticated_user));
    }
}
