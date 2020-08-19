<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Test\Fixtures\Controller;

use Ordermind\LogicalAuthorizationBundle\Annotation\Routing\Permissions;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/route-role", name="route_role", methods={"GET"}))
     *
     * @Permissions({
     *   "role": "ROLE_ADMIN"
     * })
     */
    public function routeRoleAction(): Response
    {
        return new Response('');
    }

    /**
     * @Route("/route-role-multiple", name="route_role_multiple", methods={"GET"}))
     *
     * @Permissions({
     *   "role": {"ROLE_SALES", "ROLE_ADMIN"}
     * })
     */
    public function routeRoleMultipleAction(): Response
    {
        return new Response('');
    }

    /**
     * @Route("/route-no-bypass", name="route_no_bypass", methods={"GET"}))
     *
     * @Permissions({
     *   "no_bypass": true,
     *   FALSE
     * })
     */
    public function routeNoBypassAction(): Response
    {
        return new Response('');
    }

    /**
     * @Route("/route-host", name="route_host", methods={"GET"}))
     *
     * @Permissions({
     *   "host": "test.com"
     * })
     */
    public function routeHostAction(): Response
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
    public function routeMethodAction(): Response
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
    public function routeMethodLowercaseAction(): Response
    {
        return new Response('');
    }

    /**
     * @Route("/route-ip", name="route_ip", methods={"GET"}))
     *
     * @Permissions({
     *   "ip": "127.0.0.1"
     * })
     */
    public function routeIpAction(): Response
    {
        return new Response('');
    }

    /**
     * @Route("/route-complex", name="route_complex", methods={"GET"}))
     *
     * @Permissions({
     *   "AND": {
     *     "role": {"ROLE_SALES", "ROLE_ADMIN"},
     *     "ip": "127.0.0.1"
     *   }
     * })
     */
    public function routeComplexAction(): Response
    {
        return new Response('');
    }
}
