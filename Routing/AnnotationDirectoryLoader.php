<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Routing;

use Symfony\Component\Routing\Loader\AnnotationDirectoryLoader as AnnotationDirectoryLoaderBase;

class AnnotationDirectoryLoader extends AnnotationDirectoryLoaderBase
{
  /**
   * {@inheritdoc}
   */
    public function supports($resource, $type = null): bool
    {
        if (!is_string($resource)) {
            return false;
        }

        try {
            $path = $this->locator->locate($resource);
        } catch (\Exception $e) {
            return false;
        }

        return is_dir($path) && 'logauth_annotation' === $type;
    }
}
