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

  protected function sendRequestAs($method = 'GET', $slug, $user = null) {
    $params = array();
    if($user) {
      $params = array(
        'PHP_AUTH_USER' => $user->getUsername(),
        'PHP_AUTH_PW'   => $this->user_credentials[$user->getUsername()],
      );
    }
    $this->client->request($method, $slug, array(), array(), $params);
  }

  /*------------RepositoryManager event tests------------*/

  /*---onUnknownResult---*/

  public function testOnUnknownResultRoleAllow() {
    $this->testEntityOperations->setRepositoryManager($this->testEntityRoleAuthorRepositoryManager);
    $this->testEntityOperations->createTestEntity();
    $this->sendRequestAs('GET', '/test/count-unknown-result-roleauthor', static::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $entities_count = $response->getContent();
    $this->assertEquals(1, $entities_count);
  }

  public function testOnUnknownResultRoleDisallow() {
    $this->testEntityOperations->setRepositoryManager($this->testEntityRoleAuthorRepositoryManager);
    $this->testEntityOperations->createTestEntity();
    $entities_count = $this->sendRequestAs('GET', '/test/count-unknown-result-roleauthor', static::$authenticated_user);
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
    $this->testEntityOperations->createTestEntity();
    $this->sendRequestAs('GET', '/test/count-unknown-result-roleauthor', static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $entities_count = $response->getContent();
    $this->assertEquals(1, $entities_count);
  }

  public function testOnUnknownResultFlagBypassAccessDisallow() {
    $this->testEntityOperations->setRepositoryManager($this->testEntityNoBypassRepositoryManager);
    $this->testEntityOperations->createTestEntity();
    $this->sendRequestAs('GET', '/test/count-unknown-result-nobypass', static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $entities_count = $response->getContent();
    $this->assertEquals(0, $entities_count);
  }

  public function testOnUnknownResultFlagHasAccountAllow() {
    $this->testEntityOperations->setRepositoryManager($this->testEntityHasAccountNoInterfaceRepositoryManager);
    $this->testEntityOperations->createTestEntity();
    $this->sendRequestAs('GET', '/test/count-unknown-result-hasaccount', static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $entities_count = $response->getContent();
    $this->assertEquals(1, $entities_count);
  }

  public function testOnUnknownResultFlagHasAccountDisallow() {
    $this->testEntityOperations->setRepositoryManager($this->testEntityHasAccountNoInterfaceRepositoryManager);
    $this->testEntityOperations->createTestEntity();
    $this->sendRequestAs('GET', '/test/count-unknown-result-hasaccount');
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
    $this->testEntityOperations->createTestEntity(static::$authenticated_user);
    $this->sendRequestAs('GET', '/test/count-unknown-result-roleauthor', static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $entities_count = $response->getContent();
    $this->assertEquals(1, $entities_count);
  }

  public function testOnUnknownResultFlagIsAuthorDisallow() {
    $this->testEntityOperations->setRepositoryManager($this->testEntityRoleAuthorRepositoryManager);
    $this->testEntityOperations->createTestEntity();
    $this->sendRequestAs('GET', '/test/count-unknown-result-roleauthor', static::$authenticated_user);
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
    $model = $this->testEntityOperations->createTestEntity();
    $this->sendRequestAs('GET', '/test/find-single-model-result-roleauthor/' . $model->getId(), static::$admin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $entity_found = $decoder->decode($response->getContent(), 'json');
    $this->assertTrue($entity_found);
  }

  public function testOnSingleModelResultRoleDisallow() {
    $this->testEntityOperations->setRepositoryManager($this->testEntityRoleAuthorRepositoryManager);
    $model = $this->testEntityOperations->createTestEntity();
    $this->sendRequestAs('GET', '/test/find-single-model-result-roleauthor/' . $model->getId(), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $entity_found = $decoder->decode($response->getContent(), 'json');
    $this->assertFalse($entity_found);
    //Kolla att entiteten fortfarande finns i databasen
    $this->assertTrue((bool) $this->testEntityOperations->getSingleResult($model->getId()));
  }

  public function testOnSingleModelResultFlagBypassAccessAllow() {
    $this->testEntityOperations->setRepositoryManager($this->testEntityRoleAuthorRepositoryManager);
    $model = $this->testEntityOperations->createTestEntity();
    $this->sendRequestAs('GET', '/test/find-single-model-result-roleauthor/' . $model->getId(), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $entity_found = $decoder->decode($response->getContent(), 'json');
    $this->assertTrue($entity_found);
  }

  public function testOnSingleModelResultFlagBypassAccessDisallow() {
    $this->testEntityOperations->setRepositoryManager($this->testEntityNoBypassRepositoryManager);
    $model = $this->testEntityOperations->createTestEntity();
    $this->sendRequestAs('GET', '/test/find-single-model-result-nobypass/' . $model->getId(), static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $entity_found = $decoder->decode($response->getContent(), 'json');
    $this->assertFalse($entity_found);
    //Kolla att entiteten fortfarande finns i databasen
    $this->assertTrue((bool) $this->testEntityOperations->getSingleResult($model->getId()));
  }

  public function testOnSingleModelResultFlagHasAccountAllow() {
    $this->testEntityOperations->setRepositoryManager($this->testEntityHasAccountNoInterfaceRepositoryManager);
    $model = $this->testEntityOperations->createTestEntity();
    $this->sendRequestAs('GET', '/test/find-single-model-result-hasaccount/' . $model->getId(), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $entity_found = $decoder->decode($response->getContent(), 'json');
    $this->assertTrue($entity_found);
  }

  public function testOnSingleModelResultFlagHasAccountDisallow() {
    $this->testEntityOperations->setRepositoryManager($this->testEntityHasAccountNoInterfaceRepositoryManager);
    $model = $this->testEntityOperations->createTestEntity();
    $this->sendRequestAs('GET', '/test/find-single-model-result-hasaccount/' . $model->getId());
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $entity_found = $decoder->decode($response->getContent(), 'json');
    $this->assertFalse($entity_found);
    //Kolla att entiteten fortfarande finns i databasen
    $this->assertTrue((bool) $this->testEntityOperations->getSingleResult($model->getId()));
  }

  public function testOnSingleModelResultFlagIsAuthorAllow() {
    $this->testEntityOperations->setRepositoryManager($this->testEntityRoleAuthorRepositoryManager);
    $model = $this->testEntityOperations->createTestEntity(static::$authenticated_user);
    $this->sendRequestAs('GET', '/test/find-single-model-result-roleauthor/' . $model->getId(), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $entity_found = $decoder->decode($response->getContent(), 'json');
    $this->assertTrue($entity_found);
  }

  public function testOnSingleModelResultFlagIsAuthorDisallow() {
    $this->testEntityOperations->setRepositoryManager($this->testEntityRoleAuthorRepositoryManager);
    $model = $this->testEntityOperations->createTestEntity();
    $this->sendRequestAs('GET', '/test/find-single-model-result-roleauthor/' . $model->getId(), static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $decoder = new JsonDecode();
    $entity_found = $decoder->decode($response->getContent(), 'json');
    $this->assertFalse($entity_found);
    //Kolla att entiteten fortfarande finns i databasen
    $this->assertTrue((bool) $this->testEntityOperations->getSingleResult($model->getId()));
  }

  /*---onMultipleModelResult---*/

  public function testOnMultipleModelResultRoleAllow() {

  }

  public function testOnMultipleModelResultRoleDisallow() {

  }

  public function testOnMultipleModelResultFlagBypassAccessAllow() {

  }

  public function testOnMultipleModelResultFlagBypassAccessDisallow() {

  }

  public function testOnMultipleModelResultFlagHasAccountAllow() {

  }

  public function testOnMultipleModelResultFlagHasAccountDisallow() {

  }

  public function testOnMultipleModelResultFlagIsAuthorAllow() {

  }

  public function testOnMultipleModelResultFlagIsAuthorDisallow() {

  }

  /*---onBeforeCreate---*/

  public function testOnBeforeCreateRoleAllow() {

  }

  public function testOnBeforeCreateRoleDisallow() {

  }

  public function testOnBeforeCreateFlagBypassAccessAllow() {

  }

  public function testOnBeforeCreateFlagBypassAccessDisallow() {

  }

  public function testOnBeforeCreateFlagHasAccountAllow() {

  }

  public function testOnBeforeCreateFlagHasAccountDisallow() {

  }

  public function testOnBeforeCreateFlagIsAuthorAllow() {

  }

  public function testOnBeforeCreateFlagIsAuthorDisallow() {

  }

  /*---onLazyModelCollectionResult---*/

  public function testOnLazyModelCollectionResultRoleAllow() {

  }

  public function testOnLazyModelCollectionResultRoleDisallow() {

  }

  public function testOnLazyModelCollectionResultFlagBypassAccessAllow() {

  }

  public function testOnLazyModelCollectionResultFlagBypassAccessDisallow() {

  }

  public function testOnLazyModelCollectionResultFlagHasAccountAllow() {

  }

  public function testOnLazyModelCollectionResultFlagHasAccountDisallow() {

  }

  public function testOnLazyModelCollectionResultFlagIsAuthorAllow() {

  }

  public function testOnLazyModelCollectionResultFlagIsAuthorDisallow() {

  }

  /*----------ModelManager event tests------------*/

  /*---onBeforeMethodCall---*/

  public function testOnBeforeMethodCallRoleAllow() {

  }

  public function testOnBeforeMethodCallRoleDisallow() {

  }

  public function testOnBeforeMethodCallFlagBypassAccessAllow() {

  }

  public function testOnBeforeMethodCallFlagBypassAccessDisallow() {

  }

  public function testOnBeforeMethodCallFlagHasAccountAllow() {

  }

  public function testOnBeforeMethodCallFlagHasAccountDisallow() {

  }

  public function testOnBeforeMethodCallFlagIsAuthorAllow() {

  }

  public function testOnBeforeMethodCallFlagIsAuthorDisallow() {

  }

  /*---onBeforeSave---*/

  public function testOnBeforeSaveRoleAllow() {

  }

  public function testOnBeforeSaveRoleDisallow() {

  }

  public function testOnBeforeSaveFlagBypassAccessAllow() {

  }

  public function testOnBeforeSaveFlagBypassAccessDisallow() {

  }

  public function testOnBeforeSaveFlagHasAccountAllow() {

  }

  public function testOnBeforeSaveFlagHasAccountDisallow() {

  }

  public function testOnBeforeSaveFlagIsAuthorAllow() {

  }

  public function testOnBeforeSaveFlagIsAuthorDisallow() {

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
//     $this->sendRequestAs('POST', '/test/create-entity', static::$admin_user);
//     $entities = $this->testEntityOperations->findTestEntities();
//     $this->assertEquals(1, count($entities));
//   }
//
//   public function testCreateEntityDisallow() {
//     $this->sendRequestAs('POST', '/test/create-entity', static::$authenticated_user);
//     $entities = $this->testEntityOperations->findTestEntities();
//     $this->assertEquals(0, count($entities));
//   }
//
//   public function testCreateEntityBypass() {
//     $this->sendRequestAs('POST', '/test/create-entity', static::$superadmin_user);
//     $entities = $this->testEntityOperations->findTestEntities();
//     $this->assertEquals(1, count($entities));
//   }

  /*----Read----*/

//   public function testReadEntitiesAllowRole() {
//     $this->testEntityOperations->createTestEntity(static::$admin_user);
//     $this->sendRequestAs('GET', '/test/count-entities', static::$admin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(1, $entities_count);
//   }
//
//   public function testReadEntitiesAllowAuthor() {
//     $this->testEntityOperations->createTestEntity(static::$authenticated_user);
//     $this->sendRequestAs('GET', '/test/count-entities', static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(1, $entities_count);
//   }
//
//   public function testReadEntitiesDisallow() {
//     $this->testEntityOperations->createTestEntity(static::$admin_user);
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
//     $this->testEntityOperations->createTestEntity(static::$admin_user);
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
//     $this->testEntityOperations->createTestEntity(static::$admin_user);
//     $this->sendRequestAs('GET', '/test/count-entities-lazy', static::$admin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(1, $entities_count);
//   }
//
//   public function testReadEntitiesLazyLoadAllowAuthor() {
//     $this->testEntityOperations->createTestEntity(static::$authenticated_user);
//     $this->sendRequestAs('GET', '/test/count-entities-lazy', static::$authenticated_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(1, $entities_count);
//   }
//
//   public function testReadEntitiesLazyLoadDisallow() {
//     $this->testEntityOperations->createTestEntity(static::$admin_user);
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
//     $this->testEntityOperations->createTestEntity(static::$admin_user);
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
//     $this->testEntityOperations->createTestEntity(static::$admin_user);
//     $this->sendRequestAs('GET', '/test/read-field-1', static::$admin_user);
//     $response = $this->client->getResponse();
//     $this->assertEquals(200, $response->getStatusCode());
//     $entities_count = $response->getContent();
//     $this->assertEquals(1, $entities_count);
//   }

//   /*----Update field----*/
//   public function testUpdateEntityAllowRole() {
//     $this->testEntityOperations->createTestEntity(static::$admin_user);
//     $this->sendRequestAs('GET', '/test/count-entities', static::$admin_user);
//   }
}
