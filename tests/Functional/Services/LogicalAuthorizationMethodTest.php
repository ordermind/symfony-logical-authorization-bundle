<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Test\Functional\Services;

use InvalidArgumentException;
use Ordermind\LogicalAuthorizationBundle\DataCollector\Collector;
use Ordermind\LogicalAuthorizationBundle\Event\AddPermissionsEvent;
use Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException;
use Ordermind\LogicalAuthorizationBundle\Interfaces\ModelInterface;
use Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface;
use Ordermind\LogicalAuthorizationBundle\PermissionCheckers\HostChecker;
use Ordermind\LogicalAuthorizationBundle\PermissionCheckers\IpChecker;
use Ordermind\LogicalAuthorizationBundle\PermissionCheckers\MethodChecker;
use Ordermind\LogicalAuthorizationBundle\PermissionCheckers\RoleChecker;
use Ordermind\LogicalAuthorizationBundle\PermissionCheckers\SimpleConditionChecker\Checkers\UserCanBypassAccessChecker as BypassAccessConditionChecker;
use Ordermind\LogicalAuthorizationBundle\PermissionCheckers\SimpleConditionChecker\Checkers\UserHasAccountChecker as HasAccountConditionChecker;
use Ordermind\LogicalAuthorizationBundle\PermissionCheckers\SimpleConditionChecker\Checkers\UserIsAuthorChecker as IsAuthorConditionChecker;
use Ordermind\LogicalAuthorizationBundle\PermissionCheckers\SimpleConditionChecker\SimpleConditionCheckerManager;
use Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorization;
use Ordermind\LogicalAuthorizationBundle\Test\Fixtures\BypassAccessChecker\AlwaysDeny;
use Ordermind\LogicalAuthorizationBundle\Test\Fixtures\Model\ErroneousModel;
use Ordermind\LogicalAuthorizationBundle\Test\Fixtures\Model\ErroneousUser;
use Ordermind\LogicalAuthorizationBundle\Test\Fixtures\Model\TestModelBoolean;
use Ordermind\LogicalAuthorizationBundle\Test\Fixtures\Model\TestUser;
use Ordermind\LogicalAuthorizationBundle\Test\Fixtures\ModelDecorator\ModelDecorator;
use Ordermind\LogicalAuthorizationBundle\Test\Fixtures\PermissionCheckers\TestConditionChecker;
use Ordermind\LogicalAuthorizationBundle\Test\Fixtures\PermissionCheckers\TestPermissionChecker;
use Ordermind\LogicalPermissions\LogicalPermissionsFacade;
use Ordermind\LogicalPermissions\PermissionCheckerLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use TypeError;

class LogicalAuthorizationMethodTest extends LogicalAuthorizationBase
{
    // ------------ Permission types ---------------

    // --- Simple conditions --- //

