<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Test\Functional\Services;

use Ordermind\LogicalAuthorizationBundle\Test\Fixtures\Model\TestModelHasAccountNoInterface;
use Ordermind\LogicalAuthorizationBundle\Test\Fixtures\Model\TestModelNoBypass;
use Ordermind\LogicalAuthorizationBundle\Test\Fixtures\Model\TestModelRoleAuthor;

class LogicalAuthorizationModelTest extends LogicalAuthorizationBase
{
    public function testModelRoleAllow()
    {
        $model = new TestModelRoleAuthor();
        $this->assertTrue($this->laModel->checkModelAccess(get_class($model), 'create', static::$userAdmin));
        $this->assertTrue($this->laModel->checkModelAccess(get_class($model), 'read', static::$userAdmin));
        $this->assertTrue($this->laModel->checkModelAccess(get_class($model), 'update', static::$userAdmin));
        $this->assertTrue($this->laModel->checkModelAccess(get_class($model), 'delete', static::$userAdmin));
    }

    public function testModelRoleDisallow()
    {
        $model = new TestModelRoleAuthor();
        $this->assertFalse($this->laModel->checkModelAccess(get_class($model), 'create', static::$userAuthenticated));
        $this->assertFalse($this->laModel->checkModelAccess(get_class($model), 'read', static::$userAuthenticated));
        $this->assertFalse($this->laModel->checkModelAccess(get_class($model), 'update', static::$userAuthenticated));
        $this->assertFalse($this->laModel->checkModelAccess(get_class($model), 'delete', static::$userAuthenticated));
    }

    public function testModelConditionBypassAccessAllow()
    {
        $model = new TestModelRoleAuthor();
        $this->assertTrue($this->laModel->checkModelAccess(get_class($model), 'create', static::$userSuperadmin));
        $this->assertTrue($this->laModel->checkModelAccess(get_class($model), 'read', static::$userSuperadmin));
        $this->assertTrue($this->laModel->checkModelAccess(get_class($model), 'update', static::$userSuperadmin));
        $this->assertTrue($this->laModel->checkModelAccess(get_class($model), 'delete', static::$userSuperadmin));
    }

    public function testModelConditionBypassAccessDisallow()
    {
        $model = new TestModelNoBypass();
        $this->assertFalse($this->laModel->checkModelAccess(get_class($model), 'create', static::$userSuperadmin));
        $this->assertFalse($this->laModel->checkModelAccess(get_class($model), 'read', static::$userSuperadmin));
        $this->assertFalse($this->laModel->checkModelAccess(get_class($model), 'update', static::$userSuperadmin));
        $this->assertFalse($this->laModel->checkModelAccess(get_class($model), 'delete', static::$userSuperadmin));
    }

    public function testModelConditionHasAccountAllow()
    {
        $model = new TestModelHasAccountNoInterface();
        $this->assertTrue($this->laModel->checkModelAccess(get_class($model), 'create', static::$userAuthenticated));
        $this->assertTrue($this->laModel->checkModelAccess(get_class($model), 'read', static::$userAuthenticated));
        $this->assertTrue($this->laModel->checkModelAccess(get_class($model), 'update', static::$userAuthenticated));
        $this->assertTrue($this->laModel->checkModelAccess(get_class($model), 'delete', static::$userAuthenticated));
    }

    public function testModelConditionHasAccountDisallow()
    {
        $model = new TestModelHasAccountNoInterface();
        $this->assertFalse($this->laModel->checkModelAccess(get_class($model), 'create', 'anon.'));
        $this->assertFalse($this->laModel->checkModelAccess(get_class($model), 'read', 'anon.'));
        $this->assertFalse($this->laModel->checkModelAccess(get_class($model), 'update', 'anon.'));
        $this->assertFalse($this->laModel->checkModelAccess(get_class($model), 'delete', 'anon.'));
    }

    public function testModelConditionIsAuthorAllow()
    {
        static::$userAuthenticated->setId(1);
        $model = new TestModelRoleAuthor();
        $model->setAuthor(static::$userAuthenticated);
        $this->assertTrue($this->laModel->checkModelAccess($model, 'read', static::$userAuthenticated));
        $this->assertTrue($this->laModel->checkModelAccess($model, 'update', static::$userAuthenticated));
        $this->assertTrue($this->laModel->checkModelAccess($model, 'delete', static::$userAuthenticated));
    }

