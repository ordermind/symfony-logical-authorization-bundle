<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Services;

use Ordermind\LogicalAuthorizationBundle\Services\LogicalPermissionsProxyInterface;
use Ordermind\LogicalAuthorizationBundle\Services\HelperInterface;

class LogicalAuthorization implements LogicalAuthorizationInterface
{

    protected $lpProxy;
    protected $helper;

  /**
   * @internal
   *
   * @param Ordermind\LogicalAuthorizationBundle\Services\LogicalPermissionsProxyInterface $lpProxy The logical permissions proxy to use
   * @param Ordermind\LogicalAuthorizationBundle\Services\HelperInterface                  $helper  LogicalAuthorization helper service
   */
    public function __construct(LogicalPermissionsProxyInterface $lpProxy, HelperInterface $helper)
    {
        $this->lpProxy = $lpProxy;
        if (!$this->lpProxy->getBypassCallback()) {
            $this->lpProxy->setBypassCallback(function ($context) {
                return $this->lpProxy->checkAccess(['flag' => 'user_can_bypass_access'], $context, false);
            });
        }
        $this->helper = $helper;
    }

  /**
   * {@inheritdoc}
   */
    public function checkAccess($permissions, array $context, bool $allow_bypass = true): bool
    {
        try {
            return $this->lpProxy->checkAccess($permissions, $context, $allow_bypass);
        } catch (\Exception $e) {
            $class = get_class($e);
            $message = $e->getMessage();
            $this->helper->handleError("An exception was caught while checking access: \"$message\" at ".$e->getFile()." line ".$e->getLine(), array('exception' => $class, 'permissions' => $permissions, 'context' => $context));
        }

        return false;
    }
}
