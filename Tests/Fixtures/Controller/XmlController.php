<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\Fixtures\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class XmlController extends Controller {
  public function routeXmlAction(Request $request) {
    return new Response('');
  }
  public function routeXmlAllowedAction(Request $request) {
    return new Response('');
  }
  public function routeXmlDeniedAction(Request $request) {
    return new Response('');
  }
}