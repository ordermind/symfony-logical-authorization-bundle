<?php

namespace Ordermind\LogicalAuthorizationBundle\Services;

interface PermissionTreeBuilderInterface {
  public function getTree();
}