<?php

namespace Ordermind\LogicalAuthorizationBundle\PermissionTypes\Ip;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\IpUtils;

use Ordermind\LogicalAuthorizationBundle\PermissionTypes\PermissionTypeInterface;

class Ip implements PermissionTypeInterface {
  protected $requestStack;

  public function __construct(RequestStack $requestStack) {
    $this->requestStack = $requestStack;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'ip';
  }

  /**
   * Checks if the current request comes from an allowed ip address
   *
   * @param string $ip The ip to evaluate
   * @param array $context The context for evaluating the ip
   *
   * @return bool TRUE if the ip is allowed or FALSE if it is not allowed
   */
  public function checkPermission($ip, $context) {
    if(!is_string($ip)) {
      throw new \InvalidArgumentException('The ip parameter must be a string.');
    }
    if(!$ip) {
      throw new \InvalidArgumentException('The ip parameter cannot be empty.');
    }

    $currentRequest = $this->requestStack->getCurrentRequest();

    return IpUtils::checkIp($currentRequest->getClientIp(), $ip);
  }
}
