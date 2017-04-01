<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\ORM\Fixtures\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends Controller {

  /**
    * @Route("/count-unknown-result", name="count_unknown_result")
    * @Method({"GET"})
    */
  public function countUnknownResultAction(Request $request) {
    $operations = $this->get('test_entity_operations');
    $operations->setRepositoryManager($this->get($request->get('repository_manager_service')));
    $result = $operations->getUnknownResult();
    return new Response(count($result));
  }

  /**
    * @Route("/find-single-model-result/{id}", name="find_single_model_result")
    * @Method({"GET"})
    */
  public function findSingleModelResultAction(Request $request, $id) {
    $operations = $this->get('test_entity_operations');
    $operations->setRepositoryManager($this->get($request->get('repository_manager_service')));
    $result = $operations->getSingleModelResult($id);
    return new JsonResponse((bool) $result);
  }

  /**
    * @Route("/count-multiple-model-result", name="count_multiple_model_result")
    * @Method({"GET"})
    */
  public function countMultipleModelResultAction(Request $request) {
    $operations = $this->get('test_entity_operations');
    $operations->setRepositoryManager($this->get($request->get('repository_manager_service')));
    $result = $operations->getMultipleModelResult();
    return new Response(count($result));
  }

  /**
    * @Route("/count-entities-lazy", name="test_count_entities_lazy")
    * @Method({"GET"})
    */
  public function countEntitiesLazyLoadAction(Request $request) {
    $operations = $this->get('test_entity_operations');
    $operations->setRepositoryManager($this->get($request->get('repository_manager_service')));
    $collection = $operations->getLazyLoadedModelResult();
    return new Response(count($collection));
  }

  /**
    * @Route("/create-entity", name="create_entity")
    * @Method({"POST"})
    */
  public function createEntityRoleAuthorAction(Request $request) {
    $user = $this->get('ordermind_logical_authorization.service.user_helper')->getCurrentUser();
    $operations = $this->get('test_entity_operations');
    $operations->setRepositoryManager($this->get($request->get('repository_manager_service')));
    $modelManager = $operations->createTestModel($user);
    return new JsonResponse((bool) $modelManager);
  }

  /**
    * @Route("/call-method-getter", name="call_method_getter")
    * @Method({"GET"})
    */
  public function callMethodGetterRoleAction(Request $request) {
    $operations = $this->get('test_entity_operations');
    $operations->setRepositoryManager($this->get($request->get('repository_manager_service')));
    $modelManager = $operations->createTestModel(null, true);
    $operations->callMethodSetter($modelManager, true);

    return new Response($operations->callMethodGetter($modelManager));
  }

  /**
    * @Route("/call-method-getter-author", name="call_method_getter_author")
    * @Method({"GET"})
    */
  public function callMethodGetterAuthorAction(Request $request) {
    $user = $this->get('ordermind_logical_authorization.service.user_helper')->getCurrentUser();
    $operations = $this->get('test_entity_operations');
    $operations->setRepositoryManager($this->get($request->get('repository_manager_service')));
    $modelManager = $operations->createTestModel($user, true);
    $operations->callMethodSetter($modelManager, true);

    return new Response($operations->callMethodGetter($modelManager));
  }

}
