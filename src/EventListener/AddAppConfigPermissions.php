<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\EventListener;

use Ordermind\LogicalAuthorizationBundle\Event\AddPermissionsEventInterface;

/**
 * Adds permissions from app config file
 */
class AddAppConfigPermissions
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @internal
     *
     * @param array $config The logauth.config parameter
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Event listener callback for adding permissions to the tree
     *
     * @param Ordermind\LogicalAuthorizationBundle\Event\AddPermissionsEventInterface $event
     */
    public function onAddPermissions(AddPermissionsEventInterface $event)
    {
        if (!empty($this->config['permissions'])) {
            $event->insertTree($this->config['permissions']);
        }
    }
}
