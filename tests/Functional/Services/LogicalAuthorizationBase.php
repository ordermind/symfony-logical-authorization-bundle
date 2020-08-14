<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Test\Functional\Services;

use Ordermind\LogicalAuthorizationBundle\Services\HelperInterface;
use Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorizationInterface;
use Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorizationModelInterface;
use Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorizationRouteInterface;
use Ordermind\LogicalAuthorizationBundle\Services\PermissionTreeBuilderInterface;
use Ordermind\LogicalAuthorizationBundle\Test\AppKernel;
use Ordermind\LogicalAuthorizationBundle\Test\Fixtures\Model\TestUser;
use Ordermind\LogicalPermissions\LogicalPermissionsFacade;
use Ordermind\LogicalPermissions\PermissionCheckerLocatorInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Role\RoleHierarchy;
use Twig\Environment as TwigEnvironment;

abstract class LogicalAuthorizationBase extends WebTestCase
{
    /**
     * @var TestUser
     */
    protected static $userSuperadmin;

    /**
     * @var TestUser
     */
    protected static $userAdmin;

    /**
     * @var TestUser
     */
    protected static $userAuthenticated;

    /**
     * @var string[string]
     */
    protected $userCredentials = [
        'authenticated_user' => 'userpass',
        'admin_user'         => 'adminpass',
        'superadmin_user'    => 'superadminpass',
    ];

    /**
     * @var array
     */
    protected $loadServices = [];

    /**
     * @var KernelBrowser
     */
    protected $client;

    /**
     * @var LogicalAuthorizationInterface
     */
    protected $logicalAuthorization;

    /**
     * @var LogicalAuthorizationModelInterface
     */
    protected $laModel;

    /**
     * @var LogicalAuthorizationRouteInterface
     */
    protected $laRoute;

    /**
     * @var HelperInterface
     */
    protected $helper;

    /**
     * @var PermissionTreeBuilderInterface
     */
    protected $treeBuilder;

    /**
     * @var PermissionCheckerLocatorInterface
     */
    protected $lpLocator;

    /**
     * @var LogicalPermissionsFacade
     */
    protected $lpFacade;

    /**
     * @var TwigEnvironment
     */
    protected $twig;

    /**
     * {@inheritDoc}
     */
    protected static function createKernel(array $options = [])
    {
        require_once __DIR__ . '/../../AppKernel.php';

        return new AppKernel('test', true);
    }

    /**
     * This method is run before each public test method.
     */
    protected function setUp(): void
    {
        $this->client = static::createClient();
        // $this->client->catchExceptions(false);
        $container = static::$kernel->getContainer();

        $this->logicalAuthorization = $container->get('test.logauth.service.logauth');
        $this->laModel = $container->get('test.logauth.service.logauth_model');
        $this->laRoute = $container->get('test.logauth.service.logauth_route');
        $this->helper = $container->get('test.logauth.service.helper');
        $this->treeBuilder = $container->get('test.logauth.service.permission_tree_builder');
        $this->lpLocator = $container->get('test.logical_permissions.permission_checker_locator');
        $this->lpFacade = $container->get('test.logical_permissions.facade');
        $this->twig = $container->get('twig');
        $roleHierarchy = $container->getParameter('security.role_hierarchy.roles');
        $this->roleHierarchy = new RoleHierarchy($roleHierarchy);

        $this->addUsers();
    }

    /**
     * This method is run after each public test method.
     *
     * It is important to reset all non-static properties to minimize memory leaks.
     */
    protected function tearDown(): void
    {
        $this->client = null;

        parent::tearDown();
    }

    protected function addUsers()
    {
        //Create new normal user
        if (!static::$userAuthenticated) {
            static::$userAuthenticated = new TestUser();
            static::$userAuthenticated->setUsername('authenticated_user');
            static::$userAuthenticated->setPassword($this->userCredentials['authenticated_user']);
            static::$userAuthenticated->setEmail('user@email.com');
        }

        //Create new admin user
        if (!static::$userAdmin) {
            static::$userAdmin = new TestUser();
            static::$userAdmin->setUsername('admin_user');
            static::$userAdmin->setPassword($this->userCredentials['admin_user']);
            static::$userAdmin->setEmail('admin@email.com');
            static::$userAdmin->setRoles(['ROLE_ADMIN']);
        }

        //Create superadmin user
        if (!static::$userSuperadmin) {
            static::$userSuperadmin = new TestUser();
            static::$userSuperadmin->setUsername('superadmin_user');
            static::$userSuperadmin->setPassword($this->userCredentials['superadmin_user']);
            static::$userSuperadmin->setEmail('superadmin@email.com');
            static::$userSuperadmin->setBypassAccess(true);
        }
    }

    protected function sendRequestAs($method, $slug, array $params = [], $user = null)
    {
        $headers = [];
        if ($user) {
            $headers = [
                'PHP_AUTH_USER' => $user->getUsername(),
                'PHP_AUTH_PW'   => $this->userCredentials[$user->getUsername()],
            ];
        }
        $this->client->request($method, $slug, $params, [], $headers);
    }
}
