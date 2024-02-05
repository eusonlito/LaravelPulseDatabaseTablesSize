<?php declare(strict_types=1);

namespace EuSonLito\LaravelPulse\DatabaseTablesSize\Livewire;

use Illuminate\Support\Facades\View as ViewFacace;
use Illuminate\View\View;
use Laravel\Pulse\Facades\Pulse;
use Laravel\Pulse\Livewire\Card;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Url;

class DatabaseTablesSize extends Card
{
    /**
     * @var string
     */
    #[Url(as: 'database-tables-size')]
    public string $connection = 'mysql';

    /**
     * @return \Illuminate\View\View
     */
    #[Lazy]
    public function render(): View
    {
        $sizes = $this->sizesLoad();

        return ViewFacace::make('database-tables-size::livewire.card', [
            'connections' => $this->connections($sizes),
            'sizes' => $this->sizes($sizes),
        ]);
    }

    /**
     * @param array $sizes
     *
     * @return array
     */
    protected function connections(array $sizes): array
    {
        $keys = array_keys($sizes);

        return array_combine($keys, $keys);
    }

    /**
     * @return array
     */
    protected function sizesLoad(): array
    {
        return ($values = Pulse::values('database-tables-size', ['result'])->first())
            ? json_decode($values->value, true, JSON_THROW_ON_ERROR)
            : [];
    }

    /**
     * @param array $sizes
     *
     * @return array
     */
    protected function sizes(array $sizes): array
    {
        return $sizes[$this->connection] ?? array_values($sizes)[0] ?? [];
    }
}
