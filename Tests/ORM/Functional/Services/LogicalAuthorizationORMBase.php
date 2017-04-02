<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\ORM\Functional\Services;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Serializer\Encoder\JsonDecode;

abstract class LogicalAuthorizationORMBase extends WebTestCase {
  protected static $superadmin_user;
  protected static $admin_user;
  protected static $authenticated_user;
  protected $user_credentials = [
    'authenticated_user' => 'userpass',
    'admin_user' => 'adminpass',
    'superadmin_user' => 'superadminpass',
  ];
  protected $load_services = array();
  protected $testEntityRoleAuthorRepositoryManager;
  protected $testEntityHasAccountNoInterfaceRepositoryManager;
  protected $testEntityNoBypassRepositoryManager;
  protected $testUserRepositoryManager;
  protected $testEntityOperations;
  protected $client;

  /**
   * This method is run before each public test method
   */
  protected function setUp() {
    require_once __DIR__.'/../../AppKernel.php';
    $kernel = new \AppKernel('test', true);
    $kernel->boot();
    $this->client = static::createClient();

    $this->load_services['testEntityOperations'] = 'test_entity_operations';
    $container = $kernel->getContainer();
    foreach($this->load_services as $property_name => $service_name) {
      $this->$property_name = $container->get($service_name);
    }

    $this->deleteAll(array(
      $this->testEntityRoleAuthorRepositoryManager,
      $this->testEntityHasAccountNoInterfaceRepositoryManager,
      $this->testEntityNoBypassRepositoryManager,
    ));

    $this->addUsers();
  }

