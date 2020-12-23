<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\DebugDataCollector;

use Symfony\Component\HttpKernel\DataCollector\LateDataCollectorInterface;
use Symfony\Component\VarDumper\Cloner\Data;

/**
 * Data collector for debugging purposes.
 */
interface CollectorInterface extends LateDataCollectorInterface
{
    /**
     * Gets the full permission tree.
     *
     * @return Data
     */
    public function getPermissionTree(): Data;

    /**
     * Gets the log items that have been collected.
     *
     * @return array
     */
    public function getLog(): array;
}
