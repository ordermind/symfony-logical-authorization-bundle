<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Helpers;

function iterable_to_array(iterable $iterable): array
{
    if (is_array($iterable)) {
        return array_values($iterable);
    }

    return iterator_to_array($iterable);
}
