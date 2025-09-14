<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__.'/app',
        __DIR__.'/config',
        __DIR__.'/database',
        __DIR__.'/routes',
        __DIR__.'/tests',
    ]);

    $rectorConfig->sets([
        \Rector\Set\ValueObject\LevelSetList::UP_TO_PHP_82,
        \Rector\Set\ValueObject\SetList::CODE_QUALITY,
        \Rector\Set\ValueObject\SetList::DEAD_CODE,
        \Rector\Set\ValueObject\SetList::EARLY_RETURN,
        \Rector\Set\ValueObject\SetList::TYPE_DECLARATION,
    ]);

    // PHPUnit rules commented out due to missing dependencies
    // $rectorConfig->rules([
    //     \Rector\PhpUnit\Rector\ClassMethod\AddDoesNotPerformAssertionsToNonAssertingTestRector::class,
    //     \Rector\PhpUnit\Rector\MethodCall\AssertEqualsToSameRector::class,
    //     \Rector\PhpUnit\Rector\MethodCall\AssertSameTrueFalseToAssertTrueFalseRector::class,
    // ]);
};
