<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Test\Functional\Services;

use InvalidArgumentException;
use Ordermind\LogicalAuthorizationBundle\DataCollector\Collector;
use Ordermind\LogicalAuthorizationBundle\Event\AddPermissionsEvent;
use Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException;
use Ordermind\LogicalAuthorizationBundle\PermissionType\Flag\FlagManager;
use Ordermind\LogicalAuthorizationBundle\PermissionType\Flag\Flags\UserCanBypassAccess as BypassAccessFlag;
use Ordermind\LogicalAuthorizationBundle\PermissionType\Flag\Flags\UserHasAccount as HasAccountFlag;
use Ordermind\LogicalAuthorizationBundle\PermissionType\Flag\Flags\UserIsAuthor as IsAuthorFlag;
use Ordermind\LogicalAuthorizationBundle\PermissionType\Host\Host;
use Ordermind\LogicalAuthorizationBundle\PermissionType\Ip\Ip;
use Ordermind\LogicalAuthorizationBundle\PermissionType\Method\Method;
use Ordermind\LogicalAuthorizationBundle\PermissionType\Role\Role;
use Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorization;
use Ordermind\LogicalAuthorizationBundle\Services\LogicalPermissionsProxy;
use Ordermind\LogicalAuthorizationBundle\Test\Fixtures\BypassAccessChecker\AlwaysDeny;
use Ordermind\LogicalAuthorizationBundle\Test\Fixtures\Model\ErroneousModel;
use Ordermind\LogicalAuthorizationBundle\Test\Fixtures\Model\ErroneousUser;
use Ordermind\LogicalAuthorizationBundle\Test\Fixtures\Model\TestModelBoolean;
use Ordermind\LogicalAuthorizationBundle\Test\Fixtures\Model\TestUser;
use Ordermind\LogicalAuthorizationBundle\Test\Fixtures\ModelDecorator\ModelDecorator;
use Ordermind\LogicalAuthorizationBundle\Test\Fixtures\PermissionTypes\TestFlag;
use Ordermind\LogicalAuthorizationBundle\Test\Fixtures\PermissionTypes\TestType;
use Ordermind\LogicalPermissions\Exceptions\PermissionTypeAlreadyExistsException;
use Ordermind\LogicalPermissions\Exceptions\PermissionTypeNotRegisteredException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use TypeError;

class LogicalAuthorizationMethodTest extends LogicalAuthorizationBase
{
    // ------------ Permission types ---------------

    // --- Flag --- //

    public function testFlagBypassAccessWrongContextType()
    {
        $this->expectException(TypeError::class);
        $flag = new BypassAccessFlag();
        $flag->checkFlag(null);
    }

    public function testFlagBypassAccessMissingUser()
    {
        $this->expectException(InvalidArgumentException::class);
        $flag = new BypassAccessFlag();
        $flag->checkFlag([]);
    }

    public function testFlagBypassAccessWrongUserType()
    {
        $this->expectException(InvalidArgumentException::class);
        $flag = new BypassAccessFlag();
        $flag->checkFlag(['user' => []]);
    }

    public function testFlagBypassAccessWrongReturnType()
    {
        $this->expectException(TypeError::class);
        $user = new ErroneousUser();
        $flag = new BypassAccessFlag();
        $flag->checkFlag(['user' => $user]);
    }

    public function testFlagBypassAccessAnonymousUserDisallow()
    {
        $flag = new BypassAccessFlag();
        $this->assertFalse($flag->checkFlag(['user' => 'anon.']));
    }

    public function testFlagBypassAccessDisallow()
    {
        $user = new TestUser();
        $flag = new BypassAccessFlag();
        $this->assertFalse($flag->checkFlag(['user' => $user]));
    }

    public function testFlagBypassAccessAllow()
    {
        $user = new TestUser();
        $user->setBypassAccess(true);
        $flag = new BypassAccessFlag();
        $this->assertTrue($flag->checkFlag(['user' => $user]));
    }

    public function testFlagHasAccountWrongContextType()
    {
        $this->expectException(TypeError::class);
        $flag = new HasAccountFlag();
        $flag->checkFlag(null);
    }

    public function testFlagHasAccountMissingUser()
    {
        $this->expectException(InvalidArgumentException::class);
        $flag = new HasAccountFlag();
        $flag->checkFlag([]);
    }

    public function testFlagHasAccountDisallow()
    {
        $flag = new HasAccountFlag();
        $this->assertFalse($flag->checkFlag(['user' => 'anon.']));
    }

    public function testFlagHasAccountAllow()
    {
        $user = new TestUser();
        $flag = new HasAccountFlag();
        $this->assertTrue($flag->checkFlag(['user' => $user]));
    }

    public function testFlagIsAuthorWrongContextType()
    {
        $this->expectException(TypeError::class);
        $flag = new IsAuthorFlag();
        $flag->checkFlag(null);
    }

    public function testFlagIsAuthorMissingUser()
    {
        $this->expectException(InvalidArgumentException::class);
        $flag = new IsAuthorFlag();
        $flag->checkFlag([]);
    }

    public function testFlagIsAuthorWrongUserType()
    {
        $this->expectException(InvalidArgumentException::class);
        $flag = new IsAuthorFlag();
        $flag->checkFlag(['user' => []]);
    }

