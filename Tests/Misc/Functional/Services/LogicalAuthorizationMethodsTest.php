<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\Misc\Functional\Services;

use Ordermind\LogicalAuthorizationBundle\Services\LogicalPermissionsManager;
use Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorization;
use Ordermind\LogicalAuthorizationBundle\PermissionTypes\Flag\Flags\BypassAccess as BypassAccessFlag;
use Ordermind\LogicalAuthorizationBundle\PermissionTypes\Flag\Flags\HasAccount as HasAccountFlag;
use Ordermind\LogicalAuthorizationBundle\PermissionTypes\Flag\Flags\IsAuthor as IsAuthorFlag;
use Ordermind\LogicalAuthorizationBundle\PermissionTypes\Flag\FlagManager;
use Ordermind\LogicalAuthorizationBundle\Tests\Misc\Fixtures\Entity\ErroneousUser;
use Ordermind\LogicalAuthorizationBundle\Tests\Misc\Fixtures\Entity\TestUser;
use Ordermind\LogicalAuthorizationBundle\Tests\Misc\Fixtures\Entity\ErroneousEntity;
use Ordermind\LogicalAuthorizationBundle\Tests\Misc\Fixtures\Entity\TestEntity;
use Ordermind\LogicalAuthorizationBundle\Tests\Misc\Fixtures\PermissionTypes\TestFlag;
use Ordermind\LogicalAuthorizationBundle\PermissionTypes\Role\Role;
use Ordermind\LogicalAuthorizationBundle\Tests\Misc\Fixtures\PermissionTypes\TestType;

class LogicalAuthorizationMethodsTest extends LogicalAuthorizationMiscBase {

  /*------------ Permission types ---------------*/

