@aware(['livret'])
@props(['nodeid'])

<div>
    @php
    //  dd($livret);
        $node = $livret['nodes'][$nodeid];
        $opened = array_key_exists('opened', $node['state']) ? $node['state']['opened'] : false;
        $children = array_key_exists('children', $node) ? $node['children'] : [];

        $xmodelkey = "livret.nodes." . $nodeid . ".state.checked";
    @endphp

    <div>
        <div class="flex">
            <div wire:click="toggleNode('{{ $nodeid }}')">
                @if($children != [] )               
                    @if($opened)
                    <x-heroicon-o-chevron-down class="w-6 h-6" />
                    @else
                    <x-heroicon-o-chevron-right class="w-6 h-6" />
                    @endif
                @endif
            </div>
            <input type="checkbox" class="rounded w-5 h-5" id="nodeid" wire:model.live="{{ $xmodelkey }}"><span 
            {{-- class="border-solid border-2 
            @switch ($node['type'])
                @case('fonctions')
                    text-blue border-rose
                @break
                @case('competences')
                    text-violet indent-4 border-indigo-500/50
                @break
                @case('savoirfaires')
                    text-red-600 indent-6
                @break
                @case('TACHE')
                    text-green-600 indent-8
                @break
                @case('STAGE')
                    text-sky-600 indent-10
                @break
            @endswitch
            " --}}
            >{{ $node['text'] }}</span>
            @if(array_key_exists('date_valid', $node) && $node['date_valid']!=NULL )
            {{-- {{dd($node)}} --}}
                ({{$node['date_valid'].' par '.$node['valideur']}})
            @endif
        </div>
        {{-- ({{dd($node)}}) --}}
        @if($opened)
            @if($children != [])
            <div class="flex">
                @foreach($children as $childnode)
                <div class="w-1/5"><span>-</span></div>
                    <div class="w-4/5">
                        @component('fcmcommun::treeview.treeview-node' , ['livret' => $livret , 'nodeid' => $childnode ])
                        @endcomponent
                    </div>    
                @endforeach
                </div>

            @endif
        @endif
    </div>
</div>
