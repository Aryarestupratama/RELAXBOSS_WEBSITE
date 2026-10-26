@props(['tag' => 'h3'])
<{{ $tag }} data-slot="card-title" {{ $attributes->class(['text-lg leading-snug font-bold']) }}>{{ $slot }}</{{ $tag }}>
