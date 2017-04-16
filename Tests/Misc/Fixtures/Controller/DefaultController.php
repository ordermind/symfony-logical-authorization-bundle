<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\Misc\Fixtures\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

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
    return new Response(true);
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
    return new Response(true);
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
    return new Response(true);
  }

  /**
    * @Route("/count-unknown-result", name="count_unknown_result")
    * @Method({"GET"})
    */
  public function countUnknownResultAction(Request $request) {
    $operations = $this->get('test_model_operations');
    $operations->setRepositoryManager($this->get($request->get('repository_manager_service')));
    $result = $operations->getUnknownResult();
    return new Response(count($result));
  }

  /**
    * @Route("/count-available-routes", name="count_available_routes")
    * @Method({"GET"})
    */
  public function countAvailableRoutesAction(Request $request) {
    $user = $this->get('ordermind_logical_authorization.service.user_helper')->getCurrentUser();
    $laRoute = $this->get('ordermind_logical_authorization.service.logical_authorization_route');
    $result = $laRoute->getAllAvailableRoutes($user);
    return new Response(count($result));
  }
}