    public function testFlagIsAuthorMissingModel()
    {
        $this->expectException(InvalidArgumentException::class);
        $user = new TestUser();
        $flag = new IsAuthorFlag();
        $flag->checkFlag(['user' => $user]);
    }

    public function testFlagIsAuthorModelClassString()
    {
        $user = new TestUser();
        $flag = new IsAuthorFlag();
        $this->assertFalse($flag->checkFlag(['user' => $user, 'model' => 'Ordermind\LogicalAuthorizationBundle\Test\Fixtures\Model\TestUser']));
    }

    public function testFlagIsAuthorWrongModelType()
    {
        $this->expectException(InvalidArgumentException::class);
        $user = new TestUser();
        $flag = new IsAuthorFlag();
        $flag->checkFlag(['user' => $user, 'model' => []]);
    }

    public function testFlagIsAuthorModelWrongAuthorType()
    {
        $this->expectException(TypeError::class);
        $user = new TestUser();
        $model = new ErroneousModel();
        $flag = new IsAuthorFlag();
        $flag->checkFlag(['user' => $user, 'model' => $model]);
    }

    public function testFlagIsAuthorModelAnonymousUserDisallow()
    {
        $model = new TestModelBoolean();
        $flag = new IsAuthorFlag();
        $this->assertFalse($flag->checkFlag(['user' => 'anon.', 'model' => $model]));
    }

    public function testFlagIsAuthorModelAnonymousAuthorAllow()
    {
        $user = new TestUser();
        $model = new TestModelBoolean();
        $flag = new IsAuthorFlag();
        $this->assertTrue($flag->checkFlag(['user' => $user, 'model' => $model]));
    }

    public function testFlagIsAuthorModelAllow()
    {
        $user = new TestUser();
        $model = new TestModelBoolean();
        $model->setAuthor($user);
        $flag = new IsAuthorFlag();
        $this->assertTrue($flag->checkFlag(['user' => $user, 'model' => $model]));
    }

    public function testFlagManagerAddFlagWrongNameType()
    {
        $this->expectException(TypeError::class);
        $flagManager = new FlagManager();
        $flag = new TestFlag();
        $flag->setName(true);
        $flagManager->addFlag($flag);
    }

    public function testFlagManagerAddFlagEmptyName()
    {
        $this->expectException(InvalidArgumentException::class);
        $flagManager = new FlagManager();
        $flag = new TestFlag();
        $flag->setName('');
        $flagManager->addFlag($flag);
    }

    public function testFlagManagerAddFlagAlreadyRegistered()
    {
        $this->expectException(InvalidArgumentException::class);
        $flagManager = new FlagManager();
        $flag = new TestFlag();
        $flag->setName('test');
        $flagManager->addFlag($flag);
        $flagManager->addFlag($flag);
    }

    public function testFlagManagerAddFlag()
    {
        $flagManager = new FlagManager();
        $flag = new TestFlag();
        $flag->setName('test');
        $flagManager->addFlag($flag);
        $flags = $flagManager->getFlags();
        $this->assertTrue(isset($flags['test']));
        $this->assertSame($flag, $flags['test']);
    }

    public function testFlagManagerRemoveFlagWrongNameType()
    {
        $this->expectException(TypeError::class);
        $flagManager = new FlagManager();
        $flag = new TestFlag();
        $flag->setName('test');
        $flagManager->addFlag($flag);
        $flagManager->removeFlag(true);
    }

    public function testFlagManagerRemoveFlagEmptyName()
    {
        $this->expectException(InvalidArgumentException::class);
        $flagManager = new FlagManager();
        $flag = new TestFlag();
        $flag->setName('test');
        $flagManager->addFlag($flag);
        $flagManager->removeFlag('');
    }

    public function testFlagManagerRemoveFlagNotRegistered()
    {
        $this->expectException(InvalidArgumentException::class);
        $flagManager = new FlagManager();
        $flagManager->removeFlag('test');
    }

