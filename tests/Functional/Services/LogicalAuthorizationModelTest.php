<?php

namespace Ordermind\LogicalAuthorizationBundle\Test\Functional\Services;

use Ordermind\LogicalAuthorizationBundle\Test\Fixtures\Model\TestModelHasAccountNoInterface;
use Ordermind\LogicalAuthorizationBundle\Test\Fixtures\Model\TestModelNoBypass;
use Ordermind\LogicalAuthorizationBundle\Test\Fixtures\Model\TestModelRoleAuthor;

class LogicalAuthorizationModelTest extends LogicalAuthorizationBase
{
    public function testModelRoleAllow()
    {
        $model = new TestModelRoleAuthor();
        $this->assertTrue($this->laModel->checkModelAccess(get_class($model), 'create', static::$admin_user));
        $this->assertTrue($this->laModel->checkModelAccess(get_class($model), 'read', static::$admin_user));
        $this->assertTrue($this->laModel->checkModelAccess(get_class($model), 'update', static::$admin_user));
        $this->assertTrue($this->laModel->checkModelAccess(get_class($model), 'delete', static::$admin_user));
    }

    public function testModelRoleDisallow()
    {
        $model = new TestModelRoleAuthor();
        $this->assertFalse($this->laModel->checkModelAccess(get_class($model), 'create', static::$authenticated_user));
        $this->assertFalse($this->laModel->checkModelAccess(get_class($model), 'read', static::$authenticated_user));
        $this->assertFalse($this->laModel->checkModelAccess(get_class($model), 'update', static::$authenticated_user));
        $this->assertFalse($this->laModel->checkModelAccess(get_class($model), 'delete', static::$authenticated_user));
    }

    public function testModelFlagBypassAccessAllow()
    {
        $model = new TestModelRoleAuthor();
        $this->assertTrue($this->laModel->checkModelAccess(get_class($model), 'create', static::$superadmin_user));
        $this->assertTrue($this->laModel->checkModelAccess(get_class($model), 'read', static::$superadmin_user));
        $this->assertTrue($this->laModel->checkModelAccess(get_class($model), 'update', static::$superadmin_user));
        $this->assertTrue($this->laModel->checkModelAccess(get_class($model), 'delete', static::$superadmin_user));
    }

    public function testModelFlagBypassAccessDisallow()
    {
        $model = new TestModelNoBypass();
        $this->assertFalse($this->laModel->checkModelAccess(get_class($model), 'create', static::$superadmin_user));
        $this->assertFalse($this->laModel->checkModelAccess(get_class($model), 'read', static::$superadmin_user));
        $this->assertFalse($this->laModel->checkModelAccess(get_class($model), 'update', static::$superadmin_user));
        $this->assertFalse($this->laModel->checkModelAccess(get_class($model), 'delete', static::$superadmin_user));
    }

    public function testModelFlagHasAccountAllow()
    {
        $model = new TestModelHasAccountNoInterface();
        $this->assertTrue($this->laModel->checkModelAccess(get_class($model), 'create', static::$authenticated_user));
        $this->assertTrue($this->laModel->checkModelAccess(get_class($model), 'read', static::$authenticated_user));
        $this->assertTrue($this->laModel->checkModelAccess(get_class($model), 'update', static::$authenticated_user));
        $this->assertTrue($this->laModel->checkModelAccess(get_class($model), 'delete', static::$authenticated_user));
    }

    public function testModelFlagHasAccountDisallow()
    {
        $model = new TestModelHasAccountNoInterface();
        $this->assertFalse($this->laModel->checkModelAccess(get_class($model), 'create', 'anon.'));
        $this->assertFalse($this->laModel->checkModelAccess(get_class($model), 'read', 'anon.'));
        $this->assertFalse($this->laModel->checkModelAccess(get_class($model), 'update', 'anon.'));
        $this->assertFalse($this->laModel->checkModelAccess(get_class($model), 'delete', 'anon.'));
    }

    public function testModelFlagIsAuthorAllow()
    {
        static::$authenticated_user->setId(1);
        $model = new TestModelRoleAuthor();
        $model->setAuthor(static::$authenticated_user);
        $this->assertTrue($this->laModel->checkModelAccess($model, 'read', static::$authenticated_user));
        $this->assertTrue($this->laModel->checkModelAccess($model, 'update', static::$authenticated_user));
        $this->assertTrue($this->laModel->checkModelAccess($model, 'delete', static::$authenticated_user));
    }

