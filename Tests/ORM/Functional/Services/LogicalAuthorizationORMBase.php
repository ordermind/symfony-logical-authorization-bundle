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
  protected $testEntityOverriddenPermissionsRepositoryManager;
  protected $testEntityVariousPermissionsRepositoryManager;
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
      $this->testEntityRoleAuthorRepositoryManager,
      $this->testEntityHasAccountNoInterfaceRepositoryManager,
      $this->testEntityNoBypassRepositoryManager,
      $this->testEntityOverriddenPermissionsRepositoryManager,
      $this->testEntityVariousPermissionsRepositoryManager,
    ));

    $this->addUsers();
  }

  /**
   * This method is run after each public test method
   *
   * It is important to reset all non-static properties to minimize memory leaks.
   */
  protected function tearDown() {
    if(!is_null($this->testEntityRoleAuthorRepositoryManager)) {
      $this->testEntityRoleAuthorRepositoryManager->getObjectManager()->getConnection()->close();
      $this->testEntityRoleAuthorRepositoryManager = null;
    }
    if(!is_null($this->testEntityHasAccountNoInterfaceRepositoryManager)) {
      $this->testEntityHasAccountNoInterfaceRepositoryManager->getObjectManager()->getConnection()->close();
      $this->testEntityHasAccountNoInterfaceRepositoryManager = null;
    }
    if(!is_null($this->testEntityNoBypassRepositoryManager)) {
      $this->testEntityNoBypassRepositoryManager->getObjectManager()->getConnection()->close();
      $this->testEntityNoBypassRepositoryManager = null;
    }
    if(!is_null($this->testEntityOverriddenPermissionsRepositoryManager)) {
      $this->testEntityOverriddenPermissionsRepositoryManager->getObjectManager()->getConnection()->close();
      $this->testEntityOverriddenPermissionsRepositoryManager = null;
    }
    if(!is_null($this->testEntityVariousPermissionsRepositoryManager)) {
      $this->testEntityVariousPermissionsRepositoryManager->getObjectManager()->getConnection()->close();
      $this->testEntityVariousPermissionsRepositoryManager = null;
    }
    if(!is_null($this->testUserRepositoryManager)) {
      $this->testUserRepositoryManager->getObjectManager()->getConnection()->close();
      $this->testUserRepositoryManager = null;
    }
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

  /*------------RepositoryManager event tests------------*/

  /*---onUnknownResult---*/

//   public function testOnUnknownResultRoleAllow() {
//     $this->testModelOperations->setRepositoryManager($this->testEntityRoleAuthorRepositoryManager);
//     $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/count-unknown-result', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$admin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(1, $entities_count);
//   }
//
//   public function testOnUnknownResultRoleDisallow() {
//     $this->testModelOperations->setRepositoryManager($this->testEntityRoleAuthorRepositoryManager);
//     $this->testModelOperations->createTestModel();
//     $entities_count = $this->sendRequestAs('GET', '/test/count-unknown-result', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(0, $entities_count);
//     //Kolla att entiteten fortfarande finns i databasen
//     $entities = $this->testModelOperations->getUnknownResult();
//     $this->assertEquals(1, count($entities));
//   }
//
//   public function testOnUnknownResultFlagBypassAccessAllow() {
//     $this->testModelOperations->setRepositoryManager($this->testEntityRoleAuthorRepositoryManager);
//     $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/count-unknown-result', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$superadmin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(1, $entities_count);
//   }
//
//   public function testOnUnknownResultFlagBypassAccessDisallow() {
//     $this->testModelOperations->setRepositoryManager($this->testEntityNoBypassRepositoryManager);
//     $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/count-unknown-result', array('repository_manager_service' => $this->load_services['testEntityNoBypassRepositoryManager']), static::$superadmin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(0, $entities_count);
//     //Kolla att entiteten fortfarande finns i databasen
//     $entities = $this->testModelOperations->getUnknownResult();
//     $this->assertEquals(1, count($entities));
//   }
//
//   public function testOnUnknownResultFlagHasAccountAllow() {
//     $this->testModelOperations->setRepositoryManager($this->testEntityHasAccountNoInterfaceRepositoryManager);
//     $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/count-unknown-result', array('repository_manager_service' => $this->load_services['testEntityHasAccountNoInterfaceRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(1, $entities_count);
//   }
//
//   public function testOnUnknownResultFlagHasAccountDisallow() {
//     $this->testModelOperations->setRepositoryManager($this->testEntityHasAccountNoInterfaceRepositoryManager);
//     $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/count-unknown-result', array('repository_manager_service' => $this->load_services['testEntityHasAccountNoInterfaceRepositoryManager']));
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(0, $entities_count);
//     //Kolla att entiteten fortfarande finns i databasen
//     $entities = $this->testModelOperations->getUnknownResult();
//     $this->assertEquals(1, count($entities));
//   }
//
//   public function testOnUnknownResultFlagIsAuthorAllow() {
//     $this->testModelOperations->setRepositoryManager($this->testEntityRoleAuthorRepositoryManager);
//     $this->testModelOperations->createTestModel(static::$authenticated_user);
//     $this->sendRequestAs('GET', '/test/count-unknown-result', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(1, $entities_count);
//   }
//
//   public function testOnUnknownResultFlagIsAuthorDisallow() {
//     $this->testModelOperations->setRepositoryManager($this->testEntityRoleAuthorRepositoryManager);
//     $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/count-unknown-result', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(0, $entities_count);
//     //Kolla att entiteten fortfarande finns i databasen
//     $entities = $this->testModelOperations->getUnknownResult();
//     $this->assertEquals(1, count($entities));
//   }
//
//   /*---onSingleModelResult---*/
//
//   public function testOnSingleModelResultRoleAllow() {
//     $this->testModelOperations->setRepositoryManager($this->testEntityRoleAuthorRepositoryManager);
//     $modelManager = $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/find-single-model-result/' . $modelManager->getId(), array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$admin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $decoder = new JsonDecode();
//     $entity_found = $decoder->decode($response->getContent(), 'json');
//     $this->assertTrue($entity_found);
//   }
//
//   public function testOnSingleModelResultRoleDisallow() {
//     $this->testModelOperations->setRepositoryManager($this->testEntityRoleAuthorRepositoryManager);
//     $modelManager = $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/find-single-model-result/' . $modelManager->getId(), array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $decoder = new JsonDecode();
//     $entity_found = $decoder->decode($response->getContent(), 'json');
//     $this->assertFalse($entity_found);
//     //Kolla att entiteten fortfarande finns i databasen
//     $this->assertTrue((bool) $this->testModelOperations->getSingleModelResult($modelManager->getId()));
//   }
//
//   public function testOnSingleModelResultFlagBypassAccessAllow() {
//     $this->testModelOperations->setRepositoryManager($this->testEntityRoleAuthorRepositoryManager);
//     $modelManager = $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/find-single-model-result/' . $modelManager->getId(), array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$superadmin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $decoder = new JsonDecode();
//     $entity_found = $decoder->decode($response->getContent(), 'json');
//     $this->assertTrue($entity_found);
//   }
//
//   public function testOnSingleModelResultFlagBypassAccessDisallow() {
//     $this->testModelOperations->setRepositoryManager($this->testEntityNoBypassRepositoryManager);
//     $modelManager = $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/find-single-model-result/' . $modelManager->getId(), array('repository_manager_service' => $this->load_services['testEntityNoBypassRepositoryManager']), static::$superadmin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $decoder = new JsonDecode();
//     $entity_found = $decoder->decode($response->getContent(), 'json');
//     $this->assertFalse($entity_found);
//     //Kolla att entiteten fortfarande finns i databasen
//     $this->assertTrue((bool) $this->testModelOperations->getSingleModelResult($modelManager->getId()));
//   }
//
//   public function testOnSingleModelResultFlagHasAccountAllow() {
//     $this->testModelOperations->setRepositoryManager($this->testEntityHasAccountNoInterfaceRepositoryManager);
//     $modelManager = $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/find-single-model-result/' . $modelManager->getId(), array('repository_manager_service' => $this->load_services['testEntityHasAccountNoInterfaceRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $decoder = new JsonDecode();
//     $entity_found = $decoder->decode($response->getContent(), 'json');
//     $this->assertTrue($entity_found);
//   }
//
//   public function testOnSingleModelResultFlagHasAccountDisallow() {
//     $this->testModelOperations->setRepositoryManager($this->testEntityHasAccountNoInterfaceRepositoryManager);
//     $modelManager = $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/find-single-model-result/' . $modelManager->getId(), array('repository_manager_service' => $this->load_services['testEntityHasAccountNoInterfaceRepositoryManager']));
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $decoder = new JsonDecode();
//     $entity_found = $decoder->decode($response->getContent(), 'json');
//     $this->assertFalse($entity_found);
//     //Kolla att entiteten fortfarande finns i databasen
//     $this->assertTrue((bool) $this->testModelOperations->getSingleModelResult($modelManager->getId()));
//   }
//
//   public function testOnSingleModelResultFlagIsAuthorAllow() {
//     $this->testModelOperations->setRepositoryManager($this->testEntityRoleAuthorRepositoryManager);
//     $modelManager = $this->testModelOperations->createTestModel(static::$authenticated_user);
//     $this->sendRequestAs('GET', '/test/find-single-model-result/' . $modelManager->getId(), array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $decoder = new JsonDecode();
//     $entity_found = $decoder->decode($response->getContent(), 'json');
//     $this->assertTrue($entity_found);
//   }
//
//   public function testOnSingleModelResultFlagIsAuthorDisallow() {
//     $this->testModelOperations->setRepositoryManager($this->testEntityRoleAuthorRepositoryManager);
//     $modelManager = $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/find-single-model-result/' . $modelManager->getId(), array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $decoder = new JsonDecode();
//     $entity_found = $decoder->decode($response->getContent(), 'json');
//     $this->assertFalse($entity_found);
//     //Kolla att entiteten fortfarande finns i databasen
//     $this->assertTrue((bool) $this->testModelOperations->getSingleModelResult($modelManager->getId()));
//   }
//
//   /*---onMultipleModelResult---*/
//
//   public function testOnMultipleModelResultRoleAllow() {
//     $this->testModelOperations->setRepositoryManager($this->testEntityRoleAuthorRepositoryManager);
//     $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/count-multiple-model-result', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$admin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(1, $entities_count);
//   }
//
//   public function testOnMultipleModelResultRoleDisallow() {
//     $this->testModelOperations->setRepositoryManager($this->testEntityRoleAuthorRepositoryManager);
//     $this->testModelOperations->createTestModel();
//     $entities_count = $this->sendRequestAs('GET', '/test/count-multiple-model-result', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(0, $entities_count);
//     //Kolla att entiteten fortfarande finns i databasen
//     $entities = $this->testModelOperations->getMultipleModelResult();
//     $this->assertEquals(1, count($entities));
//   }
//
//   public function testOnMultipleModelResultFlagBypassAccessAllow() {
//     $this->testModelOperations->setRepositoryManager($this->testEntityRoleAuthorRepositoryManager);
//     $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/count-multiple-model-result', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$superadmin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(1, $entities_count);
//   }
//
//   public function testOnMultipleModelResultFlagBypassAccessDisallow() {
//     $this->testModelOperations->setRepositoryManager($this->testEntityNoBypassRepositoryManager);
//     $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/count-multiple-model-result', array('repository_manager_service' => $this->load_services['testEntityNoBypassRepositoryManager']), static::$superadmin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(0, $entities_count);
//     //Kolla att entiteten fortfarande finns i databasen
//     $entities = $this->testModelOperations->getMultipleModelResult();
//     $this->assertEquals(1, count($entities));
//   }
//
//   public function testOnMultipleModelResultFlagHasAccountAllow() {
//     $this->testModelOperations->setRepositoryManager($this->testEntityHasAccountNoInterfaceRepositoryManager);
//     $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/count-multiple-model-result', array('repository_manager_service' => $this->load_services['testEntityHasAccountNoInterfaceRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(1, $entities_count);
//   }
//
//   public function testOnMultipleModelResultFlagHasAccountDisallow() {
//     $this->testModelOperations->setRepositoryManager($this->testEntityHasAccountNoInterfaceRepositoryManager);
//     $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/count-multiple-model-result', array('repository_manager_service' => $this->load_services['testEntityHasAccountNoInterfaceRepositoryManager']));
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(0, $entities_count);
//     //Kolla att entiteten fortfarande finns i databasen
//     $entities = $this->testModelOperations->getMultipleModelResult();
//     $this->assertEquals(1, count($entities));
//   }
//
//   public function testOnMultipleModelResultFlagIsAuthorAllow() {
//     $this->testModelOperations->setRepositoryManager($this->testEntityRoleAuthorRepositoryManager);
//     $this->testModelOperations->createTestModel(static::$authenticated_user);
//     $this->sendRequestAs('GET', '/test/count-multiple-model-result', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(1, $entities_count);
//   }
//
//   public function testOnMultipleModelResultFlagIsAuthorDisallow() {
//     $this->testModelOperations->setRepositoryManager($this->testEntityRoleAuthorRepositoryManager);
//     $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/count-multiple-model-result', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(0, $entities_count);
//     //Kolla att entiteten fortfarande finns i databasen
//     $entities = $this->testModelOperations->getMultipleModelResult();
//     $this->assertEquals(1, count($entities));
//   }
//
//   /*---onBeforeCreate---*/
//
//   public function testOnBeforeCreateRoleAllow() {
//     $this->sendRequestAs('GET', '/test/create-entity', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$admin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $decoder = new JsonDecode();
//     $entity_created = $decoder->decode($response->getContent(), 'json');
//     $this->assertTrue($entity_created);
//   }
//
//   public function testOnBeforeCreateRoleDisallow() {
//     $this->sendRequestAs('GET', '/test/create-entity', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $decoder = new JsonDecode();
//     $entity_created = $decoder->decode($response->getContent(), 'json');
//     $this->assertFalse($entity_created);
//   }
//
//   public function testOnBeforeCreateFlagBypassAccessAllow() {
//     $this->sendRequestAs('GET', '/test/create-entity', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$superadmin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $decoder = new JsonDecode();
//     $entity_created = $decoder->decode($response->getContent(), 'json');
//     $this->assertTrue($entity_created);
//   }
//
//   public function testOnBeforeCreateFlagBypassAccessDisallow() {
//     $this->sendRequestAs('GET', '/test/create-entity', array('repository_manager_service' => $this->load_services['testEntityNoBypassRepositoryManager']), static::$superadmin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $decoder = new JsonDecode();
//     $entity_created = $decoder->decode($response->getContent(), 'json');
//     $this->assertFalse($entity_created);
//   }
//
//   public function testOnBeforeCreateFlagHasAccountAllow() {
//     $this->sendRequestAs('GET', '/test/create-entity', array('repository_manager_service' => $this->load_services['testEntityHasAccountNoInterfaceRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $decoder = new JsonDecode();
//     $entity_created = $decoder->decode($response->getContent(), 'json');
//     $this->assertTrue($entity_created);
//   }
//
//   public function testOnBeforeCreateFlagHasAccountDisallow() {
//     $this->sendRequestAs('GET', '/test/create-entity', array('repository_manager_service' => $this->load_services['testEntityHasAccountNoInterfaceRepositoryManager']));
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $decoder = new JsonDecode();
//     $entity_created = $decoder->decode($response->getContent(), 'json');
//     $this->assertFalse($entity_created);
//   }
//
//   /*---onLazyModelCollectionResult---*/
//
//   public function testOnLazyModelCollectionResultRoleAllow() {
//     $this->testModelOperations->setRepositoryManager($this->testEntityRoleAuthorRepositoryManager);
//     $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/count-entities-lazy', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$admin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(1, $entities_count);
//   }
//
//   public function testOnLazyModelCollectionResultRoleDisallow() {
//     $this->testModelOperations->setRepositoryManager($this->testEntityRoleAuthorRepositoryManager);
//     $this->testModelOperations->createTestModel();
//     $entities_count = $this->sendRequestAs('GET', '/test/count-entities-lazy', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(0, $entities_count);
//     //Kolla att entiteten fortfarande finns i databasen
//     $entities = $this->testModelOperations->getLazyLoadedModelResult();
//     $this->assertEquals(1, count($entities));
//   }
//
//   public function testOnLazyModelCollectionResultFlagBypassAccessAllow() {
//     $this->testModelOperations->setRepositoryManager($this->testEntityRoleAuthorRepositoryManager);
//     $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/count-entities-lazy', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$superadmin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(1, $entities_count);
//   }
//
//   public function testOnLazyModelCollectionResultFlagBypassAccessDisallow() {
//     $this->testModelOperations->setRepositoryManager($this->testEntityNoBypassRepositoryManager);
//     $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/count-entities-lazy', array('repository_manager_service' => $this->load_services['testEntityNoBypassRepositoryManager']), static::$superadmin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(0, $entities_count);
//     //Kolla att entiteten fortfarande finns i databasen
//     $entities = $this->testModelOperations->getLazyLoadedModelResult();
//     $this->assertEquals(1, count($entities));
//   }
//
//   public function testOnLazyModelCollectionResultFlagHasAccountAllow() {
//     $this->testModelOperations->setRepositoryManager($this->testEntityHasAccountNoInterfaceRepositoryManager);
//     $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/count-entities-lazy', array('repository_manager_service' => $this->load_services['testEntityHasAccountNoInterfaceRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(1, $entities_count);
//   }
//
//   public function testOnLazyModelCollectionResultFlagHasAccountDisallow() {
//     $this->testModelOperations->setRepositoryManager($this->testEntityHasAccountNoInterfaceRepositoryManager);
//     $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/count-entities-lazy', array('repository_manager_service' => $this->load_services['testEntityHasAccountNoInterfaceRepositoryManager']));
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(0, $entities_count);
//     //Kolla att entiteten fortfarande finns i databasen
//     $entities = $this->testModelOperations->getLazyLoadedModelResult();
//     $this->assertEquals(1, count($entities));
//   }
//
//   public function testOnLazyModelCollectionResultFlagIsAuthorAllow() {
//     $this->testModelOperations->setRepositoryManager($this->testEntityRoleAuthorRepositoryManager);
//     $this->testModelOperations->createTestModel(static::$authenticated_user);
//     $this->sendRequestAs('GET', '/test/count-entities-lazy', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(1, $entities_count);
//   }
//
//   public function testOnLazyModelCollectionResultFlagIsAuthorDisallow() {
//     $this->testModelOperations->setRepositoryManager($this->testEntityRoleAuthorRepositoryManager);
//     $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/count-entities-lazy', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(0, $entities_count);
//     //Kolla att entiteten fortfarande finns i databasen
//     $entities = $this->testModelOperations->getLazyLoadedModelResult();
//     $this->assertEquals(1, count($entities));
//   }
//
//   /*----------ModelManager event tests------------*/
//
//   /*---onBeforeMethodCall getter---*/
//
//   public function testOnBeforeMethodCallGetterRoleAllow() {
//     $this->sendRequestAs('GET', '/test/call-method-getter', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$admin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $field_value = $response->getContent();
//     $this->assertSame('test', $field_value);
//   }
//
//   public function testOnBeforeMethodCallGetterRoleDisallow() {
//     $this->sendRequestAs('GET', '/test/call-method-getter', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $field_value = $response->getContent();
//     $this->assertNotSame('test', $field_value);
//   }
//
//   public function testOnBeforeMethodCallGetterFlagBypassAccessAllow() {
//     $this->sendRequestAs('GET', '/test/call-method-getter', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$superadmin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $field_value = $response->getContent();
//     $this->assertSame('test', $field_value);
//   }
//
//   public function testOnBeforeMethodCallGetterFlagBypassAccessDisallow() {
//     $this->sendRequestAs('GET', '/test/call-method-getter', array('repository_manager_service' => $this->load_services['testEntityNoBypassRepositoryManager']), static::$superadmin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $field_value = $response->getContent();
//     $this->assertNotSame('test', $field_value);
//   }
//
//   public function testOnBeforeMethodCallGetterFlagHasAccountAllow() {
//     $this->sendRequestAs('GET', '/test/call-method-getter', array('repository_manager_service' => $this->load_services['testEntityHasAccountNoInterfaceRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $field_value = $response->getContent();
//     $this->assertSame('test', $field_value);
//   }
//
//   public function testOnBeforeMethodCallGetterFlagHasAccountDisallow() {
//     $this->sendRequestAs('GET', '/test/call-method-getter', array('repository_manager_service' => $this->load_services['testEntityHasAccountNoInterfaceRepositoryManager']));
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $field_value = $response->getContent();
//     $this->assertNotSame('test', $field_value);
//   }
//
//   public function testOnBeforeMethodCallGetterFlagIsAuthorAllow() {
//     $this->sendRequestAs('GET', '/test/call-method-getter-author', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $field_value = $response->getContent();
//     $this->assertSame('test', $field_value);
//   }
//
//   public function testOnBeforeMethodCallGetterFlagIsAuthorDisallow() {
//     $this->sendRequestAs('GET', '/test/call-method-getter', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $field_value = $response->getContent();
//     $this->assertNotSame('test', $field_value);
//   }
//
//   /*---onBeforeMethodCall setter---*/
//
//   public function testOnBeforeMethodCallSetterRoleAllow() {
//     $this->sendRequestAs('GET', '/test/call-method-setter', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$admin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $field_value = $response->getContent();
//     $this->assertSame('test', $field_value);
//   }
//
//   public function testOnBeforeMethodCallSetterRoleDisallow() {
//     $this->sendRequestAs('GET', '/test/call-method-setter', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $field_value = $response->getContent();
//     $this->assertNotSame('test', $field_value);
//   }
//
//   public function testOnBeforeMethodCallSetterFlagBypassAccessAllow() {
//     $this->sendRequestAs('GET', '/test/call-method-setter', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$superadmin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $field_value = $response->getContent();
//     $this->assertSame('test', $field_value);
//   }
//
//   public function testOnBeforeMethodCallSetterFlagBypassAccessDisallow() {
//     $this->sendRequestAs('GET', '/test/call-method-setter', array('repository_manager_service' => $this->load_services['testEntityNoBypassRepositoryManager']), static::$superadmin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $field_value = $response->getContent();
//     $this->assertNotSame('test', $field_value);
//   }
//
//   public function testOnBeforeMethodCallSetterFlagHasAccountAllow() {
//     $this->sendRequestAs('GET', '/test/call-method-setter', array('repository_manager_service' => $this->load_services['testEntityHasAccountNoInterfaceRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $field_value = $response->getContent();
//     $this->assertSame('test', $field_value);
//   }
//
//   public function testOnBeforeMethodCallSetterFlagHasAccountDisallow() {
//     $this->sendRequestAs('GET', '/test/call-method-setter', array('repository_manager_service' => $this->load_services['testEntityHasAccountNoInterfaceRepositoryManager']));
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $field_value = $response->getContent();
//     $this->assertNotSame('test', $field_value);
//   }
//
//   public function testOnBeforeMethodCallSetterFlagIsAuthorAllow() {
//     $this->sendRequestAs('GET', '/test/call-method-setter-author', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $field_value = $response->getContent();
//     $this->assertSame('test', $field_value);
//   }
//
//   public function testOnBeforeMethodCallSetterFlagIsAuthorDisallow() {
//     $this->sendRequestAs('GET', '/test/call-method-setter', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $field_value = $response->getContent();
//     $this->assertNotSame('test', $field_value);
//   }
//
//   /*---onBeforeSave create---*/
//
//   public function testOnBeforeSaveCreateRoleAllow() {
//     $this->sendRequestAs('GET', '/test/save-model-create', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$admin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(1, $entities_count);
//   }
//
//   public function testOnBeforeSaveCreateRoleDisallow() {
//     $this->sendRequestAs('GET', '/test/save-model-create', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(0, $entities_count);
//   }
//
//   public function testOnBeforeSaveCreateFlagBypassAccessAllow() {
//     $this->sendRequestAs('GET', '/test/save-model-create', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$superadmin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(1, $entities_count);
//   }
//
//   public function testOnBeforeSaveCreateFlagBypassAccessDisallow() {
//     $this->sendRequestAs('GET', '/test/save-model-create', array('repository_manager_service' => $this->load_services['testEntityNoBypassRepositoryManager']), static::$superadmin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(0, $entities_count);
//   }
//
//   public function testOnBeforeSaveCreateFlagHasAccountAllow() {
//     $this->sendRequestAs('GET', '/test/save-model-create', array('repository_manager_service' => $this->load_services['testEntityHasAccountNoInterfaceRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(1, $entities_count);
//   }
//
//   public function testOnBeforeSaveCreateFlagHasAccountDisallow() {
//     $this->sendRequestAs('GET', '/test/save-model-create', array('repository_manager_service' => $this->load_services['testEntityHasAccountNoInterfaceRepositoryManager']));
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(0, $entities_count);
//   }
//
//   /*---onBeforeSave update---*/
//
//   public function testOnBeforeSaveUpdateRoleAllow() {
//     $this->sendRequestAs('GET', '/test/save-model-update', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$admin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $field_value = $response->getContent();
//     $this->assertSame('test', $field_value);
//   }
//
//   public function testOnBeforeSaveUpdateRoleDisallow() {
//     $this->sendRequestAs('GET', '/test/save-model-update', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $field_value = $response->getContent();
//     $this->assertNotSame('test', $field_value);
//   }
//
//   public function testOnBeforeSaveUpdateFlagBypassAccessAllow() {
//     $this->sendRequestAs('GET', '/test/save-model-update', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$superadmin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $field_value = $response->getContent();
//     $this->assertSame('test', $field_value);
//   }
//
//   public function testOnBeforeSaveUpdateFlagBypassAccessDisallow() {
//     $this->sendRequestAs('GET', '/test/save-model-update', array('repository_manager_service' => $this->load_services['testEntityNoBypassRepositoryManager']), static::$superadmin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $field_value = $response->getContent();
//     $this->assertNotSame('test', $field_value);
//   }
//
//   public function testOnBeforeSaveUpdateFlagHasAccountAllow() {
//     $this->sendRequestAs('GET', '/test/save-model-update', array('repository_manager_service' => $this->load_services['testEntityHasAccountNoInterfaceRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $field_value = $response->getContent();
//     $this->assertSame('test', $field_value);
//   }
//
//   public function testOnBeforeSaveUpdateFlagHasAccountDisallow() {
//     $this->sendRequestAs('GET', '/test/save-model-update', array('repository_manager_service' => $this->load_services['testEntityHasAccountNoInterfaceRepositoryManager']));
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $field_value = $response->getContent();
//     $this->assertNotSame('test', $field_value);
//   }
//
//   public function testOnBeforeSaveUpdateFlagIsAuthorAllow() {
//     $this->sendRequestAs('GET', '/test/save-model-update-author', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $field_value = $response->getContent();
//     $this->assertSame('test', $field_value);
//   }
//
//   public function testOnBeforeSaveUpdateFlagIsAuthorDisallow() {
//     $this->sendRequestAs('GET', '/test/save-model-update', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']));
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $field_value = $response->getContent();
//     $this->assertNotSame('test', $field_value);
//   }
//
//   /*---onBeforeDelete---*/
//
//   public function testOnBeforeDeleteRoleAllow() {
//     $this->sendRequestAs('GET', '/test/delete-model', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$admin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(0, $entities_count);
//   }
//
//   public function testOnBeforeDeleteRoleDisallow() {
//     $this->sendRequestAs('GET', '/test/delete-model', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(1, $entities_count);
//   }
//
//   public function testOnBeforeDeleteFlagBypassAccessAllow() {
//     $this->sendRequestAs('GET', '/test/delete-model', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$superadmin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(0, $entities_count);
//   }
//
//   public function testOnBeforeDeleteFlagBypassAccessDisallow() {
//     $this->sendRequestAs('GET', '/test/delete-model', array('repository_manager_service' => $this->load_services['testEntityNoBypassRepositoryManager']), static::$superadmin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(1, $entities_count);
//   }
//
//   public function testOnBeforeDeleteFlagHasAccountAllow() {
//     $this->sendRequestAs('GET', '/test/delete-model', array('repository_manager_service' => $this->load_services['testEntityHasAccountNoInterfaceRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(0, $entities_count);
//   }
//
//   public function testOnBeforeDeleteFlagHasAccountDisallow() {
//     $this->sendRequestAs('GET', '/test/delete-model', array('repository_manager_service' => $this->load_services['testEntityHasAccountNoInterfaceRepositoryManager']));
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(1, $entities_count);
//   }
//
//   public function testOnBeforeDeleteFlagIsAuthorAllow() {
//     $this->sendRequestAs('GET', '/test/delete-model-author', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(0, $entities_count);
//   }
//
//   public function testOnBeforeDeleteFlagIsAuthorDisallow() {
//     $this->sendRequestAs('GET', '/test/delete-model', array('repository_manager_service' => $this->load_services['testEntityRoleAuthorRepositoryManager']));
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(1, $entities_count);
//   }
//
//   public function testPermissionsOverride() {
//     $this->testModelOperations->setRepositoryManager($this->testEntityOverriddenPermissionsRepositoryManager);
//     $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/count-unknown-result', array('repository_manager_service' => $this->load_services['testEntityOverriddenPermissionsRepositoryManager']), static::$admin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(1, $entities_count);
//   }

  public function testAvailableActionsAnonymous() {
    $this->sendRequestAs('GET', '/test/get-available-actions', array('repository_manager_service' => $this->load_services['testEntityVariousPermissionsRepositoryManager']));
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $actions = json_decode($response->getContent(), true);
    print_r($actions);
  }

//   public function testAvailableActionsAuthenticated() {
//     $this->sendRequestAs('GET', '/test/get-available-actions', array('repository_manager_service' => $this->load_services['testEntityVariousPermissionsRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $decoder = new JsonDecode();
//     $actions = $decoder->decode($response->getContent(), 'json');
//     print_r($actions);
//   }
//
//   public function testAvailableActionsAdmin() {
//     $this->sendRequestAs('GET', '/test/get-available-actions', array('repository_manager_service' => $this->load_services['testEntityVariousPermissionsRepositoryManager']), static::$admin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $decoder = new JsonDecode();
//     $actions = $decoder->decode($response->getContent(), 'json');
//     print_r($actions);
//   }
//
//   public function testAvailableActionsSuperadmin() {
//     $this->sendRequestAs('GET', '/test/get-available-actions', array('repository_manager_service' => $this->load_services['testEntityVariousPermissionsRepositoryManager']), static::$superadmin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $decoder = new JsonDecode();
//     $actions = $decoder->decode($response->getContent(), 'json');
//     print_r($actions);
//   }

}