    public function testFlagManagerRemoveFlag()
    {
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

    public function testFlagManagerCheckPermissionWrongNameType()
    {
        $this->expectException(TypeError::class);
        $flagManager = new FlagManager();
        $flagManager->checkPermission(true, []);
    }

    public function testFlagManagerCheckPermissionEmptyName()
    {
        $this->expectException(InvalidArgumentException::class);
        $flagManager = new FlagManager();
        $flagManager->checkPermission('', []);
    }

    public function testFlagManagerCheckPermissionNotRegistered()
    {
        $this->expectException(InvalidArgumentException::class);
        $flagManager = new FlagManager();
        $flagManager->checkPermission('test', []);
    }

    public function testFlagManagerCheckPermission()
    {
        $flagManager = new FlagManager();
        $flag = new TestFlag();
        $flag->setName('test');
        $flagManager->addFlag($flag);
        $this->assertTrue($flagManager->checkPermission('test', []));
    }

    // --- Role --- //

    public function testRoleWrongRoleType()
    {
        $this->expectException(TypeError::class);
        $role = new Role($this->roleHierarchy);
        $role->checkPermission(true, []);
    }

    public function testRoleEmptyRole()
    {
        $this->expectException(InvalidArgumentException::class);
        $role = new Role($this->roleHierarchy);
        $role->checkPermission('', []);
    }

    public function testRoleWrongContextType()
    {
        $this->expectException(TypeError::class);
        $role = new Role($this->roleHierarchy);
        $role->checkPermission('ROLE_USER', null);
    }

    public function testRoleMissingUser()
    {
        $this->expectException(InvalidArgumentException::class);
        $role = new Role($this->roleHierarchy);
        $role->checkPermission('ROLE_USER', []);
    }

    public function testRoleWrongUserType()
    {
        $this->expectException(InvalidArgumentException::class);
        $role = new Role($this->roleHierarchy);
        $role->checkPermission('ROLE_USER', ['user' => []]);
    }

    public function testRoleAnonymousUserDisallow()
    {
        $role = new Role($this->roleHierarchy);
        $this->assertFalse($role->checkPermission('ROLE_USER', ['user' => 'anon.']));
    }

    public function testRoleDisallow()
    {
        $user = new TestUser();
        $role = new Role($this->roleHierarchy);
        $this->assertFalse($role->checkPermission('ROLE_ADMIN', ['user' => $user]));
    }

    public function testRoleAllow()
    {
        $user = new TestUser();
        $role = new Role($this->roleHierarchy);
        $this->assertTrue($role->checkPermission('ROLE_USER', ['user' => $user]));
        $user->setRoles(['ROLE_ADMIN']);
        $this->assertTrue($role->checkPermission('ROLE_ADMIN', ['user' => $user]));
    }

    public function testRoleHierarchyDisallow()
    {
        $user = new TestUser();
        $user->setRoles(['ROLE_ADMIN']);
        $role = new Role($this->roleHierarchy);
        $this->assertFalse($role->checkPermission('ROLE_CHILD', ['user' => $user]));
    }

    public function testRoleHierarchyAllow()
    {
        $user = new TestUser();
        $user->setRoles(['ROLE_PARENT']);
        $role = new Role($this->roleHierarchy);
        $this->assertTrue($role->checkPermission('ROLE_CHILD', ['user' => $user]));
    }

    // --- Host --- //

    public function testHostWrongHostType()
    {
        $this->expectException(TypeError::class);
        $requestStack = new RequestStack();
        $host = new Host($requestStack);
        $host->checkPermission(1, []);
    }

    public function testHostEmptyHost()
    {
        $this->expectException(InvalidArgumentException::class);
        $requestStack = new RequestStack();
        $host = new Host($requestStack);
        $host->checkPermission('', []);
    }

    public function testHostDisallow()
    {
        $requestStack = new RequestStack();
        $request = Request::create('https://test.com/test', 'GET', [], [], [], ['REMOTE_ADDR' => '127.0.0.55']);
        $requestStack->push($request);
        $host = new Host($requestStack);
        $this->assertFalse($host->checkPermission('test.se', []));
    }

    public function testHostAllow()
    {
        $requestStack = new RequestStack();
        $request = Request::create('https://test.com/test', 'GET', [], [], [], ['REMOTE_ADDR' => '127.0.0.55']);
        $requestStack->push($request);
        $host = new Host($requestStack);
        $this->assertTrue($host->checkPermission('test.com', []));
    }

    // --- Method --- //

    public function testMethodWrongMethodType()
    {
        $this->expectException(TypeError::class);
        $requestStack = new RequestStack();
        $method = new Method($requestStack);
        $method->checkPermission(1, []);
    }

    public function testMethodEmptyMethod()
    {
        $this->expectException(InvalidArgumentException::class);
        $requestStack = new RequestStack();
        $method = new Method($requestStack);
        $method->checkPermission('', []);
    }

    public function testMethodDisallow()
    {
        $requestStack = new RequestStack();
        $request = Request::create('https://test.com/test', 'GET', [], [], [], ['REMOTE_ADDR' => '127.0.0.55']);
        $requestStack->push($request);
        $method = new Method($requestStack);
        $this->assertFalse($method->checkPermission('PUSH', []));
    }

    public function testMethodAllow()
    {
        $requestStack = new RequestStack();
        $request = Request::create('https://test.com/test', 'GET', [], [], [], ['REMOTE_ADDR' => '127.0.0.55']);
        $requestStack->push($request);
        $method = new Method($requestStack);
        $this->assertTrue($method->checkPermission('GET', []));
    }

    // --- Ip --- //

    public function testIpWrongIpType()
    {
        $this->expectException(TypeError::class);
        $requestStack = new RequestStack();
        $ip = new Ip($requestStack);
        $ip->checkPermission(1, []);
    }

    public function testIpEmptyIp()
    {
        $this->expectException(InvalidArgumentException::class);
        $requestStack = new RequestStack();
        $ip = new Ip($requestStack);
        $ip->checkPermission('', []);
    }

    public function testIpDisallow()
    {
        $requestStack = new RequestStack();
        $request = Request::create('https://test.com/test', 'GET', [], [], [], ['REMOTE_ADDR' => '127.0.0.55']);
        $requestStack->push($request);
        $ip = new Ip($requestStack);
        $this->assertFalse($ip->checkPermission('127.0.0.1', []));
    }

    public function testIpAllow()
    {
        $requestStack = new RequestStack();
        $request = Request::create('https://test.com/test', 'GET', [], [], [], ['REMOTE_ADDR' => '127.0.0.55']);
        $requestStack->push($request);
        $ip = new Ip($requestStack);
        $this->assertTrue($ip->checkPermission('127.0.0.55', []));
    }

    // ------------ Services -------------

    public function testHelperCurrentUser()
    {
        $this->sendRequestAs('GET', '/test/get-current-username', [], static::$authenticated_user);
        $response = $this->client->getResponse();
        $this->assertEquals(static::$authenticated_user->getUsername(), $response->getContent());
    }

    public function testHelperCurrentUserAnonymous()
    {
        $this->sendRequestAs('GET', '/test/get-current-username');
        $response = $this->client->getResponse();
        $this->assertSame('anon.', $response->getContent());
    }

    public function testCheckAccessPermissionTypeNotRegistered()
    {
        $this->expectException(LogicalAuthorizationException::class);
        $this->expectExceptionMessageMatches('/service tag to register a permission type/');
        $this->la->checkAccess(['test' => 'hej'], ['user' => 'anon.']);
    }

    public function testCheckAccessOtherExceptions()
    {
        $this->expectException(LogicalAuthorizationException::class);
        $this->expectExceptionMessageMatches('/^An exception was caught while checking access: /');
        $this->la->checkAccess(['test' => 'hej'], []);
    }

    public function testCheckAccessDisallow()
    {
        $lpProxy = new LogicalPermissionsProxy();
        $type = new TestType();
        $lpProxy->addType($type);
        $la = new LogicalAuthorization($lpProxy, $this->helper, new AlwaysDeny());
        $this->assertFalse($la->checkAccess(['test' => 'no'], []));
    }

    public function testCheckAccessAllow()
    {
        $lpProxy = new LogicalPermissionsProxy();
        $type = new TestType();
        $lpProxy->addType($type);
        $la = new LogicalAuthorization($lpProxy, $this->helper, new AlwaysDeny());
        $this->assertTrue($la->checkAccess(['test' => 'yes'], []));
    }

    public function testGetAvailableActionsModelClass()
    {
        $model = new TestModelBoolean();
        $available_actions = $this->laModel->getAvailableActions(get_class($model), ['create', 'read', 'update', 'delete'], ['get', 'set'], 'anon.');
        foreach ($available_actions as $key => $value) {
            if ($key !== 'fields') {
                $this->assertSame($key, $value);
                continue;
            }
            foreach ($value as $field_name => $field_actions) {
                $this->assertTrue(property_exists($model, $field_name));
                foreach ($field_actions as $field_action_key => $field_action_value) {
                    $this->assertSame($field_action_key, $field_action_value);
                }
            }
        }
    }

    public function testGetAvailableActionsModelObject()
    {
        $model = new TestModelBoolean();
        $available_actions_model = $this->laModel->getAvailableActions($model, ['create', 'read', 'update', 'delete'], ['get', 'set'], 'anon.');
        $available_actions_class = $this->laModel->getAvailableActions(get_class($model), ['create', 'read', 'update', 'delete'], ['get', 'set'], 'anon.');
        $this->assertSame($available_actions_model, $available_actions_class);
    }

    public function testGetAvailableActionsModelDecorator()
    {
        $model = new TestModelBoolean();
        $modelDecorator = new ModelDecorator($model);
        $available_actions_model_decorator = $this->laModel->getAvailableActions($modelDecorator, ['create', 'read', 'update', 'delete'], ['get', 'set'], 'anon.');
        $available_actions_class = $this->laModel->getAvailableActions(get_class($model), ['create', 'read', 'update', 'delete'], ['get', 'set'], 'anon.');
        $this->assertSame($available_actions_model_decorator, $available_actions_class);
    }

    public function testCheckModelAccessWrongModelType()
    {
        $this->expectException(LogicalAuthorizationException::class);
        $user = new TestUser();
        $this->laModel->checkModelAccess(null, 'read', $user);
    }

    public function testCheckModelAccessModelClassDoesntExist()
    {
        $this->expectException(LogicalAuthorizationException::class);
        $user = new TestUser();
        $this->laModel->checkModelAccess('TestModelBoolean', 'read', $user);
    }

    public function testCheckModelAccessWrongActionType()
    {
        $this->expectException(TypeError::class);
        $user = new TestUser();
        $model = new TestModelBoolean();
        $this->laModel->checkModelAccess($model, null, $user);
    }

    public function testCheckModelAccessEmptyAction()
    {
        $this->expectException(LogicalAuthorizationException::class);
        $user = new TestUser();
        $model = new TestModelBoolean();
        $this->laModel->checkModelAccess($model, '', $user);
    }

    public function testCheckModelAccessWrongUserType()
    {
        $this->expectException(LogicalAuthorizationException::class);
        $model = new TestModelBoolean();
        $this->laModel->checkModelAccess($model, 'read', []);
    }

    public function testCheckModelAccessMissingUser()
    {
        $model = new TestModelBoolean();
        $this->assertTrue($this->laModel->checkModelAccess($model, 'read'));
    }

    public function testCheckModelAccessMissingPermissions()
    {
        $user = new TestUser();
        $model = new ErroneousModel();
        $this->assertTrue($this->laModel->checkModelAccess($model, 'read', $user));
    }

    public function testCheckModelAccessClassDisallow()
    {
        $user = new TestUser();
        $model = new TestModelBoolean();
        $this->assertFalse($this->laModel->checkModelAccess(get_class($model), 'read', $user));
    }

    public function testCheckModelAccessClassAllow()
    {
        $user = new TestUser();
        $model = new TestModelBoolean();
        $this->assertTrue($this->laModel->checkModelAccess(get_class($model), 'create', $user));
    }

    public function testCheckModelAccessObjectDisallow()
    {
        $user = new TestUser();
        $model = new TestModelBoolean();
        $this->assertFalse($this->laModel->checkModelAccess($model, 'read', $user));
    }

    public function testCheckModelAccessObjectAllow()
    {
        $user = new TestUser();
        $model = new TestModelBoolean();
        $this->assertTrue($this->laModel->checkModelAccess($model, 'create', $user));
    }

    public function testCheckModelDecoratorAccessDisallow()
    {
        $user = new TestUser();
        $model = new TestModelBoolean();
        $modelDecorator = new ModelDecorator($model);
        $this->assertFalse($this->laModel->checkModelAccess($modelDecorator, 'read', $user));
    }

    public function testCheckModelDecoratorAccessAllow()
    {
        $user = new TestUser();
        $model = new TestModelBoolean();
        $modelDecorator = new ModelDecorator($model);
        $this->assertTrue($this->laModel->checkModelAccess($modelDecorator, 'create', $user));
    }

    public function testCheckFieldAccessWrongModelType()
    {
        $this->expectException(LogicalAuthorizationException::class);
        $user = new TestUser();
        $this->laModel->checkFieldAccess(null, 'field', 'action', $user);
    }

    public function testCheckFieldAccessModelClassDoesntExist()
    {
        $this->expectException(LogicalAuthorizationException::class);
        $user = new TestUser();
        $this->laModel->checkFieldAccess('TestModelBoolean', 'field', 'action', $user);
    }

    public function testCheckFieldAccessWrongFieldType()
    {
        $this->expectException(TypeError::class);
        $user = new TestUser();
        $model = new TestModelBoolean();
        $this->laModel->checkFieldAccess($model, null, 'action', $user);
    }

    public function testCheckFieldAccessEmptyField()
    {
        $this->expectException(LogicalAuthorizationException::class);
        $user = new TestUser();
        $model = new TestModelBoolean();
        $this->laModel->checkFieldAccess($model, '', 'action', $user);
    }

    public function testCheckFieldAccessWrongActionType()
    {
        $this->expectException(TypeError::class);
        $user = new TestUser();
        $model = new TestModelBoolean();
        $this->laModel->checkFieldAccess($model, 'field1', null, $user);
    }

    public function testCheckFieldAccessEmptyAction()
    {
        $this->expectException(LogicalAuthorizationException::class);
        $user = new TestUser();
        $model = new TestModelBoolean();
        $this->laModel->checkFieldAccess($model, 'field1', '', $user);
    }

    public function testCheckFieldAccessWrongUserType()
    {
        $this->expectException(LogicalAuthorizationException::class);
        $model = new TestModelBoolean();
        $this->laModel->checkFieldAccess($model, 'field1', 'get', []);
    }

    public function testCheckFieldAccessMissingUser()
    {
        $model = new TestModelBoolean();
        $this->assertTrue($this->laModel->checkFieldAccess($model, 'field1', 'set'));
    }

    public function testCheckFieldAccessMissingModelPermissions()
    {
        $user = new TestUser();
        $model = new ErroneousModel();
        $this->assertTrue($this->laModel->checkFieldAccess($model, 'field1', 'get', $user));
    }

    public function testCheckFieldAccessMissingFieldPermissions()
    {
        $user = new TestUser();
        $model = new TestModelBoolean();
        $this->assertTrue($this->laModel->checkFieldAccess($model, 'test', 'set', $user));
    }

    public function testCheckFieldAccessWrongAction()
    {
        $user = new TestUser();
        $model = new TestModelBoolean();
        $this->assertTrue($this->laModel->checkFieldAccess($model, 'field1', 'read', $user));
    }

    public function testCheckFieldAccessClassDisallow()
    {
        $user = new TestUser();
        $model = new TestModelBoolean();
        $this->assertFalse($this->laModel->checkFieldAccess(get_class($model), 'field1', 'set', $user));
    }

    public function testCheckFieldAccessClassAllow()
    {
        $user = new TestUser();
        $model = new TestModelBoolean();
        $this->assertTrue($this->laModel->checkFieldAccess(get_class($model), 'field1', 'get', $user));
    }

    public function testCheckFieldAccessObjectDisallow()
    {
        $user = new TestUser();
        $model = new TestModelBoolean();
        $this->assertFalse($this->laModel->checkFieldAccess($model, 'field1', 'set', $user));
    }

    public function testCheckFieldAccessObjectAllow()
    {
        $user = new TestUser();
        $model = new TestModelBoolean();
        $this->assertTrue($this->laModel->checkFieldAccess($model, 'field1', 'get', $user));
    }

    public function testCheckModelDecoratorFieldAccessDisallow()
    {
        $user = new TestUser();
        $model = new TestModelBoolean();
        $modelDecorator = new ModelDecorator($model);
        $this->assertFalse($this->laModel->checkFieldAccess($modelDecorator, 'field1', 'set', $user));
    }

    public function testCheckModelDecoratorFieldAccessAllow()
    {
        $user = new TestUser();
        $model = new TestModelBoolean();
        $modelDecorator = new ModelDecorator($model);
        $this->assertTrue($this->laModel->checkFieldAccess($modelDecorator, 'field1', 'get', $user));
    }

    public function testGetAvailableRoutes()
    {
        $available_routes = $this->laRoute->getAvailableRoutes('anon.');
        $this->assertTrue(isset($available_routes['routes']) && is_array($available_routes['routes']) && !empty($available_routes['routes']));
        foreach ($available_routes['routes'] as $key => $value) {
            $this->assertSame($key, $value);
        }
        $this->assertTrue(isset($available_routes['route_patterns']) && is_array($available_routes['route_patterns']) && !empty($available_routes['route_patterns']));
        foreach ($available_routes['route_patterns'] as $key => $value) {
            $this->assertSame($key, $value);
        }
    }

    public function testCheckRouteAccessWrongRouteType()
    {
        $this->expectException(TypeError::class);
        $user = new TestUser();
        $this->laRoute->checkRouteAccess(null, $user);
    }

    public function testCheckRouteAccessEmptyRoute()
    {
        $this->expectException(LogicalAuthorizationException::class);
        $user = new TestUser();
        $this->laRoute->checkRouteAccess('', $user);
    }

    public function testCheckRouteAccessWrongUserType()
    {
        $this->expectException(LogicalAuthorizationException::class);
        $this->laRoute->checkRouteAccess('route_allowed', []);
    }

    public function testCheckRouteAccessRouteDoesntExist()
    {
        $this->expectException(LogicalAuthorizationException::class);
        $user = new TestUser();
        $this->laRoute->checkRouteAccess('hej', $user);
    }

    public function testCheckRouteAccessMissingUser()
    {
        $this->assertTrue($this->laRoute->checkRouteAccess('route_no_bypass'));
    }

    public function testCheckRouteAccessDisallow()
    {
        $this->assertFalse($this->laRoute->checkRouteAccess('route_no_bypass', 'anon.'));
    }

    public function testCheckRouteAccessAllow()
    {
        $this->assertTrue($this->laRoute->checkRouteAccess('route_allowed', 'anon.'));
    }

    public function testLogicalPermissionsProxyAddTypeAlreadyExists()
    {
        $this->expectException(PermissionTypeAlreadyExistsException::class);
        $laProxy = new LogicalPermissionsProxy();
        $type = new TestType();
        $laProxy->addType($type);
        $laProxy->addType($type);
    }

    public function testLogicalPermissionsProxyAddType()
    {
        $laProxy = new LogicalPermissionsProxy();
        $type = new TestType();
        $laProxy->addType($type);
        $this->assertTrue($laProxy->typeExists('test'));
    }

    public function testLogicalPermissionsProxyCheckAccessTypeDoesntExist()
    {
        $this->expectException(PermissionTypeNotRegisteredException::class);
        $laProxy = new LogicalPermissionsProxy();
        $laProxy->checkAccess(['test' => 'hej'], []);
    }

    public function testGetTree()
    {
        $tree = $this->treeBuilder->getTree();
        $this->assertTrue(!isset($tree['fetch']));
        $tree = $this->treeBuilder->getTree(false, true);
        $this->assertSame('static_cache', $tree['fetch']);
        $tree = $this->treeBuilder->getTree(true, true);
        $this->assertSame('no_cache', $tree['fetch']);
    }

    public function testGetTreeFromCache()
    {
        $tree = $this->treeBuilder->getTree(false, true);
        $this->assertSame('cache', $tree['fetch']);
    }

    public function testEventInsertTreeWrongTreeType()
    {
        $this->expectException(TypeError::class);
        $laProxy = new LogicalPermissionsProxy();
        $event = new AddPermissionsEvent($laProxy->getValidPermissionKeys());
        $event->insertTree('key');
    }

    public function testEventInsertTreeGetTree()
    {
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

    public function testDebugCollectorRouteLogFormat()
    {
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

    public function testDebugCollectorModelLogFormat()
    {
        $request = new Request();
        $response = new Response();
        $user = new TestUser();
        $model = new TestModelBoolean();
        $model->setAuthor($user);
        $debugCollector = new Collector($this->treeBuilder, $this->lpProxy, $this->twig);
        $debugCollector->addPermissionCheck(true, 'model', ['model' => $model, 'action' => 'read'], $user, [], ['user' => $user]);
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

    public function testDebugCollectorFieldLogFormat()
    {
        $request = new Request();
        $response = new Response();
        $user = new TestUser();
        $model = new TestModelBoolean();
        $model->setAuthor($user);
        $debugCollector = new Collector($this->treeBuilder, $this->lpProxy, $this->twig);
        $debugCollector->addPermissionCheck(true, 'field', ['model' => $model, 'field' => 'field1', 'action' => 'get'], $user, [], ['user' => $user]);
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

    public function testDebugCollectorPermissionFormatBoolean()
    {
        $request = new Request();
        $response = new Response();
        $debugCollector = new Collector($this->treeBuilder, $this->lpProxy, $this->twig);
        $user = new TestUser();
        $model = new TestModelBoolean();
        $model->setAuthor($user);

        $permissions = true;
        $debugCollector->addPermissionCheck(true, 'field', ['model' => $model, 'field' => 'field1', 'action' => 'get'], static::$superadmin_user, $permissions, ['model' => $model, 'user' => static::$superadmin_user]);
        $result = [
            'type'                        => 'field',
            'field'                       => 'field1',
            'user'                        => static::$superadmin_user,
            'permissions'                 => $permissions,
            'action'                      => 'get',
            'item_name'                   => 'Ordermind\LogicalAuthorizationBundle\Test\Fixtures\Model\TestModelBoolean:field1',
            'item'                        => $model,
            'permission_no_bypass_checks' => [],
            'bypassed_access'             => true,
            'permission_checks'           => [['permissions' => true, 'resolve' => true]],
            'access'                      => true,
            'message'                     => '',
        ];

        $debugCollector->collect($request, $response);
        $log = $debugCollector->getLog();
        $item = array_shift($log);
        foreach ($item as $key => $value) {
            if ($key === 'backtrace') {
                $this->assertLessThanOrEqual(11, count($value));
                $this->assertSame('testDebugCollectorPermissionFormatBoolean', $value[0]['function']);
                continue;
            }
            $this->assertSame($result[$key], $value);
        }
    }

    public function testDebugCollectorPermissionFormatTypeClose()
    {
        $request = new Request();
        $response = new Response();
        $debugCollector = new Collector($this->treeBuilder, $this->lpProxy, $this->twig);
        $user = new TestUser();
        $model = new TestModelBoolean();
        $model->setAuthor($user);

        $permissions = [
            'NOT' => [
                'flag' => 'user_has_account',
            ],
        ];
        $debugCollector->addPermissionCheck(true, 'route', 'testroute', 'anon.', $permissions, ['user' => 'anon.']);
        $result = [
            'type'                        => 'route',
            'user'                        => 'anon.',
            'item_name'                   => 'testroute',
            'permission_no_bypass_checks' => [],
            'bypassed_access'             => false,
            'permissions'                 => $permissions,
            'access'                      => true,
            'permission_checks'           => [['permissions' => ['NOT' => ['flag' => 'user_has_account']], 'resolve' => true], ['permissions' => ['flag' => 'user_has_account'], 'resolve' => false]],
            'message'                     => '',
        ];

        $debugCollector->collect($request, $response);
        $log = $debugCollector->getLog();
        $item = array_shift($log);
        foreach ($item as $key => $value) {
            if ($key === 'backtrace') {
                $this->assertLessThanOrEqual(11, count($value));
                $this->assertSame('testDebugCollectorPermissionFormatTypeClose', $value[0]['function']);
                continue;
            }
            $this->assertSame($result[$key], $value);
        }
    }

    public function testDebugCollectorPermissionFormatTypeSeparate()
    {
        $request = new Request();
        $response = new Response();
        $debugCollector = new Collector($this->treeBuilder, $this->lpProxy, $this->twig);
        $user = new TestUser();
        $model = new TestModelBoolean();
        $model->setAuthor($user);

        $permissions = [
      'flag' => [
        'NOT' => 'user_has_account',
      ],
    ];
        $debugCollector->addPermissionCheck(true, 'route', 'testroute', 'anon.', $permissions, ['user' => 'anon.']);
        $result = [
      'type'                        => 'route',
      'user'                        => 'anon.',
      'item_name'                   => 'testroute',
      'permission_no_bypass_checks' => [],
      'bypassed_access'             => false,
      'permissions'                 => $permissions,
      'access'                      => true,
      'permission_checks'           => [['permissions' => ['flag' => ['NOT' => 'user_has_account']], 'resolve' => true], ['permissions' => ['NOT' => 'user_has_account'], 'resolve' => true], ['permissions' => 'user_has_account', 'resolve' => false]],
      'message'                     => '',
    ];

        $debugCollector->collect($request, $response);
        $log = $debugCollector->getLog();
        $item = array_shift($log);
        foreach ($item as $key => $value) {
            if ($key === 'backtrace') {
                $this->assertLessThanOrEqual(11, count($value));
                $this->assertSame('testDebugCollectorPermissionFormatTypeSeparate', $value[0]['function']);
                continue;
            }
            $this->assertSame($result[$key], $value);
        }
    }

    public function testDebugCollectorPermissionFormatMixed()
    {
        $request = new Request();
        $response = new Response();
        $debugCollector = new Collector($this->treeBuilder, $this->lpProxy, $this->twig);
        $user = new TestUser();
        $model = new TestModelBoolean();
        $model->setAuthor($user);

        $permissions = [
      'NO_BYPASS' => [
        'NOT' => [
          'flag' => 'user_has_account',
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
        true,
        'TRUE',
        'flag' => [
          'NOT' => [
            'OR' => [
              ['NOT' => 'user_has_account'],
              ['NOT' => 'user_is_author'],
            ],
          ],
        ],
      ],
      'flag' => 'user_has_account',
    ];
        $debugCollector->addPermissionCheck(true, 'field', ['model' => $model, 'field' => 'field1', 'action' => 'get'], $user, $permissions, ['model' => $model, 'user' => $user]);
        $result = [
      'type'                        => 'field',
      'field'                       => 'field1',
      'user'                        => $user,
      'permissions'                 => $permissions,
      'action'                      => 'get',
      'item_name'                   => 'Ordermind\LogicalAuthorizationBundle\Test\Fixtures\Model\TestModelBoolean:field1',
      'item'                        => $model,
      'permission_no_bypass_checks' => array_reverse([['permissions' => ['flag' => 'user_has_account'], 'resolve' => true], ['permissions' => ['NOT' => ['flag' => 'user_has_account']], 'resolve' => false]]),
      'bypassed_access'             => false,
      'permission_checks'           => [],
      'permission_checks'           => array_reverse([
        0  => ['permissions' => 'ROLE_ADMIN', 'resolve' => false],
        1  => ['permissions' => 'ROLE_ADMIN', 'resolve' => false],
        2  => ['permissions' => ['AND' => ['ROLE_ADMIN', 'ROLE_ADMIN']], 'resolve' => false],
        3  => ['permissions' => ['NOT' => ['AND' => ['ROLE_ADMIN', 'ROLE_ADMIN']]], 'resolve' => true],
        4  => ['permissions' => ['OR' => ['NOT' => ['AND' => ['ROLE_ADMIN', 'ROLE_ADMIN']]]], 'resolve' => true],
        5  => ['permissions' => ['role' => ['OR' => ['NOT' => ['AND' => ['ROLE_ADMIN', 'ROLE_ADMIN']]]]], 'resolve' => true],
        6  => ['permissions' => true, 'resolve' => true],
        7  => ['permissions' => 'TRUE', 'resolve' => true],
        8  => ['permissions' => 'user_has_account', 'resolve' => true],
        9  => ['permissions' => ['NOT' => 'user_has_account'], 'resolve' => false],
        10 => ['permissions' => 'user_is_author', 'resolve' => true],
        11 => ['permissions' => ['NOT' => 'user_is_author'], 'resolve' => false],
        12 => ['permissions' => ['OR' => [['NOT' => 'user_has_account'], ['NOT' => 'user_is_author']]], 'resolve' => false],
        13 => ['permissions' => ['NOT' => ['OR' => [['NOT' => 'user_has_account'], ['NOT' => 'user_is_author']]]], 'resolve' => true],
        14 => ['permissions' => ['flag' => ['NOT' => ['OR' => [['NOT' => 'user_has_account'], ['NOT' => 'user_is_author']]]]], 'resolve' => true],
        15 => ['permissions' => ['AND' => ['role' => ['OR' => ['NOT' => ['AND' => ['ROLE_ADMIN', 'ROLE_ADMIN']]]], '0' => true, '1' => 'TRUE', 'flag' => ['NOT' => ['OR' => [['NOT' => 'user_has_account'], ['NOT' => 'user_is_author']]]]]], 'resolve' => true],
        16 => ['permissions' => ['flag' => 'user_has_account'], 'resolve' => true],
        17 => ['permissions' => ['AND' => ['role' => ['OR' => ['NOT' => ['AND' => ['ROLE_ADMIN', 'ROLE_ADMIN']]]], '0' => true, '1' => 'TRUE', 'flag' => ['NOT' => ['OR' => [['NOT' => 'user_has_account'], ['NOT' => 'user_is_author']]]]], 'flag' => 'user_has_account'], 'resolve' => true],
      ]),
      'access'  => true,
      'message' => '',
    ];

        $debugCollector->collect($request, $response);
        $log = $debugCollector->getLog();
        $item = array_shift($log);
        foreach ($item as $key => $value) {
            if ($key === 'backtrace') {
                $this->assertLessThanOrEqual(11, count($value));
                $this->assertSame('testDebugCollectorPermissionFormatMixed', $value[0]['function']);
                continue;
            }
            if ($key === 'permission_checks') {
                foreach ($value as $i => $value2) {
                    $this->assertSame($result[$key][$i], $value2);
                }
                continue;
            }
            $this->assertSame($result[$key], $value);
        }
    }

    public function testDebugCollectorExceptionHandling()
    {
        $request = new Request();
        $response = new Response();
        $debugCollector = new Collector($this->treeBuilder, $this->lpProxy, $this->twig);
        $user = new TestUser();
        $model = new TestModelBoolean();
        $model->setAuthor($user);
        $permissions = ['no_bypass' => true, 'flag' => ['flag' => 'user_has_account']];
        $debugCollector->addPermissionCheck(false, 'field', ['model' => $model, 'field' => 'field1', 'action' => 'get'], $user, $permissions, ['model' => $model, 'user' => $user]);
        $debugCollector->collect($request, $response);
        $log = $debugCollector->getLog();
        $first_item = array_shift($log);
        $this->assertArrayHasKey('message', $first_item);
    }

    public function testDebugCollectorExceptionHandlingNoDebug()
    {
        $request = new Request();
        $response = new Response();
        $debugCollector = new Collector($this->treeBuilder, $this->lpProxy, $this->twig);
        $user = new TestUser();
        $model = new TestModelBoolean();
        $model->setAuthor($user);
        $permissions = ['no_bypass' => ['flag' => ['flag' => 'user_has_account']], true];
        $debugCollector->addPermissionCheck(false, 'field', ['model' => $model, 'field' => 'field1', 'action' => 'get'], $user, $permissions, ['model' => $model, 'user' => $user]);
        $debugCollector->collect($request, $response);
        $log = $debugCollector->getLog();
        $first_item = array_shift($log);
        $this->assertArrayHasKey('message', $first_item);
    }
}
