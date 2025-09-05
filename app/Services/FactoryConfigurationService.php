<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\Eloquent\Factories\Factory;

class FactoryConfigurationService
{
    private string $factoryClass;

    public function __construct(string $factoryClass = Factory::class)
    {
        $this->factoryClass = $factoryClass;
    }

    public function configureNaming(): void
    {
        $factoryClass = $this->factoryClass;
        $factoryClass::guessFactoryNamesUsing(
            function (string $modelName) {
                return 'Database\\Factories\\'.class_basename($modelName).'Factory';
            }
        );
    }
}
