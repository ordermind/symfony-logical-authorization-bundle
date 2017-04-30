<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\Misc\Functional\Services;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class LogicalAuthorizationMiscBase extends WebTestCase {
  protected static $superadmin_user;
  protected static $admin_user;
  protected static $authenticated_user;
  protected $user_credentials = [
    'authenticated_user' => 'userpass',
    'admin_user' => 'adminpass',
    'superadmin_user' => 'superadminpass',
  ];
  protected $load_services = array();
  protected $testUserRepositoryDecorator;
  protected $testModelOperations;
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
    $this->testUserRepositoryDecorator = $container->get('repository_decorator.test_user');
    $this->testModelOperations = $container->get('test_model_operations');
    $this->la = $container->get('ordermind_logical_authorization.service.logical_authorization');
    $this->laModel = $container->get('ordermind_logical_authorization.service.logical_authorization_model');
    $this->laRoute = $container->get('ordermind_logical_authorization.service.logical_authorization_route');
    $this->helper = $container->get('ordermind_logical_authorization.service.helper');

    $this->addUsers();
  }

  /**
   * This method is run after each public test method
   *
   * It is important to reset all non-static properties to minimize memory leaks.
   */
  protected function tearDown() {
    if(!is_null($this->testUserRepositoryDecorator)) {
      $this->testUserRepositoryDecorator->getObjectManager()->getConnection()->close();
      $this->testUserRepositoryDecorator = null;
    }
    $this->testModelOperations = null;
    $this->client = null;

    parent::tearDown();
  }

  protected function deleteAll($decorators) {
    foreach($decorators as $repositoryDecorator) {
      $modelDecorators = $repositoryDecorator->findAll();
      foreach($modelDecorators as $modelDecorator) {
        $modelDecorator->delete(false);
      }
      $repositoryDecorator->getObjectManager()->flush();
    }
  }

  protected function addUsers() {
    //Create new normal user
    if(!static::$authenticated_user || get_class(static::$authenticated_user->getModel()) !== $this->testUserRepositoryDecorator->getClassName()) {
      static::$authenticated_user = $this->testUserRepositoryDecorator->create('authenticated_user', $this->user_credentials['authenticated_user'], [], 'user@email.com');
      static::$authenticated_user->save();
    }

    //Create new admin user
    if(!static::$admin_user || get_class(static::$admin_user->getModel()) !== $this->testUserRepositoryDecorator->getClassName()) {
      static::$admin_user = $this->testUserRepositoryDecorator->create('admin_user', $this->user_credentials['admin_user'], ['ROLE_ADMIN'], 'admin@email.com');
      static::$admin_user->save();
    }

    //Create superadmin user
    if(!static::$superadmin_user || get_class(static::$superadmin_user->getModel()) !== $this->testUserRepositoryDecorator->getClassName()) {
      static::$superadmin_user = $this->testUserRepositoryDecorator->create('superadmin_user', $this->user_credentials['superadmin_user'], [], 'superadmin@email.com');
      static::$superadmin_user->setBypassAccess(true);
      static::$superadmin_user->save();
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

