<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Test\Fixtures\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class YmlController extends AbstractController
{
    public function routeYmlAction(): Response
    {
        return new Response('');
    }

    public function routeYmlAllowedAction(): Response
    {
        return new Response('');
    }

    public function routeYmlDeniedAction(): Response
    {
        return new Response('');
    }
}
