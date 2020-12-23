<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\DebugDataCollector;

use Ordermind\LogicalPermissions\PermissionTree\RawPermissionTree;

class PermissionCheckLogItem
{
    /**
     * @var bool
     */
    private $access;

    /**
     * @var string
     */
    private $type;

    /**
     * @var array|string
     */
    private $item;

    /**
     * @var object|string|null
     */
    private $user;

    /**
     * @var RawPermissionTree
     */
    private $rawPermissionTree;

    /**
     * @var array
     */
    private $context;

    /**
     * @var string
     */
    private $message;

    /**
     * @var array
     */
    private $backTrace;

    /**
     * @internal
     */
    public function __construct(
        bool $access,
        string $type,
        $item,
        $user,
        RawPermissionTree $rawPermissionTree,
        array $context,
        string $message,
        array $backTrace
    ) {
        $this->access = $access;
        $this->type = $type;
        $this->item = $item;
        $this->user = $user;
        $this->rawPermissionTree = $rawPermissionTree;
        $this->context = $context;
        $this->message = $message;
        $this->backTrace = $backTrace;
    }

    public function getAccess(): bool
    {
        return $this->access;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getItem()
    {
        return $this->item;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getRawPermissionTree(): RawPermissionTree
    {
        return $this->rawPermissionTree;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getBackTrace(): array
    {
        return $this->backTrace;
    }
}
