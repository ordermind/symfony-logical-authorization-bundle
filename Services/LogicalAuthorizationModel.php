<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Services;

use Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorizationInterface;
use Ordermind\LogicalAuthorizationBundle\Services\PermissionTreeBuilderInterface;
use Ordermind\LogicalAuthorizationBundle\Services\HelperInterface;
use Ordermind\LogicalAuthorizationBundle\DataCollector\CollectorInterface;
use Ordermind\LogicalAuthorizationBundle\Interfaces\ModelDecoratorInterface;

/**
 * {@inheritdoc}
 */
class LogicalAuthorizationModel implements LogicalAuthorizationModelInterface
{
    /**
     * @var Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorizationInterface
     */
    protected $la;

    /**
     * @var Ordermind\LogicalAuthorizationBundle\Services\PermissionTreeBuilderInterface
     */
    protected $treeBuilder;

    /**
     * @var Ordermind\LogicalAuthorizationBundle\Services\HelperInterface
     */
    protected $helper;

    /**
     * @var Ordermind\LogicalAuthorizationBundle\DataCollector\CollectorInterface
     */
    protected $debugCollector;

    /**
     * @internal
     *
     * @param Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorizationInterface  $la             LogicalAuthorization service
     * @param Ordermind\LogicalAuthorizationBundle\Services\PermissionTreeBuilderInterface $treeBuilder    Permission tree builder service
     * @param Ordermind\LogicalAuthorizationBundle\Services\HelperInterface                $helper         LogicalAuthorization helper service
     * @param Ordermind\LogicalAuthorizationBundle\DataCollector\CollectorInterface        $debugCollector (optional) Collector service
     */
    public function __construct(LogicalAuthorizationInterface $la, PermissionTreeBuilderInterface $treeBuilder, HelperInterface $helper, CollectorInterface $debugCollector = null)
    {
        $this->la = $la;
        $this->treeBuilder = $treeBuilder;
        $this->helper = $helper;
        $this->debugCollector = $debugCollector;
    }

