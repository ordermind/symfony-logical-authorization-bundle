<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\ODM\Fixtures\Controller;

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
    $operations = $this->get('test_model_operations');
    $operations->setRepositoryDecorator($this->get($request->get('repository_decorator_service')));
    $result = $operations->getUnknownResult();
    return new Response(count($result));
  }

  /**
    * @Route("/find-single-model-result/{id}", name="find_single_model_result")
    * @Method({"GET"})
    */
  public function findSingleModelResultAction(Request $request, $id) {
    $operations = $this->get('test_model_operations');
    $operations->setRepositoryDecorator($this->get($request->get('repository_decorator_service')));
    $result = $operations->getSingleModelResult($id);
    return new JsonResponse((bool) $result);
  }

  /**
    * @Route("/count-multiple-model-result", name="count_multiple_model_result")
    * @Method({"GET"})
    */
  public function countMultipleModelResultAction(Request $request) {
    $operations = $this->get('test_model_operations');
    $operations->setRepositoryDecorator($this->get($request->get('repository_decorator_service')));
    $result = $operations->getMultipleModelResult();
    return new Response(count($result));
  }

  /**
    * @Route("/count-documents-lazy", name="test_count_documents_lazy")
    * @Method({"GET"})
    */
  public function countDocumentsLazyLoadAction(Request $request) {
    $operations = $this->get('test_model_operations');
    $operations->setRepositoryDecorator($this->get($request->get('repository_decorator_service')));
    $collection = $operations->getLazyLoadedModelResult();
    return new Response(count($collection));
  }

  /**
    * @Route("/create-document", name="create_document")
    * @Method({"GET"})
    */
  public function createDocumentAction(Request $request) {
    $operations = $this->get('test_model_operations');
    $operations->setRepositoryDecorator($this->get($request->get('repository_decorator_service')));
    $modelDecorator = $operations->createTestModel();
    return new JsonResponse(is_object($modelDecorator) && $modelDecorator instanceof \Ordermind\LogicalAuthorizationBundle\Services\ModelDecoratorInterface);
  }

  /**
    * @Route("/call-method-getter", name="call_method_getter")
    * @Method({"GET"})
    */
  public function callMethodGetterAction(Request $request) {
    $operations = $this->get('test_model_operations');
    $operations->setRepositoryDecorator($this->get($request->get('repository_decorator_service')));
    $modelDecorator = $operations->createTestModel(null, true);
    $operations->callMethodSetter($modelDecorator, true);

    return new Response($operations->callMethodGetter($modelDecorator));
  }

  /**
    * @Route("/call-method-getter-author", name="call_method_getter_author")
    * @Method({"GET"})
    */
  public function callMethodGetterAuthorAction(Request $request) {
    $user = $this->get('ordermind_logical_authorization.service.helper')->getCurrentUser();
    $operations = $this->get('test_model_operations');
    $operations->setRepositoryDecorator($this->get($request->get('repository_decorator_service')));
    $modelDecorator = $operations->createTestModel($user, true);
    $operations->callMethodSetter($modelDecorator, true);

    return new Response($operations->callMethodGetter($modelDecorator));
  }

  /**
    * @Route("/call-method-setter", name="call_method_setter")
    * @Method({"GET"})
    */
  public function callMethodSetterAction(Request $request) {
    $operations = $this->get('test_model_operations');
    $operations->setRepositoryDecorator($this->get($request->get('repository_decorator_service')));
    $modelDecorator = $operations->createTestModel(null, true);
    $operations->callMethodSetter($modelDecorator);

    return new Response($operations->callMethodGetter($modelDecorator, true));
  }

  /**
    * @Route("/call-method-setter-author", name="call_method_setter_author")
    * @Method({"GET"})
    */
  public function callMethodSetterAuthorAction(Request $request) {
    $user = $this->get('ordermind_logical_authorization.service.helper')->getCurrentUser();
    $operations = $this->get('test_model_operations');
    $operations->setRepositoryDecorator($this->get($request->get('repository_decorator_service')));
    $modelDecorator = $operations->createTestModel($user, true);
    $operations->callMethodSetter($modelDecorator);

    return new Response($operations->callMethodGetter($modelDecorator, true));
  }

  /**
    * @Route("/save-model-create", name="save_model_create")
    * @Method({"GET"})
    */
  public function saveModelCreateAction(Request $request) {
    $operations = $this->get('test_model_operations');
    $operations->setRepositoryDecorator($this->get($request->get('repository_decorator_service')));
    $operations->createTestModel();
    $result = $operations->getMultipleModelResult(true);
    return new Response(count($result));
  }

  /**
    * @Route("/save-model-update", name="save_model_update")
    * @Method({"GET"})
    */
  public function saveModelUpdateAction(Request $request) {
    $operations = $this->get('test_model_operations');
    $operations->setRepositoryDecorator($this->get($request->get('repository_decorator_service')));
    $modelDecorator = $operations->createTestModel(null, true);
    $operations->callMethodSetter($modelDecorator, true);
    $modelDecorator->save();
    $modelDecorator->getObjectManager()->detach($modelDecorator->getModel());
    $persistedModelDecorator = $operations->getSingleModelResult($modelDecorator->getModel()->getId(), true);
    return new Response($operations->callMethodGetter($persistedModelDecorator, true));
  }

  /**
    * @Route("/save-model-update-author", name="save_model_update_author")
    * @Method({"GET"})
    */
  public function saveModelUpdateAuthorAction(Request $request) {
    $user = $this->get('ordermind_logical_authorization.service.helper')->getCurrentUser();
    $operations = $this->get('test_model_operations');
    $operations->setRepositoryDecorator($this->get($request->get('repository_decorator_service')));
    $modelDecorator = $operations->createTestModel($user, true);
    $operations->callMethodSetter($modelDecorator, true);
    $modelDecorator->save();
    $modelDecorator->getObjectManager()->detach($modelDecorator->getModel());
    $persistedModelDecorator = $operations->getSingleModelResult($modelDecorator->getModel()->getId(), true);
    return new Response($operations->callMethodGetter($persistedModelDecorator, true));
  }

  /**
    * @Route("/delete-model", name="delete_model")
    * @Method({"GET"})
    */
  public function deleteModelAction(Request $request) {
    $operations = $this->get('test_model_operations');
    $operations->setRepositoryDecorator($this->get($request->get('repository_decorator_service')));
    $modelDecorator = $operations->createTestModel(null, true);
    $modelDecorator->delete();
    $result = $operations->getMultipleModelResult(true);
    return new Response(count($result));
  }

  /**
    * @Route("/delete-model-author", name="delete_model_author")
    * @Method({"GET"})
    */
  public function deleteModelAuthorAction(Request $request) {
    $user = $this->get('ordermind_logical_authorization.service.helper')->getCurrentUser();
    $operations = $this->get('test_model_operations');
    $operations->setRepositoryDecorator($this->get($request->get('repository_decorator_service')));
    $modelDecorator = $operations->createTestModel($user, true);
    $modelDecorator->delete();
    $result = $operations->getMultipleModelResult(true);
    return new Response(count($result));
  }

  /**
    * @Route("/get-available-actions", name="get_available_actions")
    * @Method({"GET"})
    */
  public function getAvailableActionsAction(Request $request) {
    $user = $this->get('ordermind_logical_authorization.service.helper')->getCurrentUser();
    $operations = $this->get('test_model_operations');
    $operations->setRepositoryDecorator($this->get($request->get('repository_decorator_service')));
    $modelDecorator = $operations->createTestModel($user, true);
    $result = $modelDecorator->getAvailableActions();
    return new JsonResponse($result);
  }

}
