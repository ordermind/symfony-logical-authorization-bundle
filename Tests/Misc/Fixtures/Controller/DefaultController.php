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
    $user = $this->get('ordermind_logical_authorization.service.user_helper')->getCurrentUser();
    $laRoute = $this->get('ordermind_logical_authorization.service.logical_authorization_route');
    $result = $laRoute->getAllAvailableRoutes($user);
    if(empty($result['routes'])) return new Response(0);
    return new Response(count($result['routes']));
  }

  /**
    * @Route("/count-available-route-patterns", name="count_available_route_patterns")
    * @Method({"GET"})
    */
  public function countAvailableRoutePatternsAction(Request $request) {
    $user = $this->get('ordermind_logical_authorization.service.user_helper')->getCurrentUser();
    $laRoute = $this->get('ordermind_logical_authorization.service.logical_authorization_route');
    $result = $laRoute->getAllAvailableRoutes($user);
    if(empty($result['route_patterns'])) return new Response(0);
    return new Response(count($result['route_patterns']));
  }
}