  /**
    * @expectedException InvalidArgumentException
    */
  public function testFlagBypassAccessWrongContextType() {
    $flag = new BypassAccessFlag();
    $flag->checkFlag(null);
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testFlagBypassAccessMissingUser() {
    $flag = new BypassAccessFlag();
    $flag->checkFlag([]);
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testFlagBypassAccessWrongUserType() {
    $flag = new BypassAccessFlag();
    $flag->checkFlag(['user' => []]);
  }

  /**
    * @expectedException UnexpectedValueException
    */
  public function testFlagBypassAccessWrongReturnType() {
    $user = new ErroneousUser();
    $flag = new BypassAccessFlag();
    $flag->checkFlag(['user' => $user]);
  }

  public function testFlagBypassAccessAnonymousUserNo() {
    $flag = new BypassAccessFlag();
    $this->assertFalse($flag->checkFlag(['user' => 'anon.']));
  }

  public function testFlagBypassAccessNo() {
    $user = new TestUser();
    $flag = new BypassAccessFlag();
    $this->assertFalse($flag->checkFlag(['user' => $user]));
  }

  public function testFlagBypassAccessYes() {
    $user = new TestUser();
    $user->setBypassAccess(true);
    $flag = new BypassAccessFlag();
    $this->assertTrue($flag->checkFlag(['user' => $user]));
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testFlagHasAccountWrongContextType() {
    $flag = new HasAccountFlag();
    $flag->checkFlag(null);
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testFlagHasAccountMissingUser() {
    $flag = new HasAccountFlag();
    $flag->checkFlag([]);
  }

  public function testFlagHasAccountNo() {
    $flag = new HasAccountFlag();
    $this->assertFalse($flag->checkFlag(['user' => 'anon.']));
  }

  public function testFlagHasAccountYes() {
    $user = new TestUser();
    $flag = new HasAccountFlag();
    $this->assertTrue($flag->checkFlag(['user' => $user]));
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testFlagIsAuthorWrongContextType() {
    $flag = new IsAuthorFlag();
    $flag->checkFlag(null);
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testFlagIsAuthorMissingUser() {
    $flag = new IsAuthorFlag();
    $flag->checkFlag([]);
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testFlagIsAuthorWrongUserType() {
    $flag = new IsAuthorFlag();
    $flag->checkFlag(['user' => []]);
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testFlagIsAuthorMissingModel() {
    $user = new TestUser();
    $flag = new IsAuthorFlag();
    $flag->checkFlag(['user' => $user]);
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testFlagIsAuthorModelClassString() {
    $user = new TestUser();
    $flag = new IsAuthorFlag();
    $flag->checkFlag(['user' => $user, 'model' => 'Ordermind\LogicalAuthorizationBundle\Tests\Misc\Fixtures\Entity\TestUser']);
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testFlagIsAuthorWrongModelType() {
    $user = new TestUser();
    $flag = new IsAuthorFlag();
    $flag->checkFlag(['user' => $user, 'model' => []]);
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testFlagIsAuthorModelWrongAuthorType() {
    $user = new TestUser();
    $model = new ErroneousEntity();
    $flag = new IsAuthorFlag();
    $flag->checkFlag(['user' => $user, 'model' => $model]);
  }

  public function testFlagIsAuthorModelAnonymousUserNo() {
    $model = new TestEntity();
    $flag = new IsAuthorFlag();
    $this->assertFalse($flag->checkFlag(['user' => 'anon.', 'model' => $model]));
  }

  public function testFlagIsAuthorModelAnonymousAuthorNo() {
    $user = new TestUser();
    $model = new TestEntity();
    $flag = new IsAuthorFlag();
    $this->assertFalse($flag->checkFlag(['user' => $user, 'model' => $model]));
  }

  public function testFlagIsAuthorModelYes() {
    $user = new TestUser();
    $model = new TestEntity();
    $model->setAuthor($user);
    $flag = new IsAuthorFlag();
    $this->assertTrue($flag->checkFlag(['user' => $user, 'model' => $model]));
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testFlagManagerAddFlagWrongNameType() {
    $flagManager = new FlagManager();
    $flag = new TestFlag();
    $flag->setName(true);
    $flagManager->addFlag($flag);
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testFlagManagerAddFlagEmptyName() {
    $flagManager = new FlagManager();
    $flag = new TestFlag();
    $flag->setName('');
    $flagManager->addFlag($flag);
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testFlagManagerAddFlagAlreadyRegistered() {
    $flagManager = new FlagManager();
    $flag = new TestFlag();
    $flag->setName('test');
    $flagManager->addFlag($flag);
    $flagManager->addFlag($flag);
  }

  public function testFlagManagerAddFlag() {
    $flagManager = new FlagManager();
    $flag = new TestFlag();
    $flag->setName('test');
    $flagManager->addFlag($flag);
    $flags = $flagManager->getFlags();
    $this->assertTrue(isset($flags['test']));
    $this->assertSame($flag, $flags['test']);
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testFlagManagerRemoveFlagWrongNameType() {
    $flagManager = new FlagManager();
    $flag = new TestFlag();
    $flag->setName('test');
    $flagManager->addFlag($flag);
    $flagManager->removeFlag(true);
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testFlagManagerRemoveFlagEmptyName() {
    $flagManager = new FlagManager();
    $flag = new TestFlag();
    $flag->setName('test');
    $flagManager->addFlag($flag);
    $flagManager->removeFlag('');
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testFlagManagerRemoveFlagNotRegistered() {
    $flagManager = new FlagManager();
    $flagManager->removeFlag('test');
  }

  public function testFlagManagerRemoveFlag() {
    $flagManager = new FlagManager();
    $flag = new TestFlag();
    $flag->setName('test');
    $flagManager->addFlag($flag);
    $flags = $flagManager->getFlags();
    $this->assertTrue(isset($flags['test']));
    $flagManager->removeFlag('test');
    $flags = $flagManager->getFlags();
    $this->assertFalse(isset($flags['test']));
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testFlagManagerCheckPermissionWrongNameType() {
    $flagManager = new FlagManager();
    $flagManager->checkPermission(true, []);
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testFlagManagerCheckPermissionEmptyName() {
    $flagManager = new FlagManager();
    $flagManager->checkPermission('', []);
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testFlagManagerCheckPermissionNotRegistered() {
    $flagManager = new FlagManager();
    $flagManager->checkPermission('test', []);
  }

  public function testFlagManagerCheckPermission() {
    $flagManager = new FlagManager();
    $flag = new TestFlag();
    $flag->setName('test');
    $flagManager->addFlag($flag);
    $this->assertTrue($flagManager->checkPermission('test', []));
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testRoleWrongRoleType() {
    $role = new Role();
    $role->checkPermission(true, []);
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testRoleEmptyRole() {
    $role = new Role();
    $role->checkPermission('', []);
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testRoleWrongContextType() {
    $role = new Role();
    $role->checkPermission('ROLE_USER', null);
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testRoleMissingUser() {
    $role = new Role();
    $role->checkPermission('ROLE_USER', []);
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testRoleWrongUserType() {
    $role = new Role();
    $role->checkPermission('ROLE_USER', ['user' => []]);
  }

  public function testRoleAnonymousUserNo() {
    $role = new Role();
    $this->assertFalse($role->checkPermission('ROLE_USER', ['user' => 'anon.']));
  }

  public function testRoleNo() {
    $user = new TestUser();
    $role = new Role();
    $this->assertFalse($role->checkPermission('ROLE_ADMIN', ['user' => $user]));
  }

  public function testRoleYes() {
    $user = new TestUser();
    $role = new Role();
    $this->assertTrue($role->checkPermission('ROLE_USER', ['user' => $user]));
    $user->setRoles(['ROLE_ADMIN']);
    $this->assertTrue($role->checkPermission('ROLE_ADMIN', ['user' => $user]));
  }

  /*------------ Services -------------*/

  /**
    * @expectedException Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException
    * @expectedExceptionMessageRegExp /service tag to register a permission type/
    */
  public function testCheckAccessPermissionTypeNotRegistered() {
    $this->la->checkAccess(['test' => 'hej'], ['user' => 'anon.']);
  }

  /**
    * @expectedException Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException
    * @expectedExceptionMessageRegExp /^An exception was caught while checking access: /
    */
  public function testCheckAccessOtherExceptions() {
    $this->la->checkAccess(['test' => 'hej'], []);
  }

  public function testCheckAccessNo() {
    $lpManager = new LogicalPermissionsManager();
    $type = new TestType();
    $lpManager->addType($type);
    $lpManager->setBypassCallback(function($context) { return false; });
    $la = new LogicalAuthorization($lpManager);
    $this->assertFalse($la->checkAccess(['test' => 'no'], []));
  }

  public function testCheckAccessYes() {
    $lpManager = new LogicalPermissionsManager();
    $type = new TestType();
    $lpManager->addType($type);
    $lpManager->setBypassCallback(function($context) { return false; });
    $la = new LogicalAuthorization($lpManager);
    $this->assertTrue($la->checkAccess(['test' => 'yes'], []));
  }

}
