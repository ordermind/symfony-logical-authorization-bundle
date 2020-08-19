<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\PermissionCheckers\SimpleConditionChecker;

use InvalidArgumentException;
use Ordermind\LogicalAuthorizationBundle\PermissionCheckers\SimpleConditionChecker\Exceptions\SimpleConditionCheckerNotRegisteredException;

class SimpleConditionCheckerManager implements SimpleConditionCheckerManagerInterface
{
    /**
     * @var SimpleConditionCheckerInterface[]
     */
    protected $conditions = [];

    /**
     * {@inheritdoc}
     */
    public static function getName(): string
    {
        return 'condition';
    }

    /**
     * SimpleConditionCheckerManager constructor.
     *
     * @param iterable|SimpleConditionCheckerInterface[] $conditions
     */
    public function __construct(iterable $conditions = [])
    {
        foreach ($conditions as $condition) {
            $this->addCondition($condition);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addCondition(SimpleConditionCheckerInterface $condition)
    {
        $name = $condition->getName();
        if (!$name) {
            throw new InvalidArgumentException('The name of a condition cannot be empty.');
        }
        if ($this->conditionExists($name)) {
            throw new InvalidArgumentException(
                "The condition \"$name\" already exists! If you want to change the class that handles a condition, you "
                    . 'may do so by overriding the service definition for that condition.'
            );
        }

        $conditions = $this->getConditions();
        $conditions[$name] = $condition;
        $this->setConditions($conditions);
    }

    /**
     * {@inheritdoc}
     */
    public function removeCondition(string $name)
    {
        if (!$name) {
            throw new InvalidArgumentException('The name parameter cannot be empty.');
        }
        if (!$this->conditionExists($name)) {
            throw new SimpleConditionCheckerNotRegisteredException(
                sprintf(
                    'The condition "%s" has not been registered. Please use the "%s" service tag to register a '
                        . 'condition.',
                    $name,
                    'logauth.tag.permission_type.condition'
                )
            );
        }

        $conditions = $this->getConditions();
        unset($conditions[$name]);
        $this->setConditions($conditions);
    }

    /**
     * {@inheritdoc}
     */
    public function getConditions(): array
    {
        return $this->conditions;
    }

    /**
     * {@inheritdoc}
     */
    public function checkPermission(string $name, $context): bool
    {
        if (!$name) {
            throw new InvalidArgumentException('The name parameter cannot be empty.');
        }
        if (!$this->conditionExists($name)) {
            throw new SimpleConditionCheckerNotRegisteredException(
                sprintf(
                    'The condition "%s" has not been registered. Please use the "%s" service tag to register a '
                        . 'condition.',
                    $name,
                    'logauth.tag.permission_type.condition'
                )
            );
        }

        $conditions = $this->getConditions();

        return $conditions[$name]->checkCondition($context);
    }

    /**
     * @internal
     *
     * @param array $conditions
     */
    protected function setConditions(array $conditions)
    {
        $this->conditions = $conditions;
    }

    /**
     * @internal
     *
     * @param string $name
     *
     * @return bool
     */
    protected function conditionExists(string $name): bool
    {
        $conditions = $this->getConditions();

        return isset($conditions[$name]);
    }
}
