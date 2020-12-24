<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Services;

use Ordermind\LogicalAuthorizationBundle\DebugDataCollector\BackTraceFactory;
use Ordermind\LogicalAuthorizationBundle\DebugDataCollector\LogItemsWriter;
use Ordermind\LogicalAuthorizationBundle\DebugDataCollector\PermissionCheckLogItem;
use Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException;
use Ordermind\LogicalPermissions\PermissionTree\RawPermissionTree;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * {@inheritDoc}
 */
class Helper implements HelperInterface
{
    protected string $environment;

    protected TokenStorageInterface $tokenStorage;

    protected ?LoggerInterface $logger;

    protected ?LogItemsWriter $logItemsWriter;

    protected ?BackTraceFactory $backTraceFactory;

    public function __construct(
        string $environment,
        TokenStorageInterface $tokenStorage,
        ?LoggerInterface $logger = null,
        ?LogItemsWriter $logItemsWriter = null,
        ?BackTraceFactory $backTraceFactory = null
    ) {
        $this->environment = $environment;
        $this->tokenStorage = $tokenStorage;
        $this->logger = $logger;
        $this->logItemsWriter = $logItemsWriter;
        $this->backTraceFactory = $backTraceFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrentUser()
    {
        $token = $this->tokenStorage->getToken();
        if (!is_null($token)) {
            return $token->getUser();
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function handleError(string $message, array $context): void
    {
        if ('prod' === $this->environment && !is_null($this->logger)) {
            $this->logger->error($message, $context);
        } else {
            $message .= "\nContext:\n";
            foreach ($context as $key => $value) {
                $message .= "$key => " . print_r($value, true) . "\n";
            }

            throw new LogicalAuthorizationException($message);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function logPermissionCheckForDebug(
        bool $access,
        string $type,
        $item,
        $user,
        RawPermissionTree $rawPermissionTree,
        array $context,
        string $message = ''
    ): void {
        if (!$this->logItemsWriter || !$this->backTraceFactory) {
            return;
        }

        $backTrace = $this->backTraceFactory->createBackTrace();
        $logItem = new PermissionCheckLogItem(
            $access,
            $type,
            $item,
            $user,
            $rawPermissionTree,
            $context,
            $message,
            $backTrace
        );

        $this->logItemsWriter->appendLogItem($logItem);
    }
}
