@php
    $fileUrl = $getRecord()->getUrl();
    $isImage = str_starts_with($getRecord()->mime_type, 'image/');
@endphp

@if ($isImage)
    <a data-fancybox="gallery"
       data-src="{{ $fileUrl }}"
       data-caption="{{ $getRecord()->file_name }}"
       class="text-blue-600 hover:underline">
        {{ $getRecord()->file_name }}
    </a>
@else
    <a href="{{ $fileUrl }}"
       target="_blank"
       class="text-blue-600 hover:underline">
        {{ $getRecord()->file_name }}
    </a>
@endif
