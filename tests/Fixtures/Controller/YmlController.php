<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Test\Fixtures\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class YmlController extends AbstractController
{
    public function routeYmlAction()
    {
        return new Response('');
    }

    public function routeYmlAllowedAction()
    {
        return new Response('');
    }

    public function routeYmlDeniedAction()
    {
        return new Response('');
    }
}
