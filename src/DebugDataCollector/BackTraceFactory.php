<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\DebugDataCollector;

class BackTraceFactory
{
    public function createBackTrace(): array
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 11);
        array_shift($backtrace);

        return $backtrace;
    }
}
