<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\DebugDataCollector;

class LogItemsWriter
{
    protected LogItemCollectionRepository $repository;

    public function __construct(LogItemCollectionRepository $repository)
    {
        $this->repository = $repository;
    }

    public function appendLogItem(PermissionCheckLogItem $logItem): self
    {
        $collection = $this->repository->getCollection();
        $collection->add($logItem);

        return $this;
    }
}
