<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Routing;

use Symfony\Component\Routing\Loader\AnnotationFileLoader as AnnotationFileLoaderBase;

/**
 * {@inheritdoc}
 */
class AnnotationFileLoader extends AnnotationFileLoaderBase
{
    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null): bool
    {
        return
            is_string($resource)
            && 'php' === pathinfo($resource, PATHINFO_EXTENSION)
            && 'logauth_annotation' === $type;
    }
}
