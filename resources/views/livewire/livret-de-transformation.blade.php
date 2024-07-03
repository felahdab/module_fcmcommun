@push(config('mingle.stack'))
    <script src="{!! asset('modules/fcmcommun/livret-de-transformation.js') !!}"></script>
    <link rel="stylesheet" href="{!! asset('modules/fcmcommun/livret-de-transformation.css') !!}">
@endpush

<div
        x-init="
        window.Mingle.Elements['{{ $this->component() }}']
            .boot(
                '{{ $this->mingleId }}',
                '{{ $_instance->getId() }}',
            )
    "
>
    <div id="{{ $this->mingleId }}-container" wire:ignore x-ignore>
        <div
                id="{{ $this->mingleId }}"
                data-mingle-boot="{{ json_encode($this->mingleBoot(collect())) }}"
                data-mingle-data="{{ json_encode($this->mingleData(collect())) }}"
        ></div>
    </div>
</div>
