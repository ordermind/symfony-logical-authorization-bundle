<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Routing;

use Symfony\Component\Routing\Loader\AnnotationDirectoryLoader as AnnotationDirectoryLoaderBase;

/**
 * {@inheritdoc}
 */
class AnnotationDirectoryLoader extends AnnotationDirectoryLoaderBase
{
    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null): bool
    {
        if (!is_string($resource) || !is_string($type)) {
            return false;
        }

        if ('logauth_annotation' !== $type) {
            return false;
        }

        return parent::supports($resource, 'annotation');
    }
}
