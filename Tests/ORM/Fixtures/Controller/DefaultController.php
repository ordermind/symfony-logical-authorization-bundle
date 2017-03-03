<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\ORM\Fixtures\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use AppBundle\Entity\TestEntity;

class DefaultController extends Controller {
  /**
    * @Route("/authenticated", name="test_authenticated", options={
    *  "logical_authorization": {
    *     "role": "ROLE_USER"
    *   }
    * })
    */
  public function authenticatedAction(Request $request) {
    return new Response('');
  }

  /**
    * @Route("/admin", name="test_admin", options={
    *  "logical_authorization": {
    *     "role": "ROLE_ADMIN"
    *   }
    * })
    */
  public function adminAction(Request $request) {
    return new Response('');
  }

  /**
    * @Route("/create-entity", name="test_create_entity")
    * @Method({"POST"})
    */
  public function createEntityAction(Request $request) {
    $user = $this->get('ordermind_logical_authorization.service.user_helper')->getCurrentUser();
    $operations = $this->get('test_entity_operations');
    $operations->createTestEntity($user);
    return new Response('');
  }

  /**
    * @Route("/count-entities", name="test_count_entities")
    * @Method({"GET"})
    */
  public function countEntitiesAction(Request $request) {
    $operations = $this->get('test_entity_operations');
    $entities = $operations->findTestEntities();
    return new Response(count($entities));
  }

  /**
    * @Route("/count-entities-lazy", name="test_count_entities_lazy")
    * @Method({"GET"})
    */
  public function countEntitiesLazyLoadAction(Request $request) {
    $operations = $this->get('test_entity_operations');
    $collection = $operations->findTestEntitiesLazyLoad();
    return new Response(count($collection));
  }

  /**
    * @Route("/read-field-1", name="test_read_field1")
    * @Method({"GET"})
    */
  public function readField1Action(Request $request) {
    $operations = $this->get('test_entity_operations');
    $entities = $operations->findTestEntities();
    if($entities) {
      $modelManager = array_shift($entities);
      return new Response($modelManager->getField1());
    }
    throw new AccessDeniedHttpException('Permission denied.');
  }

  /**
    * @Route("/update-entity", name="test_update_entity")
    * @Method({"POST"})
    */
  public function updateEntityAction(Request $request) {
    $operations = $this->get('test_entity_operations');
    $entities = $operations->findTestEntities(true);
    if($entities) {
      $entity = array_shift($entities);
      $operations->updateTestEntity($entity);
    }
    return new Response('');
  }
}
