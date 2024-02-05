<?php declare(strict_types=1);

namespace EuSonLito\LaravelPulse\DatabaseTablesSize;

use EuSonLito\LaravelPulse\DatabaseTablesSize\Livewire\DatabaseTablesSize;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Livewire\LivewireManager;

class DatabaseTablesSizeServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'database-tables-size');

        $this->callAfterResolving('livewire', function (LivewireManager $livewire, Application $app) {
            $livewire->component('database-tables-size', DatabaseTablesSize::class);
        });
    }
}
