<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Routing;

use Symfony\Component\Routing\Loader\AnnotationDirectoryLoader as AnnotationDirectoryLoaderBase;

/**
 * {@inheritDoc}
 */
class AnnotationDirectoryLoader extends AnnotationDirectoryLoaderBase
{
    /**
     * {@inheritDoc}
     */
    public function supports($resource, ?string $type = null): bool
    {
        if (!is_string($resource)) {
            return false;
        }

        if ('logauth_annotation' !== $type) {
            return false;
        }

        return parent::supports($resource, 'annotation');
    }
}
