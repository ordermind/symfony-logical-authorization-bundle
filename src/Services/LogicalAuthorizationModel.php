<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Services;

use Ordermind\LogicalAuthorizationBundle\DataCollector\CollectorInterface;
use Ordermind\LogicalAuthorizationBundle\Interfaces\ModelDecoratorInterface;
use Ordermind\LogicalPermissions\PermissionTree\RawPermissionTree;
use ReflectionClass;

/**
 * {@inheritDoc}
 */
class LogicalAuthorizationModel implements LogicalAuthorizationModelInterface
{
    /**
     * @var LogicalAuthorizationInterface
     */
    protected $logicalAuthorization;

    /**
     * @var PermissionTreeBuilderInterface
     */
    protected $treeBuilder;

    /**
     * @var HelperInterface
     */
    protected $helper;

    /**
     * @var CollectorInterface|null
     */
    protected $debugCollector;

    public function __construct(
        LogicalAuthorizationInterface $logicalAuthorization,
        PermissionTreeBuilderInterface $treeBuilder,
        HelperInterface $helper,
        ?CollectorInterface $debugCollector = null
    ) {
        $this->logicalAuthorization = $logicalAuthorization;
        $this->treeBuilder = $treeBuilder;
        $this->helper = $helper;
        $this->debugCollector = $debugCollector;
    }

    /**
     * {@inheritDoc}
     */
    public function getAvailableActions($model, array $modelActions, array $fieldActions, $user = null): array
    {
        if ($model instanceof ModelDecoratorInterface) {
            $model = $model->getModel();
        }
        if ($user instanceof ModelDecoratorInterface) {
            $user = $user->getModel();
        }

        $availableActions = [];
        foreach ($modelActions as $action) {
            if ($this->checkModelAccess($model, $action, $user)) {
                $availableActions[$action] = $action;
            }
        }
        $reflectionClass = new ReflectionClass($model);
        foreach ($reflectionClass->getProperties() as $property) {
            $fieldName = $property->getName();
            foreach ($fieldActions as $action) {
                if ($this->checkFieldAccess($model, $fieldName, $action, $user)) {
                    if (!isset($availableActions['fields'])) {
                        $availableActions['fields'] = [];
                    }
                    if (!isset($availableActions['fields'][$fieldName])) {
                        $availableActions['fields'][$fieldName] = [];
                    }
                    $availableActions['fields'][$fieldName][$action] = $action;
                }
            }
        }

        return $availableActions;
    }

