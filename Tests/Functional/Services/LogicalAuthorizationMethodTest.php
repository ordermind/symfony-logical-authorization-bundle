<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\Functional\Services;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;

use Ordermind\LogicalAuthorizationBundle\Services\LogicalPermissionsProxy;
use Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorization;
use Ordermind\LogicalAuthorizationBundle\Services\Helper;
use Ordermind\LogicalAuthorizationBundle\PermissionTypes\Flag\Flags\BypassAccess as BypassAccessFlag;
use Ordermind\LogicalAuthorizationBundle\PermissionTypes\Flag\Flags\HasAccount as HasAccountFlag;
use Ordermind\LogicalAuthorizationBundle\PermissionTypes\Flag\Flags\IsAuthor as IsAuthorFlag;
use Ordermind\LogicalAuthorizationBundle\PermissionTypes\Flag\FlagManager;
use Ordermind\LogicalAuthorizationBundle\Tests\Fixtures\Model\ErroneousUser;
use Ordermind\LogicalAuthorizationBundle\Tests\Fixtures\Model\TestUser;
use Ordermind\LogicalAuthorizationBundle\Tests\Fixtures\Model\ErroneousModel;
use Ordermind\LogicalAuthorizationBundle\Tests\Fixtures\Model\TestModelBoolean;
use Ordermind\LogicalAuthorizationBundle\Tests\Fixtures\ModelDecorator\ModelDecorator;
use Ordermind\LogicalAuthorizationBundle\Tests\Fixtures\PermissionTypes\TestFlag;
use Ordermind\LogicalAuthorizationBundle\PermissionTypes\Role\Role;
use Ordermind\LogicalAuthorizationBundle\PermissionTypes\Host\Host;
use Ordermind\LogicalAuthorizationBundle\PermissionTypes\Method\Method;
use Ordermind\LogicalAuthorizationBundle\PermissionTypes\Ip\Ip;
use Ordermind\LogicalAuthorizationBundle\Tests\Fixtures\PermissionTypes\TestType;
use Ordermind\LogicalAuthorizationBundle\Event\AddPermissionsEvent;
use Ordermind\LogicalAuthorizationBundle\DataCollector\Collector;

class LogicalAuthorizationMethodTest extends LogicalAuthorizationBase {

  /*------------ Permission types ---------------*/

  // --- Flag --- //

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

  public function testFlagBypassAccessAnonymousUserDisallow() {
    $flag = new BypassAccessFlag();
    $this->assertFalse($flag->checkFlag(['user' => 'anon.']));
  }

  public function testFlagBypassAccessDisallow() {
    $user = new TestUser();
    $flag = new BypassAccessFlag();
    $this->assertFalse($flag->checkFlag(['user' => $user]));
  }

