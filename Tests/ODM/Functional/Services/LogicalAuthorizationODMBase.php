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
  protected $testDocumentRoleAuthorRepositoryDecorator;
  protected $testDocumentHasAccountNoInterfaceRepositoryDecorator;
  protected $testDocumentNoBypassRepositoryDecorator;
  protected $testDocumentOverriddenPermissionsRepositoryDecorator;
  protected $testDocumentVariousPermissionsRepositoryDecorator;
  protected $testUserRepositoryDecorator;
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

    $this->load_services['testUserRepositoryDecorator'] = 'repository_decorator.test_user';
    $this->load_services['testModelOperations'] = 'test_model_operations';
    $container = $kernel->getContainer();
    foreach($this->load_services as $property_name => $service_name) {
      $this->$property_name = $container->get($service_name);
    }

    $this->deleteAll(array(
      $this->testDocumentRoleAuthorRepositoryDecorator,
      $this->testDocumentHasAccountNoInterfaceRepositoryDecorator,
      $this->testDocumentNoBypassRepositoryDecorator,
      $this->testDocumentOverriddenPermissionsRepositoryDecorator,
      $this->testDocumentVariousPermissionsRepositoryDecorator,
    ));

    $this->addUsers();
  }

  /**
   * This method is run after each public test method
   *
   * It is important to reset all non-static properties to minimize memory leaks.
   */
  protected function tearDown() {
    if(!is_null($this->testDocumentRoleAuthorRepositoryDecorator)) {
      $this->testDocumentRoleAuthorRepositoryDecorator->getObjectManager()->getConnection()->close();
      $this->testDocumentRoleAuthorRepositoryDecorator = null;
    }
    if(!is_null($this->testDocumentHasAccountNoInterfaceRepositoryDecorator)) {
      $this->testDocumentHasAccountNoInterfaceRepositoryDecorator->getObjectManager()->getConnection()->close();
      $this->testDocumentHasAccountNoInterfaceRepositoryDecorator = null;
    }
    if(!is_null($this->testDocumentNoBypassRepositoryDecorator)) {
      $this->testDocumentNoBypassRepositoryDecorator->getObjectManager()->getConnection()->close();
      $this->testDocumentNoBypassRepositoryDecorator = null;
    }
    if(!is_null($this->testDocumentOverriddenPermissionsRepositoryDecorator)) {
      $this->testDocumentOverriddenPermissionsRepositoryDecorator->getObjectManager()->getConnection()->close();
      $this->testDocumentOverriddenPermissionsRepositoryDecorator = null;
    }
    if(!is_null($this->testDocumentVariousPermissionsRepositoryDecorator)) {
      $this->testDocumentVariousPermissionsRepositoryDecorator->getObjectManager()->getConnection()->close();
      $this->testDocumentVariousPermissionsRepositoryDecorator = null;
    }
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
    //Create new nodmal user
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

  /*------------RepositoryDecorator event tests------------*/

  /*---onUnknownResult---*/

  public function testOnUnknownResultRoleAllow() {
    $this->testModelOperations->setRepositoryDecorator($this->testDocumentRoleAuthorRepositoryDecorator);
    $this->testModelOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/count-unknown-result', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(1, $documents_count);
  }

  public function testOnUnknownResultRoleDisallow() {
    $this->testModelOperations->setRepositoryDecorator($this->testDocumentRoleAuthorRepositoryDecorator);
    $this->testModelOperations->createTestModel();
    $documents_count = $this->sendRequestAs('GET', '/test/count-unknown-result', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(0, $documents_count);
    //Kolla att entiteten fortfarande finns i databasen
    $documents = $this->testModelOperations->getUnknownResult();
    $this->assertEquals(1, count($documents));
  }

  public function testOnUnknownResultFlagBypassAccessAllow() {
    $this->testModelOperations->setRepositoryDecorator($this->testDocumentRoleAuthorRepositoryDecorator);
    $this->testModelOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/count-unknown-result', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(1, $documents_count);
  }

  public function testOnUnknownResultFlagBypassAccessDisallow() {
    $this->testModelOperations->setRepositoryDecorator($this->testDocumentNoBypassRepositoryDecorator);
    $this->testModelOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/count-unknown-result', array('repository_decorator_service' => $this->load_services['testDocumentNoBypassRepositoryDecorator']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(0, $documents_count);
    //Kolla att entiteten fortfarande finns i databasen
    $documents = $this->testModelOperations->getUnknownResult();
    $this->assertEquals(1, count($documents));
  }

  public function testOnUnknownResultFlagHasAccountAllow() {
    $this->testModelOperations->setRepositoryDecorator($this->testDocumentHasAccountNoInterfaceRepositoryDecorator);
    $this->testModelOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/count-unknown-result', array('repository_decorator_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(1, $documents_count);
  }

  public function testOnUnknownResultFlagHasAccountDisallow() {
    $this->testModelOperations->setRepositoryDecorator($this->testDocumentHasAccountNoInterfaceRepositoryDecorator);
    $this->testModelOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/count-unknown-result', array('repository_decorator_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryDecorator']));
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(0, $documents_count);
    //Kolla att entiteten fortfarande finns i databasen
    $documents = $this->testModelOperations->getUnknownResult();
    $this->assertEquals(1, count($documents));
  }

  public function testOnUnknownResultFlagIsAuthorAllow() {
    $this->testModelOperations->setRepositoryDecorator($this->testDocumentRoleAuthorRepositoryDecorator);
    $this->testModelOperations->createTestModel(static::$authenticated_user);
    $this->sendRequestAs('GET', '/test/count-unknown-result', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(1, $documents_count);
  }

  public function testOnUnknownResultFlagIsAuthorDisallow() {
    $this->testModelOperations->setRepositoryDecorator($this->testDocumentRoleAuthorRepositoryDecorator);
    $this->testModelOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/count-unknown-result', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(0, $documents_count);
    //Kolla att entiteten fortfarande finns i databasen
    $documents = $this->testModelOperations->getUnknownResult();
    $this->assertEquals(1, count($documents));
  }

  /*---onSingleModelResult---*/

  public function testOnSingleModelResultRoleAllow() {
    $this->testModelOperations->setRepositoryDecorator($this->testDocumentRoleAuthorRepositoryDecorator);
    $modelDecorator = $this->testModelOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/find-single-model-result/' . $modelDecorator->getId(), array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $document_found = $decoder->decode($response->getContent(), 'json');
    $this->assertTrue($document_found);
  }

  public function testOnSingleModelResultRoleDisallow() {
    $this->testModelOperations->setRepositoryDecorator($this->testDocumentRoleAuthorRepositoryDecorator);
    $modelDecorator = $this->testModelOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/find-single-model-result/' . $modelDecorator->getId(), array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $document_found = $decoder->decode($response->getContent(), 'json');
    $this->assertFalse($document_found);
    //Kolla att entiteten fortfarande finns i databasen
    $this->assertTrue((bool) $this->testModelOperations->getSingleModelResult($modelDecorator->getId()));
  }

  public function testOnSingleModelResultFlagBypassAccessAllow() {
    $this->testModelOperations->setRepositoryDecorator($this->testDocumentRoleAuthorRepositoryDecorator);
    $modelDecorator = $this->testModelOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/find-single-model-result/' . $modelDecorator->getId(), array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $document_found = $decoder->decode($response->getContent(), 'json');
    $this->assertTrue($document_found);
  }

  public function testOnSingleModelResultFlagBypassAccessDisallow() {
    $this->testModelOperations->setRepositoryDecorator($this->testDocumentNoBypassRepositoryDecorator);
    $modelDecorator = $this->testModelOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/find-single-model-result/' . $modelDecorator->getId(), array('repository_decorator_service' => $this->load_services['testDocumentNoBypassRepositoryDecorator']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $document_found = $decoder->decode($response->getContent(), 'json');
    $this->assertFalse($document_found);
    //Kolla att entiteten fortfarande finns i databasen
    $this->assertTrue((bool) $this->testModelOperations->getSingleModelResult($modelDecorator->getId()));
  }

  public function testOnSingleModelResultFlagHasAccountAllow() {
    $this->testModelOperations->setRepositoryDecorator($this->testDocumentHasAccountNoInterfaceRepositoryDecorator);
    $modelDecorator = $this->testModelOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/find-single-model-result/' . $modelDecorator->getId(), array('repository_decorator_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $document_found = $decoder->decode($response->getContent(), 'json');
    $this->assertTrue($document_found);
  }

  public function testOnSingleModelResultFlagHasAccountDisallow() {
    $this->testModelOperations->setRepositoryDecorator($this->testDocumentHasAccountNoInterfaceRepositoryDecorator);
    $modelDecorator = $this->testModelOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/find-single-model-result/' . $modelDecorator->getId(), array('repository_decorator_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryDecorator']));
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $document_found = $decoder->decode($response->getContent(), 'json');
    $this->assertFalse($document_found);
    //Kolla att entiteten fortfarande finns i databasen
    $this->assertTrue((bool) $this->testModelOperations->getSingleModelResult($modelDecorator->getId()));
  }

  public function testOnSingleModelResultFlagIsAuthorAllow() {
    $this->testModelOperations->setRepositoryDecorator($this->testDocumentRoleAuthorRepositoryDecorator);
    $modelDecorator = $this->testModelOperations->createTestModel(static::$authenticated_user);
    $this->sendRequestAs('GET', '/test/find-single-model-result/' . $modelDecorator->getId(), array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $document_found = $decoder->decode($response->getContent(), 'json');
    $this->assertTrue($document_found);
  }

  public function testOnSingleModelResultFlagIsAuthorDisallow() {
    $this->testModelOperations->setRepositoryDecorator($this->testDocumentRoleAuthorRepositoryDecorator);
    $modelDecorator = $this->testModelOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/find-single-model-result/' . $modelDecorator->getId(), array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $document_found = $decoder->decode($response->getContent(), 'json');
    $this->assertFalse($document_found);
    //Kolla att entiteten fortfarande finns i databasen
    $this->assertTrue((bool) $this->testModelOperations->getSingleModelResult($modelDecorator->getId()));
  }

  /*---onMultipleModelResult---*/

  public function testOnMultipleModelResultRoleAllow() {
    $this->testModelOperations->setRepositoryDecorator($this->testDocumentRoleAuthorRepositoryDecorator);
    $this->testModelOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/count-multiple-model-result', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(1, $documents_count);
  }

  public function testOnMultipleModelResultRoleDisallow() {
    $this->testModelOperations->setRepositoryDecorator($this->testDocumentRoleAuthorRepositoryDecorator);
    $this->testModelOperations->createTestModel();
    $documents_count = $this->sendRequestAs('GET', '/test/count-multiple-model-result', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(0, $documents_count);
    //Kolla att entiteten fortfarande finns i databasen
    $documents = $this->testModelOperations->getMultipleModelResult();
    $this->assertEquals(1, count($documents));
  }

  public function testOnMultipleModelResultFlagBypassAccessAllow() {
    $this->testModelOperations->setRepositoryDecorator($this->testDocumentRoleAuthorRepositoryDecorator);
    $this->testModelOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/count-multiple-model-result', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(1, $documents_count);
  }

  public function testOnMultipleModelResultFlagBypassAccessDisallow() {
    $this->testModelOperations->setRepositoryDecorator($this->testDocumentNoBypassRepositoryDecorator);
    $this->testModelOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/count-multiple-model-result', array('repository_decorator_service' => $this->load_services['testDocumentNoBypassRepositoryDecorator']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(0, $documents_count);
    //Kolla att entiteten fortfarande finns i databasen
    $documents = $this->testModelOperations->getMultipleModelResult();
    $this->assertEquals(1, count($documents));
  }

  public function testOnMultipleModelResultFlagHasAccountAllow() {
    $this->testModelOperations->setRepositoryDecorator($this->testDocumentHasAccountNoInterfaceRepositoryDecorator);
    $this->testModelOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/count-multiple-model-result', array('repository_decorator_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(1, $documents_count);
  }

  public function testOnMultipleModelResultFlagHasAccountDisallow() {
    $this->testModelOperations->setRepositoryDecorator($this->testDocumentHasAccountNoInterfaceRepositoryDecorator);
    $this->testModelOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/count-multiple-model-result', array('repository_decorator_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryDecorator']));
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(0, $documents_count);
    //Kolla att entiteten fortfarande finns i databasen
    $documents = $this->testModelOperations->getMultipleModelResult();
    $this->assertEquals(1, count($documents));
  }

  public function testOnMultipleModelResultFlagIsAuthorAllow() {
    $this->testModelOperations->setRepositoryDecorator($this->testDocumentRoleAuthorRepositoryDecorator);
    $this->testModelOperations->createTestModel(static::$authenticated_user);
    $this->sendRequestAs('GET', '/test/count-multiple-model-result', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(1, $documents_count);
  }

  public function testOnMultipleModelResultFlagIsAuthorDisallow() {
    $this->testModelOperations->setRepositoryDecorator($this->testDocumentRoleAuthorRepositoryDecorator);
    $this->testModelOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/count-multiple-model-result', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(0, $documents_count);
    //Kolla att entiteten fortfarande finns i databasen
    $documents = $this->testModelOperations->getMultipleModelResult();
    $this->assertEquals(1, count($documents));
  }

  /*---onBeforeCreate---*/

  public function testOnBeforeCreateRoleAllow() {
    $this->sendRequestAs('GET', '/test/create-document', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $document_created = $decoder->decode($response->getContent(), 'json');
    $this->assertTrue($document_created);
  }

  public function testOnBeforeCreateRoleDisallow() {
    $this->sendRequestAs('GET', '/test/create-document', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $document_created = $decoder->decode($response->getContent(), 'json');
    $this->assertFalse($document_created);
  }

  public function testOnBeforeCreateFlagBypassAccessAllow() {
    $this->sendRequestAs('GET', '/test/create-document', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $document_created = $decoder->decode($response->getContent(), 'json');
    $this->assertTrue($document_created);
  }

  public function testOnBeforeCreateFlagBypassAccessDisallow() {
    $this->sendRequestAs('GET', '/test/create-document', array('repository_decorator_service' => $this->load_services['testDocumentNoBypassRepositoryDecorator']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $document_created = $decoder->decode($response->getContent(), 'json');
    $this->assertFalse($document_created);
  }

  public function testOnBeforeCreateFlagHasAccountAllow() {
    $this->sendRequestAs('GET', '/test/create-document', array('repository_decorator_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $document_created = $decoder->decode($response->getContent(), 'json');
    $this->assertTrue($document_created);
  }

  public function testOnBeforeCreateFlagHasAccountDisallow() {
    $this->sendRequestAs('GET', '/test/create-document', array('repository_decorator_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryDecorator']));
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $document_created = $decoder->decode($response->getContent(), 'json');
    $this->assertFalse($document_created);
  }

  /*---onLazyModelCollectionResult---*/

  public function testOnLazyModelCollectionResultRoleAllow() {
    $this->testModelOperations->setRepositoryDecorator($this->testDocumentRoleAuthorRepositoryDecorator);
    $this->testModelOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/count-documents-lazy', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(1, $documents_count);
  }

  public function testOnLazyModelCollectionResultRoleDisallow() {
    $this->testModelOperations->setRepositoryDecorator($this->testDocumentRoleAuthorRepositoryDecorator);
    $this->testModelOperations->createTestModel();
    $documents_count = $this->sendRequestAs('GET', '/test/count-documents-lazy', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(0, $documents_count);
    //Kolla att entiteten fortfarande finns i databasen
    $documents = $this->testModelOperations->getLazyLoadedModelResult();
    $this->assertEquals(1, count($documents));
  }

  public function testOnLazyModelCollectionResultFlagBypassAccessAllow() {
    $this->testModelOperations->setRepositoryDecorator($this->testDocumentRoleAuthorRepositoryDecorator);
    $this->testModelOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/count-documents-lazy', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(1, $documents_count);
  }

  public function testOnLazyModelCollectionResultFlagBypassAccessDisallow() {
    $this->testModelOperations->setRepositoryDecorator($this->testDocumentNoBypassRepositoryDecorator);
    $this->testModelOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/count-documents-lazy', array('repository_decorator_service' => $this->load_services['testDocumentNoBypassRepositoryDecorator']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(0, $documents_count);
    //Kolla att entiteten fortfarande finns i databasen
    $documents = $this->testModelOperations->getLazyLoadedModelResult();
    $this->assertEquals(1, count($documents));
  }

  public function testOnLazyModelCollectionResultFlagHasAccountAllow() {
    $this->testModelOperations->setRepositoryDecorator($this->testDocumentHasAccountNoInterfaceRepositoryDecorator);
    $this->testModelOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/count-documents-lazy', array('repository_decorator_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(1, $documents_count);
  }

  public function testOnLazyModelCollectionResultFlagHasAccountDisallow() {
    $this->testModelOperations->setRepositoryDecorator($this->testDocumentHasAccountNoInterfaceRepositoryDecorator);
    $this->testModelOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/count-documents-lazy', array('repository_decorator_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryDecorator']));
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(0, $documents_count);
    //Kolla att entiteten fortfarande finns i databasen
    $documents = $this->testModelOperations->getLazyLoadedModelResult();
    $this->assertEquals(1, count($documents));
  }

  public function testOnLazyModelCollectionResultFlagIsAuthorAllow() {
    $this->testModelOperations->setRepositoryDecorator($this->testDocumentRoleAuthorRepositoryDecorator);
    $this->testModelOperations->createTestModel(static::$authenticated_user);
    $this->sendRequestAs('GET', '/test/count-documents-lazy', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(1, $documents_count);
  }

  public function testOnLazyModelCollectionResultFlagIsAuthorDisallow() {
    $this->testModelOperations->setRepositoryDecorator($this->testDocumentRoleAuthorRepositoryDecorator);
    $this->testModelOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/count-documents-lazy', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(0, $documents_count);
    //Kolla att entiteten fortfarande finns i databasen
    $documents = $this->testModelOperations->getLazyLoadedModelResult();
    $this->assertEquals(1, count($documents));
  }

  /*----------ModelDecorator event tests------------*/

  /*---onBeforeMethodCall getter---*/

  public function testOnBeforeMethodCallGetterRoleAllow() {
    $this->sendRequestAs('GET', '/test/call-method-getter', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertSame('test', $field_value);
  }

  public function testOnBeforeMethodCallGetterRoleDisallow() {
    $this->sendRequestAs('GET', '/test/call-method-getter', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertNotSame('test', $field_value);
  }

  public function testOnBeforeMethodCallGetterFlagBypassAccessAllow() {
    $this->sendRequestAs('GET', '/test/call-method-getter', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertSame('test', $field_value);
  }

  public function testOnBeforeMethodCallGetterFlagBypassAccessDisallow() {
    $this->sendRequestAs('GET', '/test/call-method-getter', array('repository_decorator_service' => $this->load_services['testDocumentNoBypassRepositoryDecorator']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertNotSame('test', $field_value);
  }

  public function testOnBeforeMethodCallGetterFlagHasAccountAllow() {
    $this->sendRequestAs('GET', '/test/call-method-getter', array('repository_decorator_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertSame('test', $field_value);
  }

  public function testOnBeforeMethodCallGetterFlagHasAccountDisallow() {
    $this->sendRequestAs('GET', '/test/call-method-getter', array('repository_decorator_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryDecorator']));
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertNotSame('test', $field_value);
  }

  public function testOnBeforeMethodCallGetterFlagIsAuthorAllow() {
    $this->sendRequestAs('GET', '/test/call-method-getter-author', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertSame('test', $field_value);
  }

  public function testOnBeforeMethodCallGetterFlagIsAuthorDisallow() {
    $this->sendRequestAs('GET', '/test/call-method-getter', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertNotSame('test', $field_value);
  }

  /*---onBeforeMethodCall setter---*/

  public function testOnBeforeMethodCallSetterRoleAllow() {
    $this->sendRequestAs('GET', '/test/call-method-setter', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertSame('test', $field_value);
  }

  public function testOnBeforeMethodCallSetterRoleDisallow() {
    $this->sendRequestAs('GET', '/test/call-method-setter', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertNotSame('test', $field_value);
  }

  public function testOnBeforeMethodCallSetterFlagBypassAccessAllow() {
    $this->sendRequestAs('GET', '/test/call-method-setter', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertSame('test', $field_value);
  }

  public function testOnBeforeMethodCallSetterFlagBypassAccessDisallow() {
    $this->sendRequestAs('GET', '/test/call-method-setter', array('repository_decorator_service' => $this->load_services['testDocumentNoBypassRepositoryDecorator']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertNotSame('test', $field_value);
  }

  public function testOnBeforeMethodCallSetterFlagHasAccountAllow() {
    $this->sendRequestAs('GET', '/test/call-method-setter', array('repository_decorator_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertSame('test', $field_value);
  }

  public function testOnBeforeMethodCallSetterFlagHasAccountDisallow() {
    $this->sendRequestAs('GET', '/test/call-method-setter', array('repository_decorator_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryDecorator']));
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertNotSame('test', $field_value);
  }

  public function testOnBeforeMethodCallSetterFlagIsAuthorAllow() {
    $this->sendRequestAs('GET', '/test/call-method-setter-author', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertSame('test', $field_value);
  }

  public function testOnBeforeMethodCallSetterFlagIsAuthorDisallow() {
    $this->sendRequestAs('GET', '/test/call-method-setter', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertNotSame('test', $field_value);
  }

  /*---onBeforeSave create---*/

  public function testOnBeforeSaveCreateRoleAllow() {
    $this->sendRequestAs('GET', '/test/save-model-create', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(1, $documents_count);
  }

  public function testOnBeforeSaveCreateRoleDisallow() {
    $this->sendRequestAs('GET', '/test/save-model-create', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(0, $documents_count);
  }

  public function testOnBeforeSaveCreateFlagBypassAccessAllow() {
    $this->sendRequestAs('GET', '/test/save-model-create', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(1, $documents_count);
  }

  public function testOnBeforeSaveCreateFlagBypassAccessDisallow() {
    $this->sendRequestAs('GET', '/test/save-model-create', array('repository_decorator_service' => $this->load_services['testDocumentNoBypassRepositoryDecorator']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(0, $documents_count);
  }

  public function testOnBeforeSaveCreateFlagHasAccountAllow() {
    $this->sendRequestAs('GET', '/test/save-model-create', array('repository_decorator_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(1, $documents_count);
  }

  public function testOnBeforeSaveCreateFlagHasAccountDisallow() {
    $this->sendRequestAs('GET', '/test/save-model-create', array('repository_decorator_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryDecorator']));
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(0, $documents_count);
  }

  /*---onBeforeSave update---*/

  public function testOnBeforeSaveUpdateRoleAllow() {
    $this->sendRequestAs('GET', '/test/save-model-update', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertSame('test', $field_value);
  }

  public function testOnBeforeSaveUpdateRoleDisallow() {
    $this->sendRequestAs('GET', '/test/save-model-update', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertNotSame('test', $field_value);
  }

  public function testOnBeforeSaveUpdateFlagBypassAccessAllow() {
    $this->sendRequestAs('GET', '/test/save-model-update', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertSame('test', $field_value);
  }

  public function testOnBeforeSaveUpdateFlagBypassAccessDisallow() {
    $this->sendRequestAs('GET', '/test/save-model-update', array('repository_decorator_service' => $this->load_services['testDocumentNoBypassRepositoryDecorator']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertNotSame('test', $field_value);
  }

  public function testOnBeforeSaveUpdateFlagHasAccountAllow() {
    $this->sendRequestAs('GET', '/test/save-model-update', array('repository_decorator_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertSame('test', $field_value);
  }

  public function testOnBeforeSaveUpdateFlagHasAccountDisallow() {
    $this->sendRequestAs('GET', '/test/save-model-update', array('repository_decorator_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryDecorator']));
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertNotSame('test', $field_value);
  }

  public function testOnBeforeSaveUpdateFlagIsAuthorAllow() {
    $this->sendRequestAs('GET', '/test/save-model-update-author', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertSame('test', $field_value);
  }

  public function testOnBeforeSaveUpdateFlagIsAuthorDisallow() {
    $this->sendRequestAs('GET', '/test/save-model-update', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']));
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $field_value = $response->getContent();
    $this->assertNotSame('test', $field_value);
  }

  /*---onBeforeDelete---*/

  public function testOnBeforeDeleteRoleAllow() {
    $this->sendRequestAs('GET', '/test/delete-model', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(0, $documents_count);
  }

  public function testOnBeforeDeleteRoleDisallow() {
    $this->sendRequestAs('GET', '/test/delete-model', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(1, $documents_count);
  }

  public function testOnBeforeDeleteFlagBypassAccessAllow() {
    $this->sendRequestAs('GET', '/test/delete-model', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(0, $documents_count);
  }

  public function testOnBeforeDeleteFlagBypassAccessDisallow() {
    $this->sendRequestAs('GET', '/test/delete-model', array('repository_decorator_service' => $this->load_services['testDocumentNoBypassRepositoryDecorator']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(1, $documents_count);
  }

  public function testOnBeforeDeleteFlagHasAccountAllow() {
    $this->sendRequestAs('GET', '/test/delete-model', array('repository_decorator_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(0, $documents_count);
  }

  public function testOnBeforeDeleteFlagHasAccountDisallow() {
    $this->sendRequestAs('GET', '/test/delete-model', array('repository_decorator_service' => $this->load_services['testDocumentHasAccountNoInterfaceRepositoryDecorator']));
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(1, $documents_count);
  }

  public function testOnBeforeDeleteFlagIsAuthorAllow() {
    $this->sendRequestAs('GET', '/test/delete-model-author', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(0, $documents_count);
  }

  public function testOnBeforeDeleteFlagIsAuthorDisallow() {
    $this->sendRequestAs('GET', '/test/delete-model', array('repository_decorator_service' => $this->load_services['testDocumentRoleAuthorRepositoryDecorator']));
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $documents_count = $response->getContent();
    $this->assertEquals(1, $documents_count);
  }

  public function testPermissionsOverride() {
    $this->testModelOperations->setRepositoryDecorator($this->testDocumentOverriddenPermissionsRepositoryDecorator);
    $this->testModelOperations->createTestModel();
    $this->sendRequestAs('GET', '/test/count-unknown-result', array('repository_decorator_service' => $this->load_services['testDocumentOverriddenPermissionsRepositoryDecorator']), static::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $entities_count = $response->getContent();
    $this->assertEquals(1, $entities_count);
  }

  public function testAvailableActionsAnonymous() {
    $this->sendRequestAs('GET', '/test/get-available-actions', array('repository_decorator_service' => $this->load_services['testDocumentVariousPermissionsRepositoryDecorator']));
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $actions = json_decode($response->getContent(), true);
    $expected_actions = [];
    $this->assertSame($expected_actions, $actions);
  }

  public function testAvailableActionsAuthenticated() {
    $this->sendRequestAs('GET', '/test/get-available-actions', array('repository_decorator_service' => $this->load_services['testDocumentVariousPermissionsRepositoryDecorator']), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $actions = json_decode($response->getContent(), true);
    $expected_actions = [
      'read' => true,
      'fields' => [
        'id' => [
          'get' => true,
        ],
        'field1' => [
          'get' => true,
        ],
        'field3' => [
          'get' => true,
        ],
        'author' => [
          'get' => true,
        ],
      ],
    ];
    $this->assertSame($expected_actions, $actions);
  }

  public function testAvailableActionsAdmin() {
    $this->sendRequestAs('GET', '/test/get-available-actions', array('repository_decorator_service' => $this->load_services['testDocumentVariousPermissionsRepositoryDecorator']), static::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $actions = json_decode($response->getContent(), true);
    $expected_actions = [
      'read' => true,
      'update' => true,
      'fields' => [
        'id' => [
          'get' => true,
        ],
        'field1' => [
          'get' => true,
          'set' => true,
        ],
        'field2' => [
          'set' => true,
        ],
        'field3' => [
          'get' => true,
          'set' => true,
        ],
        'author' => [
          'get' => true,
        ],
      ],
    ];
    $this->assertSame($expected_actions, $actions);
  }

  public function testAvailableActionsSuperadmin() {
    $this->sendRequestAs('GET', '/test/get-available-actions', array('repository_decorator_service' => $this->load_services['testDocumentVariousPermissionsRepositoryDecorator']), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $actions = json_decode($response->getContent(), true);
    $expected_actions = [
      'create' => true,
      'read' => true,
      'update' => true,
      'fields' => [
        'id' => [
          'get' => true,
        ],
        'field1' => [
          'get' => true,
          'set' => true,
        ],
        'field2' => [
          'get' => true,
          'set' => true,
        ],
        'field3' => [
          'get' => true,
          'set' => true,
        ],
        'author' => [
          'get' => true,
          'set' => true,
        ],
      ],
    ];
    $this->assertSame($expected_actions, $actions);
  }
}