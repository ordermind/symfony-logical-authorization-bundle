<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Routing;

use Symfony\Component\Routing\Route as RouteBase;

class Route extends RouteBase implements RouteInterface {
  /**
    * @var string
    */
  private $path = '/';

  /**
    * @var string
    */
  private $host = '';

  /**
    * @var array
    */
  private $schemes = array();

  /**
    * @var array
    */
  private $methods = array();

  /**
    * @var array
    */
  private $defaults = array();

  /**
    * @var array
    */
  private $requirements = array();

  /**
    * @var array
    */
  private $options = array();

  /**
    * @var null|CompiledRoute
    */
  private $compiled;

  /**
    * @var string
    */
  private $condition = '';

  private $permissions;

  public function __construct($path, array $defaults = [], array $requirements = [], array $options = [], $host = '', $schemes = [], $methods = [], $condition = '', $permissions = null) {
    $this->setPath($path);
    $this->setDefaults($defaults);
    $this->setRequirements($requirements);
    $this->setOptions($options);
    $this->setHost($host);
    $this->setSchemes($schemes);
    $this->setMethods($methods);
    $this->setCondition($condition);
    $this->setPermissions($permissions);
  }

  /**
   * {@inheritdoc}
   */
  public function serialize() {
    return serialize(array(
      'path' => $this->path,
      'host' => $this->host,
      'defaults' => $this->defaults,
      'requirements' => $this->requirements,
      'options' => $this->options,
      'schemes' => $this->schemes,
      'methods' => $this->methods,
      'condition' => $this->condition,
      'compiled' => $this->compiled,
      'permissions' => $this->permissions,
    ));
  }

  /**
   * {@inheritdoc}
   */
  public function unserialize($serialized) {
    $data = unserialize($serialized);
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
      $this->permissions = $data['permissions'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setPermissions($permissions) {
    $this->permissions = $permissions;
  }

  /**
   * {@inheritdoc}
   */
  public function getPermissions() {
    return $this->permissions;
  }
}
