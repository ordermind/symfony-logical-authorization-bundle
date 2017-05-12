<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\Fixtures\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class YmlController extends Controller {
  public function routeYmlAction(Request $request) {
    return new Response('');
  }
}