    public function testModelConditionIsAuthorDisallow()
    {
        static::$userAuthenticated->setId(1);
        static::$userAdmin->setId(2);
        $model = new TestModelRoleAuthor();
        $model->setAuthor(static::$userAdmin);
        $this->assertFalse($this->laModel->checkModelAccess($model, 'read', static::$userAuthenticated));
        $this->assertFalse($this->laModel->checkModelAccess($model, 'update', static::$userAuthenticated));
        $this->assertFalse($this->laModel->checkModelAccess($model, 'delete', static::$userAuthenticated));
    }

    public function testFieldRoleAllow()
    {
        $model = new TestModelRoleAuthor();
        $this->assertTrue($this->laModel->checkFieldAccess(get_class($model), 'field1', 'set', static::$userAdmin));
        $this->assertTrue($this->laModel->checkFieldAccess(get_class($model), 'field1', 'get', static::$userAdmin));
    }

    public function testFieldRoleDisallow()
    {
        $model = new TestModelRoleAuthor();
        $this->assertFalse(
            $this->laModel->checkFieldAccess(get_class($model), 'field1', 'set', static::$userAuthenticated)
        );
        $this->assertFalse(
            $this->laModel->checkFieldAccess(get_class($model), 'field1', 'get', static::$userAuthenticated)
        );
    }

    public function testFieldConditionBypassAccessAllow()
    {
        $model = new TestModelRoleAuthor();
        $this->assertTrue(
            $this->laModel->checkFieldAccess(get_class($model), 'field1', 'set', static::$userSuperadmin)
        );
        $this->assertTrue(
            $this->laModel->checkFieldAccess(get_class($model), 'field1', 'get', static::$userSuperadmin)
        );
    }

    public function testFieldConditionBypassAccessDisallow()
    {
        $model = new TestModelNoBypass();
        $this->assertFalse(
            $this->laModel->checkFieldAccess(get_class($model), 'field1', 'set', static::$userSuperadmin)
        );
        $this->assertFalse(
            $this->laModel->checkFieldAccess(get_class($model), 'field1', 'get', static::$userSuperadmin)
        );
    }

    public function testFieldConditionHasAccountAllow()
    {
        $model = new TestModelHasAccountNoInterface();
        $this->assertTrue(
            $this->laModel->checkFieldAccess(get_class($model), 'field1', 'set', static::$userAuthenticated)
        );
        $this->assertTrue(
            $this->laModel->checkFieldAccess(get_class($model), 'field1', 'get', static::$userAuthenticated)
        );
    }

    public function testFieldConditionHasAccountDisallow()
    {
        $model = new TestModelHasAccountNoInterface();
        $this->assertFalse($this->laModel->checkFieldAccess(get_class($model), 'field1', 'set', 'anon.'));
        $this->assertFalse($this->laModel->checkFieldAccess(get_class($model), 'field1', 'get', 'anon.'));
    }

    public function testFieldConditionIsAuthorAllow()
    {
        static::$userAuthenticated->setId(1);
        $model = new TestModelRoleAuthor();
        $model->setAuthor(static::$userAuthenticated);
        $this->assertTrue($this->laModel->checkFieldAccess($model, 'field1', 'set', static::$userAuthenticated));
        $this->assertTrue($this->laModel->checkFieldAccess($model, 'field1', 'get', static::$userAuthenticated));
    }

    public function testFieldConditionIsAuthorDisallow()
    {
        static::$userAuthenticated->setId(1);
        static::$userAdmin->setId(2);
        $model = new TestModelRoleAuthor();
        $model->setAuthor(static::$userAdmin);
        $this->assertFalse($this->laModel->checkFieldAccess($model, 'field1', 'set', static::$userAuthenticated));
        $this->assertFalse($this->laModel->checkFieldAccess($model, 'field1', 'get', static::$userAuthenticated));
    }
}
