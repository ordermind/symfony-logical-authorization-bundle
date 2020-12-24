<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\DebugDataCollector;

use Ordermind\LogicalPermissions\PermissionTree\RawPermissionTree;

class PermissionCheckLogItem
{
    protected bool $access;

    protected string $type;

    /**
     * @var array|string
     */
    protected $item;

    /**
     * @var object|string|null
     */
    protected $user;

    protected RawPermissionTree $rawPermissionTree;

    protected array $context;

    protected string $message;

    protected array $backTrace;

    /**
     * @internal
     *
     * @param array|string       $item
     * @param object|string|null $user
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

    public function toArray(): array
    {
        return [
            'access'      => $this->getAccess(),
            'type'        => $this->getType(),
            'item'        => $this->getItem(),
            'user'        => $this->getUser(),
            'permissions' => $this->getRawPermissionTree()->getValue(),
            'context'     => $this->getContext(),
            'message'     => $this->getMessage(),
            'backtrace'   => $this->getBackTrace(),
        ];
    }
}
