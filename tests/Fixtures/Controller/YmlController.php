<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Test\Fixtures\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
