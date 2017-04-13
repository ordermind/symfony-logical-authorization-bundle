<?php

namespace Ordermind\LogicalAuthorizationBundle\Doctrine\Annotation;

/**
 * @Annotation
 */
class LogicalAuthorization {
  protected $permissions;

  public function __construct(array $data) {
    $this->permissions = $data['value'];
  }

  public function getPermissions() {
    return $this->permissions;
  }
}