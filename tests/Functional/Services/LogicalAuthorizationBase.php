<?php

namespace Ordermind\LogicalAuthorizationBundle\Test\Functional\Services;

use Ordermind\LogicalAuthorizationBundle\Test\Fixtures\Model\TestUser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Role\RoleHierarchy;

abstract class LogicalAuthorizationBase extends WebTestCase
{
    protected static $superadmin_user;
    protected static $admin_user;
    protected static $authenticated_user;
    protected $user_credentials = [
        'authenticated_user' => 'userpass',
        'admin_user'         => 'adminpass',
        'superadmin_user'    => 'superadminpass',
    ];
    protected $load_services = [];
    protected $client;
    protected $la;
    protected $helper;
    protected $twig;

    /**
     * {@inheritDoc}
     */
    protected static function createKernel(array $options = [])
    {
        require_once __DIR__ . '/../../AppKernel.php';

        return new \AppKernel('test', true);
    }

    /**
     * This method is run before each public test method.
     */
    protected function setUp(): void
    {
        $this->client = static::createClient();
        // $this->client->catchExceptions(false);
        $container = static::$kernel->getContainer();

        $this->la = $container->get('test.logauth.service.logauth');
        $this->lpProxy = $container->get('test.logauth.service.logical_permissions_proxy');
        $this->laModel = $container->get('test.logauth.service.logauth_model');
        $this->laRoute = $container->get('test.logauth.service.logauth_route');
        $this->helper = $container->get('test.logauth.service.helper');
        $this->treeBuilder = $container->get('test.logauth.service.permission_tree_builder');
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
        if (!static::$authenticated_user) {
            static::$authenticated_user = new TestUser();
            static::$authenticated_user->setUsername('authenticated_user');
            static::$authenticated_user->setPassword($this->user_credentials['authenticated_user']);
            static::$authenticated_user->setEmail('user@email.com');
        }

        //Create new admin user
        if (!static::$admin_user) {
            static::$admin_user = new TestUser();
            static::$admin_user->setUsername('admin_user');
            static::$admin_user->setPassword($this->user_credentials['admin_user']);
            static::$admin_user->setEmail('admin@email.com');
            static::$admin_user->setRoles(['ROLE_ADMIN']);
        }

        //Create superadmin user
        if (!static::$superadmin_user) {
            static::$superadmin_user = new TestUser();
            static::$superadmin_user->setUsername('superadmin_user');
            static::$superadmin_user->setPassword($this->user_credentials['superadmin_user']);
            static::$superadmin_user->setEmail('superadmin@email.com');
            static::$superadmin_user->setBypassAccess(true);
        }
    }

    protected function sendRequestAs($method = 'GET', $slug, array $params = [], $user = null)
    {
        $headers = [];
        if ($user) {
            $headers = [
                'PHP_AUTH_USER' => $user->getUsername(),
                'PHP_AUTH_PW'   => $this->user_credentials[$user->getUsername()],
            ];
        }
        $this->client->request($method, $slug, $params, [], $headers);
    }
}
