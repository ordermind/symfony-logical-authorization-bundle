<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Services;

use Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * {@inheritdoc}
 */
class Helper implements HelperInterface
{
    /**
     * @var string
     */
    protected $environment;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @internal
     *
     * @param string                $environment
     * @param TokenStorageInterface $tokenStorage
     * @param LoggerInterface       $logger
     */
    public function __construct($environment, TokenStorageInterface $tokenStorage, LoggerInterface $logger = null)
    {
        $this->environment = $environment;
        $this->tokenStorage = $tokenStorage;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function handleError(string $message, array $context)
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
}
