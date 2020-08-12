<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Test\Fixtures\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class XmlController extends AbstractController
{
    public function routeXmlAction()
    {
        return new Response('');
    }

    public function routeXmlAllowedAction()
    {
        return new Response('');
    }

    public function routeXmlDeniedAction()
    {
        return new Response('');
    }
}
