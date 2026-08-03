@props(['items' => []])

<nav class="breadcrumbs" aria-label="{{ __('public.breadcrumbs') }}">
    <ol>
        @foreach ($items as $item)
            <li>
                @if (! empty($item['current']))
                    <span aria-current="page">{{ $item['label'] }}</span>
                @else
                    <x-public.link :href="$item['url']">{{ $item['label'] }}</x-public.link>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
