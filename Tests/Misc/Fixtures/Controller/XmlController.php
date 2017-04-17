<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\Misc\Fixtures\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class XmlController extends Controller {
  public function routeXmlAction(Request $request) {
    return new Response('');
  }
}