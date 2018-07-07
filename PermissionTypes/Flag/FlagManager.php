<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\PermissionTypes\Flag;

use Ordermind\LogicalAuthorizationBundle\PermissionTypes\Flag\FlagInterface;
use Ordermind\LogicalAuthorizationBundle\PermissionTypes\Flag\Exceptions\FlagNotRegisteredException;

/**
 * {@inheritdoc}
 */
class FlagManager implements FlagManagerInterface
{

    protected $flags = [];

  /**
   * {@inheritdoc}
   */
    public function getName(): string
    {
        return 'flag';
    }

  /**
   * {@inheritdoc}
   */
    public function addFlag(FlagInterface $flag)
    {
        $name = $flag->getName();
        if (!is_string($name)) {
            throw new \InvalidArgumentException('The name of a flag must be a string.');
        }
        if (!$name) {
            throw new \InvalidArgumentException('The name of a flag cannot be empty.');
        }
        if ($this->flagExists($name)) {
            throw new \InvalidArgumentException("The flag \"$name\" already exists! If you want to change the class that handles a flag, you may do so by overriding the service definition for that flag.");
        }

        $flags = $this->getFlags();
        $flags[$name] = $flag;
        $this->setFlags($flags);
    }

  /**
   * {@inheritdoc}
   */
    public function removeFlag(string $name)
    {
        if (!$name) {
            throw new \InvalidArgumentException('The name parameter cannot be empty.');
        }
        if (!$this->flagExists($name)) {
            throw new FlagNotRegisteredException("The flag \"$name\" has not been registered. Please use the 'logauth.tag.permission_type.flag' service tag to register a flag.");
        }

        $flags = $this->getFlags();
        unset($flags[$name]);
        $this->setFlags($flags);
    }

  /**
   * {@inheritdoc}
   */
    public function getFlags(): array
    {
        return $this->flags;
    }

  /**
   * {@inheritdoc}
   */
    public function checkPermission(string $name, array $context): bool
    {
        if (!$name) {
            throw new \InvalidArgumentException('The name parameter cannot be empty.');
        }
        if (!$this->flagExists($name)) {
            throw new FlagNotRegisteredException("The flag \"$name\" has not been registered. Please use the 'logauth.tag.permission_type.flag' service tag to register a flag.");
        }

        $flags = $this->getFlags();

        return $flags[$name]->checkFlag($context);
    }

    /**
     * @internal
     *
     * @param array $flags
     */
    protected function setFlags(array $flags)
    {
        $this->flags = $flags;
    }

    /**
     * @internal
     *
     * @param string $name
     *
     * @return bool
     */
    protected function flagExists(string $name): bool
    {
        $flags = $this->getFlags();

        return isset($flags[$name]);
    }
}
