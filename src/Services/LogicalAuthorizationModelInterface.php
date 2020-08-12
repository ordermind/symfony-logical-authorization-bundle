<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Services;

/**
 * Service for checking access to models and their fields.
 */
interface LogicalAuthorizationModelInterface
{
    /**
     * Gets all available model and field actions on a model for a given user.
     *
     * This method is primarily meant to facilitate client-side authorization by providing a map of all available
     * actions on a model. The map has the following structure:
     * [
     *    'model_action1' => 'model_action1',
     *    'model_action3' => 'model_action3',
     *    'fields' => [
     *        'field_name1' => [
     *            'field_action1' => 'field_action1',
     *        ],
     *    ],
     * ]
     *
     * @param object|string $model        a model object or class string
     * @param array         $modelActions a list of model actions that should be evaluated
     * @param array         $fieldActions a list of field actions that should be evaluated
     * @param object|string $user         (optional) Either a user object or a string to signify an anonymous user. If
     *                                    no user is supplied, the current user will be used.
     *
     * @return array A map of available actions
     */
    public function getAvailableActions($model, array $modelActions, array $fieldActions, $user = null): array;

    /**
     * Checks access for an action on a model for a given user.
     *
     * If something goes wrong an error will be logged and the method will return FALSE. If no permissions are defined
     * for this action on the provided model it will return TRUE.
     *
     * @param object|string $model  a model object or class string
     * @param string        $action examples of model actions are "create", "read", "update" and "delete"
     * @param object|string $user   (optional) Either a user object or a string to signify an anonymous user. If no user
     *                              is supplied, the current user will be used.
     *
     * @return bool TRUE if access is granted or FALSE if access is denied
     */
    public function checkModelAccess($model, string $action, $user = null): bool;

    /**
     * Checks access for an action on a specific field in a model for a given user.
     *
     * If something goes wrong an error will be logged and the method will return FALSE. If no permissions are defined
     * for this action on the provided field and model it will return TRUE.
     *
     * @param object|string $model     a model object or class string
     * @param string        $fieldName the name of the field
     * @param string        $action    examples of field actions are "get" and "set"
     * @param object|string $user      (optional) Either a user object or a string to signify an anonymous user. If no
     *                                 user is supplied, the current user will be used.
     *
     * @return bool TRUE if access is granted or FALSE if access is denied
     */
    public function checkFieldAccess($model, string $fieldName, string $action, $user = null): bool;
}
