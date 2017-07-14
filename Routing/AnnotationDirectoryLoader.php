<?php

namespace Ordermind\LogicalAuthorizationBundle\Routing;

use Symfony\Component\Routing\Loader\AnnotationDirectoryLoader as AnnotationDirectoryLoaderBase;

class AnnotationDirectoryLoader extends AnnotationDirectoryLoaderBase {
    public function supports($resource, $type = null)
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