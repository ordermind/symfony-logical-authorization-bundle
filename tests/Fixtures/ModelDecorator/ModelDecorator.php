<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Test\Fixtures\ModelDecorator;

use Ordermind\LogicalAuthorizationBundle\Interfaces\ModelDecoratorInterface;
use Ordermind\LogicalAuthorizationBundle\Interfaces\ModelInterface;

class ModelDecorator implements ModelDecoratorInterface
{
    /**
     * @var ModelInterface
     */
    protected $model;

    public function __construct(ModelInterface $model)
    {
        $this->model = $model;
    }

    public function getModel(): ModelInterface
    {
        return $this->model;
    }
}
