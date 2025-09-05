<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__.'/tests',
    ]);

    $rectorConfig->rules([
        \Rector\PhpUnit\Rector\ClassMethod\AddDoesNotPerformAssertionsToNonAssertingTestRector::class,
        \Rector\PhpUnit\Rector\MethodCall\AssertEqualsToSameRector::class,
        \Rector\PhpUnit\Rector\MethodCall\AssertSameTrueFalseToAssertTrueFalseRector::class,
    ]);
};
