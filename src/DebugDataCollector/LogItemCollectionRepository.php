<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\DebugDataCollector;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class LogItemCollectionRepository
{
    /**
     * @var Collection<PermissionCheckLogItem>
     */
    protected Collection $collection;

    public function __construct()
    {
        $this->collection = new ArrayCollection();
    }

    public function getCollection(): Collection
    {
        return $this->collection;
    }
}