  /**
   * This method is run after each public test method
   *
   * It is important to reset all non-static properties to minimize memory leaks.
   */
  protected function tearDown() {
    $this->testEntityRoleAuthorRepositoryManager = null;
    $this->testEntityHasAccountNoInterfaceRepositoryManager = null;
    $this->testEntityNoBypassRepositoryManager = null;
    $this->testUserRepositoryManager = null;
    $this->testEntityOperations = null;
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
    if(!static::$authenticated_user) {
      static::$authenticated_user = $this->testUserRepositoryManager->create('authenticated_user', $this->user_credentials['authenticated_user'], [], 'user@email.com');
      static::$authenticated_user->save();
    }

    //Create new admin user
    if(!static::$admin_user) {
      static::$admin_user = $this->testUserRepositoryManager->create('admin_user', $this->user_credentials['admin_user'], ['ROLE_ADMIN'], 'admin@email.com');
      static::$admin_user->save();
    }

    //Create superadmin user
    if(!static::$superadmin_user) {
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

  /*------------RepositoryManager event tests------------*/

  /*---onUnknownResult---*/

  public function testOnUnknownResultRoleAllow() {
    $this->testEntityOperations->setRepositoryManager($this->testEntityRoleAuthorRepositoryManager);
    $this->testEntityOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/count-unknown-result', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $entities_count = $response->getContent();
    $this->assertEquals(1, $entities_count);
  }

  public function testOnUnknownResultRoleDisallow() {
    $this->testEntityOperations->setRepositoryManager($this->testEntityRoleAuthorRepositoryManager);
    $this->testEntityOperations->createTestModel();
    $entities_count = $this->sendRequestAs('GET', '/test/count-unknown-result', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $entities_count = $response->getContent();
    $this->assertEquals(0, $entities_count);
    //Kolla att entiteten fortfarande finns i databasen
    $entities = $this->testEntityOperations->getUnknownResult();
    $this->assertEquals(1, count($entities));
  }

  public function testOnUnknownResultFlagBypassAccessAllow() {
    $this->testEntityOperations->setRepositoryManager($this->testEntityRoleAuthorRepositoryManager);
    $this->testEntityOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/count-unknown-result', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $entities_count = $response->getContent();
    $this->assertEquals(1, $entities_count);
  }

  public function testOnUnknownResultFlagBypassAccessDisallow() {
    $this->testEntityOperations->setRepositoryManager($this->testEntityNoBypassRepositoryManager);
    $this->testEntityOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/count-unknown-result', array('repository_manager_service' => $this->load_services['testEntityNoBypassRepositoryManager']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $entities_count = $response->getContent();
    $this->assertEquals(0, $entities_count);
    //Kolla att entiteten fortfarande finns i databasen
    $entities = $this->testEntityOperations->getUnknownResult();
    $this->assertEquals(1, count($entities));
  }

  public function testOnUnknownResultFlagHasAccountAllow() {
    $this->testEntityOperations->setRepositoryManager($this->testEntityHasAccountNoInterfaceRepositoryManager);
    $this->testEntityOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/count-unknown-result', array('repository_manager_service' => $this->load_services['testEntityHasAccountNoInterfaceRepositoryManager']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $entities_count = $response->getContent();
    $this->assertEquals(1, $entities_count);
  }

  public function testOnUnknownResultFlagHasAccountDisallow() {
    $this->testEntityOperations->setRepositoryManager($this->testEntityHasAccountNoInterfaceRepositoryManager);
    $this->testEntityOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/count-unknown-result', array('repository_manager_service' => $this->load_services['testEntityHasAccountNoInterfaceRepositoryManager']));
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $entities_count = $response->getContent();
    $this->assertEquals(0, $entities_count);
    //Kolla att entiteten fortfarande finns i databasen
    $entities = $this->testEntityOperations->getUnknownResult();
    $this->assertEquals(1, count($entities));
  }

  public function testOnUnknownResultFlagIsAuthorAllow() {
    $this->testEntityOperations->setRepositoryManager($this->testEntityRoleAuthorRepositoryManager);
    $this->testEntityOperations->createTestModel(static::$authenticated_user);
    $this->sendRequestAs('GET', '/test/count-unknown-result', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $entities_count = $response->getContent();
    $this->assertEquals(1, $entities_count);
  }

  public function testOnUnknownResultFlagIsAuthorDisallow() {
    $this->testEntityOperations->setRepositoryManager($this->testEntityRoleAuthorRepositoryManager);
    $this->testEntityOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/count-unknown-result', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $entities_count = $response->getContent();
    $this->assertEquals(0, $entities_count);
    //Kolla att entiteten fortfarande finns i databasen
    $entities = $this->testEntityOperations->getUnknownResult();
    $this->assertEquals(1, count($entities));
  }

  /*---onSingleModelResult---*/

  public function testOnSingleModelResultRoleAllow() {
    $this->testEntityOperations->setRepositoryManager($this->testEntityRoleAuthorRepositoryManager);
    $modelManager = $this->testEntityOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/find-single-model-result/' . $modelManager->getId(), array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $entity_found = $decoder->decode($response->getContent(), 'json');
    $this->assertTrue($entity_found);
  }

  public function testOnSingleModelResultRoleDisallow() {
    $this->testEntityOperations->setRepositoryManager($this->testEntityRoleAuthorRepositoryManager);
    $modelManager = $this->testEntityOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/find-single-model-result/' . $modelManager->getId(), array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $entity_found = $decoder->decode($response->getContent(), 'json');
    $this->assertFalse($entity_found);
    //Kolla att entiteten fortfarande finns i databasen
    $this->assertTrue((bool) $this->testEntityOperations->getSingleModelResult($modelManager->getId()));
  }

  public function testOnSingleModelResultFlagBypassAccessAllow() {
    $this->testEntityOperations->setRepositoryManager($this->testEntityRoleAuthorRepositoryManager);
    $modelManager = $this->testEntityOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/find-single-model-result/' . $modelManager->getId(), array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $entity_found = $decoder->decode($response->getContent(), 'json');
    $this->assertTrue($entity_found);
  }

  public function testOnSingleModelResultFlagBypassAccessDisallow() {
    $this->testEntityOperations->setRepositoryManager($this->testEntityNoBypassRepositoryManager);
    $modelManager = $this->testEntityOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/find-single-model-result/' . $modelManager->getId(), array('repository_manager_service' => $this->load_services['testEntityNoBypassRepositoryManager']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $entity_found = $decoder->decode($response->getContent(), 'json');
    $this->assertFalse($entity_found);
    //Kolla att entiteten fortfarande finns i databasen
    $this->assertTrue((bool) $this->testEntityOperations->getSingleModelResult($modelManager->getId()));
  }

  public function testOnSingleModelResultFlagHasAccountAllow() {
    $this->testEntityOperations->setRepositoryManager($this->testEntityHasAccountNoInterfaceRepositoryManager);
    $modelManager = $this->testEntityOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/find-single-model-result/' . $modelManager->getId(), array('repository_manager_service' => $this->load_services['testEntityHasAccountNoInterfaceRepositoryManager']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $entity_found = $decoder->decode($response->getContent(), 'json');
    $this->assertTrue($entity_found);
  }

  public function testOnSingleModelResultFlagHasAccountDisallow() {
    $this->testEntityOperations->setRepositoryManager($this->testEntityHasAccountNoInterfaceRepositoryManager);
    $modelManager = $this->testEntityOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/find-single-model-result/' . $modelManager->getId(), array('repository_manager_service' => $this->load_services['testEntityHasAccountNoInterfaceRepositoryManager']));
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $entity_found = $decoder->decode($response->getContent(), 'json');
    $this->assertFalse($entity_found);
    //Kolla att entiteten fortfarande finns i databasen
    $this->assertTrue((bool) $this->testEntityOperations->getSingleModelResult($modelManager->getId()));
  }

  public function testOnSingleModelResultFlagIsAuthorAllow() {
    $this->testEntityOperations->setRepositoryManager($this->testEntityRoleAuthorRepositoryManager);
    $modelManager = $this->testEntityOperations->createTestModel(static::$authenticated_user);
    $this->sendRequestAs('GET', '/test/find-single-model-result/' . $modelManager->getId(), array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $entity_found = $decoder->decode($response->getContent(), 'json');
    $this->assertTrue($entity_found);
  }

  public function testOnSingleModelResultFlagIsAuthorDisallow() {
    $this->testEntityOperations->setRepositoryManager($this->testEntityRoleAuthorRepositoryManager);
    $modelManager = $this->testEntityOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/find-single-model-result/' . $modelManager->getId(), array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $entity_found = $decoder->decode($response->getContent(), 'json');
    $this->assertFalse($entity_found);
    //Kolla att entiteten fortfarande finns i databasen
    $this->assertTrue((bool) $this->testEntityOperations->getSingleModelResult($modelManager->getId()));
  }

  /*---onMultipleModelResult---*/

  public function testOnMultipleModelResultRoleAllow() {
    $this->testEntityOperations->setRepositoryManager($this->testEntityRoleAuthorRepositoryManager);
    $this->testEntityOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/count-multiple-model-result', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $entities_count = $response->getContent();
    $this->assertEquals(1, $entities_count);
  }

  public function testOnMultipleModelResultRoleDisallow() {
    $this->testEntityOperations->setRepositoryManager($this->testEntityRoleAuthorRepositoryManager);
    $this->testEntityOperations->createTestModel();
    $entities_count = $this->sendRequestAs('GET', '/test/count-multiple-model-result', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $entities_count = $response->getContent();
    $this->assertEquals(0, $entities_count);
    //Kolla att entiteten fortfarande finns i databasen
    $entities = $this->testEntityOperations->getMultipleModelResult();
    $this->assertEquals(1, count($entities));
  }

  public function testOnMultipleModelResultFlagBypassAccessAllow() {
    $this->testEntityOperations->setRepositoryManager($this->testEntityRoleAuthorRepositoryManager);
    $this->testEntityOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/count-multiple-model-result', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $entities_count = $response->getContent();
    $this->assertEquals(1, $entities_count);
  }

  public function testOnMultipleModelResultFlagBypassAccessDisallow() {
    $this->testEntityOperations->setRepositoryManager($this->testEntityNoBypassRepositoryManager);
    $this->testEntityOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/count-multiple-model-result', array('repository_manager_service' => $this->load_services['testEntityNoBypassRepositoryManager']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $entities_count = $response->getContent();
    $this->assertEquals(0, $entities_count);
    //Kolla att entiteten fortfarande finns i databasen
    $entities = $this->testEntityOperations->getMultipleModelResult();
    $this->assertEquals(1, count($entities));
  }

  public function testOnMultipleModelResultFlagHasAccountAllow() {
    $this->testEntityOperations->setRepositoryManager($this->testEntityHasAccountNoInterfaceRepositoryManager);
    $this->testEntityOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/count-multiple-model-result', array('repository_manager_service' => $this->load_services['testEntityHasAccountNoInterfaceRepositoryManager']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $entities_count = $response->getContent();
    $this->assertEquals(1, $entities_count);
  }

  public function testOnMultipleModelResultFlagHasAccountDisallow() {
    $this->testEntityOperations->setRepositoryManager($this->testEntityHasAccountNoInterfaceRepositoryManager);
    $this->testEntityOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/count-multiple-model-result', array('repository_manager_service' => $this->load_services['testEntityHasAccountNoInterfaceRepositoryManager']));
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $entities_count = $response->getContent();
    $this->assertEquals(0, $entities_count);
    //Kolla att entiteten fortfarande finns i databasen
    $entities = $this->testEntityOperations->getMultipleModelResult();
    $this->assertEquals(1, count($entities));
  }

  public function testOnMultipleModelResultFlagIsAuthorAllow() {
    $this->testEntityOperations->setRepositoryManager($this->testEntityRoleAuthorRepositoryManager);
    $this->testEntityOperations->createTestModel(static::$authenticated_user);
    $this->sendRequestAs('GET', '/test/count-multiple-model-result', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $entities_count = $response->getContent();
    $this->assertEquals(1, $entities_count);
  }

  public function testOnMultipleModelResultFlagIsAuthorDisallow() {
    $this->testEntityOperations->setRepositoryManager($this->testEntityRoleAuthorRepositoryManager);
    $this->testEntityOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/count-multiple-model-result', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $entities_count = $response->getContent();
    $this->assertEquals(0, $entities_count);
    //Kolla att entiteten fortfarande finns i databasen
    $entities = $this->testEntityOperations->getMultipleModelResult();
    $this->assertEquals(1, count($entities));
  }

  /*---onBeforeCreate---*/

  public function testOnBeforeCreateRoleAllow() {
    $this->sendRequestAs('GET', '/test/create-entity', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $entity_created = $decoder->decode($response->getContent(), 'json');
    $this->assertTrue($entity_created);
  }

  public function testOnBeforeCreateRoleDisallow() {
    $this->sendRequestAs('GET', '/test/create-entity', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $entity_created = $decoder->decode($response->getContent(), 'json');
    $this->assertFalse($entity_created);
  }

  public function testOnBeforeCreateFlagBypassAccessAllow() {
    $this->sendRequestAs('GET', '/test/create-entity', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $entity_created = $decoder->decode($response->getContent(), 'json');
    $this->assertTrue($entity_created);
  }

  public function testOnBeforeCreateFlagBypassAccessDisallow() {
    $this->sendRequestAs('GET', '/test/create-entity', array('repository_manager_service' => $this->load_services['testEntityNoBypassRepositoryManager']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $entity_created = $decoder->decode($response->getContent(), 'json');
    $this->assertFalse($entity_created);
  }

  public function testOnBeforeCreateFlagHasAccountAllow() {
    $this->sendRequestAs('GET', '/test/create-entity', array('repository_manager_service' => $this->load_services['testEntityHasAccountNoInterfaceRepositoryManager']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $entity_created = $decoder->decode($response->getContent(), 'json');
    $this->assertTrue($entity_created);
  }

  public function testOnBeforeCreateFlagHasAccountDisallow() {
    $this->sendRequestAs('GET', '/test/create-entity', array('repository_manager_service' => $this->load_services['testEntityHasAccountNoInterfaceRepositoryManager']));
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $entity_created = $decoder->decode($response->getContent(), 'json');
    $this->assertFalse($entity_created);
  }

  /*---onLazyModelCollectionResult---*/

  public function testOnLazyModelCollectionResultRoleAllow() {
    $this->testEntityOperations->setRepositoryManager($this->testEntityRoleAuthorRepositoryManager);
    $this->testEntityOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/count-entities-lazy', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $entities_count = $response->getContent();
    $this->assertEquals(1, $entities_count);
  }

  public function testOnLazyModelCollectionResultRoleDisallow() {
    $this->testEntityOperations->setRepositoryManager($this->testEntityRoleAuthorRepositoryManager);
    $this->testEntityOperations->createTestModel();
    $entities_count = $this->sendRequestAs('GET', '/test/count-entities-lazy', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $entities_count = $response->getContent();
    $this->assertEquals(0, $entities_count);
    //Kolla att entiteten fortfarande finns i databasen
    $entities = $this->testEntityOperations->getLazyLoadedModelResult();
    $this->assertEquals(1, count($entities));
  }

  public function testOnLazyModelCollectionResultFlagBypassAccessAllow() {
    $this->testEntityOperations->setRepositoryManager($this->testEntityRoleAuthorRepositoryManager);
    $this->testEntityOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/count-entities-lazy', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $entities_count = $response->getContent();
    $this->assertEquals(1, $entities_count);
  }

  public function testOnLazyModelCollectionResultFlagBypassAccessDisallow() {
    $this->testEntityOperations->setRepositoryManager($this->testEntityNoBypassRepositoryManager);
    $this->testEntityOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/count-entities-lazy', array('repository_manager_service' => $this->load_services['testEntityNoBypassRepositoryManager']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $entities_count = $response->getContent();
    $this->assertEquals(0, $entities_count);
    //Kolla att entiteten fortfarande finns i databasen
    $entities = $this->testEntityOperations->getLazyLoadedModelResult();
    $this->assertEquals(1, count($entities));
  }

  public function testOnLazyModelCollectionResultFlagHasAccountAllow() {
    $this->testEntityOperations->setRepositoryManager($this->testEntityHasAccountNoInterfaceRepositoryManager);
    $this->testEntityOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/count-entities-lazy', array('repository_manager_service' => $this->load_services['testEntityHasAccountNoInterfaceRepositoryManager']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $entities_count = $response->getContent();
    $this->assertEquals(1, $entities_count);
  }

  public function testOnLazyModelCollectionResultFlagHasAccountDisallow() {
    $this->testEntityOperations->setRepositoryManager($this->testEntityHasAccountNoInterfaceRepositoryManager);
    $this->testEntityOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/count-entities-lazy', array('repository_manager_service' => $this->load_services['testEntityHasAccountNoInterfaceRepositoryManager']));
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $entities_count = $response->getContent();
    $this->assertEquals(0, $entities_count);
    //Kolla att entiteten fortfarande finns i databasen
    $entities = $this->testEntityOperations->getLazyLoadedModelResult();
    $this->assertEquals(1, count($entities));
  }

  public function testOnLazyModelCollectionResultFlagIsAuthorAllow() {
    $this->testEntityOperations->setRepositoryManager($this->testEntityRoleAuthorRepositoryManager);
    $this->testEntityOperations->createTestModel(static::$authenticated_user);
    $this->sendRequestAs('GET', '/test/count-entities-lazy', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $entities_count = $response->getContent();
    $this->assertEquals(1, $entities_count);
  }

  public function testOnLazyModelCollectionResultFlagIsAuthorDisallow() {
    $this->testEntityOperations->setRepositoryManager($this->testEntityRoleAuthorRepositoryManager);
    $this->testEntityOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/count-entities-lazy', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $entities_count = $response->getContent();
    $this->assertEquals(0, $entities_count);
    //Kolla att entiteten fortfarande finns i databasen
    $entities = $this->testEntityOperations->getLazyLoadedModelResult();
    $this->assertEquals(1, count($entities));
  }

  /*----------ModelManager event tests------------*/

  /*---onBeforeMethodCall getter---*/

  public function testOnBeforeMethodCallGetterRoleAllow() {
    $this->sendRequestAs('GET', '/test/call-method-getter', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertSame('test', $field_value);
  }

  public function testOnBeforeMethodCallGetterRoleDisallow() {
    $this->sendRequestAs('GET', '/test/call-method-getter', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertNotSame('test', $field_value);
  }

  public function testOnBeforeMethodCallGetterFlagBypassAccessAllow() {
    $this->sendRequestAs('GET', '/test/call-method-getter', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertSame('test', $field_value);
  }

  public function testOnBeforeMethodCallGetterFlagBypassAccessDisallow() {
    $this->sendRequestAs('GET', '/test/call-method-getter', array('repository_manager_service' => $this->load_services['testEntityNoBypassRepositoryManager']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertNotSame('test', $field_value);
  }

  public function testOnBeforeMethodCallGetterFlagHasAccountAllow() {
    $this->sendRequestAs('GET', '/test/call-method-getter', array('repository_manager_service' => $this->load_services['testEntityHasAccountNoInterfaceRepositoryManager']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertSame('test', $field_value);
  }

  public function testOnBeforeMethodCallGetterFlagHasAccountDisallow() {
    $this->sendRequestAs('GET', '/test/call-method-getter', array('repository_manager_service' => $this->load_services['testEntityHasAccountNoInterfaceRepositoryManager']));
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertNotSame('test', $field_value);
  }

  public function testOnBeforeMethodCallGetterFlagIsAuthorAllow() {
    $this->sendRequestAs('GET', '/test/call-method-getter-author', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertSame('test', $field_value);
  }

  public function testOnBeforeMethodCallGetterFlagIsAuthorDisallow() {
    $this->sendRequestAs('GET', '/test/call-method-getter', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertNotSame('test', $field_value);
  }

  /*---onBeforeMethodCall setter---*/

  public function testOnBeforeMethodCallSetterRoleAllow() {
    $this->sendRequestAs('GET', '/test/call-method-setter', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertSame('test', $field_value);
  }

  public function testOnBeforeMethodCallSetterRoleDisallow() {
    $this->sendRequestAs('GET', '/test/call-method-setter', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertNotSame('test', $field_value);
  }

  public function testOnBeforeMethodCallSetterFlagBypassAccessAllow() {
    $this->sendRequestAs('GET', '/test/call-method-setter', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertSame('test', $field_value);
  }

  public function testOnBeforeMethodCallSetterFlagBypassAccessDisallow() {
    $this->sendRequestAs('GET', '/test/call-method-setter', array('repository_manager_service' => $this->load_services['testEntityNoBypassRepositoryManager']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertNotSame('test', $field_value);
  }

  public function testOnBeforeMethodCallSetterFlagHasAccountAllow() {
    $this->sendRequestAs('GET', '/test/call-method-setter', array('repository_manager_service' => $this->load_services['testEntityHasAccountNoInterfaceRepositoryManager']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertSame('test', $field_value);
  }

  public function testOnBeforeMethodCallSetterFlagHasAccountDisallow() {
    $this->sendRequestAs('GET', '/test/call-method-setter', array('repository_manager_service' => $this->load_services['testEntityHasAccountNoInterfaceRepositoryManager']));
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertNotSame('test', $field_value);
  }

  public function testOnBeforeMethodCallSetterFlagIsAuthorAllow() {
    $this->sendRequestAs('GET', '/test/call-method-setter-author', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertSame('test', $field_value);
  }

  public function testOnBeforeMethodCallSetterFlagIsAuthorDisallow() {
    $this->sendRequestAs('GET', '/test/call-method-setter', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertNotSame('test', $field_value);
  }

  /*---onBeforeSave create---*/

  public function testOnBeforeSaveCreateRoleAllow() {
    $this->sendRequestAs('GET', '/test/save-model-create', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $entity_created = $decoder->decode($response->getContent(), 'json');
    $this->assertTrue($entity_created);
  }

  public function testOnBeforeSaveCreateRoleDisallow() {
    $this->sendRequestAs('GET', '/test/save-model-create', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $entity_created = $decoder->decode($response->getContent(), 'json');
    $this->assertFalse($entity_created);
  }

  public function testOnBeforeSaveCreateFlagBypassAccessAllow() {
    $this->sendRequestAs('GET', '/test/save-model-create', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $entity_created = $decoder->decode($response->getContent(), 'json');
    $this->assertTrue($entity_created);
  }

  public function testOnBeforeSaveCreateFlagBypassAccessDisallow() {
    $this->sendRequestAs('GET', '/test/save-model-create', array('repository_manager_service' => $this->load_services['testEntityNoBypassRepositoryManager']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $entity_created = $decoder->decode($response->getContent(), 'json');
    $this->assertFalse($entity_created);
  }

  public function testOnBeforeSaveCreateFlagHasAccountAllow() {
    $this->sendRequestAs('GET', '/test/save-model-create', array('repository_manager_service' => $this->load_services['testEntityHasAccountNoInterfaceRepositoryManager']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $entity_created = $decoder->decode($response->getContent(), 'json');
    $this->assertTrue($entity_created);
  }

  public function testOnBeforeSaveCreateFlagHasAccountDisallow() {
    $this->sendRequestAs('GET', '/test/save-model-create', array('repository_manager_service' => $this->load_services['testEntityHasAccountNoInterfaceRepositoryManager']));
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $entity_created = $decoder->decode($response->getContent(), 'json');
    $this->assertFalse($entity_created);
  }

  /*---onBeforeSave update---*/

  public function testOnBeforeSaveUpdateRoleAllow() {
    $this->sendRequestAs('GET', '/test/save-model-update', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $entity_updated = $decoder->decode($response->getContent(), 'json');
    $this->assertTrue($entity_updated);
  }

  public function testOnBeforeSaveUpdateRoleDisallow() {
    $this->sendRequestAs('GET', '/test/save-model-update', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $entity_updated = $decoder->decode($response->getContent(), 'json');
    $this->assertFalse($entity_updated);
  }

  public function testOnBeforeSaveUpdateFlagBypassAccessAllow() {
    $this->sendRequestAs('GET', '/test/save-model-update', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $entity_updated = $decoder->decode($response->getContent(), 'json');
    $this->assertTrue($entity_updated);
  }

  public function testOnBeforeSaveUpdateFlagBypassAccessDisallow() {
    $this->sendRequestAs('GET', '/test/save-model-update', array('repository_manager_service' => $this->load_services['testEntityNoBypassRepositoryManager']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $entity_updated = $decoder->decode($response->getContent(), 'json');
    $this->assertFalse($entity_updated);
  }

  public function testOnBeforeSaveUpdateFlagHasAccountAllow() {
    $this->sendRequestAs('GET', '/test/save-model-update', array('repository_manager_service' => $this->load_services['testEntityHasAccountNoInterfaceRepositoryManager']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $entity_updated = $decoder->decode($response->getContent(), 'json');
    $this->assertTrue($entity_updated);
  }

  public function testOnBeforeSaveUpdateFlagHasAccountDisallow() {
    $this->sendRequestAs('GET', '/test/save-model-update', array('repository_manager_service' => $this->load_services['testEntityHasAccountNoInterfaceRepositoryManager']));
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $entity_updated = $decoder->decode($response->getContent(), 'json');
    $this->assertFalse($entity_updated);
  }

  public function testOnBeforeSaveUpdateFlagIsAuthorAllow() {
    $this->sendRequestAs('GET', '/test/save-model-update-author', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $entity_updated = $decoder->decode($response->getContent(), 'json');
    $this->assertTrue($entity_updated);
  }

  public function testOnBeforeSaveUpdateFlagIsAuthorDisallow() {
    $this->sendRequestAs('GET', '/test/save-model-update', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']));
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $entity_updated = $decoder->decode($response->getContent(), 'json');
    $this->assertFalse($entity_updated);
  }

  /*---onBeforeDelete---*/

  public function testOnBeforeDeleteRoleAllow() {

  }

  public function testOnBeforeDeleteRoleDisallow() {

  }

  public function testOnBeforeDeleteFlagBypassAccessAllow() {

  }

  public function testOnBeforeDeleteFlagBypassAccessDisallow() {

  }

  public function testOnBeforeDeleteFlagHasAccountAllow() {

  }

  public function testOnBeforeDeleteFlagHasAccountDisallow() {

  }

  public function testOnBeforeDeleteFlagIsAuthorAllow() {

  }

  public function testOnBeforeDeleteFlagIsAuthorDisallow() {

  }

  /*------------Entity tests------------*/

  /*----Create----*/

//   public function testCreateEntityAllowRole() {
//     $this->sendRequestAs('GET', '/test/create-entity', static::$admin_user);
//     $entities = $this->testEntityOperations->findTestEntities();
//     $this->assertEquals(1, count($entities));
//   }
//
//   public function testCreateEntityDisallow() {
//     $this->sendRequestAs('GET', '/test/create-entity', static::$authenticated_user);
//     $entities = $this->testEntityOperations->findTestEntities();
//     $this->assertEquals(0, count($entities));
//   }
//
//   public function testCreateEntityBypass() {
//     $this->sendRequestAs('GET', '/test/create-entity', static::$superadmin_user);
//     $entities = $this->testEntityOperations->findTestEntities();
//     $this->assertEquals(1, count($entities));
//   }

  /*----Read----*/

//   public function testReadEntitiesAllowRole() {
//     $this->testEntityOperations->createTestModel(static::$admin_user);
//     $this->sendRequestAs('GET', '/test/count-entities', static::$admin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(1, $entities_count);
//   }
//
//   public function testReadEntitiesAllowAuthor() {
//     $this->testEntityOperations->createTestModel(static::$authenticated_user);
//     $this->sendRequestAs('GET', '/test/count-entities', static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(1, $entities_count);
//   }
//
//   public function testReadEntitiesDisallow() {
//     $this->testEntityOperations->createTestModel(static::$admin_user);
//     $entities_count = $this->sendRequestAs('GET', '/test/count-entities', static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(0, $entities_count);
//     //Kolla att entiteten fortfarande finns i databasen
//     $entities = $this->testEntityOperations->findTestEntities();
//     $this->assertEquals(1, count($entities));
//   }
//
//   public function testReadEntitiesNoBypass() {
//     $this->testEntityOperations->createTestModel(static::$admin_user);
//     $this->sendRequestAs('GET', '/test/count-entities', static::$superadmin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(0, $entities_count);
//     //Kolla att entiteten fortfarande finns i databasen
//     $entities = $this->testEntityOperations->findTestEntities();
//     $this->assertEquals(1, count($entities));
//   }
//
//   public function testReadEntitiesLazyLoadAllowRole() {
//     $this->testEntityOperations->createTestModel(static::$admin_user);
//     $this->sendRequestAs('GET', '/test/count-entities-lazy', static::$admin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(1, $entities_count);
//   }
//
//   public function testReadEntitiesLazyLoadAllowAuthor() {
//     $this->testEntityOperations->createTestModel(static::$authenticated_user);
//     $this->sendRequestAs('GET', '/test/count-entities-lazy', static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(1, $entities_count);
//   }
//
//   public function testReadEntitiesLazyLoadDisallow() {
//     $this->testEntityOperations->createTestModel(static::$admin_user);
//     $entities_count = $this->sendRequestAs('GET', '/test/count-entities-lazy', static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(0, $entities_count);
//     //Kolla att entiteten fortfarande finns i databasen
//     $entities = $this->testEntityOperations->findTestEntities();
//     $this->assertEquals(1, count($entities));
//   }
//
//   public function testReadEntitiesLazyLoadNoBypass() {
//     $this->testEntityOperations->createTestModel(static::$admin_user);
//     $this->sendRequestAs('GET', '/test/count-entities-lazy', static::$superadmin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(0, $entities_count);
//     //Kolla att entiteten fortfarande finns i databasen
//     $entities = $this->testEntityOperations->findTestEntities();
//     $this->assertEquals(1, count($entities));
//   }

//   /*----Read field-----*/
//   public function testReadField1AllowRole() {
//     $this->testEntityOperations->createTestModel(static::$admin_user);
//     $this->sendRequestAs('GET', '/test/read-field-1', static::$admin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(1, $entities_count);
//   }

//   /*----Update field----*/
//   public function testUpdateEntityAllowRole() {
//     $this->testEntityOperations->createTestModel(static::$admin_user);
//     $this->sendRequestAs('GET', '/test/count-entities', static::$admin_user);
//   }
}
