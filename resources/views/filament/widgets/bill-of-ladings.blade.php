{{-- File: resources/views/filament/widgets/bill-of-ladings.blade.php
     Responsibility: Dashboard cards linking to the Bill of Ladings lists.
     What it does: one card per shipment list (Export, Import) carrying the
       list's icon and a one-line description, each linking to its index page.
     How to use: rendered by App\Filament\Widgets\BillOfLadingsWidget.
     How to extend: copy a card block and point it at another resource. --}}
<x-filament-widgets::widget>
    <x-filament::section heading="Bill of Ladings">
        <div class="grid gap-4 sm:grid-cols-2">
            <a
                href="{{ $exportUrl }}"
                class="flex items-center gap-4 rounded-xl bg-gray-50 p-4 ring-1 ring-gray-950/5 transition hover:bg-gray-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 dark:bg-white/5 dark:ring-white/10 dark:hover:bg-white/10"
            >
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                    @svg('heroicon-o-arrow-up-tray', 'h-5 w-5')
                </span>
                <span class="min-w-0">
                    <span class="block font-semibold text-gray-950 dark:text-white">Export</span>
                    <span class="block text-sm text-gray-500 dark:text-gray-400">Export shipment list</span>
                </span>
            </a>

            <a
                href="{{ $importUrl }}"
                class="flex items-center gap-4 rounded-xl bg-gray-50 p-4 ring-1 ring-gray-950/5 transition hover:bg-gray-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 dark:bg-white/5 dark:ring-white/10 dark:hover:bg-white/10"
            >
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                    @svg('heroicon-o-arrow-down-tray', 'h-5 w-5')
                </span>
                <span class="min-w-0">
                    <span class="block font-semibold text-gray-950 dark:text-white">Import</span>
                    <span class="block text-sm text-gray-500 dark:text-gray-400">Import shipment list</span>
                </span>
            </a>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
