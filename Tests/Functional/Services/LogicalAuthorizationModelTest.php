<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\Functional\Services;

use Ordermind\LogicalAuthorizationBundle\Tests\Fixtures\Model\TestModelHasAccountNoInterface;
use Ordermind\LogicalAuthorizationBundle\Tests\Fixtures\Model\TestModelNoBypass;
use Ordermind\LogicalAuthorizationBundle\Tests\Fixtures\Model\TestModelRoleAuthor;

class LogicalAuthorizationModelTest extends LogicalAuthorizationBase {
  public function testModelRoleAllow() {
    $model = new TestModelRoleAuthor();
    $this->assertTrue($this->laModel->checkModelAccess(get_class($model), 'create', self::$admin_user));
    $this->assertTrue($this->laModel->checkModelAccess(get_class($model), 'read', self::$admin_user));
    $this->assertTrue($this->laModel->checkModelAccess(get_class($model), 'update', self::$admin_user));
    $this->assertTrue($this->laModel->checkModelAccess(get_class($model), 'delete', self::$admin_user));
  }

  public function testModelRoleDisallow() {
    $model = new TestModelRoleAuthor();
    $this->assertFalse($this->laModel->checkModelAccess(get_class($model), 'create', self::$authenticated_user));
    $this->assertFalse($this->laModel->checkModelAccess(get_class($model), 'read', self::$authenticated_user));
    $this->assertFalse($this->laModel->checkModelAccess(get_class($model), 'update', self::$authenticated_user));
    $this->assertFalse($this->laModel->checkModelAccess(get_class($model), 'delete', self::$authenticated_user));
  }

  public function testModelFlagBypassAccessAllow() {
    $model = new TestModelRoleAuthor();
    $this->assertTrue($this->laModel->checkModelAccess(get_class($model), 'create', self::$superadmin_user));
    $this->assertTrue($this->laModel->checkModelAccess(get_class($model), 'read', self::$superadmin_user));
    $this->assertTrue($this->laModel->checkModelAccess(get_class($model), 'update', self::$superadmin_user));
    $this->assertTrue($this->laModel->checkModelAccess(get_class($model), 'delete', self::$superadmin_user));
  }

  public function testModelFlagBypassAccessDisallow() {
    $model = new TestModelNoBypass();
    $this->assertFalse($this->laModel->checkModelAccess(get_class($model), 'create', self::$superadmin_user));
    $this->assertFalse($this->laModel->checkModelAccess(get_class($model), 'read', self::$superadmin_user));
    $this->assertFalse($this->laModel->checkModelAccess(get_class($model), 'update', self::$superadmin_user));
    $this->assertFalse($this->laModel->checkModelAccess(get_class($model), 'delete', self::$superadmin_user));
  }

  public function testModelFlagHasAccountAllow() {
    $model = new TestModelHasAccountNoInterface();
    $this->assertTrue($this->laModel->checkModelAccess(get_class($model), 'create', self::$authenticated_user));
    $this->assertTrue($this->laModel->checkModelAccess(get_class($model), 'read', self::$authenticated_user));
    $this->assertTrue($this->laModel->checkModelAccess(get_class($model), 'update', self::$authenticated_user));
    $this->assertTrue($this->laModel->checkModelAccess(get_class($model), 'delete', self::$authenticated_user));
  }

  public function testModelFlagHasAccountDisallow() {
    $model = new TestModelHasAccountNoInterface();
    $this->assertFalse($this->laModel->checkModelAccess(get_class($model), 'create', 'anon.'));
    $this->assertFalse($this->laModel->checkModelAccess(get_class($model), 'read', 'anon.'));
    $this->assertFalse($this->laModel->checkModelAccess(get_class($model), 'update', 'anon.'));
    $this->assertFalse($this->laModel->checkModelAccess(get_class($model), 'delete', 'anon.'));
  }

  public function testModelFlagIsAuthorAllow() {
    self::$authenticated_user->setId(1);
    $model = new TestModelRoleAuthor();
    $model->setAuthor(self::$authenticated_user);
    $this->assertTrue($this->laModel->checkModelAccess($model, 'read', self::$authenticated_user));
    $this->assertTrue($this->laModel->checkModelAccess($model, 'update', self::$authenticated_user));
    $this->assertTrue($this->laModel->checkModelAccess($model, 'delete', self::$authenticated_user));
  }

