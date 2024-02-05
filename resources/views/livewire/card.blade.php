<x-pulse::card :cols="$cols" :rows="$rows" :class="$class">
    <x-pulse::card-header name="Databases Table Size">
        <x-slot:icon>
        </x-slot:icon>

        <x-slot:actions>
            <x-pulse::select wire:model.live="connection" label="Connection" :options="$connections" />
        </x-slot:actions>
    </x-pulse::card-header>

    <x-pulse::scroll :expand="$expand" wire:poll.1m="">
        @if (empty($sizes))

        <x-pulse::no-results />

        @else

        <x-pulse::table>
            <colgroup>
                <col width="100%" />
                <col width="0%" />
                <col width="0%" />
                <col width="0%" />
                <col width="0%" />
            </colgroup>

            <x-pulse::thead>
                <tr>
                    <x-pulse::th>Table</x-pulse::th>
                    <x-pulse::th class="text-right">Total</x-pulse::th>
                    <x-pulse::th class="text-right">Table</x-pulse::th>
                    <x-pulse::th class="text-right">Index</x-pulse::th>
                    <x-pulse::th class="text-right">Rows</x-pulse::th>
                </tr>
            </x-pulse::thead>

            <tbody>
                @foreach ($sizes as $size)

                <tr wire:key="{{ $size['table_name'] }}">
                    <x-pulse::td class="max-w-[1px]">
                        <code class="block text-xs text-gray-900 dark:text-gray-100 truncate">
                            {{ $size['table_name'] }}
                        </code>
                    </x-pulse::td>

                    <x-pulse::td numeric class="text-gray-700 dark:text-gray-300 font-bold">
                        {{ $size['total_size'] }} MB
                    </x-pulse::td>

                    <x-pulse::td numeric class="text-gray-700 dark:text-gray-300 font-bold">
                        {{ $size['table_size'] }} MB
                    </x-pulse::td>

                    <x-pulse::td numeric class="text-gray-700 dark:text-gray-300 font-bold">
                        {{ $size['index_size'] }} MB
                    </x-pulse::td>

                    <x-pulse::td numeric class="text-gray-700 dark:text-gray-300 font-bold">
                        {{ $size['table_rows'] }}
                    </x-pulse::td>
                </tr>

                @endforeach
            </tbody>
        </x-pulse::table>

        @endif
    </x-pulse::scroll>
</x-pulse::card>
