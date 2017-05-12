<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\Functional\Services;

use Ordermind\LogicalAuthorizationBundle\Services\Decorator\ModelDecoratorInterface;
use Ordermind\LogicalAuthorizationBundle\Services\Decorator\RepositoryDecoratorInterface;

class LogicalAuthorizationMiscTest extends LogicalAuthorizationMiscBase {
  public function testOnLazyModelCollectionAlwaysAllow() {
    // The configuration "check_lazy_loaded_models" is not set to true in this test suite and therefore lazy loaded entities are not checked for authorization.
    $this->forbiddenEntityRepositoryDecorator->create()->save();
    $this->sendRequestAs('GET', '/test/count-forbidden-entities-lazy', [], static::$superadmin_user);
    $response = $this->client->getResponse();
    $this->assertEquals(200, $response->getStatusCode());
    $entities_count = $response->getContent();
    $this->assertEquals(1, $entities_count);
  }

  public function testRepositoryDecoratorFactory() {
    $this->assertTrue($this->testEntityRepositoryDecorator instanceof RepositoryDecoratorInterface);
  }

  public function testModelDecoratorFactory() {
    $modelDecorator = $this->testEntityRepositoryDecorator->create();
    $this->assertTrue($modelDecorator instanceof ModelDecoratorInterface);
  }
}