  public function testFlagBypassAccessAllow() {
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

  public function testFlagHasAccountDisallow() {
    $flag = new HasAccountFlag();
    $this->assertFalse($flag->checkFlag(['user' => 'anon.']));
  }

  public function testFlagHasAccountAllow() {
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

  public function testFlagIsAuthorModelClassString() {
    $user = new TestUser();
    $flag = new IsAuthorFlag();
    $this->assertFalse($flag->checkFlag(['user' => $user, 'model' => 'Ordermind\LogicalAuthorizationBundle\Tests\Fixtures\Model\TestUser']));
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
    $model = new ErroneousModel();
    $flag = new IsAuthorFlag();
    $flag->checkFlag(['user' => $user, 'model' => $model]);
  }

  public function testFlagIsAuthorModelAnonymousUserDisallow() {
    $model = new TestModelBoolean();
    $flag = new IsAuthorFlag();
    $this->assertFalse($flag->checkFlag(['user' => 'anon.', 'model' => $model]));
  }

  public function testFlagIsAuthorModelAnonymousAuthorAllow() {
    $user = new TestUser();
    $model = new TestModelBoolean();
    $flag = new IsAuthorFlag();
    $this->assertTrue($flag->checkFlag(['user' => $user, 'model' => $model]));
  }

  public function testFlagIsAuthorModelAllow() {
    $user = new TestUser();
    $model = new TestModelBoolean();
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

  // --- Role --- //

  /**
    * @expectedException InvalidArgumentException
    */
  public function testRoleWrongRoleType() {
    $role = new Role($this->roleHierarchy);
    $role->checkPermission(true, []);
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testRoleEmptyRole() {
    $role = new Role($this->roleHierarchy);
    $role->checkPermission('', []);
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testRoleWrongContextType() {
    $role = new Role($this->roleHierarchy);
    $role->checkPermission('ROLE_USER', null);
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testRoleMissingUser() {
    $role = new Role($this->roleHierarchy);
    $role->checkPermission('ROLE_USER', []);
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testRoleWrongUserType() {
    $role = new Role($this->roleHierarchy);
    $role->checkPermission('ROLE_USER', ['user' => []]);
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testRoleInvalidRoleType() {
    $user = new TestUser();
    $roles = array(
      'ROLE_ADMIN',
      5,
    );
    $user->setRoles($roles);
    $role = new Role($this->roleHierarchy);
    $role->checkPermission('ROLE_USER', ['user' => $user]);
  }

  public function testRoleAnonymousUserDisallow() {
    $role = new Role($this->roleHierarchy);
    $this->assertFalse($role->checkPermission('ROLE_USER', ['user' => 'anon.']));
  }

  public function testRoleDisallow() {
    $user = new TestUser();
    $role = new Role($this->roleHierarchy);
    $this->assertFalse($role->checkPermission('ROLE_ADMIN', ['user' => $user]));
  }

  public function testRoleAllow() {
    $user = new TestUser();
    $role = new Role($this->roleHierarchy);
    $this->assertTrue($role->checkPermission('ROLE_USER', ['user' => $user]));
    $user->setRoles(['ROLE_ADMIN']);
    $this->assertTrue($role->checkPermission('ROLE_ADMIN', ['user' => $user]));
  }

  public function testRoleHierarchyDisallow() {
    $user = new TestUser();
    $user->setRoles(['ROLE_ADMIN']);
    $role = new Role($this->roleHierarchy);
    $this->assertFalse($role->checkPermission('ROLE_CHILD', ['user' => $user]));
  }

  public function testRoleHierarchyAllow() {
    $user = new TestUser();
    $user->setRoles(['ROLE_PARENT']);
    $role = new Role($this->roleHierarchy);
    $this->assertTrue($role->checkPermission('ROLE_CHILD', ['user' => $user]));
  }

  // --- Host --- //

  /**
    * @expectedException InvalidArgumentException
    */
  public function testHostWrongHostType() {
    $requestStack = new RequestStack();
    $host = new Host($requestStack);
    $host->checkPermission(1, []);
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testHostEmptyHost() {
    $requestStack = new RequestStack();
    $host = new Host($requestStack);
    $host->checkPermission('', []);
  }

  public function testHostDisallow() {
    $requestStack = new RequestStack();
    $request = Request::create('https://test.com/test', 'GET', [], [], [], ['REMOTE_ADDR' => '127.0.0.55']);
    $requestStack->push($request);
    $host = new Host($requestStack);
    $this->assertFalse($host->checkPermission('test.se', []));
  }

  public function testHostAllow() {
    $requestStack = new RequestStack();
    $request = Request::create('https://test.com/test', 'GET', [], [], [], ['REMOTE_ADDR' => '127.0.0.55']);
    $requestStack->push($request);
    $host = new Host($requestStack);
    $this->assertTrue($host->checkPermission('test.com', []));
  }

  // --- Method --- //

  /**
    * @expectedException InvalidArgumentException
    */
  public function testMethodWrongMethodType() {
    $requestStack = new RequestStack();
    $method = new Method($requestStack);
    $method->checkPermission(1, []);
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testMethodEmptyMethod() {
    $requestStack = new RequestStack();
    $method = new Method($requestStack);
    $method->checkPermission('', []);
  }

  public function testMethodDisallow() {
    $requestStack = new RequestStack();
    $request = Request::create('https://test.com/test', 'GET', [], [], [], ['REMOTE_ADDR' => '127.0.0.55']);
    $requestStack->push($request);
    $method = new Method($requestStack);
    $this->assertFalse($method->checkPermission('PUSH', []));
  }

  public function testMethodAllow() {
    $requestStack = new RequestStack();
    $request = Request::create('https://test.com/test', 'GET', [], [], [], ['REMOTE_ADDR' => '127.0.0.55']);
    $requestStack->push($request);
    $method = new Method($requestStack);
    $this->assertTrue($method->checkPermission('GET', []));
  }

  // --- Ip --- //

  /**
    * @expectedException InvalidArgumentException
    */
  public function testIpWrongIpType() {
    $requestStack = new RequestStack();
    $ip = new Ip($requestStack);
    $ip->checkPermission(1, []);
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testIpEmptyIp() {
    $requestStack = new RequestStack();
    $ip = new Ip($requestStack);
    $ip->checkPermission('', []);
  }

  public function testIpDisallow() {
    $requestStack = new RequestStack();
    $request = Request::create('https://test.com/test', 'GET', [], [], [], ['REMOTE_ADDR' => '127.0.0.55']);
    $requestStack->push($request);
    $ip = new Ip($requestStack);
    $this->assertFalse($ip->checkPermission('127.0.0.1', []));
  }

  public function testIpAllow() {
    $requestStack = new RequestStack();
    $request = Request::create('https://test.com/test', 'GET', [], [], [], ['REMOTE_ADDR' => '127.0.0.55']);
    $requestStack->push($request);
    $ip = new Ip($requestStack);
    $this->assertTrue($ip->checkPermission('127.0.0.55', []));
  }

  /*------------ Services -------------*/

  public function testHelperCurrentUser() {
    $this->sendRequestAs('GET', '/test/get-current-username', [], static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(static::$authenticated_user->getUsername(), $response->getContent());
  }

  public function testHelperCurrentUserAnonymous() {
    $this->sendRequestAs('GET', '/test/get-current-username');
    $response = $this->client->getResponse();
    $this->assertSame('anon.', $response->getContent());
  }

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

  public function testCheckAccessDisallow() {
    $lpProxy = new LogicalPermissionsProxy();
    $type = new TestType();
    $lpProxy->addType($type);
    $lpProxy->setBypassCallback(function($context) { return false; });
    $la = new LogicalAuthorization($lpProxy, $this->helper);
    $this->assertFalse($la->checkAccess(['test' => 'no'], []));
  }

  public function testCheckAccessAllow() {
    $lpProxy = new LogicalPermissionsProxy();
    $type = new TestType();
    $lpProxy->addType($type);
    $lpProxy->setBypassCallback(function($context) { return false; });
    $la = new LogicalAuthorization($lpProxy, $this->helper);
    $this->assertTrue($la->checkAccess(['test' => 'yes'], []));
  }

  public function testGetAvailableActionsModelClass() {
    $model = new TestModelBoolean();
    $available_actions = $this->laModel->getAvailableActions(get_class($model), ['create', 'read', 'update', 'delete'], ['get', 'set'], 'anon.');
    foreach($available_actions as $key => $value) {
      if($key !== 'fields') {
        $this->assertSame($key, $value);
        continue;
      }
      foreach($value as $field_name => $field_actions) {
        $this->assertTrue(property_exists($model, $field_name));
        foreach($field_actions as $field_action_key => $field_action_value) {
          $this->assertSame($field_action_key, $field_action_value);
        }
      }
    }
  }

  public function testGetAvailableActionsModelObject() {
    $model = new TestModelBoolean();
    $available_actions_model = $this->laModel->getAvailableActions($model, ['create', 'read', 'update', 'delete'], ['get', 'set'], 'anon.');
    $available_actions_class = $this->laModel->getAvailableActions(get_class($model), ['create', 'read', 'update', 'delete'], ['get', 'set'], 'anon.');
    $this->assertSame($available_actions_model, $available_actions_class);
  }

  public function testGetAvailableActionsModelDecorator() {
    $model = new TestModelBoolean();
    $modelDecorator = new ModelDecorator($model);
    $available_actions_model_decorator = $this->laModel->getAvailableActions($modelDecorator, ['create', 'read', 'update', 'delete'], ['get', 'set'], 'anon.');
    $available_actions_class = $this->laModel->getAvailableActions(get_class($model), ['create', 'read', 'update', 'delete'], ['get', 'set'], 'anon.');
    $this->assertSame($available_actions_model_decorator, $available_actions_class);
  }

  /**
    * @expectedException Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException
    */
  public function testCheckModelAccessWrongModelType() {
    $user = new TestUser();
    $this->laModel->checkModelAccess(null, 'read', $user);
  }

  /**
    * @expectedException Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException
    */
  public function testCheckModelAccessModelClassDoesntExist() {
    $user = new TestUser();
    $this->laModel->checkModelAccess('TestModelBoolean', 'read', $user);
  }

  /**
    * @expectedException Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException
    */
  public function testCheckModelAccessWrongActionType() {
    $user = new TestUser();
    $model = new TestModelBoolean();
    $this->laModel->checkModelAccess($model, null, $user);
  }

  /**
    * @expectedException Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException
    */
  public function testCheckModelAccessEmptyAction() {
    $user = new TestUser();
    $model = new TestModelBoolean();
    $this->laModel->checkModelAccess($model, '', $user);
  }

  /**
    * @expectedException Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException
    */
  public function testCheckModelAccessWrongUserType() {
    $model = new TestModelBoolean();
    $this->laModel->checkModelAccess($model, 'read', []);
  }

  public function testCheckModelAccessMissingUser() {
    $model = new TestModelBoolean();
    $this->assertTrue($this->laModel->checkModelAccess($model, 'read'));
  }

  public function testCheckModelAccessMissingPermissions() {
    $user = new TestUser();
    $model = new ErroneousModel();
    $this->assertTrue($this->laModel->checkModelAccess($model, 'read', $user));
  }

  public function testCheckModelAccessClassDisallow() {
    $user = new TestUser();
    $model = new TestModelBoolean();
    $this->assertFalse($this->laModel->checkModelAccess(get_class($model), 'read', $user));
  }

  public function testCheckModelAccessClassAllow() {
    $user = new TestUser();
    $model = new TestModelBoolean();
    $this->assertTrue($this->laModel->checkModelAccess(get_class($model), 'create', $user));
  }

  public function testCheckModelAccessObjectDisallow() {
    $user = new TestUser();
    $model = new TestModelBoolean();
    $this->assertFalse($this->laModel->checkModelAccess($model, 'read', $user));
  }

  public function testCheckModelAccessObjectAllow() {
    $user = new TestUser();
    $model = new TestModelBoolean();
    $this->assertTrue($this->laModel->checkModelAccess($model, 'create', $user));
  }

  public function testCheckModelDecoratorAccessDisallow() {
    $user = new TestUser();
    $model = new TestModelBoolean();
    $modelDecorator = new ModelDecorator($model);
    $this->assertFalse($this->laModel->checkModelAccess($modelDecorator, 'read', $user));
  }

  public function testCheckModelDecoratorAccessAllow() {
    $user = new TestUser();
    $model = new TestModelBoolean();
    $modelDecorator = new ModelDecorator($model);
    $this->assertTrue($this->laModel->checkModelAccess($modelDecorator, 'create', $user));
  }

  /**
    * @expectedException Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException
    */
  public function testCheckFieldAccessWrongModelType() {
    $user = new TestUser();
    $this->laModel->checkFieldAccess(null, null, null, $user);
  }

  /**
    * @expectedException Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException
    */
  public function testCheckFieldAccessModelClassDoesntExist() {
    $user = new TestUser();
    $this->laModel->checkFieldAccess('TestModelBoolean', null, null, $user);
  }

  /**
    * @expectedException Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException
    */
  public function testCheckFieldAccessWrongFieldType() {
    $user = new TestUser();
    $model = new TestModelBoolean();
    $this->laModel->checkFieldAccess($model, null, null, $user);
  }

  /**
    * @expectedException Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException
    */
  public function testCheckFieldAccessEmptyField() {
    $user = new TestUser();
    $model = new TestModelBoolean();
    $this->laModel->checkFieldAccess($model, '', null, $user);
  }

  /**
    * @expectedException Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException
    */
  public function testCheckFieldAccessWrongActionType() {
    $user = new TestUser();
    $model = new TestModelBoolean();
    $this->laModel->checkFieldAccess($model, 'field1', null, $user);
  }

  /**
    * @expectedException Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException
    */
  public function testCheckFieldAccessEmptyAction() {
    $user = new TestUser();
    $model = new TestModelBoolean();
    $this->laModel->checkFieldAccess($model, 'field1', '', $user);
  }

  /**
    * @expectedException Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException
    */
  public function testCheckFieldAccessWrongUserType() {
    $model = new TestModelBoolean();
    $this->laModel->checkFieldAccess($model, 'field1', 'get', []);
  }

  public function testCheckFieldAccessMissingUser() {
    $model = new TestModelBoolean();
    $this->assertTrue($this->laModel->checkFieldAccess($model, 'field1', 'set'));
  }

  public function testCheckFieldAccessMissingModelPermissions() {
    $user = new TestUser();
    $model = new ErroneousModel();
    $this->assertTrue($this->laModel->checkFieldAccess($model, 'field1', 'get', $user));
  }

  public function testCheckFieldAccessMissingFieldPermissions() {
    $user = new TestUser();
    $model = new TestModelBoolean();
    $this->assertTrue($this->laModel->checkFieldAccess($model, 'test', 'set', $user));
  }

  public function testCheckFieldAccessWrongAction() {
    $user = new TestUser();
    $model = new TestModelBoolean();
    $this->assertTrue($this->laModel->checkFieldAccess($model, 'field1', 'read', $user));
  }

  public function testCheckFieldAccessClassDisallow() {
    $user = new TestUser();
    $model = new TestModelBoolean();
    $this->assertFalse($this->laModel->checkFieldAccess(get_class($model), 'field1', 'set', $user));
  }

  public function testCheckFieldAccessClassAllow() {
    $user = new TestUser();
    $model = new TestModelBoolean();
    $this->assertTrue($this->laModel->checkFieldAccess(get_class($model), 'field1', 'get', $user));
  }

  public function testCheckFieldAccessObjectDisallow() {
    $user = new TestUser();
    $model = new TestModelBoolean();
    $this->assertFalse($this->laModel->checkFieldAccess($model, 'field1', 'set', $user));
  }

  public function testCheckFieldAccessObjectAllow() {
    $user = new TestUser();
    $model = new TestModelBoolean();
    $this->assertTrue($this->laModel->checkFieldAccess($model, 'field1', 'get', $user));
  }

  public function testCheckModelDecoratorFieldAccessDisallow() {
    $user = new TestUser();
    $model = new TestModelBoolean();
    $modelDecorator = new ModelDecorator($model);
    $this->assertFalse($this->laModel->checkFieldAccess($modelDecorator, 'field1', 'set', $user));
  }

  public function testCheckModelDecoratorFieldAccessAllow() {
    $user = new TestUser();
    $model = new TestModelBoolean();
    $modelDecorator = new ModelDecorator($model);
    $this->assertTrue($this->laModel->checkFieldAccess($modelDecorator, 'field1', 'get', $user));
  }

  public function testGetAvailableRoutes() {
    $available_routes = $this->laRoute->getAvailableRoutes('anon.');
    $this->assertTrue(isset($available_routes['routes']) && is_array($available_routes['routes']) && !empty($available_routes['routes']));
    foreach($available_routes['routes'] as $key => $value) {
      $this->assertSame($key, $value);
    }
    $this->assertTrue(isset($available_routes['route_patterns']) && is_array($available_routes['route_patterns']) && !empty($available_routes['route_patterns']));
    foreach($available_routes['route_patterns'] as $key => $value) {
      $this->assertSame($key, $value);
    }
  }

  /**
    * @expectedException Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException
    */
  public function testCheckRouteAccessWrongRouteType() {
    $user = new TestUser();
    $this->laRoute->checkRouteAccess(null, $user);
  }

  /**
    * @expectedException Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException
    */
  public function testCheckRouteAccessEmptyRoute() {
    $user = new TestUser();
    $this->laRoute->checkRouteAccess('', $user);
  }

  /**
    * @expectedException Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException
    */
  public function testCheckRouteAccessWrongUserType() {
    $this->laRoute->checkRouteAccess('route_allowed', []);
  }

  /**
    * @expectedException Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException
    */
  public function testCheckRouteAccessRouteDoesntExist() {
    $user = new TestUser();
    $this->laRoute->checkRouteAccess('hej', $user);
  }

  public function testCheckRouteAccessMissingUser() {
    $this->assertTrue($this->laRoute->checkRouteAccess('route_no_bypass'));
  }

  public function testCheckRouteAccessDisallow() {
    $this->assertFalse($this->laRoute->checkRouteAccess('route_no_bypass', 'anon.'));
  }

  public function testCheckRouteAccessAllow() {
    $this->assertTrue($this->laRoute->checkRouteAccess('route_allowed', 'anon.'));
  }

  /**
    * @expectedException Ordermind\LogicalPermissions\Exceptions\PermissionTypeAlreadyExistsException
    */
  public function testLogicalPermissionsProxyAddTypeAlreadyExists() {
    $laProxy = new LogicalPermissionsProxy();
    $type = new TestType();
    $laProxy->addType($type);
    $laProxy->addType($type);
  }

  public function testLogicalPermissionsProxyAddType() {
    $laProxy = new LogicalPermissionsProxy();
    $type = new TestType();
    $laProxy->addType($type);
    $this->assertTrue($laProxy->typeExists('test'));
  }

  /**
    * @expectedException Ordermind\LogicalPermissions\Exceptions\PermissionTypeNotRegisteredException
    */
  public function testLogicalPermissionsProxyCheckAccessTypeDoesntExist() {
    $laProxy = new LogicalPermissionsProxy();
    $laProxy->checkAccess(['test' => 'hej'], []);
  }

  public function testGetTree() {
    $tree = $this->treeBuilder->getTree();
    $this->assertTrue(!isset($tree['fetch']));
    $tree = $this->treeBuilder->getTree(false, true);
    $this->assertSame('static_cache', $tree['fetch']);
    $tree = $this->treeBuilder->getTree(true, true);
    $this->assertSame('no_cache', $tree['fetch']);
  }

  public function testGetTreeFromCache() {
    $tree = $this->treeBuilder->getTree(false, true);
    $this->assertSame('cache', $tree['fetch']);
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testEventInsertTreeWrongTreeType() {
    $laProxy = new LogicalPermissionsProxy();
    $event = new AddPermissionsEvent($laProxy->getValidPermissionKeys());
    $event->insertTree('key');
  }

  public function testEventInsertTreeGetTree() {
    $laProxy = new LogicalPermissionsProxy();
    $role = new Role($this->roleHierarchy);
    $laProxy->addType($role);
    $flagManager = new FlagManager();
    $laProxy->addType($flagManager);
    $event = new AddPermissionsEvent($laProxy->getValidPermissionKeys());
    $tree1 = [
      'models' => [
        'testmodel' => [
          'create' => [
            'role' => 'role1',
          ],
          'read' => [
            'flag' => [
              'flag1',
              'flag2',
            ],
          ],
          'update' => [
            'flag' => 'flag1',
          ],
          'fields' => [
            'field1' => [
              'get' => [
                'role' => 'role1',
              ],
              'set' => [
                'flag' => 'flag1',
              ],
            ],
          ],
        ],
      ],
    ];
    $tree2 = [
      'models' => [
        'testmodel' => [
          'create' => [
            'role' => [
              'newrole1',
              'newrole2',
            ],
          ],
          'read' => [
            'flag' => 'newflag1',
          ],
          'fields' => [
            'field1' => [
              'get' => [
                'OR' => [
                  'role' => 'newrole1',
                  'flag' => 'newflag1',
                ],
              ],
            ],
          ],
        ],
      ],
    ];

    $result = [
      'models' => [
        'testmodel' => [
          'create' => [
            'role' => [
              'newrole1',
              'newrole2',
            ],
          ],
          'read' => [
            'flag' => 'newflag1',
          ],
          'update' => [
            'flag' => 'flag1',
          ],
          'fields' => [
            'field1' => [
              'get' => [
                'OR' => [
                  'role' => 'newrole1',
                  'flag' => 'newflag1',
                ],
              ],
              'set' => [
                'flag' => 'flag1',
              ],
            ],
          ],
        ],
      ],
    ];

    $event->insertTree($tree1);
    $event->insertTree($tree2);
    $this->assertSame($result, $event->getTree());
  }

  public function testDebugCollectorRouteLogFormat() {
    $request = new Request();
    $response = new Response();
    $user = new TestUser();
    $debugCollector = new Collector($this->treeBuilder, $this->lpProxy, $this->twig);
    $debugCollector->addPermissionCheck(true, 'route', 'route_role', $user, [], ['user' => $user]);
    $debugCollector->collect($request, $response);
    $log = $debugCollector->getLog();

    $first_item = array_shift($log);
    $this->assertSame('route', $first_item['type']);
    $this->assertSame('route_role', $first_item['item_name']);
    $this->assertArrayNotHasKey('item', $first_item);
    $this->assertArrayNotHasKey('action', $first_item);
    $this->assertSame($user, $first_item['user']);
    $this->assertSame([], $first_item['permissions']);
    $this->assertArrayNotHasKey('context', $first_item);
  }

  public function testDebugCollectorModelLogFormat() {
    $request = new Request();
    $response = new Response();
    $user = new TestUser();
    $model = new TestModelBoolean();
    $model->setAuthor($user);
    $debugCollector = new Collector($this->treeBuilder, $this->lpProxy, $this->twig);
    $debugCollector->addPermissionCheck(true, 'model', array('model' => $model, 'action' => 'read'), $user, [], ['user' => $user]);
    $debugCollector->collect($request, $response);
    $log = $debugCollector->getLog();

    $first_item = array_shift($log);
    $this->assertSame('model', $first_item['type']);
    $this->assertSame(get_class($model), $first_item['item_name']);
    $this->assertSame($model, $first_item['item']);
    $this->assertSame('read', $first_item['action']);
    $this->assertSame($user, $first_item['user']);
    $this->assertSame([], $first_item['permissions']);
    $this->assertArrayNotHasKey('context', $first_item);
  }

  public function testDebugCollectorFieldLogFormat() {
    $request = new Request();
    $response = new Response();
    $user = new TestUser();
    $model = new TestModelBoolean();
    $model->setAuthor($user);
    $debugCollector = new Collector($this->treeBuilder, $this->lpProxy, $this->twig);
    $debugCollector->addPermissionCheck(true, 'field', array('model' => $model, 'field' => 'field1', 'action' => 'get'), $user, [], ['user' => $user]);
    $debugCollector->collect($request, $response);
    $log = $debugCollector->getLog();

    $first_item = array_shift($log);
    $this->assertSame('field', $first_item['type']);
    $this->assertSame(get_class($model) . ':field1', $first_item['item_name']);
    $this->assertSame($model, $first_item['item']);
    $this->assertSame('get', $first_item['action']);
    $this->assertSame($user, $first_item['user']);
    $this->assertSame([], $first_item['permissions']);
    $this->assertArrayNotHasKey('context', $first_item);
  }

  public function testDebugCollectorPermissionFormatBoolean() {
    $request = new Request();
    $response = new Response();
    $debugCollector = new Collector($this->treeBuilder, $this->lpProxy, $this->twig);
    $user = new TestUser();
    $model = new TestModelBoolean();
    $model->setAuthor($user);

    $permissions = true;
    $debugCollector->addPermissionCheck(true, 'field', array('model' => $model, 'field' => 'field1', 'action' => 'get'), static::$superadmin_user, $permissions, ['model' => $model, 'user' => static::$superadmin_user]);
    $result = [
      'type' => 'field',
      'field' => 'field1',
      'user' => static::$superadmin_user,
      'permissions' => $permissions,
      'action' => 'get',
      'item_name' => 'Ordermind\LogicalAuthorizationBundle\Tests\Fixtures\Model\TestModelBoolean:field1',
      'item' => $model,
      'permission_no_bypass_checks' => [],
      'bypassed_access' => true,
      'permission_checks' => [['permissions' => true, 'resolve' => true]],
      'access' => true,
      'message' => '',
    ];

    $debugCollector->collect($request, $response);
    $log = $debugCollector->getLog();
    $item = array_shift($log);
    foreach($item as $key => $value) {
      if($key === 'backtrace') {
        $this->assertEquals(10, count($value));
        $this->assertSame('testDebugCollectorPermissionFormatBoolean', $value[0]['function']);
        continue;
      }
      $this->assertSame($result[$key], $value);
    }
  }

  public function testDebugCollectorPermissionFormatTypeClose() {
    $request = new Request();
    $response = new Response();
    $debugCollector = new Collector($this->treeBuilder, $this->lpProxy, $this->twig);
    $user = new TestUser();
    $model = new TestModelBoolean();
    $model->setAuthor($user);

    $permissions = [
      'NOT' => [
        'flag' => 'has_account',
      ],
    ];
    $debugCollector->addPermissionCheck(true, 'route', 'testroute', 'anon.', $permissions, ['user' => 'anon.']);
    $result = [
      'type' => 'route',
      'user' => 'anon.',
      'item_name' => 'testroute',
      'permission_no_bypass_checks' => [],
      'bypassed_access' => false,
      'permissions' => $permissions,
      'access' => true,
      'permission_checks' => [['permissions' => ['NOT' => ['flag' => 'has_account']], 'resolve' => true], ['permissions' => ['flag' => 'has_account'], 'resolve' => false]],
      'message' => '',
    ];

    $debugCollector->collect($request, $response);
    $log = $debugCollector->getLog();
    $item = array_shift($log);
    foreach($item as $key => $value) {
      if($key === 'backtrace') {
        $this->assertEquals(10, count($value));
        $this->assertSame('testDebugCollectorPermissionFormatTypeClose', $value[0]['function']);
        continue;
      }
      $this->assertSame($result[$key], $value);
    }
  }

  public function testDebugCollectorPermissionFormatTypeSeparate() {
    $request = new Request();
    $response = new Response();
    $debugCollector = new Collector($this->treeBuilder, $this->lpProxy, $this->twig);
    $user = new TestUser();
    $model = new TestModelBoolean();
    $model->setAuthor($user);

    $permissions = [
      'flag' => [
        'NOT' => 'has_account',
      ],
    ];
    $debugCollector->addPermissionCheck(true, 'route', 'testroute', 'anon.', $permissions, ['user' => 'anon.']);
    $result = [
      'type' => 'route',
      'user' => 'anon.',
      'item_name' => 'testroute',
      'permission_no_bypass_checks' => [],
      'bypassed_access' => false,
      'permissions' => $permissions,
      'access' => true,
      'permission_checks' => [['permissions' => ['flag' => ['NOT' => 'has_account']], 'resolve' => true], ['permissions' => ['NOT' => 'has_account'], 'resolve' => true], ['permissions' => 'has_account', 'resolve' => false]],
      'message' => '',
    ];

    $debugCollector->collect($request, $response);
    $log = $debugCollector->getLog();
    $item = array_shift($log);
    foreach($item as $key => $value) {
      if($key === 'backtrace') {
        $this->assertEquals(10, count($value));
        $this->assertSame('testDebugCollectorPermissionFormatTypeSeparate', $value[0]['function']);
        continue;
      }
      $this->assertSame($result[$key], $value);
    }
  }

  public function testDebugCollectorPermissionFormatMixed() {
    $request = new Request();
    $response = new Response();
    $debugCollector = new Collector($this->treeBuilder, $this->lpProxy, $this->twig);
    $user = new TestUser();
    $model = new TestModelBoolean();
    $model->setAuthor($user);

    $permissions = [
      'NO_BYPASS' => [
        'NOT' => [
          'flag' => 'has_account',
        ],
      ],
      'AND' => [
        'role' => [
          'OR' => [
            'NOT' => [
              'AND' => [
                'ROLE_ADMIN',
                'ROLE_ADMIN',
              ],
            ],
          ],
        ],
        TRUE,
        'TRUE',
        'flag' => [
          'NOT' => [
            'OR' => [
              ['NOT' => 'has_account'],
              ['NOT' => 'is_author'],
            ],
          ],
        ],
      ],
      'flag' => 'has_account',
    ];
    $debugCollector->addPermissionCheck(true, 'field', array('model' => $model, 'field' => 'field1', 'action' => 'get'), $user, $permissions, ['model' => $model, 'user' => $user]);
    $result = [
      'type' => 'field',
      'field' => 'field1',
      'user' => $user,
      'permissions' => $permissions,
      'action' => 'get',
      'item_name' => 'Ordermind\LogicalAuthorizationBundle\Tests\Fixtures\Model\TestModelBoolean:field1',
      'item' => $model,
      'permission_no_bypass_checks' => array_reverse([['permissions' => ['flag' => 'has_account'], 'resolve' => true], ['permissions' => ['NOT' => ['flag' => 'has_account']], 'resolve' => false]]),
      'bypassed_access' => false,
      'permission_checks' => [],
      'permission_checks' => array_reverse([
        0 => ['permissions' => 'ROLE_ADMIN', 'resolve' => false],
        1 => ['permissions' => 'ROLE_ADMIN', 'resolve' => false],
        2 => ['permissions' => ['AND' => ['ROLE_ADMIN','ROLE_ADMIN']], 'resolve' => false],
        3 => ['permissions' => ['NOT' => ['AND' => ['ROLE_ADMIN','ROLE_ADMIN']]], 'resolve' => true],
        4 => ['permissions' => ['OR' => ['NOT' => ['AND' => ['ROLE_ADMIN','ROLE_ADMIN']]]], 'resolve' => true],
        5 => ['permissions' => ['role' => ['OR' => ['NOT' => ['AND' => ['ROLE_ADMIN','ROLE_ADMIN']]]]], 'resolve' => true],
        6 => ['permissions' => true, 'resolve' => true],
        7 => ['permissions' => 'TRUE', 'resolve' => true],
        8 => ['permissions' => 'has_account', 'resolve' => true],
        9 => ['permissions' => ['NOT' => 'has_account'], 'resolve' => false],
        10 => ['permissions' => 'is_author', 'resolve' => true],
        11 => ['permissions' => ['NOT' => 'is_author'], 'resolve' => false],
        12 => ['permissions' => ['OR' => [['NOT' => 'has_account'],['NOT' => 'is_author']]], 'resolve' => false],
        13 => ['permissions' => ['NOT' => ['OR' => [['NOT' => 'has_account'],['NOT' => 'is_author']]]], 'resolve' => true],
        14 => ['permissions' => ['flag' => ['NOT' => ['OR' => [['NOT' => 'has_account'],['NOT' => 'is_author']]]]], 'resolve' => true],
        15 => ['permissions' => ['AND' => ['role' => ['OR' => ['NOT' => ['AND' => ['ROLE_ADMIN','ROLE_ADMIN']]]],'0' => true,'1' => 'TRUE','flag' => ['NOT' => ['OR' => [['NOT' => 'has_account'],['NOT' => 'is_author']]]]]], 'resolve' => true],
        16 => ['permissions' => ['flag' => 'has_account'], 'resolve' => true],
        17 => ['permissions' => ['AND' => ['role' => ['OR' => ['NOT' => ['AND' => ['ROLE_ADMIN','ROLE_ADMIN']]]],'0' => true,'1' => 'TRUE','flag' => ['NOT' => ['OR' => [['NOT' => 'has_account'],['NOT' => 'is_author']]]]],'flag' => 'has_account'], 'resolve' => true],
      ]),
      'access' => true,
      'message' => '',
    ];

    $debugCollector->collect($request, $response);
    $log = $debugCollector->getLog();
    $item = array_shift($log);
    foreach($item as $key => $value) {
      if($key === 'backtrace') {
        $this->assertEquals(10, count($value));
        $this->assertSame('testDebugCollectorPermissionFormatMixed', $value[0]['function']);
        continue;
      }
      if($key === 'permission_checks') {
        foreach($value as $i => $value2) {
          $this->assertSame($result[$key][$i], $value2);
        }
        continue;
      }
      $this->assertSame($result[$key], $value);
    }
  }

  public function testDebugCollectorExceptionHandling() {
    $request = new Request();
    $response = new Response();
    $debugCollector = new Collector($this->treeBuilder, $this->lpProxy, $this->twig);
    $user = new TestUser();
    $model = new TestModelBoolean();
    $model->setAuthor($user);
    $permissions = ['no_bypass' => true, 'flag' => ['flag' => 'has_account']];
    $debugCollector->addPermissionCheck(false, 'field', array('model' => $model, 'field' => 'field1', 'action' => 'get'), $user, $permissions, ['model' => $model, 'user' => $user]);
    $debugCollector->collect($request, $response);
    $log = $debugCollector->getLog();
    $first_item = array_shift($log);
    $this->assertArrayHasKey('message', $first_item);
  }

  public function testDebugCollectorExceptionHandlingNoDebug() {
    $request = new Request();
    $response = new Response();
    $debugCollector = new Collector($this->treeBuilder, $this->lpProxy, $this->twig);
    $user = new TestUser();
    $model = new TestModelBoolean();
    $model->setAuthor($user);
    $permissions = ['no_bypass' => ['flag' => ['flag' => 'has_account']], true];
    $debugCollector->addPermissionCheck(false, 'field', array('model' => $model, 'field' => 'field1', 'action' => 'get'), $user, $permissions, ['model' => $model, 'user' => $user]);
    $debugCollector->collect($request, $response);
    $log = $debugCollector->getLog();
    $first_item = array_shift($log);
    $this->assertArrayHasKey('message', $first_item);
  }
}
