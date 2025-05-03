<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="flex items-center justify-between gap-x-3 mt-6">
            <x-filament::button type="button" color="gray" wire:click="restoreDefaults"
                x-on:click="$dispatch('open-modal', { id: 'confirm-restore-defaults' })">
                Restore Defaults
            </x-filament::button>

            <x-filament::button type="submit">
                Save Settings
            </x-filament::button>
        </div>
    </form>

    <x-filament::modal id="confirm-restore-defaults" icon="heroicon-o-exclamation-triangle" icon-color="warning"
        heading="Restore Default Settings"
        description="Are you sure you want to restore all settings to their default values? This action cannot be undone."
        width="md">
        <x-slot name="footerActions">
            <x-filament::button color="gray"
                x-on:click="$dispatch('close-modal', { id: 'confirm-restore-defaults' })">
                Cancel
            </x-filament::button>

            <x-filament::button color="warning" wire:click="restoreDefaults"
                x-on:click="$dispatch('close-modal', { id: 'confirm-restore-defaults' })">
                Yes, Restore Defaults
            </x-filament::button>
        </x-slot>
    </x-filament::modal>
</x-filament-panels::page>
