# DatabaseTablesSize card for Laravel Pulse

This card will show you database tables size.

![Screenshot from 2024-02-05 13-43-42](https://github.com/eusonlito/LaravelPulseDatabaseTablesSize/assets/644551/21f71ca7-019a-41c4-a148-f5e28c9d86c2)

## Installation

Require the package with Composer:

```shell
composer require eusonlito/laravel-pulse-database-tables-size:dev-master
```

## Register the recorder

```diff
return [
    // ...
    
    'recorders' => [
+        \EuSonLito\LaravelPulse\DatabaseTablesSize\Recorders\DatabaseTablesSizeRecorder::class => [
+            'enabled' => env('PULSE_DATABASE_TABLES_SIZE_ENABLED', true),
+            'sample_rate' => env('PULSE_DATABASE_TABLES_SIZE_SAMPLE_RATE', 1),
+            'connections' => [env('DB_CONNECTION', 'mysql')],
+            'ignore' => [
+                '#^/pulse$#', // Pulse dashboard...
+            ],
+        ],
    ]
]
```

You also need to be running [the `pulse:check` command](https://laravel.com/docs/10.x/pulse#dashboard-cards).

## Add to your dashboard

To add the card to the Pulse dashboard, you must first [publish the vendor view](https://laravel.com/docs/10.x/pulse#dashboard-customization).

Then, you can modify the `dashboard.blade.php` file:

```diff
<x-pulse>
+    <livewire:database-tables-size cols="6" rows="8" />

    <livewire:pulse.servers cols="full" />

    <livewire:pulse.usage cols="4" rows="2" />

    <livewire:pulse.queues cols="4" />

    <livewire:pulse.cache cols="4" />

    <livewire:pulse.slow-queries cols="8" />

    <livewire:pulse.exceptions cols="6" />

    <livewire:pulse.slow-requests cols="6" />

    <livewire:pulse.slow-jobs cols="6" />

    <livewire:pulse.slow-outgoing-requests cols="6" />
</x-pulse>
```

That's it!

