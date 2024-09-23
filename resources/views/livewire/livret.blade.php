<div>
    @if ($this->updateAction->isVisible())
            {{ $this->updateAction }}
    @endif

    @component('fcmcommun::treeview.treeview', ['livret' => $livret])
    @endcomponent

    <x-filament-actions::modals />

</div>
