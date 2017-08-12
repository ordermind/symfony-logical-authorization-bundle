<?php

namespace Ordermind\LogicalAuthorizationBundle\PermissionTypes\Host;

use Symfony\Component\HttpFoundation\RequestStack;

use Ordermind\LogicalAuthorizationBundle\PermissionTypes\PermissionTypeInterface;

class Host implements PermissionTypeInterface {
  protected $requestStack;

  public function __construct(RequestStack $requestStack) {
    $this->requestStack = $requestStack;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'host';
  }

  /**
   * Checks if the current request uses an allowed host
   *
   * @param string $host The host to evaluate
   * @param array $context The context for evaluating the host
   *
   * @return bool TRUE if the host is allowed or FALSE if it is not allowed
   */
  public function checkPermission($host, $context) {
    if(!is_string($host)) {
      throw new \InvalidArgumentException('The host parameter must be a string.');
    }
    if(!$host) {
      throw new \InvalidArgumentException('The host parameter cannot be empty.');
    }

    $currentRequest = $this->requestStack->getCurrentRequest();

    if(!$currentRequest) return false;

    return !!preg_match('{'.$host.'}i', $currentRequest->getHost());
  }
}
