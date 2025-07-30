<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\attendance;  

class Map extends Component
{
    public function render()
    {
        $attendances =  attendance::with('user')->get();
        return view('livewire.map',[
        'attendances'=> $attendances
    ]);
    }
}
