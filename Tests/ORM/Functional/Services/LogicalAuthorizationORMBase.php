<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\ORM\Functional\Services;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class LogicalAuthorizationORMBase extends WebTestCase {
  protected static $superadmin_user;
  protected static $admin_user;
  protected static $authenticated_user;
  protected $user_credentials = [
    'authenticated_user' => 'userpass',
    'admin_user' => 'adminpass',
    'superadmin_user' => 'superadminpass',
  ];
  protected $laRoute;
  protected $laModel;
  protected $em;
  protected $testEntityRepositoryManager;
  protected $testUserRepositoryManager;
  protected $testEntityOperations;
  protected $container;
  protected $client;

  /**
   * This method is run before each public test method
   */
  protected function setUp() {
    require_once __DIR__.'/../../AppKernel.php';
    $kernel = new \AppKernel('test', true);
    $kernel->boot();
    $this->container = $kernel->getContainer();
    $this->laRoute = $this->container->get('ordermind_logical_authorization.service.logical_authorization_route');
    $this->laModel = $this->container->get('ordermind_logical_authorization.service.logical_authorization_model');
    $this->testEntityOperations = $this->container->get('test_entity_operations');
    $this->client = static::createClient();
  }

  protected function deleteAll($repositoryManager) {
    $modelManagers = $repositoryManager->findAll();
    foreach($modelManagers as $modelManager) {
      $modelManager->delete(false);
    }
    $repositoryManager->getObjectManager()->flush();
  }

  protected function addUsers() {
    //Create new normal user
    if(!self::$authenticated_user) {
      self::$authenticated_user = $this->testUserRepositoryManager->create('authenticated_user', $this->user_credentials['authenticated_user'], [], 'user@email.com');
      self::$authenticated_user->save();
    }

    //Create new admin user
    if(!self::$admin_user) {
      self::$admin_user = $this->testUserRepositoryManager->create('admin_user', $this->user_credentials['admin_user'], ['ROLE_ADMIN'], 'admin@email.com');
      self::$admin_user->save();
    }

    //Create superadmin user
    if(!self::$superadmin_user) {
      self::$superadmin_user = $this->testUserRepositoryManager->create('superadmin_user', $this->user_credentials['superadmin_user'], [], 'superadmin@email.com');
      self::$superadmin_user->setBypassAccess(true);
      self::$superadmin_user->save();
    }
  }

  protected function sendRequestAs($method = 'GET', $slug, $user) {
    $this->client->request($method, $slug, array(), array(), array(
      'PHP_AUTH_USER' => $user->getUsername(),
      'PHP_AUTH_PW'   => $this->user_credentials[$user->getUsername()],
    ));
  }

  /*------------Entity tests------------*/

  /*----Create----*/

  public function testCreateEntityAllowRole() {
    $this->sendRequestAs('POST', '/test/create-entity', self::$admin_user);
    $entities = $this->testEntityOperations->findTestEntities();
    $this->assertEquals(1, count($entities));
  }

  public function testCreateEntityDisallow() {
    $this->sendRequestAs('POST', '/test/create-entity', self::$authenticated_user);
    $entities = $this->testEntityOperations->findTestEntities();
    $this->assertEquals(0, count($entities));
  }

  public function testCreateEntityBypass() {
    $this->sendRequestAs('POST', '/test/create-entity', self::$superadmin_user);
    $entities = $this->testEntityOperations->findTestEntities();
    $this->assertEquals(1, count($entities));
  }

  /*----Read----*/

  public function testReadEntitiesAllowRole() {
    $this->testEntityOperations->createTestEntity(self::$admin_user);
    $this->sendRequestAs('GET', '/test/count-entities', self::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $entities_count = $response->getContent();
    $this->assertEquals(1, $entities_count);
  }

  public function testReadEntitiesAllowAuthor() {
    $this->testEntityOperations->createTestEntity(self::$authenticated_user);
    $this->sendRequestAs('GET', '/test/count-entities', self::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $entities_count = $response->getContent();
    $this->assertEquals(1, $entities_count);
  }

  public function testReadEntitiesDisallow() {
    $this->testEntityOperations->createTestEntity(self::$admin_user);
    $entities_count = $this->sendRequestAs('GET', '/test/count-entities', self::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $entities_count = $response->getContent();
    $this->assertEquals(0, $entities_count);
    //Kolla att entiteten fortfarande finns i databasen
    $entities = $this->testEntityOperations->findTestEntities();
    $this->assertEquals(1, count($entities));
  }

  public function testReadEntitiesNoBypass() {
    $this->testEntityOperations->createTestEntity(self::$admin_user);
    $this->sendRequestAs('GET', '/test/count-entities', self::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $entities_count = $response->getContent();
    $this->assertEquals(0, $entities_count);
    //Kolla att entiteten fortfarande finns i databasen
    $entities = $this->testEntityOperations->findTestEntities();
    $this->assertEquals(1, count($entities));
  }

  public function testReadEntitiesLazyLoadAllowRole() {
    $this->testEntityOperations->createTestEntity(self::$admin_user);
    $this->sendRequestAs('GET', '/test/count-entities-lazy', self::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $entities_count = $response->getContent();
    $this->assertEquals(1, $entities_count);
  }

  public function testReadEntitiesLazyLoadAllowAuthor() {
    $this->testEntityOperations->createTestEntity(self::$authenticated_user);
    $this->sendRequestAs('GET', '/test/count-entities-lazy', self::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $entities_count = $response->getContent();
    $this->assertEquals(1, $entities_count);
  }

  public function testReadEntitiesLazyLoadDisallow() {
    $this->testEntityOperations->createTestEntity(self::$admin_user);
    $entities_count = $this->sendRequestAs('GET', '/test/count-entities-lazy', self::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $entities_count = $response->getContent();
    $this->assertEquals(0, $entities_count);
    //Kolla att entiteten fortfarande finns i databasen
    $entities = $this->testEntityOperations->findTestEntities();
    $this->assertEquals(1, count($entities));
  }

  public function testReadEntitiesLazyLoadNoBypass() {
    $this->testEntityOperations->createTestEntity(self::$admin_user);
    $this->sendRequestAs('GET', '/test/count-entities-lazy', self::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $entities_count = $response->getContent();
    $this->assertEquals(0, $entities_count);
    //Kolla att entiteten fortfarande finns i databasen
    $entities = $this->testEntityOperations->findTestEntities();
    $this->assertEquals(1, count($entities));
  }

//   /*----Read field-----*/
//   public function testReadField1AllowRole() {
//     $this->testEntityOperations->createTestEntity(self::$admin_user);
//     $this->sendRequestAs('GET', '/test/read-field-1', self::$admin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(1, $entities_count);
//   }

//   /*----Update field----*/
//   public function testUpdateEntityAllowRole() {
//     $this->testEntityOperations->createTestEntity(self::$admin_user);
//     $this->sendRequestAs('GET', '/test/count-entities', self::$admin_user);
//   }
}
