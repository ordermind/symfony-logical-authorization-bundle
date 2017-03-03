<?php

namespace Ordermind\LogicalAuthorizationBundle\PermissionTypes\Flag;

interface FlagInterface {
  public function getName();
  public function checkFlag($context);
}
 
