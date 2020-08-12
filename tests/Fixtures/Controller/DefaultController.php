<?php

namespace Ordermind\LogicalAuthorizationBundle\Test\Fixtures\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Ordermind\LogicalAuthorizationBundle\Annotation\Routing\Permissions;

class DefaultController extends AbstractController
{

  /**
    * @Route("/route-role", name="route_role")
    *
    * @Method({"GET"})
    *
    * @Permissions({
    *   "role": "ROLE_ADMIN"
    * })
    */
    public function routeRoleAction(Request $request)
    {
        return new Response('');
    }

  /**
    * @Route("/route-role-multiple", name="route_role_multiple")
    *
    * @Method({"GET"})
    *
    * @Permissions({
    *   "role": {"ROLE_SALES", "ROLE_ADMIN"}
    * })
    */
    public function routeRoleMultipleAction(Request $request)
    {
        return new Response('');
    }

    /**
      * @Route("/route-no-bypass", name="route_no_bypass")
      *
      * @Method({"GET"})
      *
      * @Permissions({
      *   "no_bypass": true,
      *   FALSE
      * })
      */
    public function routeNoBypassAction(Request $request)
    {
        return new Response('');
    }

    /**
      * @Route("/route-host", name="route_host")
      *
      * @Method({"GET"})
      *
      * @Permissions({
      *   "host": "test.com"
      * })
      */
    public function routeHostAction(Request $request)
    {
        return new Response('');
    }

    /**
      * @Route("/route-method", name="route_method")
      *
      * @Permissions({
      *   "method": "GET"
      * })
      */
    public function routeMethodAction(Request $request)
    {
        return new Response('');
    }

    /**
      * @Route("/route-method-lowercase", name="route_method_lowercase")
      *
      * @Permissions({
      *   "method": "get"
      * })
      */
    public function routeMethodLowercaseAction(Request $request)
    {
        return new Response('');
    }

    /**
      * @Route("/route-ip", name="route_ip")
      *
      * @Method({"GET"})
      *
      * @Permissions({
      *   "ip": "127.0.0.1"
      * })
      */
    public function routeIpAction(Request $request)
    {
        return new Response('');
    }

  /**
    * @Route("/route-complex", name="route_complex")
    *
    * @Method({"GET"})
    *
    * @Permissions({
    *   "AND": {
    *     "role": {"ROLE_SALES", "ROLE_ADMIN"},
    *     "ip": "127.0.0.1"
    *   }
    * })
    */
    public function routeComplexAction(Request $request)
    {
        return new Response('');
    }
}
