<!-- filepath: resources/views/livewire/filament-speed-test.blade.php -->
<div class="p-6">
    <h2 class="text-xl font-bold mb-4">Test de vitesse Filament + Livewire</h2>
    
    <div class="flex items-center space-x-4 mb-4">
        <x-filament::button wire:click="increment" color="primary">
            Tester vitesse ({{ $counter }})
        </x-filament::button>
        
        @if($lastTime)
            <div class="px-3 py-1 rounded {{ $lastTime > 500 ? 'bg-red-100 text-red-700' : ($lastTime > 200 ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700') }}">
                {{ $lastTime }}ms
            </div>
        @endif
        
        <div wire:loading class="text-blue-600">
            🔄 Processing...
        </div>
    </div>
    
    <div class="text-sm text-gray-600">
        <p>✅ < 200ms: Excellent</p>
        <p>⚠️ 200-500ms: Acceptable</p>
        <p>❌ > 500ms: Problématique</p>
    </div>
</div>
