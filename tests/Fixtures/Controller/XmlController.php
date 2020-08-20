<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Test\Fixtures\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class XmlController extends AbstractController
{
    public function routeXmlAction(): Response
    {
        return new Response('');
    }

    public function routeXmlRoleAction(): Response
    {
        return new Response('');
    }

    public function routeXmlAllowedAction(): Response
    {
        return new Response('');
    }

    public function routeXmlDeniedAction(): Response
    {
        return new Response('');
    }
}
