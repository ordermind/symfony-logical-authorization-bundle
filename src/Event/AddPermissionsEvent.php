<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * {@inheritdoc}
 */
class AddPermissionsEvent extends Event implements AddPermissionsEventInterface
{
    /**
     * @var array
     */
    protected $tree = [];

    /**
     * @var string[]
     */
    protected $permissionKeys;

    /**
     * @internal
     *
     * @param string[] $permissionKeys array of valid permission keys
     */
    public function __construct(array $permissionKeys)
    {
        $this->permissionKeys = $permissionKeys;
    }

    /**
     * {@inheritdoc}
     */
    public function getTree(): array
    {
        return $this->tree;
    }

    /**
     * {@inheritdoc}
     */
    public function insertTree(array $tree)
    {
        $this->setTree($this->mergeTrees([$this->getTree(), $tree]));
    }

    /**
     * @internal
     *
     * @param array $tree
     */
    protected function setTree(array $tree)
    {
        $this->tree = $tree;
    }

    /**
     * @internal
     *
     * @param array $trees
     *
     * @return array
     */
    protected function mergeTrees(array $trees): array
    {
        if (count($trees) == 0) {
            return [];
        }

        $tree1 = array_shift($trees);
        while (count($trees)) {
            $tree2 = array_shift($trees);
            foreach ($tree2 as $key => $value) {
                if (in_array($key, $this->permissionKeys)) {
                    $tree1 = $tree2;
                    break;
                }
                if (isset($tree1[$key]) && is_array($value)) {
                    $tree1[$key] = $this->mergeTrees([$tree1[$key], $tree2[$key]]);
                    continue;
                }
                $tree1[$key] = $value;
            }
        }

        return $tree1;
    }
}
