<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\ODM\Functional\Services;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Serializer\Encoder\JsonDecode;

abstract class LogicalAuthorizationODMBase extends WebTestCase {
  protected static $superadmin_user;
  protected static $admin_user;
  protected static $authenticated_user;
  protected $user_credentials = [
    'authenticated_user' => 'userpass',
    'admin_user' => 'adminpass',
    'superadmin_user' => 'superadminpass',
  ];
  protected $load_services = array();
  protected $testDocumentRoleAuthorRepositoryManager;
  protected $testDocumentHasAccountNoInterfaceRepositoryManager;
  protected $testDocumentNoBypassRepositoryManager;
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
      $this->testDocumentRoleAuthorRepositoryManager,
      $this->testDocumentHasAccountNoInterfaceRepositoryManager,
      $this->testDocumentNoBypassRepositoryManager,
    ));

    $this->addUsers();
  }

  /**
   * This method is run after each public test method
   *
   * It is important to reset all non-static properties to minimize memory leaks.
   */
  protected function tearDown() {
    $this->testDocumentRoleAuthorRepositoryManager->getObjectManager()->getConnection()->close();
    $this->testDocumentRoleAuthorRepositoryManager = null;
    $this->testDocumentHasAccountNoInterfaceRepositoryManager->getObjectManager()->getConnection()->close();
    $this->testDocumentHasAccountNoInterfaceRepositoryManager = null;
    $this->testDocumentNoBypassRepositoryManager->getObjectManager()->getConnection()->close();
    $this->testDocumentNoBypassRepositoryManager = null;
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
    //Create new nodmal user
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

  public function testOnUnknownResultRoleAllow() {
    $this->testModelOperations->setRepositoryManager($this->testDocumentRoleAuthorRepositoryManager);
    $this->testModelOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/count-unknown-result', array('repository_manager_service' => $this->load_services['testDocumentRoleAuthorRepositoryManager']), static::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(1, $documents_count);
  }

//   public function testOnUnknownResultRoleDisallow() {
//     $this->testModelOperations->setRepositoryManager($this->testDocumentRoleAuthorRepositoryManager);
//     $this->testModelOperations->createTestModel();
//     $documents_count = $this->sendRequestAs('GET', '/test/count-unknown-result', array('repository_manager_service' => $this->load_services['testDocumentRoleAuthorRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $documents_count = $response->getContent();
//     $this->assertEquals(0, $documents_count);
//     //Kolla att entiteten fortfarande finns i databasen
//     $documents = $this->testModelOperations->getUnknownResult();
//     $this->assertEquals(1, count($documents));
//   }
//
//   public function testOnUnknownResultFlagBypassAccessAllow() {
//     $this->testModelOperations->setRepositoryManager($this->testDocumentRoleAuthorRepositoryManager);
//     $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/count-unknown-result', array('repository_manager_service' => $this->load_services['testDocumentRoleAuthorRepositoryManager']), static::$superadmin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $documents_count = $response->getContent();
//     $this->assertEquals(1, $documents_count);
//   }
//
//   public function testOnUnknownResultFlagBypassAccessDisallow() {
//     $this->testModelOperations->setRepositoryManager($this->testDocumentNoBypassRepositoryManager);
//     $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/count-unknown-result', array('repository_manager_service' => $this->load_services['testDocumentNoBypassRepositoryManager']), static::$superadmin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $documents_count = $response->getContent();
//     $this->assertEquals(0, $documents_count);
//     //Kolla att entiteten fortfarande finns i databasen
//     $documents = $this->testModelOperations->getUnknownResult();
//     $this->assertEquals(1, count($documents));
//   }
//
//   public function testOnUnknownResultFlagHasAccountAllow() {
//     $this->testModelOperations->setRepositoryManager($this->testDocumentHasAccountNoInterfaceRepositoryManager);
//     $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/count-unknown-result', array('repository_manager_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $documents_count = $response->getContent();
//     $this->assertEquals(1, $documents_count);
//   }
//
//   public function testOnUnknownResultFlagHasAccountDisallow() {
//     $this->testModelOperations->setRepositoryManager($this->testDocumentHasAccountNoInterfaceRepositoryManager);
//     $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/count-unknown-result', array('repository_manager_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryManager']));
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $documents_count = $response->getContent();
//     $this->assertEquals(0, $documents_count);
//     //Kolla att entiteten fortfarande finns i databasen
//     $documents = $this->testModelOperations->getUnknownResult();
//     $this->assertEquals(1, count($documents));
//   }
//
//   public function testOnUnknownResultFlagIsAuthorAllow() {
//     $this->testModelOperations->setRepositoryManager($this->testDocumentRoleAuthorRepositoryManager);
//     $this->testModelOperations->createTestModel(static::$authenticated_user);
//     $this->sendRequestAs('GET', '/test/count-unknown-result', array('repository_manager_service' => $this->load_services['testDocumentRoleAuthorRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $documents_count = $response->getContent();
//     $this->assertEquals(1, $documents_count);
//   }
//
//   public function testOnUnknownResultFlagIsAuthorDisallow() {
//     $this->testModelOperations->setRepositoryManager($this->testDocumentRoleAuthorRepositoryManager);
//     $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/count-unknown-result', array('repository_manager_service' => $this->load_services['testDocumentRoleAuthorRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $documents_count = $response->getContent();
//     $this->assertEquals(0, $documents_count);
//     //Kolla att entiteten fortfarande finns i databasen
//     $documents = $this->testModelOperations->getUnknownResult();
//     $this->assertEquals(1, count($documents));
//   }
//
//   /*---onSingleModelResult---*/
//
//   public function testOnSingleModelResultRoleAllow() {
//     $this->testModelOperations->setRepositoryManager($this->testDocumentRoleAuthorRepositoryManager);
//     $modelManager = $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/find-single-model-result/' . $modelManager->getId(), array('repository_manager_service' => $this->load_services['testDocumentRoleAuthorRepositoryManager']), static::$admin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $decoder = new JsonDecode();
//     $document_found = $decoder->decode($response->getContent(), 'json');
//     $this->assertTrue($document_found);
//   }
//
//   public function testOnSingleModelResultRoleDisallow() {
//     $this->testModelOperations->setRepositoryManager($this->testDocumentRoleAuthorRepositoryManager);
//     $modelManager = $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/find-single-model-result/' . $modelManager->getId(), array('repository_manager_service' => $this->load_services['testDocumentRoleAuthorRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $decoder = new JsonDecode();
//     $document_found = $decoder->decode($response->getContent(), 'json');
//     $this->assertFalse($document_found);
//     //Kolla att entiteten fortfarande finns i databasen
//     $this->assertTrue((bool) $this->testModelOperations->getSingleModelResult($modelManager->getId()));
//   }
//
//   public function testOnSingleModelResultFlagBypassAccessAllow() {
//     $this->testModelOperations->setRepositoryManager($this->testDocumentRoleAuthorRepositoryManager);
//     $modelManager = $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/find-single-model-result/' . $modelManager->getId(), array('repository_manager_service' => $this->load_services['testDocumentRoleAuthorRepositoryManager']), static::$superadmin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $decoder = new JsonDecode();
//     $document_found = $decoder->decode($response->getContent(), 'json');
//     $this->assertTrue($document_found);
//   }
//
//   public function testOnSingleModelResultFlagBypassAccessDisallow() {
//     $this->testModelOperations->setRepositoryManager($this->testDocumentNoBypassRepositoryManager);
//     $modelManager = $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/find-single-model-result/' . $modelManager->getId(), array('repository_manager_service' => $this->load_services['testDocumentNoBypassRepositoryManager']), static::$superadmin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $decoder = new JsonDecode();
//     $document_found = $decoder->decode($response->getContent(), 'json');
//     $this->assertFalse($document_found);
//     //Kolla att entiteten fortfarande finns i databasen
//     $this->assertTrue((bool) $this->testModelOperations->getSingleModelResult($modelManager->getId()));
//   }
//
//   public function testOnSingleModelResultFlagHasAccountAllow() {
//     $this->testModelOperations->setRepositoryManager($this->testDocumentHasAccountNoInterfaceRepositoryManager);
//     $modelManager = $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/find-single-model-result/' . $modelManager->getId(), array('repository_manager_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $decoder = new JsonDecode();
//     $document_found = $decoder->decode($response->getContent(), 'json');
//     $this->assertTrue($document_found);
//   }
//
//   public function testOnSingleModelResultFlagHasAccountDisallow() {
//     $this->testModelOperations->setRepositoryManager($this->testDocumentHasAccountNoInterfaceRepositoryManager);
//     $modelManager = $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/find-single-model-result/' . $modelManager->getId(), array('repository_manager_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryManager']));
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $decoder = new JsonDecode();
//     $document_found = $decoder->decode($response->getContent(), 'json');
//     $this->assertFalse($document_found);
//     //Kolla att entiteten fortfarande finns i databasen
//     $this->assertTrue((bool) $this->testModelOperations->getSingleModelResult($modelManager->getId()));
//   }
//
//   public function testOnSingleModelResultFlagIsAuthorAllow() {
//     $this->testModelOperations->setRepositoryManager($this->testDocumentRoleAuthorRepositoryManager);
//     $modelManager = $this->testModelOperations->createTestModel(static::$authenticated_user);
//     $this->sendRequestAs('GET', '/test/find-single-model-result/' . $modelManager->getId(), array('repository_manager_service' => $this->load_services['testDocumentRoleAuthorRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $decoder = new JsonDecode();
//     $document_found = $decoder->decode($response->getContent(), 'json');
//     $this->assertTrue($document_found);
//   }
//
//   public function testOnSingleModelResultFlagIsAuthorDisallow() {
//     $this->testModelOperations->setRepositoryManager($this->testDocumentRoleAuthorRepositoryManager);
//     $modelManager = $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/find-single-model-result/' . $modelManager->getId(), array('repository_manager_service' => $this->load_services['testDocumentRoleAuthorRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $decoder = new JsonDecode();
//     $document_found = $decoder->decode($response->getContent(), 'json');
//     $this->assertFalse($document_found);
//     //Kolla att entiteten fortfarande finns i databasen
//     $this->assertTrue((bool) $this->testModelOperations->getSingleModelResult($modelManager->getId()));
//   }
//
//   /*---onMultipleModelResult---*/
//
//   public function testOnMultipleModelResultRoleAllow() {
//     $this->testModelOperations->setRepositoryManager($this->testDocumentRoleAuthorRepositoryManager);
//     $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/count-multiple-model-result', array('repository_manager_service' => $this->load_services['testDocumentRoleAuthorRepositoryManager']), static::$admin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $documents_count = $response->getContent();
//     $this->assertEquals(1, $documents_count);
//   }
//
//   public function testOnMultipleModelResultRoleDisallow() {
//     $this->testModelOperations->setRepositoryManager($this->testDocumentRoleAuthorRepositoryManager);
//     $this->testModelOperations->createTestModel();
//     $documents_count = $this->sendRequestAs('GET', '/test/count-multiple-model-result', array('repository_manager_service' => $this->load_services['testDocumentRoleAuthorRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $documents_count = $response->getContent();
//     $this->assertEquals(0, $documents_count);
//     //Kolla att entiteten fortfarande finns i databasen
//     $documents = $this->testModelOperations->getMultipleModelResult();
//     $this->assertEquals(1, count($documents));
//   }
//
//   public function testOnMultipleModelResultFlagBypassAccessAllow() {
//     $this->testModelOperations->setRepositoryManager($this->testDocumentRoleAuthorRepositoryManager);
//     $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/count-multiple-model-result', array('repository_manager_service' => $this->load_services['testDocumentRoleAuthorRepositoryManager']), static::$superadmin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $documents_count = $response->getContent();
//     $this->assertEquals(1, $documents_count);
//   }
//
//   public function testOnMultipleModelResultFlagBypassAccessDisallow() {
//     $this->testModelOperations->setRepositoryManager($this->testDocumentNoBypassRepositoryManager);
//     $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/count-multiple-model-result', array('repository_manager_service' => $this->load_services['testDocumentNoBypassRepositoryManager']), static::$superadmin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $documents_count = $response->getContent();
//     $this->assertEquals(0, $documents_count);
//     //Kolla att entiteten fortfarande finns i databasen
//     $documents = $this->testModelOperations->getMultipleModelResult();
//     $this->assertEquals(1, count($documents));
//   }
//
//   public function testOnMultipleModelResultFlagHasAccountAllow() {
//     $this->testModelOperations->setRepositoryManager($this->testDocumentHasAccountNoInterfaceRepositoryManager);
//     $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/count-multiple-model-result', array('repository_manager_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $documents_count = $response->getContent();
//     $this->assertEquals(1, $documents_count);
//   }
//
//   public function testOnMultipleModelResultFlagHasAccountDisallow() {
//     $this->testModelOperations->setRepositoryManager($this->testDocumentHasAccountNoInterfaceRepositoryManager);
//     $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/count-multiple-model-result', array('repository_manager_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryManager']));
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $documents_count = $response->getContent();
//     $this->assertEquals(0, $documents_count);
//     //Kolla att entiteten fortfarande finns i databasen
//     $documents = $this->testModelOperations->getMultipleModelResult();
//     $this->assertEquals(1, count($documents));
//   }
//
//   public function testOnMultipleModelResultFlagIsAuthorAllow() {
//     $this->testModelOperations->setRepositoryManager($this->testDocumentRoleAuthorRepositoryManager);
//     $this->testModelOperations->createTestModel(static::$authenticated_user);
//     $this->sendRequestAs('GET', '/test/count-multiple-model-result', array('repository_manager_service' => $this->load_services['testDocumentRoleAuthorRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $documents_count = $response->getContent();
//     $this->assertEquals(1, $documents_count);
//   }
//
//   public function testOnMultipleModelResultFlagIsAuthorDisallow() {
//     $this->testModelOperations->setRepositoryManager($this->testDocumentRoleAuthorRepositoryManager);
//     $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/count-multiple-model-result', array('repository_manager_service' => $this->load_services['testDocumentRoleAuthorRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $documents_count = $response->getContent();
//     $this->assertEquals(0, $documents_count);
//     //Kolla att entiteten fortfarande finns i databasen
//     $documents = $this->testModelOperations->getMultipleModelResult();
//     $this->assertEquals(1, count($documents));
//   }
//
//   /*---onBeforeCreate---*/
//
//   public function testOnBeforeCreateRoleAllow() {
//     $this->sendRequestAs('GET', '/test/create-document', array('repository_manager_service' => $this->load_services['testDocumentRoleAuthorRepositoryManager']), static::$admin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $decoder = new JsonDecode();
//     $document_created = $decoder->decode($response->getContent(), 'json');
//     $this->assertTrue($document_created);
//   }
//
//   public function testOnBeforeCreateRoleDisallow() {
//     $this->sendRequestAs('GET', '/test/create-document', array('repository_manager_service' => $this->load_services['testDocumentRoleAuthorRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $decoder = new JsonDecode();
//     $document_created = $decoder->decode($response->getContent(), 'json');
//     $this->assertFalse($document_created);
//   }
//
//   public function testOnBeforeCreateFlagBypassAccessAllow() {
//     $this->sendRequestAs('GET', '/test/create-document', array('repository_manager_service' => $this->load_services['testDocumentRoleAuthorRepositoryManager']), static::$superadmin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $decoder = new JsonDecode();
//     $document_created = $decoder->decode($response->getContent(), 'json');
//     $this->assertTrue($document_created);
//   }
//
//   public function testOnBeforeCreateFlagBypassAccessDisallow() {
//     $this->sendRequestAs('GET', '/test/create-document', array('repository_manager_service' => $this->load_services['testDocumentNoBypassRepositoryManager']), static::$superadmin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $decoder = new JsonDecode();
//     $document_created = $decoder->decode($response->getContent(), 'json');
//     $this->assertFalse($document_created);
//   }
//
//   public function testOnBeforeCreateFlagHasAccountAllow() {
//     $this->sendRequestAs('GET', '/test/create-document', array('repository_manager_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $decoder = new JsonDecode();
//     $document_created = $decoder->decode($response->getContent(), 'json');
//     $this->assertTrue($document_created);
//   }
//
//   public function testOnBeforeCreateFlagHasAccountDisallow() {
//     $this->sendRequestAs('GET', '/test/create-document', array('repository_manager_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryManager']));
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $decoder = new JsonDecode();
//     $document_created = $decoder->decode($response->getContent(), 'json');
//     $this->assertFalse($document_created);
//   }
//
//   /*---onLazyModelCollectionResult---*/
//
//   public function testOnLazyModelCollectionResultRoleAllow() {
//     $this->testModelOperations->setRepositoryManager($this->testDocumentRoleAuthorRepositoryManager);
//     $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/count-documents-lazy', array('repository_manager_service' => $this->load_services['testDocumentRoleAuthorRepositoryManager']), static::$admin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $documents_count = $response->getContent();
//     $this->assertEquals(1, $documents_count);
//   }
//
//   public function testOnLazyModelCollectionResultRoleDisallow() {
//     $this->testModelOperations->setRepositoryManager($this->testDocumentRoleAuthorRepositoryManager);
//     $this->testModelOperations->createTestModel();
//     $documents_count = $this->sendRequestAs('GET', '/test/count-documents-lazy', array('repository_manager_service' => $this->load_services['testDocumentRoleAuthorRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $documents_count = $response->getContent();
//     $this->assertEquals(0, $documents_count);
//     //Kolla att entiteten fortfarande finns i databasen
//     $documents = $this->testModelOperations->getLazyLoadedModelResult();
//     $this->assertEquals(1, count($documents));
//   }
//
//   public function testOnLazyModelCollectionResultFlagBypassAccessAllow() {
//     $this->testModelOperations->setRepositoryManager($this->testDocumentRoleAuthorRepositoryManager);
//     $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/count-documents-lazy', array('repository_manager_service' => $this->load_services['testDocumentRoleAuthorRepositoryManager']), static::$superadmin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $documents_count = $response->getContent();
//     $this->assertEquals(1, $documents_count);
//   }
//
//   public function testOnLazyModelCollectionResultFlagBypassAccessDisallow() {
//     $this->testModelOperations->setRepositoryManager($this->testDocumentNoBypassRepositoryManager);
//     $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/count-documents-lazy', array('repository_manager_service' => $this->load_services['testDocumentNoBypassRepositoryManager']), static::$superadmin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $documents_count = $response->getContent();
//     $this->assertEquals(0, $documents_count);
//     //Kolla att entiteten fortfarande finns i databasen
//     $documents = $this->testModelOperations->getLazyLoadedModelResult();
//     $this->assertEquals(1, count($documents));
//   }
//
//   public function testOnLazyModelCollectionResultFlagHasAccountAllow() {
//     $this->testModelOperations->setRepositoryManager($this->testDocumentHasAccountNoInterfaceRepositoryManager);
//     $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/count-documents-lazy', array('repository_manager_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $documents_count = $response->getContent();
//     $this->assertEquals(1, $documents_count);
//   }
//
//   public function testOnLazyModelCollectionResultFlagHasAccountDisallow() {
//     $this->testModelOperations->setRepositoryManager($this->testDocumentHasAccountNoInterfaceRepositoryManager);
//     $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/count-documents-lazy', array('repository_manager_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryManager']));
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $documents_count = $response->getContent();
//     $this->assertEquals(0, $documents_count);
//     //Kolla att entiteten fortfarande finns i databasen
//     $documents = $this->testModelOperations->getLazyLoadedModelResult();
//     $this->assertEquals(1, count($documents));
//   }
//
//   public function testOnLazyModelCollectionResultFlagIsAuthorAllow() {
//     $this->testModelOperations->setRepositoryManager($this->testDocumentRoleAuthorRepositoryManager);
//     $this->testModelOperations->createTestModel(static::$authenticated_user);
//     $this->sendRequestAs('GET', '/test/count-documents-lazy', array('repository_manager_service' => $this->load_services['testDocumentRoleAuthorRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $documents_count = $response->getContent();
//     $this->assertEquals(1, $documents_count);
//   }
//
//   public function testOnLazyModelCollectionResultFlagIsAuthorDisallow() {
//     $this->testModelOperations->setRepositoryManager($this->testDocumentRoleAuthorRepositoryManager);
//     $this->testModelOperations->createTestModel();
//     $this->sendRequestAs('GET', '/test/count-documents-lazy', array('repository_manager_service' => $this->load_services['testDocumentRoleAuthorRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $documents_count = $response->getContent();
//     $this->assertEquals(0, $documents_count);
//     //Kolla att entiteten fortfarande finns i databasen
//     $documents = $this->testModelOperations->getLazyLoadedModelResult();
//     $this->assertEquals(1, count($documents));
//   }
//
//   /*----------ModelManager event tests------------*/
//
//   /*---onBeforeMethodCall getter---*/
//
//   public function testOnBeforeMethodCallGetterRoleAllow() {
//     $this->sendRequestAs('GET', '/test/call-method-getter', array('repository_manager_service' => $this->load_services['testDocumentRoleAuthorRepositoryManager']), static::$admin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $field_value = $response->getContent();
//     $this->assertSame('test', $field_value);
//   }
//
//   public function testOnBeforeMethodCallGetterRoleDisallow() {
//     $this->sendRequestAs('GET', '/test/call-method-getter', array('repository_manager_service' => $this->load_services['testDocumentRoleAuthorRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $field_value = $response->getContent();
//     $this->assertNotSame('test', $field_value);
//   }
//
//   public function testOnBeforeMethodCallGetterFlagBypassAccessAllow() {
//     $this->sendRequestAs('GET', '/test/call-method-getter', array('repository_manager_service' => $this->load_services['testDocumentRoleAuthorRepositoryManager']), static::$superadmin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $field_value = $response->getContent();
//     $this->assertSame('test', $field_value);
//   }
//
//   public function testOnBeforeMethodCallGetterFlagBypassAccessDisallow() {
//     $this->sendRequestAs('GET', '/test/call-method-getter', array('repository_manager_service' => $this->load_services['testDocumentNoBypassRepositoryManager']), static::$superadmin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $field_value = $response->getContent();
//     $this->assertNotSame('test', $field_value);
//   }
//
//   public function testOnBeforeMethodCallGetterFlagHasAccountAllow() {
//     $this->sendRequestAs('GET', '/test/call-method-getter', array('repository_manager_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $field_value = $response->getContent();
//     $this->assertSame('test', $field_value);
//   }
//
//   public function testOnBeforeMethodCallGetterFlagHasAccountDisallow() {
//     $this->sendRequestAs('GET', '/test/call-method-getter', array('repository_manager_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryManager']));
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $field_value = $response->getContent();
//     $this->assertNotSame('test', $field_value);
//   }
//
//   public function testOnBeforeMethodCallGetterFlagIsAuthorAllow() {
//     $this->sendRequestAs('GET', '/test/call-method-getter-author', array('repository_manager_service' => $this->load_services['testDocumentRoleAuthorRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $field_value = $response->getContent();
//     $this->assertSame('test', $field_value);
//   }
//
//   public function testOnBeforeMethodCallGetterFlagIsAuthorDisallow() {
//     $this->sendRequestAs('GET', '/test/call-method-getter', array('repository_manager_service' => $this->load_services['testDocumentRoleAuthorRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $field_value = $response->getContent();
//     $this->assertNotSame('test', $field_value);
//   }
//
//   /*---onBeforeMethodCall setter---*/
//
//   public function testOnBeforeMethodCallSetterRoleAllow() {
//     $this->sendRequestAs('GET', '/test/call-method-setter', array('repository_manager_service' => $this->load_services['testDocumentRoleAuthorRepositoryManager']), static::$admin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $field_value = $response->getContent();
//     $this->assertSame('test', $field_value);
//   }
//
//   public function testOnBeforeMethodCallSetterRoleDisallow() {
//     $this->sendRequestAs('GET', '/test/call-method-setter', array('repository_manager_service' => $this->load_services['testDocumentRoleAuthorRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $field_value = $response->getContent();
//     $this->assertNotSame('test', $field_value);
//   }
//
//   public function testOnBeforeMethodCallSetterFlagBypassAccessAllow() {
//     $this->sendRequestAs('GET', '/test/call-method-setter', array('repository_manager_service' => $this->load_services['testDocumentRoleAuthorRepositoryManager']), static::$superadmin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $field_value = $response->getContent();
//     $this->assertSame('test', $field_value);
//   }
//
//   public function testOnBeforeMethodCallSetterFlagBypassAccessDisallow() {
//     $this->sendRequestAs('GET', '/test/call-method-setter', array('repository_manager_service' => $this->load_services['testDocumentNoBypassRepositoryManager']), static::$superadmin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $field_value = $response->getContent();
//     $this->assertNotSame('test', $field_value);
//   }
//
//   public function testOnBeforeMethodCallSetterFlagHasAccountAllow() {
//     $this->sendRequestAs('GET', '/test/call-method-setter', array('repository_manager_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $field_value = $response->getContent();
//     $this->assertSame('test', $field_value);
//   }
//
//   public function testOnBeforeMethodCallSetterFlagHasAccountDisallow() {
//     $this->sendRequestAs('GET', '/test/call-method-setter', array('repository_manager_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryManager']));
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $field_value = $response->getContent();
//     $this->assertNotSame('test', $field_value);
//   }
//
//   public function testOnBeforeMethodCallSetterFlagIsAuthorAllow() {
//     $this->sendRequestAs('GET', '/test/call-method-setter-author', array('repository_manager_service' => $this->load_services['testDocumentRoleAuthorRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $field_value = $response->getContent();
//     $this->assertSame('test', $field_value);
//   }
//
//   public function testOnBeforeMethodCallSetterFlagIsAuthorDisallow() {
//     $this->sendRequestAs('GET', '/test/call-method-setter', array('repository_manager_service' => $this->load_services['testDocumentRoleAuthorRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $field_value = $response->getContent();
//     $this->assertNotSame('test', $field_value);
//   }
//
//   /*---onBeforeSave create---*/
//
//   public function testOnBeforeSaveCreateRoleAllow() {
//     $this->sendRequestAs('GET', '/test/save-model-create', array('repository_manager_service' => $this->load_services['testDocumentRoleAuthorRepositoryManager']), static::$admin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $documents_count = $response->getContent();
//     $this->assertEquals(1, $documents_count);
//   }
//
//   public function testOnBeforeSaveCreateRoleDisallow() {
//     $this->sendRequestAs('GET', '/test/save-model-create', array('repository_manager_service' => $this->load_services['testDocumentRoleAuthorRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $documents_count = $response->getContent();
//     $this->assertEquals(0, $documents_count);
//   }
//
//   public function testOnBeforeSaveCreateFlagBypassAccessAllow() {
//     $this->sendRequestAs('GET', '/test/save-model-create', array('repository_manager_service' => $this->load_services['testDocumentRoleAuthorRepositoryManager']), static::$superadmin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $documents_count = $response->getContent();
//     $this->assertEquals(1, $documents_count);
//   }
//
//   public function testOnBeforeSaveCreateFlagBypassAccessDisallow() {
//     $this->sendRequestAs('GET', '/test/save-model-create', array('repository_manager_service' => $this->load_services['testDocumentNoBypassRepositoryManager']), static::$superadmin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $documents_count = $response->getContent();
//     $this->assertEquals(0, $documents_count);
//   }
//
//   public function testOnBeforeSaveCreateFlagHasAccountAllow() {
//     $this->sendRequestAs('GET', '/test/save-model-create', array('repository_manager_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $documents_count = $response->getContent();
//     $this->assertEquals(1, $documents_count);
//   }
//
//   public function testOnBeforeSaveCreateFlagHasAccountDisallow() {
//     $this->sendRequestAs('GET', '/test/save-model-create', array('repository_manager_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryManager']));
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $documents_count = $response->getContent();
//     $this->assertEquals(0, $documents_count);
//   }
//
//   /*---onBeforeSave update---*/
//
//   public function testOnBeforeSaveUpdateRoleAllow() {
//     $this->sendRequestAs('GET', '/test/save-model-update', array('repository_manager_service' => $this->load_services['testDocumentRoleAuthorRepositoryManager']), static::$admin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $field_value = $response->getContent();
//     $this->assertSame('test', $field_value);
//   }
//
//   public function testOnBeforeSaveUpdateRoleDisallow() {
//     $this->sendRequestAs('GET', '/test/save-model-update', array('repository_manager_service' => $this->load_services['testDocumentRoleAuthorRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $field_value = $response->getContent();
//     $this->assertNotSame('test', $field_value);
//   }
//
//   public function testOnBeforeSaveUpdateFlagBypassAccessAllow() {
//     $this->sendRequestAs('GET', '/test/save-model-update', array('repository_manager_service' => $this->load_services['testDocumentRoleAuthorRepositoryManager']), static::$superadmin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $field_value = $response->getContent();
//     $this->assertSame('test', $field_value);
//   }
//
//   public function testOnBeforeSaveUpdateFlagBypassAccessDisallow() {
//     $this->sendRequestAs('GET', '/test/save-model-update', array('repository_manager_service' => $this->load_services['testDocumentNoBypassRepositoryManager']), static::$superadmin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $field_value = $response->getContent();
//     $this->assertNotSame('test', $field_value);
//   }
//
//   public function testOnBeforeSaveUpdateFlagHasAccountAllow() {
//     $this->sendRequestAs('GET', '/test/save-model-update', array('repository_manager_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $field_value = $response->getContent();
//     $this->assertSame('test', $field_value);
//   }
//
//   public function testOnBeforeSaveUpdateFlagHasAccountDisallow() {
//     $this->sendRequestAs('GET', '/test/save-model-update', array('repository_manager_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryManager']));
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $field_value = $response->getContent();
//     $this->assertNotSame('test', $field_value);
//   }
//
//   public function testOnBeforeSaveUpdateFlagIsAuthorAllow() {
//     $this->sendRequestAs('GET', '/test/save-model-update-author', array('repository_manager_service' => $this->load_services['testDocumentRoleAuthorRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $field_value = $response->getContent();
//     $this->assertSame('test', $field_value);
//   }
//
//   public function testOnBeforeSaveUpdateFlagIsAuthorDisallow() {
//     $this->sendRequestAs('GET', '/test/save-model-update', array('repository_manager_service' => $this->load_services['testDocumentRoleAuthorRepositoryManager']));
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $field_value = $response->getContent();
//     $this->assertNotSame('test', $field_value);
//   }
//
//   /*---onBeforeDelete---*/
//
//   public function testOnBeforeDeleteRoleAllow() {
//     $this->sendRequestAs('GET', '/test/delete-model', array('repository_manager_service' => $this->load_services['testDocumentRoleAuthorRepositoryManager']), static::$admin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $documents_count = $response->getContent();
//     $this->assertEquals(0, $documents_count);
//   }
//
//   public function testOnBeforeDeleteRoleDisallow() {
//     $this->sendRequestAs('GET', '/test/delete-model', array('repository_manager_service' => $this->load_services['testDocumentRoleAuthorRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $documents_count = $response->getContent();
//     $this->assertEquals(1, $documents_count);
//   }
//
//   public function testOnBeforeDeleteFlagBypassAccessAllow() {
//     $this->sendRequestAs('GET', '/test/delete-model', array('repository_manager_service' => $this->load_services['testDocumentRoleAuthorRepositoryManager']), static::$superadmin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $documents_count = $response->getContent();
//     $this->assertEquals(0, $documents_count);
//   }
//
//   public function testOnBeforeDeleteFlagBypassAccessDisallow() {
//     $this->sendRequestAs('GET', '/test/delete-model', array('repository_manager_service' => $this->load_services['testDocumentNoBypassRepositoryManager']), static::$superadmin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $documents_count = $response->getContent();
//     $this->assertEquals(1, $documents_count);
//   }
//
//   public function testOnBeforeDeleteFlagHasAccountAllow() {
//     $this->sendRequestAs('GET', '/test/delete-model', array('repository_manager_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $documents_count = $response->getContent();
//     $this->assertEquals(0, $documents_count);
//   }
//
//   public function testOnBeforeDeleteFlagHasAccountDisallow() {
//     $this->sendRequestAs('GET', '/test/delete-model', array('repository_manager_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryManager']));
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $documents_count = $response->getContent();
//     $this->assertEquals(1, $documents_count);
//   }
//
//   public function testOnBeforeDeleteFlagIsAuthorAllow() {
//     $this->sendRequestAs('GET', '/test/delete-model-author', array('repository_manager_service' => $this->load_services['testDocumentRoleAuthorRepositoryManager']), static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $documents_count = $response->getContent();
//     $this->assertEquals(0, $documents_count);
//   }
//
//   public function testOnBeforeDeleteFlagIsAuthorDisallow() {
//     $this->sendRequestAs('GET', '/test/delete-model', array('repository_manager_service' => $this->load_services['testDocumentRoleAuthorRepositoryManager']));
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $documents_count = $response->getContent();
//     $this->assertEquals(1, $documents_count);
//   }
}