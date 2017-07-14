<?php

namespace Ordermind\LogicalAuthorizationBundle\Routing;

use Symfony\Component\Routing\Loader\AnnotationFileLoader as AnnotationFileLoaderBase;

class AnnotationFileLoader extends AnnotationFileLoaderBase {
    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'php' === pathinfo($resource, PATHINFO_EXTENSION) && 'logauth_annotation' === $type;
    }
}
