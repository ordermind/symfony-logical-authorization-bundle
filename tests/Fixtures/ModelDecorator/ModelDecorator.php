<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Test\Fixtures\ModelDecorator;

use Ordermind\LogicalAuthorizationBundle\Interfaces\ModelDecoratorInterface;

class ModelDecorator implements ModelDecoratorInterface
{
    /**
     * @var object
     */
    protected $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    public function getModel()
    {
        return $this->model;
    }
}
