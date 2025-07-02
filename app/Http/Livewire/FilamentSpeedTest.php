<?php
 
 namespace Modules\FcmCommun\Http\Livewire;
 
use Livewire\Component;
use Illuminate\Support\Facades\Log;
 
class FilamentSpeedTest extends Component
{
    public $counter = 0;
    public $lastTime = null;
    
    public function increment()
    {
        $start = microtime(true);
        
        // Test différents éléments
        $times = [];
        
        // 1. Simple increment
        $startSimple = microtime(true);
        $this->counter++;
        $times['increment'] = round((microtime(true) - $startSimple) * 1000, 3);
        
        // 2. Auth check
        $startAuth = microtime(true);
        $user = auth()->check();
        $times['auth'] = round((microtime(true) - $startAuth) * 1000, 3);
        
        // 3. Filament check
        $startFilament = microtime(true);
        $panels = \Filament\Facades\Filament::getCurrentPanel();
        $times['filament'] = round((microtime(true) - $startFilament) * 1000, 3);
        
        $total = round((microtime(true) - $start) * 1000, 2);
        $this->lastTime = $total;
        
        Log::info("Filament Speed Test: {$total}ms", $times);
    }
    
    public function render()
    {

        return view('fcmcommun::livewire.filament-speed-test');
    }
}
