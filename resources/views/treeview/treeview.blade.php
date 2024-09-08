@props(['livret'])

<div>
    <div>
        @foreach($livret['roots'] as $rootnode)
            @component('fcmcommun::treeview.treeview-node' , ['livret' => $livret , 'nodeid' => $rootnode ])
            @endcomponent
        @endforeach
    </div>
</div>