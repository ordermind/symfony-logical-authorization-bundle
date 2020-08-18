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
    public function supports($resource, ?string $type = null): bool
    {
        if (!is_string($resource)) {
            return false;
        }

        if ('logauth_annotation' !== $type) {
            return false;
        }

        return 'php' === pathinfo($resource, PATHINFO_EXTENSION);
    }
}