    /**
     * {@inheritdoc}
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
        $reflectionClass = new \ReflectionClass($model);
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
     * {@inheritdoc}
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
                    $this->debugCollector->addPermissionCheck(true, 'model', array('model' => $model, 'action' => $action), $user, [], [], 'No user was available during this permission check (not even an anonymous user). This usually happens during unit testing. Access was therefore automatically granted.');
                }

                return true;
            }
        }

        if (!is_string($model) && !is_object($model)) {
            $this->helper->handleError('Error checking model access: the model parameter must be either a class string or an object.', ['model' => $model, 'action' => $action, 'user' => $user]);
            if (!is_null($this->debugCollector)) {
                $this->debugCollector->addPermissionCheck(false, 'model', array('model' => $model, 'action' => $action), $user, [], [], 'There was an error checking the model access and access was therefore automatically denied. Please refer to the error log for more information.');
            }

            return false;
        }
        if (is_string($model) && !class_exists($model)) {
            $this->helper->handleError('Error checking model access: the model parameter is a class string but the class could not be found.', ['model' => $model, 'action' => $action, 'user' => $user]);
            if (!is_null($this->debugCollector)) {
                $this->debugCollector->addPermissionCheck(false, 'model', array('model' => $model, 'action' => $action), $user, [], [], 'There was an error checking the model access and access was therefore automatically denied. Please refer to the error log for more information.');
            }

            return false;
        }
        if (!$action) {
            $this->helper->handleError('Error checking model access: the action parameter cannot be empty.', ['model' => $model, 'action' => $action, 'user' => $user]);
            if (!is_null($this->debugCollector)) {
                $this->debugCollector->addPermissionCheck(false, 'model', array('model' => $model, 'action' => $action), $user, [], [], 'There was an error checking the model access and access was therefore automatically denied. Please refer to the error log for more information.');
            }

            return false;
        }
        if (!is_string($user) && !is_object($user)) {
            $this->helper->handleError('Error checking model access: the user parameter must be either a string or an object.', ['model' => $model, 'action' => $action, 'user' => $user]);
            if (!is_null($this->debugCollector)) {
                $this->debugCollector->addPermissionCheck(false, 'model', array('model' => $model, 'action' => $action), $user, [], [], 'There was an error checking the model access and access was therefore automatically denied. Please refer to the error log for more information.');
            }

            return false;
        }

        $permissions = $this->getModelPermissions($model);
        if (array_key_exists($action, $permissions)) {
            $context = ['model' => $model, 'user' => $user];
            $access = $this->la->checkAccess($permissions[$action], $context);

            if (!is_null($this->debugCollector)) {
                $this->debugCollector->addPermissionCheck($access, 'model', array('model' => $model, 'action' => $action), $user, $permissions[$action], $context);
            }

            return $access;
        }

        if (!is_null($this->debugCollector)) {
            $this->debugCollector->addPermissionCheck(true, 'model', array('model' => $model, 'action' => $action), $user, [], [], "No permissions were found for the action \"$action\" on this model. Access was therefore automatically granted.");
        }

        return true;
    }

    /**
     * {@inheritdoc}
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
                    $this->debugCollector->addPermissionCheck(true, 'field', array('model' => $model, 'field' => $fieldName, 'action' => $action), $user, [], [], 'No user was available during this permission check (not even an anonymous user). This usually happens during unit testing. Access was therefore automatically granted.');
                }

                return true;
            }
        }

        if (!is_string($model) && !is_object($model)) {
            $this->helper->handleError('Error checking field access: the model parameter must be either a class string or an object.', ['model' => $model, 'field name' => $fieldName, 'action' => $action, 'user' => $user]);
            if (!is_null($this->debugCollector)) {
                $this->debugCollector->addPermissionCheck(false, 'field', array('model' => $model, 'field' => $fieldName, 'action' => $action), $user, [], [], 'There was an error checking the field access and access was therefore automatically denied. Please refer to the error log for more information.');
            }

            return false;
        }
        if (is_string($model) && !class_exists($model)) {
            $this->helper->handleError('Error checking field access: the model parameter is a class string but the class could not be found.', ['model' => $model, 'field name' => $fieldName, 'action' => $action, 'user' => $user]);
            if (!is_null($this->debugCollector)) {
                $this->debugCollector->addPermissionCheck(false, 'field', array('model' => $model, 'field' => $fieldName, 'action' => $action), $user, [], [], 'There was an error checking the field access and access was therefore automatically denied. Please refer to the error log for more information.');
            }

            return false;
        }
        if (!$fieldName) {
            $this->helper->handleError('Error checking field access: the field_name parameter cannot be empty.', ['model' => $model, 'field name' => $fieldName, 'action' => $action, 'user' => $user]);
            if (!is_null($this->debugCollector)) {
                $this->debugCollector->addPermissionCheck(false, 'field', array('model' => $model, 'field' => $fieldName, 'action' => $action), $user, [], [], 'There was an error checking the field access and access was therefore automatically denied. Please refer to the error log for more information.');
            }

            return false;
        }
        if (!$action) {
            $this->helper->handleError('Error checking field access: the action parameter cannot be empty.', ['model' => $model, 'field name' => $fieldName, 'action' => $action, 'user' => $user]);
            if (!is_null($this->debugCollector)) {
                $this->debugCollector->addPermissionCheck(false, 'field', array('model' => $model, 'field' => $fieldName, 'action' => $action), $user, [], [], 'There was an error checking the field access and access was therefore automatically denied. Please refer to the error log for more information.');
            }

            return false;
        }
        if (!is_string($user) && !is_object($user)) {
            $this->helper->handleError('Error checking field access: the user parameter must be either a string or an object.', ['model' => $model, 'field name' => $fieldName, 'action' => $action, 'user' => $user]);
            if (!is_null($this->debugCollector)) {
                $this->debugCollector->addPermissionCheck(false, 'field', array('model' => $model, 'field' => $fieldName, 'action' => $action), $user, [], [], 'There was an error checking the field access and access was therefore automatically denied. Please refer to the error log for more information.');
            }

            return false;
        }

        $permissions = $this->getModelPermissions($model);
        if (!empty($permissions['fields'][$fieldName]) && array_key_exists($action, $permissions['fields'][$fieldName])) {
            $context = ['model' => $model, 'user' => $user];
            $access = $this->la->checkAccess($permissions['fields'][$fieldName][$action], $context);

            if (!is_null($this->debugCollector)) {
                $this->debugCollector->addPermissionCheck($access, 'field', array('model' => $model, 'field' => $fieldName, 'action' => $action), $user, $permissions['fields'][$fieldName][$action], $context);
            }

            return $access;
        }

        if (!is_null($this->debugCollector)) {
            $this->debugCollector->addPermissionCheck(true, 'field', array('model' => $model, 'field' => $fieldName, 'action' => $action), $user, [], [], "No permissions were found for the action \"$action\" on this model and field. Access was therefore automatically granted.");
        }

        return true;
    }

    /**
     * @internal
     *
     * @param object $model
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
