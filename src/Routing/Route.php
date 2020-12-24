<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Routing;

use Ordermind\LogicalPermissions\PermissionTree\RawPermissionTree;
use Symfony\Component\Routing\CompiledRoute;
use Symfony\Component\Routing\Route as RouteBase;

/**
 * Overridden route class that allows for having rawPermissionTree in a route.
 */
class Route extends RouteBase implements RouteInterface
{
    private string $path = '/';

    private ?string $host = null;

    private array $schemes = [];

    private array $methods = [];

    private array $defaults = [];

    private array $requirements = [];

    private array $options = [];

    private ?CompiledRoute $compiled = null;

    private ?string $condition = null;

    private ?RawPermissionTree $rawPermissionTree = null;

    /**
     * @internal
     *
     * @param string                 $path
     * @param array                  $defaults          (optional)
     * @param array                  $requirements      (optional)
     * @param array                  $options           (optional)
     * @param string|null            $host              (optional)
     * @param array                  $schemes           (optional)
     * @param array                  $methods           (optional)
     * @param string|null            $condition         (optional)
     * @param RawPermissionTree|null $rawPermissionTree (optional)
     */
    public function __construct(
        string $path,
        array $defaults = [],
        array $requirements = [],
        array $options = [],
        ?string $host = null,
        array $schemes = [],
        array $methods = [],
        ?string $condition = null,
        ?RawPermissionTree $rawPermissionTree = null
    ) {
        $this->setPath($path);
        $this->setDefaults($defaults);
        $this->setRequirements($requirements);
        $this->setOptions($options);
        $this->setHost($host);
        $this->setSchemes($schemes);
        $this->setMethods($methods);
        $this->setCondition($condition);
        if ($rawPermissionTree) {
            $this->setRawPermissionTree($rawPermissionTree);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function __serialize(): array
    {
        return [
            'path'               => $this->path,
            'host'               => $this->host,
            'defaults'           => $this->defaults,
            'requirements'       => $this->requirements,
            'options'            => $this->options,
            'schemes'            => $this->schemes,
            'methods'            => $this->methods,
            'condition'          => $this->condition,
            'compiled'           => $this->compiled,
            'permissions'        => $this->rawPermissionTree->getValue(),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function __unserialize(array $data): void
    {
        $this->path = $data['path'];
        $this->host = $data['host'];
        $this->defaults = $data['defaults'];
        $this->requirements = $data['requirements'];
        $this->options = $data['options'];
        $this->schemes = $data['schemes'];
        $this->methods = $data['methods'];

        if (isset($data['condition'])) {
            $this->condition = $data['condition'];
        }
        if (isset($data['compiled'])) {
            $this->compiled = $data['compiled'];
        }
        if (isset($data['permissions'])) {
            $this->rawPermissionTree = new RawPermissionTree($data['permissions']);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setRawPermissionTree(RawPermissionTree $rawPermissionTree)
    {
        $this->rawPermissionTree = $rawPermissionTree;
    }

    /**
     * {@inheritDoc}
     */
    public function getRawPermissionTree(): ?RawPermissionTree
    {
        return $this->rawPermissionTree;
    }
}
