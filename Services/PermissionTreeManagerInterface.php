<?php

namespace Ordermind\LogicalAuthorizationBundle\Services;

interface PermissionTreeManagerInterface {
  public function generateTree();
  public function getTree();
  public function mergePermissions($arrays = []);
}