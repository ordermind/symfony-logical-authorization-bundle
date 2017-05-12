<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\Misc\Fixtures\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Ordermind\LogicalAuthorizationBundle\Tests\Misc\Fixtures\Entity\TestEntity;

class DefaultController extends Controller {

  /**
    * @Route("/route-role", name="route_role", options={
    * "logical_authorization_permissions": {
    *   "role": "ROLE_ADMIN"
    * }})
    *
    * @Method({"GET"})
    */
  public function routeRoleAction(Request $request) {
    return new Response('');
  }

  /**
    * @Route("/route-no-bypass", name="route_no_bypass", options={
    * "logical_authorization_permissions": {
    *   "no_bypass": true,
    *   FALSE
    * }})
    *
    * @Method({"GET"})
    */
  public function routeNoBypassAction(Request $request) {
    return new Response('');
  }

  /**
    * @Route("/route-has-account", name="route_has_account", options={
    * "logical_authorization_permissions": {
    *   "flag": "has_account"
    * }})
    *
    * @Method({"GET"})
    */
  public function routeHasAccountAction(Request $request) {
    return new Response('');
  }

  /**
   * @Route("/pattern-allowed", name="pattern_allowed")
   */
  public function patternAllowedAction(Request $request) {
    return new Response('');
  }

  /**
   * @Route("/pattern-forbidden", name="pattern_forbidden", options={
   * "logical_authorization_permissions": {
   *   "no_bypass": true,
   *   FALSE
   * }})
   */
  public function patternForbiddenAction(Request $request) {
    return new Response('');
  }

  /**
   * @Route("/route-allowed", name="route_allowed", options={
   * "logical_authorization_permissions": {
   *   TRUE
   * }})
   */
  public function routeAllowedAction(Request $request) {
    return new Response('');
  }

  /**
   * @Route("/route-forbidden", name="route_forbidden")
   */
  public function routeForbiddenAction(Request $request) {
    return new Response('');
  }

  /**
    * @Route("/count-available-routes", name="count_available_routes")
    * @Method({"GET"})
    */
  public function countAvailableRoutesAction(Request $request) {
    $laRoute = $this->get('ordermind_logical_authorization.service.logical_authorization_route');
    $result = $laRoute->getAvailableRoutes();
    if(empty($result['routes'])) return new Response(0);
    return new Response(count($result['routes']));
  }

  /**
    * @Route("/count-available-route-patterns", name="count_available_route_patterns")
    * @Method({"GET"})
    */
  public function countAvailableRoutePatternsAction(Request $request) {
    $laRoute = $this->get('ordermind_logical_authorization.service.logical_authorization_route');
    $result = $laRoute->getAvailableRoutes();
    if(empty($result['route_patterns'])) return new Response(0);
    return new Response(count($result['route_patterns']));
  }

  /**
    * @Route("/get-current-user-id", name="get_current_user_id")
    * @Method({"GET"})
    */
  public function getCurrentUserIdAction(Request $request) {
    $user = $this->get('ordermind_logical_authorization.service.helper')->getCurrentUser();
    if(is_null($user)) return new Response($user);
    if(is_string($user)) return new Response($user);
    return new Response($user->getId());
  }

  /**
   * @Route("/load-test-entity/{id}", name="load_test_entity", options={
   * "logical_authorization_permissions": {
   *   "role": "ROLE_ADMIN"
   * }})
   * @Method({"GET"})
   */
  public function loadTestEntityAction(Request $request, TestEntity $testEntity) {
    return new Response(get_class($testEntity));
  }

  /**
    * @Route("/count-forbidden-entities-lazy", name="test_count_forbidden_entities_lazy")
    * @Method({"GET"})
    */
  public function countForbiddenEntitiesLazyLoadAction(Request $request) {
    $operations = $this->get('test_model_operations');
    $operations->setRepositoryDecorator($this->get('repository_decorator.forbidden_entity'));
    $collection = $operations->getLazyLoadedModelResult();
    return new Response(count($collection));
  }

  /**
   * @Route("/repository-decorator-create", name="test_repository_decorator_create")
   * @Method({"GET"})
   */
  public function repositoryDecoratorCreateAction(Request $request) {
    $modelDecorator = $this->get('repository_decorator.test_entity')->create()->save();
    return new Response('');
  }
}
