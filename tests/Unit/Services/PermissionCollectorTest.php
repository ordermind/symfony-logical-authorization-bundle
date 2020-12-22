<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Test\Unit\Services;

use Generator;
use Ordermind\LogicalAuthorizationBundle\PermissionCheckers\RoleChecker;
use Ordermind\LogicalAuthorizationBundle\PermissionCheckers\SimpleConditionChecker\SimpleConditionCheckerManager;
use Ordermind\LogicalAuthorizationBundle\PermissionProviders\PermissionProviderInterface;
use Ordermind\LogicalAuthorizationBundle\Services\PermissionCollector;
use Ordermind\LogicalPermissions\PermissionCheckerLocator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

class PermissionCollectorTest extends TestCase
{
    /**
     * @dataProvider provideGetPermissionTree
     */
    public function testGetPermissionTree(array $expectedResult, array $trees)
    {
        $lpLocator = new PermissionCheckerLocator();

        $roleHierarchy = $this->prophesize(RoleHierarchyInterface::class)->reveal();
        $roleChecker = new RoleChecker($roleHierarchy);
        $lpLocator->add($roleChecker);

        $conditionManager = new SimpleConditionCheckerManager();
        $lpLocator->add($conditionManager);

        $permissionProviders = array_map(function (array $tree) {
            $mockProvider = $this->prophesize(PermissionProviderInterface::class);
            $mockProvider->getPermissionTree()->willReturn($tree);

            return $mockProvider->reveal();
        }, $trees);

        $permissionCollector = new PermissionCollector($permissionProviders, $lpLocator);

        $this->assertSame($expectedResult, $permissionCollector->getPermissionTree());
    }

    public function provideGetPermissionTree(): Generator
    {
        $tree1 = [
            'models' => [
                'testmodel' => [
                    'create' => [
                        'role' => 'role1',
                    ],
                    'read' => [
                        'condition' => [
                            'condition1',
                            'condition2',
                        ],
                    ],
                    'update' => [
                        'condition' => 'condition1',
                    ],
                    'fields' => [
                        'field1' => [
                            'get' => [
                                'role' => 'role1',
                            ],
                            'set' => [
                                'condition' => 'condition1',
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $tree2 = [
            'models' => [
                'testmodel' => [
                    'create' => [
                        'role' => [
                            'newrole1',
                            'newrole2',
                        ],
                    ],
                    'read' => [
                        'condition' => 'newcondition1',
                    ],
                    'fields' => [
                        'field1' => [
                            'get' => [
                                'OR' => [
                                    'role'      => 'newrole1',
                                    'condition' => 'newcondition1',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $expectedResult = [
            'models' => [
                'testmodel' => [
                    'create' => [
                        'role' => [
                            'newrole1',
                            'newrole2',
                        ],
                    ],
                    'read' => [
                        'condition' => 'newcondition1',
                    ],
                    'update' => [
                        'condition' => 'condition1',
                    ],
                    'fields' => [
                        'field1' => [
                            'get' => [
                                'OR' => [
                                    'role'      => 'newrole1',
                                    'condition' => 'newcondition1',
                                ],
                            ],
                            'set' => [
                                'condition' => 'condition1',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        yield [$expectedResult, [$tree1, $tree2]];
    }
}