  public function testModelFlagIsAuthorDisallow() {
    self::$authenticated_user->setId(1);
    self::$admin_user->setId(2);
    $model = new TestModelRoleAuthor();
    $model->setAuthor(self::$admin_user);
    $this->assertFalse($this->laModel->checkModelAccess($model, 'read', self::$authenticated_user));
    $this->assertFalse($this->laModel->checkModelAccess($model, 'update', self::$authenticated_user));
    $this->assertFalse($this->laModel->checkModelAccess($model, 'delete', self::$authenticated_user));
  }

  public function testUserFlagIsAuthor() {
    $this->assertTrue($this->laModel->checkModelAccess(self::$authenticated_user, 'read', self::$authenticated_user));
    $this->assertTrue($this->laModel->checkModelAccess(self::$authenticated_user, 'update', self::$authenticated_user));
    self::$authenticated_user->setBypassAccess(true);
    $this->assertFalse($this->laModel->checkModelAccess(self::$authenticated_user, 'delete', self::$authenticated_user));
    $this->assertTrue($this->laModel->checkModelAccess(self::$authenticated_user, 'delete', self::$admin_user));
    self::$authenticated_user->setBypassAccess(false);
  }

  public function testFieldRoleAllow() {
    $model = new TestModelRoleAuthor();
    $this->assertTrue($this->laModel->checkFieldAccess(get_class($model), 'field1', 'set', self::$admin_user));
    $this->assertTrue($this->laModel->checkFieldAccess(get_class($model), 'field1', 'get', self::$admin_user));
  }

  public function testFieldRoleDisallow() {
    $model = new TestModelRoleAuthor();
    $this->assertFalse($this->laModel->checkFieldAccess(get_class($model), 'field1', 'set', self::$authenticated_user));
    $this->assertFalse($this->laModel->checkFieldAccess(get_class($model), 'field1', 'get', self::$authenticated_user));
  }

  public function testFieldFlagBypassAccessAllow() {
    $model = new TestModelRoleAuthor();
    $this->assertTrue($this->laModel->checkFieldAccess(get_class($model), 'field1', 'set', self::$superadmin_user));
    $this->assertTrue($this->laModel->checkFieldAccess(get_class($model), 'field1', 'get', self::$superadmin_user));
  }

  public function testFieldFlagBypassAccessDisallow() {
    $model = new TestModelNoBypass();
    $this->assertFalse($this->laModel->checkFieldAccess(get_class($model), 'field1', 'set', self::$superadmin_user));
    $this->assertFalse($this->laModel->checkFieldAccess(get_class($model), 'field1', 'get', self::$superadmin_user));
  }

  public function testFieldFlagHasAccountAllow() {
    $model = new TestModelHasAccountNoInterface();
    $this->assertTrue($this->laModel->checkFieldAccess(get_class($model), 'field1', 'set', self::$authenticated_user));
    $this->assertTrue($this->laModel->checkFieldAccess(get_class($model), 'field1', 'get', self::$authenticated_user));
  }

  public function testFieldFlagHasAccountDisallow() {
    $model = new TestModelHasAccountNoInterface();
    $this->assertFalse($this->laModel->checkFieldAccess(get_class($model), 'field1', 'set', 'anon.'));
    $this->assertFalse($this->laModel->checkFieldAccess(get_class($model), 'field1', 'get', 'anon.'));
  }

  public function testFieldFlagIsAuthorAllow() {
    self::$authenticated_user->setId(1);
    $model = new TestModelRoleAuthor();
    $model->setAuthor(self::$authenticated_user);
    $this->assertTrue($this->laModel->checkFieldAccess($model, 'field1', 'set', self::$authenticated_user));
    $this->assertTrue($this->laModel->checkFieldAccess($model, 'field1', 'get', self::$authenticated_user));
  }

  public function testFieldFlagIsAuthorDisallow() {
    self::$authenticated_user->setId(1);
    self::$admin_user->setId(2);
    $model = new TestModelRoleAuthor();
    $model->setAuthor(self::$admin_user);
    $this->assertFalse($this->laModel->checkFieldAccess($model, 'field1', 'set', self::$authenticated_user));
    $this->assertFalse($this->laModel->checkFieldAccess($model, 'field1', 'get', self::$authenticated_user));
  }
}
