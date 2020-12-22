<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\PermissionProviders;

/**
 * Provides permissions from app config file.
 */
class AppConfigPermissionProvider implements PermissionProviderInterface
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @internal
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritDoc}
     */
    public function getPermissionTree(): array
    {
        if (empty($this->config['permissions'])) {
            return [];
        }

        return $this->config['permissions'];
    }
}