    public function testModelFlagIsAuthorDisallow()
    {
        static::$authenticated_user->setId(1);
        static::$admin_user->setId(2);
        $model = new TestModelRoleAuthor();
        $model->setAuthor(static::$admin_user);
        $this->assertFalse($this->laModel->checkModelAccess($model, 'read', static::$authenticated_user));
        $this->assertFalse($this->laModel->checkModelAccess($model, 'update', static::$authenticated_user));
        $this->assertFalse($this->laModel->checkModelAccess($model, 'delete', static::$authenticated_user));
    }

    public function testUserFlagIsAuthor()
    {
        $this->assertTrue($this->laModel->checkModelAccess(static::$authenticated_user, 'read', static::$authenticated_user));
        $this->assertTrue($this->laModel->checkModelAccess(static::$authenticated_user, 'update', static::$authenticated_user));
        static::$authenticated_user->setBypassAccess(true);
        $this->assertFalse($this->laModel->checkModelAccess(static::$authenticated_user, 'delete', static::$authenticated_user));
        $this->assertTrue($this->laModel->checkModelAccess(static::$authenticated_user, 'delete', static::$admin_user));
        static::$authenticated_user->setBypassAccess(false);
    }

    public function testFieldRoleAllow()
    {
        $model = new TestModelRoleAuthor();
        $this->assertTrue($this->laModel->checkFieldAccess(get_class($model), 'field1', 'set', static::$admin_user));
        $this->assertTrue($this->laModel->checkFieldAccess(get_class($model), 'field1', 'get', static::$admin_user));
    }

    public function testFieldRoleDisallow()
    {
        $model = new TestModelRoleAuthor();
        $this->assertFalse($this->laModel->checkFieldAccess(get_class($model), 'field1', 'set', static::$authenticated_user));
        $this->assertFalse($this->laModel->checkFieldAccess(get_class($model), 'field1', 'get', static::$authenticated_user));
    }

    public function testFieldFlagBypassAccessAllow()
    {
        $model = new TestModelRoleAuthor();
        $this->assertTrue($this->laModel->checkFieldAccess(get_class($model), 'field1', 'set', static::$superadmin_user));
        $this->assertTrue($this->laModel->checkFieldAccess(get_class($model), 'field1', 'get', static::$superadmin_user));
    }

    public function testFieldFlagBypassAccessDisallow()
    {
        $model = new TestModelNoBypass();
        $this->assertFalse($this->laModel->checkFieldAccess(get_class($model), 'field1', 'set', static::$superadmin_user));
        $this->assertFalse($this->laModel->checkFieldAccess(get_class($model), 'field1', 'get', static::$superadmin_user));
    }

    public function testFieldFlagHasAccountAllow()
    {
        $model = new TestModelHasAccountNoInterface();
        $this->assertTrue($this->laModel->checkFieldAccess(get_class($model), 'field1', 'set', static::$authenticated_user));
        $this->assertTrue($this->laModel->checkFieldAccess(get_class($model), 'field1', 'get', static::$authenticated_user));
    }

    public function testFieldFlagHasAccountDisallow()
    {
        $model = new TestModelHasAccountNoInterface();
        $this->assertFalse($this->laModel->checkFieldAccess(get_class($model), 'field1', 'set', 'anon.'));
        $this->assertFalse($this->laModel->checkFieldAccess(get_class($model), 'field1', 'get', 'anon.'));
    }

    public function testFieldFlagIsAuthorAllow()
    {
        static::$authenticated_user->setId(1);
        $model = new TestModelRoleAuthor();
        $model->setAuthor(static::$authenticated_user);
        $this->assertTrue($this->laModel->checkFieldAccess($model, 'field1', 'set', static::$authenticated_user));
        $this->assertTrue($this->laModel->checkFieldAccess($model, 'field1', 'get', static::$authenticated_user));
    }

    public function testFieldFlagIsAuthorDisallow()
    {
        static::$authenticated_user->setId(1);
        static::$admin_user->setId(2);
        $model = new TestModelRoleAuthor();
        $model->setAuthor(static::$admin_user);
        $this->assertFalse($this->laModel->checkFieldAccess($model, 'field1', 'set', static::$authenticated_user));
        $this->assertFalse($this->laModel->checkFieldAccess($model, 'field1', 'get', static::$authenticated_user));
    }
}
