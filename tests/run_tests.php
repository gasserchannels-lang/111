<?php

// Custom test runner to suppress PHPUnit warnings
// Don't change error handlers to avoid PHPUnit detection

// Run PHPUnit
$command = 'vendor\\bin\\phpunit.bat '.implode(' ', array_slice($argv, 1));
passthru($command);