    public function testConditionCheckerBypassAccessMissingUser()
    {
        $condition = new BypassAccessConditionChecker();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'The context parameter must contain a "user" key to be able to evaluate the %s condition',
                $condition->getName()
            )
        );
        $condition->checkCondition([]);
    }

    public function testConditionCheckerBypassAccessWrongUserType()
    {
        $condition = new BypassAccessConditionChecker();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'The user class must implement %s to be able to evaluate the %s condition',
                UserInterface::class,
                $condition->getName()
            )
        );
        $condition->checkCondition(['user' => []]);
    }

    public function testConditionCheckerBypassAccessWrongReturnType()
    {
        $user = new ErroneousUser();
        $condition = new BypassAccessConditionChecker();

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage(
            sprintf(
                'Return value of %s must be of the type bool',
                ErroneousUser::class . '::getBypassAccess()'
            )
        );
        $condition->checkCondition(['user' => $user]);
    }

    public function testConditionCheckerBypassAccessAnonymousUserDisallow()
    {
        $condition = new BypassAccessConditionChecker();
        $this->assertFalse($condition->checkCondition(['user' => 'anon.']));
    }

    public function testConditionCheckerBypassAccessDisallow()
    {
        $user = new TestUser();
        $condition = new BypassAccessConditionChecker();
        $this->assertFalse($condition->checkCondition(['user' => $user]));
    }

    public function testConditionCheckerBypassAccessAllow()
    {
        $user = new TestUser();
        $user->setBypassAccess(true);
        $condition = new BypassAccessConditionChecker();
        $this->assertTrue($condition->checkCondition(['user' => $user]));
    }

    public function testConditionCheckerHasAccountMissingUser()
    {
        $condition = new HasAccountConditionChecker();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'The context parameter must contain a "user" key to be able to evaluate the %s condition',
                $condition->getName()
            )
        );
        $condition->checkCondition([]);
    }

    public function testConditionCheckerHasAccountDisallow()
    {
        $condition = new HasAccountConditionChecker();
        $this->assertFalse($condition->checkCondition(['user' => 'anon.']));
    }

    public function testConditionCheckerHasAccountAllow()
    {
        $user = new TestUser();
        $condition = new HasAccountConditionChecker();
        $this->assertTrue($condition->checkCondition(['user' => $user]));
    }

    public function testConditionCheckerIsAuthorMissingUser()
    {
        $condition = new IsAuthorConditionChecker();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'The context parameter must contain a "user" key to be able to evaluate the %s condition',
                $condition->getName()
            )
        );
        $condition->checkCondition([]);
    }

    public function testConditionCheckerIsAuthorWrongUserType()
    {
        $condition = new IsAuthorConditionChecker();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'The user class must implement %s to be able to evaluate the %s condition',
                UserInterface::class,
                $condition->getName()
            )
        );
        $condition->checkCondition(['user' => []]);
    }

    public function testConditionCheckerIsAuthorMissingModel()
    {
        $user = new TestUser();
        $condition = new IsAuthorConditionChecker();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'The context parameter must contain a "model" key to be able to evaluate the %s condition',
                $condition->getName()
            )
        );
        $condition->checkCondition(['user' => $user]);
    }

    public function testConditionCheckerIsAuthorModelClassString()
    {
        $user = new TestUser();
        $condition = new IsAuthorConditionChecker();
        $this->assertFalse(
            $condition->checkCondition(['user' => $user, 'model' => TestUser::class])
        );
    }

    public function testConditionCheckerIsAuthorWrongModelType()
    {
        $user = new TestUser();
        $condition = new IsAuthorConditionChecker();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'The model class must implement %s to be able to evaluate the %s condition',
                ModelInterface::class,
                $condition->getName()
            )
        );
        $condition->checkCondition(['user' => $user, 'model' => []]);
    }

    public function testConditionCheckerIsAuthorModelWrongAuthorType()
    {
        $user = new TestUser();
        $model = new ErroneousModel();
        $condition = new IsAuthorConditionChecker();

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage(
            sprintf(
                'Return value of %s must implement interface %s or be null, string returned',
                ErroneousModel::class . '::getAuthor()',
                UserInterface::class
            )
        );
        $condition->checkCondition(['user' => $user, 'model' => $model]);
    }

    public function testConditionCheckerIsAuthorModelAnonymousUserDisallow()
    {
        $model = new TestModelBoolean();
        $condition = new IsAuthorConditionChecker();
        $this->assertFalse($condition->checkCondition(['user' => 'anon.', 'model' => $model]));
    }

    public function testConditionCheckerIsAuthorModelAnonymousAuthorAllow()
    {
        $user = new TestUser();
        $model = new TestModelBoolean();
        $condition = new IsAuthorConditionChecker();
        $this->assertTrue($condition->checkCondition(['user' => $user, 'model' => $model]));
    }

    public function testConditionCheckerIsAuthorModelAllow()
    {
        $user = new TestUser();
        $model = new TestModelBoolean();
        $model->setAuthor($user);
        $condition = new IsAuthorConditionChecker();
        $this->assertTrue($condition->checkCondition(['user' => $user, 'model' => $model]));
    }

    public function testSimpleConditionCheckerManagerAddConditionCheckerAlreadyRegistered()
    {
        $conditionManager = new SimpleConditionCheckerManager();
        $condition = new TestConditionChecker();
        $conditionManager->addCondition($condition);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'The condition "%s" already exists! If you want to change the class that handles a condition, you may '
                    . 'do so by overriding the service definition for that condition',
                $condition->getName()
            )
        );
        $conditionManager->addCondition($condition);
    }

    public function testSimpleConditionCheckerManagerAddConditionChecker()
    {
        $conditionManager = new SimpleConditionCheckerManager();
        $condition = new TestConditionChecker();
        $conditionManager->addCondition($condition);
        $conditions = $conditionManager->getConditions();
        $this->assertTrue(isset($conditions['always_true']));
        $this->assertSame($condition, $conditions['always_true']);
    }

    public function testSimpleConditionCheckerManagerRemoveConditionCheckerEmptyName()
    {
        $conditionManager = new SimpleConditionCheckerManager();
        $condition = new TestConditionChecker();
        $conditionManager->addCondition($condition);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The name parameter cannot be empty');
        $conditionManager->removeCondition('');
    }

    public function testSimpleConditionCheckerManagerRemoveConditionCheckerNotRegistered()
    {
        $conditionManager = new SimpleConditionCheckerManager();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'The condition "test" has not been registered. Please use the '
                . '"logauth.tag.permission_type.condition" service tag to register a condition'
        );
        $conditionManager->removeCondition('test');
    }

    public function testSimpleConditionCheckerManagerRemoveConditionChecker()
    {
        $conditionManager = new SimpleConditionCheckerManager();
        $condition = new TestConditionChecker();
        $conditionManager->addCondition($condition);
        $conditions = $conditionManager->getConditions();
        $this->assertTrue(isset($conditions['always_true']));
        $conditionManager->removeCondition('always_true');
        $conditions = $conditionManager->getConditions();
        $this->assertFalse(isset($conditions['always_true']));
    }

    public function testSimpleConditionCheckerManagerCheckPermissionEmptyName()
    {
        $conditionManager = new SimpleConditionCheckerManager();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The name parameter cannot be empty');
        $conditionManager->checkPermission('', []);
    }

    public function testSimpleConditionCheckerManagerCheckPermissionNotRegistered()
    {
        $conditionManager = new SimpleConditionCheckerManager();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'The condition "test" has not been registered. Please use the '
                . '"logauth.tag.permission_type.condition" service tag to register a condition'
        );
        $conditionManager->checkPermission('test', []);
    }

    public function testSimpleConditionCheckerManagerCheckPermission()
    {
        $conditionManager = new SimpleConditionCheckerManager();
        $condition = new TestConditionChecker();
        $conditionManager->addCondition($condition);
        $this->assertTrue($conditionManager->checkPermission('always_true', []));
    }

    // --- RoleChecker --- //

    public function testRoleEmptyRole()
    {
        $role = new RoleChecker($this->roleHierarchy);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The role parameter cannot be empty');
        $role->checkPermission('', []);
    }

    public function testRoleWrongContextType()
    {
        $role = new RoleChecker($this->roleHierarchy);

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('The context parameter must be an array');
        $role->checkPermission('ROLE_USER', null);
    }

    public function testRoleMissingUser()
    {
        $role = new RoleChecker($this->roleHierarchy);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'The context parameter must contain a "user" key to be able to evaluate the %s permission',
                $role->getName()
            )
        );
        $role->checkPermission('ROLE_USER', []);
    }

    public function testRoleWrongUserType()
    {
        $role = new RoleChecker($this->roleHierarchy);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'The user class must implement %s to be able to evaluate the user role.',
                UserInterface::class
            )
        );
        $role->checkPermission('ROLE_USER', ['user' => []]);
    }

    public function testRoleAnonymousUserDisallow()
    {
        $role = new RoleChecker($this->roleHierarchy);
        $this->assertFalse($role->checkPermission('ROLE_USER', ['user' => 'anon.']));
    }

    public function testRoleDisallow()
    {
        $user = new TestUser();
        $role = new RoleChecker($this->roleHierarchy);
        $this->assertFalse($role->checkPermission('ROLE_ADMIN', ['user' => $user]));
    }

    public function testRoleAllow()
    {
        $user = new TestUser();
        $role = new RoleChecker($this->roleHierarchy);
        $this->assertTrue($role->checkPermission('ROLE_USER', ['user' => $user]));
        $user->setRoles(['ROLE_ADMIN']);
        $this->assertTrue($role->checkPermission('ROLE_ADMIN', ['user' => $user]));
    }

    public function testRoleHierarchyDisallow()
    {
        $user = new TestUser();
        $user->setRoles(['ROLE_ADMIN']);
        $role = new RoleChecker($this->roleHierarchy);
        $this->assertFalse($role->checkPermission('ROLE_CHILD', ['user' => $user]));
    }

    public function testRoleHierarchyAllow()
    {
        $user = new TestUser();
        $user->setRoles(['ROLE_PARENT']);
        $role = new RoleChecker($this->roleHierarchy);
        $this->assertTrue($role->checkPermission('ROLE_CHILD', ['user' => $user]));
    }

    // --- HostChecker --- //

    public function testHostEmptyHost()
    {
        $requestStack = new RequestStack();
        $host = new HostChecker($requestStack);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The host parameter cannot be empty');
        $host->checkPermission('', []);
    }

    public function testHostDisallow()
    {
        $requestStack = new RequestStack();
        $request = Request::create('https://test.com/test', 'GET', [], [], [], ['REMOTE_ADDR' => '127.0.0.55']);
        $requestStack->push($request);
        $host = new HostChecker($requestStack);
        $this->assertFalse($host->checkPermission('test.se', []));
    }

    public function testHostAllow()
    {
        $requestStack = new RequestStack();
        $request = Request::create('https://test.com/test', 'GET', [], [], [], ['REMOTE_ADDR' => '127.0.0.55']);
        $requestStack->push($request);
        $host = new HostChecker($requestStack);
        $this->assertTrue($host->checkPermission('test.com', []));
    }

    // --- MethodChecker --- //

    public function testMethodEmptyMethod()
    {
        $requestStack = new RequestStack();
        $method = new MethodChecker($requestStack);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The method parameter cannot be empty');
        $method->checkPermission('', []);
    }

    public function testMethodDisallow()
    {
        $requestStack = new RequestStack();
        $request = Request::create('https://test.com/test', 'GET', [], [], [], ['REMOTE_ADDR' => '127.0.0.55']);
        $requestStack->push($request);
        $method = new MethodChecker($requestStack);
        $this->assertFalse($method->checkPermission('PUSH', []));
    }

    public function testMethodAllow()
    {
        $requestStack = new RequestStack();
        $request = Request::create('https://test.com/test', 'GET', [], [], [], ['REMOTE_ADDR' => '127.0.0.55']);
        $requestStack->push($request);
        $method = new MethodChecker($requestStack);
        $this->assertTrue($method->checkPermission('GET', []));
    }

    // --- IpChecker --- //

    public function testIpEmptyIp()
    {
        $requestStack = new RequestStack();
        $ipPermission = new IpChecker($requestStack);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The ipAddress parameter cannot be empty');
        $ipPermission->checkPermission('', []);
    }

    public function testIpDisallow()
    {
        $requestStack = new RequestStack();
        $request = Request::create('https://test.com/test', 'GET', [], [], [], ['REMOTE_ADDR' => '127.0.0.55']);
        $requestStack->push($request);
        $ipPermission = new IpChecker($requestStack);
        $this->assertFalse($ipPermission->checkPermission('127.0.0.1', []));
    }

    public function testIpAllow()
    {
        $requestStack = new RequestStack();
        $request = Request::create('https://test.com/test', 'GET', [], [], [], ['REMOTE_ADDR' => '127.0.0.55']);
        $requestStack->push($request);
        $ipPermission = new IpChecker($requestStack);
        $this->assertTrue($ipPermission->checkPermission('127.0.0.55', []));
    }

    // ------------ Services -------------

    public function testHelperCurrentUser()
    {
        $this->sendRequestAs('GET', '/test/get-current-username', [], static::$userAuthenticated);
        $response = $this->client->getResponse();
        $this->assertEquals(static::$userAuthenticated->getUsername(), $response->getContent());
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
        $this->expectExceptionMessage(
            'The permission type "test" could not be found. Please use the "logauth.tag.permission_checker" service '
                . 'tag to register a permission checker'
        );
        $this->logicalAuthorization->checkAccess(['test' => 'hej'], ['user' => 'anon.']);
    }

    public function testCheckAccessOtherExceptions()
    {
        $this->expectException(LogicalAuthorizationException::class);
        $this->expectExceptionMessage('An exception was caught while checking access: ');
        $this->logicalAuthorization->checkAccess(['test' => 'hej'], []);
    }

    public function testCheckAccessDisallow()
    {
        $lpLocator = new PermissionCheckerLocator();
        $lpLocator->add(new TestPermissionChecker());
        $lpFacade = new LogicalPermissionsFacade($lpLocator, new AlwaysDeny());
        $logicalAuthorization = new LogicalAuthorization($lpFacade, $this->helper);
        $this->assertFalse($logicalAuthorization->checkAccess(['test' => 'no'], []));
    }

    public function testCheckAccessAllow()
    {
        $lpLocator = new PermissionCheckerLocator();
        $lpLocator->add(new TestPermissionChecker());
        $lpFacade = new LogicalPermissionsFacade($lpLocator, new AlwaysDeny());
        $logicalAuthorization = new LogicalAuthorization($lpFacade, $this->helper);
        $this->assertTrue($logicalAuthorization->checkAccess(['test' => 'yes'], []));
    }

    public function testGetAvailableActionsModelClass()
    {
        $model = new TestModelBoolean();
        $availableActions = $this->laModel->getAvailableActions(
            get_class($model),
            ['create', 'read', 'update', 'delete'],
            ['get', 'set'],
            'anon.'
        );
        foreach ($availableActions as $key => $value) {
            if ($key !== 'fields') {
                $this->assertSame($key, $value);
                continue;
            }
            foreach ($value as $fieldName => $fieldActions) {
                $this->assertTrue(property_exists($model, $fieldName));
                foreach ($fieldActions as $fieldActionKey => $fieldActionValue) {
                    $this->assertSame($fieldActionKey, $fieldActionValue);
                }
            }
        }
    }

    public function testGetAvailableActionsModelObject()
    {
        $model = new TestModelBoolean();
        $modelActions = $this->laModel->getAvailableActions(
            $model,
            ['create', 'read', 'update', 'delete'],
            ['get', 'set'],
            'anon.'
        );
        $classActions = $this->laModel->getAvailableActions(
            get_class($model),
            ['create', 'read', 'update', 'delete'],
            ['get', 'set'],
            'anon.'
        );
        $this->assertSame($modelActions, $classActions);
    }

    public function testGetAvailableActionsModelDecorator()
    {
        $model = new TestModelBoolean();
        $modelDecorator = new ModelDecorator($model);
        $decoratorActions = $this->laModel->getAvailableActions(
            $modelDecorator,
            ['create', 'read', 'update', 'delete'],
            ['get', 'set'],
            'anon.'
        );
        $classActions = $this->laModel->getAvailableActions(
            get_class($model),
            ['create', 'read', 'update', 'delete'],
            ['get', 'set'],
            'anon.'
        );
        $this->assertSame($decoratorActions, $classActions);
    }

    public function testCheckModelAccessWrongModelType()
    {
        $user = new TestUser();

        $this->expectException(LogicalAuthorizationException::class);
        $this->expectExceptionMessage(
            'Error checking model access: the model parameter must be either a class string or an object.'
        );
        $this->laModel->checkModelAccess(null, 'read', $user);
    }

    public function testCheckModelAccessModelClassDoesntExist()
    {
        $user = new TestUser();

        $this->expectException(LogicalAuthorizationException::class);
        $this->expectExceptionMessage(
            'Error checking model access: the model parameter is a class string but the class could not be found.'
        );
        $this->laModel->checkModelAccess('TestModelBoolean', 'read', $user);
    }

    public function testCheckModelAccessEmptyAction()
    {
        $user = new TestUser();
        $model = new TestModelBoolean();

        $this->expectException(LogicalAuthorizationException::class);
        $this->expectExceptionMessage('Error checking model access: the action parameter cannot be empty.');
        $this->laModel->checkModelAccess($model, '', $user);
    }

    public function testCheckModelAccessWrongUserType()
    {
        $model = new TestModelBoolean();

        $this->expectException(LogicalAuthorizationException::class);
        $this->expectExceptionMessage(
            'Error checking model access: the user parameter must be either a string or an object.'
        );
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
        $user = new TestUser();

        $this->expectException(LogicalAuthorizationException::class);
        $this->expectExceptionMessage(
            'Error checking field access: the model parameter must be either a class string or an object.'
        );
        $this->laModel->checkFieldAccess(null, 'field', 'action', $user);
    }

    public function testCheckFieldAccessModelClassDoesntExist()
    {
        $user = new TestUser();

        $this->expectException(LogicalAuthorizationException::class);
        $this->expectExceptionMessage(
            'Error checking field access: the model parameter is a class string but the class could not be found.'
        );
        $this->laModel->checkFieldAccess('TestModelBoolean', 'field', 'action', $user);
    }

    public function testCheckFieldAccessEmptyField()
    {
        $user = new TestUser();
        $model = new TestModelBoolean();

        $this->expectException(LogicalAuthorizationException::class);
        $this->expectExceptionMessage('Error checking field access: the fieldName parameter cannot be empty.');
        $this->laModel->checkFieldAccess($model, '', 'action', $user);
    }

    public function testCheckFieldAccessEmptyAction()
    {
        $user = new TestUser();
        $model = new TestModelBoolean();

        $this->expectException(LogicalAuthorizationException::class);
        $this->expectExceptionMessage('Error checking field access: the action parameter cannot be empty.');
        $this->laModel->checkFieldAccess($model, 'field1', '', $user);
    }

    public function testCheckFieldAccessWrongUserType()
    {
        $model = new TestModelBoolean();

        $this->expectException(LogicalAuthorizationException::class);
        $this->expectExceptionMessage(
            'Error checking field access: the user parameter must be either a string or an object.'
        );
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
        $availableRoutes = $this->laRoute->getAvailableRoutes('anon.');
        $this->assertTrue(
            isset($availableRoutes['routes'])
                && is_array($availableRoutes['routes'])
                && !empty($availableRoutes['routes'])
        );
        foreach ($availableRoutes['routes'] as $key => $value) {
            $this->assertSame($key, $value);
        }
        $this->assertTrue(
            isset($availableRoutes['route_patterns'])
                && is_array($availableRoutes['route_patterns'])
                && !empty($availableRoutes['route_patterns'])
        );
        foreach ($availableRoutes['route_patterns'] as $key => $value) {
            $this->assertSame($key, $value);
        }
    }

    public function testCheckRouteAccessEmptyRoute()
    {
        $user = new TestUser();

        $this->expectException(LogicalAuthorizationException::class);
        $this->expectExceptionMessage('Error checking route access: the routeName parameter cannot be empty.');
        $this->laRoute->checkRouteAccess('', $user);
    }

    public function testCheckRouteAccessWrongUserType()
    {
        $this->expectException(LogicalAuthorizationException::class);
        $this->expectExceptionMessage(
            'Error checking route access: the user parameter must be either a string or an object.'
        );
        $this->laRoute->checkRouteAccess('route_allowed', []);
    }

    public function testCheckRouteAccessRouteDoesntExist()
    {
        $user = new TestUser();

        $this->expectException(LogicalAuthorizationException::class);
        $this->expectExceptionMessage('Error checking route access: the route could not be found');
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

    public function testEventInsertTreeGetTree()
    {
        $lpLocator = new PermissionCheckerLocator();
        $role = new RoleChecker($this->roleHierarchy);
        $lpLocator->add($role);
        $conditionManager = new SimpleConditionCheckerManager();
        $lpLocator->add($conditionManager);
        $event = new AddPermissionsEvent($lpLocator->getValidPermissionTreeKeys());
        $tree1 = [
            'models' => [
                'testmodel' => [
                    'create' => [
                        'role' => 'role1',
                    ],
                    'read' => [
                        'condition' => [
                            'condition1',
                            'condition2',
                        ],
                    ],
                    'update' => [
                        'condition' => 'condition1',
                    ],
                    'fields' => [
                        'field1' => [
                            'get' => [
                                'role' => 'role1',
                            ],
                            'set' => [
                                'condition' => 'condition1',
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
                        'condition' => 'newcondition1',
                    ],
                    'fields' => [
                        'field1' => [
                            'get' => [
                                'OR' => [
                                    'role'      => 'newrole1',
                                    'condition' => 'newcondition1',
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
                        'condition' => 'newcondition1',
                    ],
                    'update' => [
                        'condition' => 'condition1',
                    ],
                    'fields' => [
                        'field1' => [
                            'get' => [
                                'OR' => [
                                    'role'      => 'newrole1',
                                    'condition' => 'newcondition1',
                                ],
                            ],
                            'set' => [
                                'condition' => 'condition1',
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
        $debugCollector = new Collector($this->treeBuilder, $this->lpFacade, $this->lpLocator);
        $debugCollector->addPermissionCheck(true, 'route', 'route_role', $user, [], ['user' => $user]);
        $debugCollector->collect($request, $response);
        $log = $debugCollector->getLog();

        $firstItem = array_shift($log);
        $this->assertSame('route', $firstItem['type']);
        $this->assertSame('route_role', $firstItem['item_name']);
        $this->assertArrayNotHasKey('item', $firstItem);
        $this->assertArrayNotHasKey('action', $firstItem);
        $this->assertSame($user, $firstItem['user']);
        $this->assertSame([], $firstItem['permissions']);
        $this->assertArrayNotHasKey('context', $firstItem);
    }

    public function testDebugCollectorModelLogFormat()
    {
        $request = new Request();
        $response = new Response();
        $user = new TestUser();
        $model = new TestModelBoolean();
        $model->setAuthor($user);
        $debugCollector = new Collector($this->treeBuilder, $this->lpFacade, $this->lpLocator);
        $debugCollector->addPermissionCheck(
            true,
            'model',
            ['model' => $model, 'action' => 'read'],
            $user,
            [],
            ['user' => $user]
        );
        $debugCollector->collect($request, $response);
        $log = $debugCollector->getLog();

        $firstItem = array_shift($log);
        $this->assertSame('model', $firstItem['type']);
        $this->assertSame(get_class($model), $firstItem['item_name']);
        $this->assertSame($model, $firstItem['item']);
        $this->assertSame('read', $firstItem['action']);
        $this->assertSame($user, $firstItem['user']);
        $this->assertSame([], $firstItem['permissions']);
        $this->assertArrayNotHasKey('context', $firstItem);
    }

    public function testDebugCollectorFieldLogFormat()
    {
        $request = new Request();
        $response = new Response();
        $user = new TestUser();
        $model = new TestModelBoolean();
        $model->setAuthor($user);
        $debugCollector = new Collector($this->treeBuilder, $this->lpFacade, $this->lpLocator);
        $debugCollector->addPermissionCheck(
            true,
            'field',
            ['model' => $model, 'field' => 'field1', 'action' => 'get'],
            $user,
            [],
            ['user' => $user]
        );
        $debugCollector->collect($request, $response);
        $log = $debugCollector->getLog();

        $firstItem = array_shift($log);
        $this->assertSame('field', $firstItem['type']);
        $this->assertSame(get_class($model) . ':field1', $firstItem['item_name']);
        $this->assertSame($model, $firstItem['item']);
        $this->assertSame('get', $firstItem['action']);
        $this->assertSame($user, $firstItem['user']);
        $this->assertSame([], $firstItem['permissions']);
        $this->assertArrayNotHasKey('context', $firstItem);
    }

    public function testDebugCollectorPermissionFormatBoolean()
    {
        $request = new Request();
        $response = new Response();
        $debugCollector = new Collector($this->treeBuilder, $this->lpFacade, $this->lpLocator);
        $user = new TestUser();
        $model = new TestModelBoolean();
        $model->setAuthor($user);

        $permissions = true;
        $debugCollector->addPermissionCheck(
            true,
            'field',
            ['model' => $model, 'field' => 'field1', 'action' => 'get'],
            static::$userSuperadmin,
            $permissions,
            ['model' => $model, 'user' => static::$userSuperadmin]
        );
        $result = [
            'type'                        => 'field',
            'field'                       => 'field1',
            'user'                        => static::$userSuperadmin,
            'permissions'                 => $permissions,
            'action'                      => 'get',
            'item_name'                   => TestModelBoolean::class . ':field1',
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
        $debugCollector = new Collector($this->treeBuilder, $this->lpFacade, $this->lpLocator);
        $user = new TestUser();
        $model = new TestModelBoolean();
        $model->setAuthor($user);

        $permissions = [
            'NOT' => [
                'condition' => 'user_has_account',
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
            'permission_checks'           => [
                [
                    'permissions' => [
                        'NOT' => ['condition' => 'user_has_account'],
                    ],
                    'resolve' => true,
                ],
                [
                    'permissions' => [
                        'condition' => 'user_has_account',
                    ],
                    'resolve' => false,
                ],
            ],
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
        $debugCollector = new Collector($this->treeBuilder, $this->lpFacade, $this->lpLocator);
        $user = new TestUser();
        $model = new TestModelBoolean();
        $model->setAuthor($user);

        $permissions = [
            'condition' => [
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
            'permission_checks'           => [
                [
                    'permissions' => [
                        'condition' => ['NOT' => 'user_has_account'],
                    ],
                    'resolve' => true,
                ],
                [
                    'permissions' => ['NOT' => 'user_has_account'],
                    'resolve'     => true,
                ],
                [
                    'permissions' => 'user_has_account',
                    'resolve'     => false,
                ],
            ],
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
        $debugCollector = new Collector($this->treeBuilder, $this->lpFacade, $this->lpLocator);
        $user = new TestUser();
        $model = new TestModelBoolean();
        $model->setAuthor($user);

        $permissions = [
            'NO_BYPASS' => [
                'NOT' => [
                    'condition' => 'user_has_account',
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
                'condition' => [
                    'NOT' => [
                        'OR' => [
                            ['NOT' => 'user_has_account'],
                            ['NOT' => 'user_is_author'],
                        ],
                    ],
                ],
            ],
            'condition' => 'user_has_account',
        ];

        $debugCollector->addPermissionCheck(
            true,
            'field',
            ['model' => $model, 'field' => 'field1', 'action' => 'get'],
            $user,
            $permissions,
            ['model' => $model, 'user' => $user]
        );
        $result = [
            'type'                        => 'field',
            'field'                       => 'field1',
            'user'                        => $user,
            'permissions'                 => $permissions,
            'action'                      => 'get',
            'item_name'                   => TestModelBoolean::class . ':field1',
            'item'                        => $model,
            'permission_no_bypass_checks' => array_reverse(
                [
                    [
                        'permissions' => ['condition' => 'user_has_account'],
                        'resolve'     => true,
                    ],
                    [
                        'permissions' => ['NOT' => ['condition' => 'user_has_account']],
                        'resolve'     => false,
                    ],
                ]
            ),
            'bypassed_access'             => false,
            'permission_checks'           => [],
            'permission_checks'           => array_reverse(
                [
                    0  => ['permissions' => 'ROLE_ADMIN', 'resolve' => false],
                    1  => ['permissions' => 'ROLE_ADMIN', 'resolve' => false],
                    2  => ['permissions' => ['AND' => ['ROLE_ADMIN', 'ROLE_ADMIN']], 'resolve' => false],
                    3  => ['permissions' => ['NOT' => ['AND' => ['ROLE_ADMIN', 'ROLE_ADMIN']]], 'resolve' => true],
                    4  => [
                        'permissions' => ['OR' => ['NOT' => ['AND' => ['ROLE_ADMIN', 'ROLE_ADMIN']]]],
                        'resolve'     => true,
                    ],
                    5  => [
                        'permissions' => ['role' => ['OR' => ['NOT' => ['AND' => ['ROLE_ADMIN', 'ROLE_ADMIN']]]]],
                        'resolve'     => true,
                    ],
                    6  => ['permissions' => true, 'resolve' => true],
                    7  => ['permissions' => 'TRUE', 'resolve' => true],
                    8  => ['permissions' => 'user_has_account', 'resolve' => true],
                    9  => ['permissions' => ['NOT' => 'user_has_account'], 'resolve' => false],
                    10 => ['permissions' => 'user_is_author', 'resolve' => true],
                    11 => ['permissions' => ['NOT' => 'user_is_author'], 'resolve' => false],
                    12 => [
                        'permissions' => ['OR' => [['NOT' => 'user_has_account'], ['NOT' => 'user_is_author']]],
                        'resolve'     => false,
                    ],
                    13 => [
                        'permissions' => ['NOT' => ['OR' => [['NOT' => 'user_has_account'], ['NOT' => 'user_is_author']]]],
                        'resolve'     => true,
                    ],
                    14 => [
                        'permissions' => [
                            'condition' => ['NOT' => ['OR' => [['NOT' => 'user_has_account'], ['NOT' => 'user_is_author']]]],
                        ],
                        'resolve' => true,
                    ],
                    15 => [
                        'permissions' => [
                            'AND' => [
                                'role'      => ['OR' => ['NOT' => ['AND' => ['ROLE_ADMIN', 'ROLE_ADMIN']]]],
                                '0'         => true,
                                '1'         => 'TRUE',
                                'condition' => ['NOT' => ['OR' => [['NOT' => 'user_has_account'], ['NOT' => 'user_is_author']]]],
                            ],
                        ],
                        'resolve' => true,
                    ],
                    16 => ['permissions' => ['condition' => 'user_has_account'], 'resolve' => true],
                    17 => [
                        'permissions' => [
                            'AND' => [
                                'role'      => ['OR' => ['NOT' => ['AND' => ['ROLE_ADMIN', 'ROLE_ADMIN']]]],
                                '0'         => true,
                                '1'         => 'TRUE',
                                'condition' => ['NOT' => ['OR' => [['NOT' => 'user_has_account'], ['NOT' => 'user_is_author']]]],
                            ],
                            'condition' => 'user_has_account',
                        ],
                        'resolve' => true,
                    ],
                ]
            ),
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
        $debugCollector = new Collector($this->treeBuilder, $this->lpFacade, $this->lpLocator);
        $user = new TestUser();
        $model = new TestModelBoolean();
        $model->setAuthor($user);
        $permissions = ['no_bypass' => true, 'condition' => ['condition' => 'user_has_account']];
        $debugCollector->addPermissionCheck(
            false,
            'field',
            ['model' => $model, 'field' => 'field1', 'action' => 'get'],
            $user,
            $permissions,
            ['model' => $model, 'user' => $user]
        );
        $debugCollector->collect($request, $response);
        $log = $debugCollector->getLog();
        $firstItem = array_shift($log);
        $this->assertArrayHasKey('message', $firstItem);
    }

    public function testDebugCollectorExceptionHandlingNoDebug()
    {
        $request = new Request();
        $response = new Response();
        $debugCollector = new Collector($this->treeBuilder, $this->lpFacade, $this->lpLocator);
        $user = new TestUser();
        $model = new TestModelBoolean();
        $model->setAuthor($user);
        $permissions = ['no_bypass' => ['condition' => ['condition' => 'user_has_account']], true];
        $debugCollector->addPermissionCheck(
            false,
            'field',
            ['model' => $model, 'field' => 'field1', 'action' => 'get'],
            $user,
            $permissions,
            ['model' => $model, 'user' => $user]
        );
        $debugCollector->collect($request, $response);
        $log = $debugCollector->getLog();
        $firstItem = array_shift($log);
        $this->assertArrayHasKey('message', $firstItem);
    }
}
