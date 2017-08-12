<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\Fixtures\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Ordermind\LogicalAuthorizationBundle\Annotation\Routing\Permissions;

class DefaultController extends Controller {

  /**
    * @Route("/route-role", name="route_role")
    * @Method({"GET"})
    * @Permissions({
    *   "role": "ROLE_ADMIN"
    * })
    */
  public function routeRoleAction(Request $request) {
    return new Response('');
  }

  /**
    * @Route("/route-no-bypass", name="route_no_bypass")
    * @Method({"GET"})
    * @Permissions({
    *   "no_bypass": true,
    *   FALSE
    * })
    */
  public function routeNoBypassAction(Request $request) {
    return new Response('');
  }

  /**
    * @Route("/route-host", name="route_host")
    * @Method({"GET"})
    * @Permissions({
    *   "host": "test.com"
    * })
    */
  public function routeHostAction(Request $request) {
    return new Response('');
  }

  /**
    * @Route("/route-method", name="route_method")
    * @Permissions({
    *   "method": "GET"
    * })
    */
  public function routeMethodAction(Request $request) {
    return new Response('');
  }

  /**
    * @Route("/route-ip", name="route_ip")
    * @Method({"GET"})
    * @Permissions({
    *   "ip": "127.0.0.1"
    * })
    */
  public function routeIpAction(Request $request) {
    return new Response('');
  }
}
