<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\Fixtures\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class YmlController extends AbstractController
{
    public function routeYmlAction(Request $request)
    {
        return new Response('');
    }
    public function routeYmlAllowedAction(Request $request)
    {
        return new Response('');
    }
    public function routeYmlDeniedAction(Request $request)
    {
        return new Response('');
    }
}
