<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\Functional\Services;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use Ordermind\LogicalAuthorizationBundle\Tests\Fixtures\Model\TestUser;

abstract class LogicalAuthorizationBase extends WebTestCase {
  protected static $superadmin_user;
  protected static $admin_user;
  protected static $authenticated_user;
  protected $user_credentials = [
    'authenticated_user' => 'userpass',
    'admin_user' => 'adminpass',
    'superadmin_user' => 'superadminpass',
  ];
  protected $load_services = array();
  protected $client;
  protected $la;
  protected $helper;

  /**
   * This method is run before each public test method
   */
  protected function setUp() {
    require_once __DIR__.'/../../AppKernel.php';
    $kernel = new \AppKernel('test', true);
    $kernel->boot();
    $container = $kernel->getContainer();

    $this->client = static::createClient();
    $this->la = $container->get('logauth.service.logauth');
    $this->lpProxy = $container->get('logauth.service.logical_permissions_proxy');
    $this->laModel = $container->get('logauth.service.logauth_model');
    $this->laRoute = $container->get('logauth.service.logauth_route');
    $this->helper = $container->get('logauth.service.helper');
    $this->treeBuilder = $container->get('logauth.service.permission_tree_builder');

    $this->addUsers();
  }

  /**
   * This method is run after each public test method
   *
   * It is important to reset all non-static properties to minimize memory leaks.
   */
  protected function tearDown() {
    $this->client = null;

    parent::tearDown();
  }

  protected function addUsers() {
    //Create new normal user
    if(!static::$authenticated_user) {
      static::$authenticated_user = new TestUser();
      static::$authenticated_user->setUsername('authenticated_user');
      static::$authenticated_user->setPassword($this->user_credentials['authenticated_user']);
      static::$authenticated_user->setEmail('user@email.com');
    }

    //Create new admin user
    if(!static::$admin_user) {
      static::$admin_user = new TestUser();
      static::$admin_user->setUsername('admin_user');
      static::$admin_user->setPassword($this->user_credentials['admin_user']);
      static::$admin_user->setEmail('admin@email.com');
      static::$admin_user->setRoles(['ROLE_ADMIN']);
    }

    //Create superadmin user
    if(!static::$superadmin_user) {
      static::$superadmin_user = new TestUser();
      static::$superadmin_user->setUsername('superadmin_user');
      static::$superadmin_user->setPassword($this->user_credentials['superadmin_user']);
      static::$superadmin_user->setEmail('superadmin@email.com');
      static::$superadmin_user->setBypassAccess(true);
    }
  }

  protected function sendRequestAs($method = 'GET', $slug, array $params = array(), $user = null) {
    $headers = array();
    if($user) {
      $headers = array(
        'PHP_AUTH_USER' => $user->getUsername(),
        'PHP_AUTH_PW'   => $this->user_credentials[$user->getUsername()],
      );
    }
    $this->client->request($method, $slug, $params, array(), $headers);
  }
}

