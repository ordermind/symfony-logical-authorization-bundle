<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\Misc\Functional\Services;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Serializer\Encoder\JsonDecode;

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
  protected $testEntityOverriddenPermissionsRepositoryManager;
  protected $testUserRepositoryManager;
  protected $testModelOperations;
  protected $client;

  /**
   * This method is run before each public test method
   */
  protected function setUp() {
    require_once __DIR__.'/../../AppKernel.php';
    $kernel = new \AppKernel('test', true);
    $kernel->boot();
    $this->client = static::createClient();

    $this->load_services['testUserRepositoryManager'] = 'repository_manager.test_user';
    $this->load_services['testModelOperations'] = 'test_model_operations';
    $container = $kernel->getContainer();
    foreach($this->load_services as $property_name => $service_name) {
      $this->$property_name = $container->get($service_name);
    }

    $this->deleteAll(array(
      $this->testEntityOverriddenPermissionsRepositoryManager,
    ));

    $this->addUsers();
  }

  /**
   * This method is run after each public test method
   *
   * It is important to reset all non-static properties to minimize memory leaks.
   */
  protected function tearDown() {
    $this->testEntityOverriddenPermissionsRepositoryManager->getObjectManager()->getConnection()->close();
    $this->testEntityOverriddenPermissionsRepositoryManager = null;
    $this->testUserRepositoryManager->getObjectManager()->getConnection()->close();
    $this->testUserRepositoryManager = null;
    $this->testModelOperations = null;
    $this->client = null;

    parent::tearDown();
  }

  protected function deleteAll($managers) {
    foreach($managers as $repositoryManager) {
      $modelManagers = $repositoryManager->findAll();
      foreach($modelManagers as $modelManager) {
        $modelManager->delete(false);
      }
      $repositoryManager->getObjectManager()->flush();
    }
  }

  protected function addUsers() {
    //Create new normal user
    if(!static::$authenticated_user || get_class(static::$authenticated_user->getModel()) !== $this->testUserRepositoryManager->getClassName()) {
      static::$authenticated_user = $this->testUserRepositoryManager->create('authenticated_user', $this->user_credentials['authenticated_user'], [], 'user@email.com');
      static::$authenticated_user->save();
    }

    //Create new admin user
    if(!static::$admin_user || get_class(static::$admin_user->getModel()) !== $this->testUserRepositoryManager->getClassName()) {
      static::$admin_user = $this->testUserRepositoryManager->create('admin_user', $this->user_credentials['admin_user'], ['ROLE_ADMIN'], 'admin@email.com');
      static::$admin_user->save();
    }

    //Create superadmin user
    if(!static::$superadmin_user || get_class(static::$superadmin_user->getModel()) !== $this->testUserRepositoryManager->getClassName()) {
      static::$superadmin_user = $this->testUserRepositoryManager->create('superadmin_user', $this->user_credentials['superadmin_user'], [], 'superadmin@email.com');
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

  public function testEntityPermissionsOverride() {
    $this->testModelOperations->setRepositoryManager($this->testEntityOverriddenPermissionsRepositoryManager);
    $this->testModelOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/count-unknown-result', array('repository_manager_service' => $this->load_services['testEntityOverriddenPermissionsRepositoryManager']), static::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $entities_count = $response->getContent();
    $this->assertEquals(1, $entities_count);
  }
}