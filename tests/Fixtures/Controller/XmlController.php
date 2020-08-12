<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Test\Fixtures\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class XmlController extends AbstractController
{
    public function routeXmlAction(Request $request)
    {
        return new Response('');
    }
    public function routeXmlAllowedAction(Request $request)
    {
        return new Response('');
    }
    public function routeXmlDeniedAction(Request $request)
    {
        return new Response('');
    }
}
