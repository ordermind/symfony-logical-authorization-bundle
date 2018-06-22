<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Annotation\Routing;

/**
 * @Annotation
 */
class Permissions {
  protected $permissions;

  public function __construct(array $data) {
    $this->permissions = $data['value'];
  }

  /**
   * Gets the permission tree for this route
   *
   * @return array|string|bool The permission tree for this route
   */
  public function getPermissions() {
    return $this->permissions;
  }
}
