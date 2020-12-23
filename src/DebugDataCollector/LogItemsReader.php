<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\DebugDataCollector;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class LogItemsReader
{
    /**
     * @var LogItemCollectionRepository
     */
    protected $repository;

    public function __construct(LogItemCollectionRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getLogItems(): Collection
    {
        return new ArrayCollection($this->repository->getCollection()->toArray());
    }
}