    /**
     * {@inheritDoc}
     */
    public function checkModelAccess($model, string $action, $user = null): bool
    {
        if ($model instanceof ModelDecoratorInterface) {
            $model = $model->getModel();
        }
        if ($user instanceof ModelDecoratorInterface) {
            $user = $user->getModel();
        }

        if (is_null($user)) {
            $user = $this->helper->getCurrentUser();
            if (is_null($user)) {
                if (!is_null($this->debugCollector)) {
                    $this->debugCollector->addPermissionCheck(
                        true,
                        'model',
                        [
                            'model'  => $model,
                            'action' => $action,
                        ],
                        $user,
                        new RawPermissionTree([]),
                        [],
                        'No user was available during this permission check (not even an anonymous user). This usually '
                            . 'happens during unit testing. Access was therefore automatically granted.'
                    );
                }

                return true;
            }
        }

        if (!is_string($model) && !is_object($model)) {
            $this->helper->handleError(
                'Error checking model access: the model parameter must be either a class string or an object.',
                ['model' => $model, 'action' => $action, 'user' => $user]
            );
            if (!is_null($this->debugCollector)) {
                $this->debugCollector->addPermissionCheck(
                    false,
                    'model',
                    [
                        'model'  => $model,
                        'action' => $action,
                    ],
                    $user,
                    new RawPermissionTree([]),
                    [],
                    'There was an error checking the model access and access was therefore automatically denied. '
                        . 'Please refer to the error log for more information.'
                );
            }

            return false;
        }
        if (is_string($model) && !class_exists($model)) {
            $this->helper->handleError(
                'Error checking model access: the model parameter is a class string but the class could not be found.',
                ['model' => $model, 'action' => $action, 'user' => $user]
            );
            if (!is_null($this->debugCollector)) {
                $this->debugCollector->addPermissionCheck(
                    false,
                    'model',
                    [
                        'model'  => $model,
                        'action' => $action,
                    ],
                    $user,
                    new RawPermissionTree([]),
                    [],
                    'There was an error checking the model access and access was therefore automatically denied. '
                        . 'Please refer to the error log for more information.'
                );
            }

            return false;
        }
        if (!$action) {
            $this->helper->handleError(
                'Error checking model access: the action parameter cannot be empty.',
                ['model' => $model, 'action' => $action, 'user' => $user]
            );
            if (!is_null($this->debugCollector)) {
                $this->debugCollector->addPermissionCheck(
                    false,
                    'model',
                    [
                        'model'  => $model,
                        'action' => $action,
                    ],
                    $user,
                    new RawPermissionTree([]),
                    [],
                    'There was an error checking the model access and access was therefore automatically denied. '
                        . 'Please refer to the error log for more information.'
                );
            }

            return false;
        }
        if (!is_string($user) && !is_object($user)) {
            $this->helper->handleError(
                'Error checking model access: the user parameter must be either a string or an object.',
                ['model' => $model, 'action' => $action, 'user' => $user]
            );
            if (!is_null($this->debugCollector)) {
                $this->debugCollector->addPermissionCheck(
                    false,
                    'model',
                    [
                        'model'  => $model,
                        'action' => $action,
                    ],
                    $user,
                    new RawPermissionTree([]),
                    [],
                    'There was an error checking the model access and access was therefore automatically denied. '
                        . 'Please refer to the error log for more information.'
                );
            }

            return false;
        }

        $permissions = $this->getModelPermissions($model);
        if (array_key_exists($action, $permissions)) {
            $context = ['model' => $model, 'user' => $user];
            $access = $this->logicalAuthorization->checkAccess(new RawPermissionTree($permissions[$action]), $context);

            if (!is_null($this->debugCollector)) {
                $this->debugCollector->addPermissionCheck(
                    $access,
                    'model',
                    [
                        'model'  => $model,
                        'action' => $action,
                    ],
                    $user,
                    new RawPermissionTree($permissions[$action]),
                    $context
                );
            }

            return $access;
        }

        if (!is_null($this->debugCollector)) {
            $this->debugCollector->addPermissionCheck(
                true,
                'model',
                [
                    'model'  => $model,
                    'action' => $action,
                ],
                $user,
                new RawPermissionTree([]),
                [],
                sprintf(
                    'No permissions were found for the action "%s" on this model. Access was therefore automatically '
                        . 'granted.',
                    $action
                )
            );
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function checkFieldAccess($model, string $fieldName, string $action, $user = null): bool
    {
        if ($model instanceof ModelDecoratorInterface) {
            $model = $model->getModel();
        }
        if ($user instanceof ModelDecoratorInterface) {
            $user = $user->getModel();
        }

        if (is_null($user)) {
            $user = $this->helper->getCurrentUser();
            if (is_null($user)) {
                if (!is_null($this->debugCollector)) {
                    $this->debugCollector->addPermissionCheck(
                        true,
                        'field',
                        [
                            'model'  => $model,
                            'field'  => $fieldName,
                            'action' => $action,
                        ],
                        $user,
                        new RawPermissionTree([]),
                        [],
                        'No user was available during this permission check (not even an anonymous user). This usually '
                            . 'happens during unit testing. Access was therefore automatically granted.'
                    );
                }

                return true;
            }
        }

        if (!is_string($model) && !is_object($model)) {
            $this->helper->handleError(
                'Error checking field access: the model parameter must be either a class string or an object.',
                ['model' => $model, 'field name' => $fieldName, 'action' => $action, 'user' => $user]
            );
            if (!is_null($this->debugCollector)) {
                $this->debugCollector->addPermissionCheck(
                    false,
                    'field',
                    [
                        'model'  => $model,
                        'field'  => $fieldName,
                        'action' => $action,
                    ],
                    $user,
                    new RawPermissionTree([]),
                    [],
                    'There was an error checking the field access and access was therefore automatically denied. '
                        . 'Please refer to the error log for more information.'
                );
            }

            return false;
        }
        if (is_string($model) && !class_exists($model)) {
            $this->helper->handleError(
                'Error checking field access: the model parameter is a class string but the class could not be found.',
                ['model' => $model, 'field name' => $fieldName, 'action' => $action, 'user' => $user]
            );
            if (!is_null($this->debugCollector)) {
                $this->debugCollector->addPermissionCheck(
                    false,
                    'field',
                    [
                        'model'  => $model,
                        'field'  => $fieldName,
                        'action' => $action,
                    ],
                    $user,
                    new RawPermissionTree([]),
                    [],
                    'There was an error checking the field access and access was therefore automatically denied. '
                        . 'Please refer to the error log for more information.'
                );
            }

            return false;
        }
        if (!$fieldName) {
            $this->helper->handleError(
                'Error checking field access: the fieldName parameter cannot be empty.',
                ['model' => $model, 'field name' => $fieldName, 'action' => $action, 'user' => $user]
            );
            if (!is_null($this->debugCollector)) {
                $this->debugCollector->addPermissionCheck(
                    false,
                    'field',
                    [
                        'model'  => $model,
                        'field'  => $fieldName,
                        'action' => $action,
                    ],
                    $user,
                    new RawPermissionTree([]),
                    [],
                    'There was an error checking the field access and access was therefore automatically denied. '
                        . 'Please refer to the error log for more information.'
                );
            }

            return false;
        }
        if (!$action) {
            $this->helper->handleError(
                'Error checking field access: the action parameter cannot be empty.',
                ['model' => $model, 'field name' => $fieldName, 'action' => $action, 'user' => $user]
            );
            if (!is_null($this->debugCollector)) {
                $this->debugCollector->addPermissionCheck(
                    false,
                    'field',
                    [
                        'model'  => $model,
                        'field'  => $fieldName,
                        'action' => $action,
                    ],
                    $user,
                    new RawPermissionTree([]),
                    [],
                    'There was an error checking the field access and access was therefore automatically denied. '
                        . 'Please refer to the error log for more information.'
                );
            }

            return false;
        }
        if (!is_string($user) && !is_object($user)) {
            $this->helper->handleError(
                'Error checking field access: the user parameter must be either a string or an object.',
                ['model' => $model, 'field name' => $fieldName, 'action' => $action, 'user' => $user]
            );
            if (!is_null($this->debugCollector)) {
                $this->debugCollector->addPermissionCheck(
                    false,
                    'field',
                    [
                        'model'  => $model,
                        'field'  => $fieldName,
                        'action' => $action,
                    ],
                    $user,
                    new RawPermissionTree([]),
                    [],
                    'There was an error checking the field access and access was therefore automatically denied. '
                        . 'Please refer to the error log for more information.'
                );
            }

            return false;
        }

        $permissions = $this->getModelPermissions($model);
        if (
            !empty($permissions['fields'][$fieldName])
            && array_key_exists($action, $permissions['fields'][$fieldName])
        ) {
            $context = ['model' => $model, 'user' => $user];
            $access = $this->logicalAuthorization->checkAccess(
                new RawPermissionTree($permissions['fields'][$fieldName][$action]),
                $context
            );

            if (!is_null($this->debugCollector)) {
                $this->debugCollector->addPermissionCheck(
                    $access,
                    'field',
                    [
                        'model'  => $model,
                        'field'  => $fieldName,
                        'action' => $action,
                    ],
                    $user,
                    new RawPermissionTree($permissions['fields'][$fieldName][$action]),
                    $context
                );
            }

            return $access;
        }

        if (!is_null($this->debugCollector)) {
            $this->debugCollector->addPermissionCheck(
                true,
                'field',
                [
                    'model'  => $model,
                    'field'  => $fieldName,
                    'action' => $action,
                ],
                $user,
                new RawPermissionTree([]),
                [],
                sprintf(
                    'No permissions were found for the action "%s" on this model and field. Access was therefore '
                        . 'automatically granted.',
                    $action
                )
            );
        }

        return true;
    }

    /**
     * @internal
     *
     * @param object|string $model
     *
     * @return array|string|bool
     */
    protected function getModelPermissions($model)
    {
        $tree = $this->treeBuilder->getTree();
        $psrClass = '';
        if (is_string($model)) {
            $psrClass = $model;
        } elseif (is_object($model)) {
            $psrClass = get_class($model);
        }

        if (!empty($tree['models']) && array_key_exists($psrClass, $tree['models'])) {
            return $tree['models'][$psrClass];
        }

        return [];
    }
}
