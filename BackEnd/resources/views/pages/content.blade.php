@extends('layouts.public')

@section('content')
    <x-public.container size="narrow" class="content-page">
        <x-public.breadcrumbs :items="[
            ['label' => __('public.navigation.home'), 'url' => $homeUrl],
            ['label' => $pageTitle, 'current' => true],
        ]" />
        <article>
            <x-public.heading level="1">{{ $pageTitle }}</x-public.heading>
            @if (! empty($pageDescription))
                <p class="content-page__lead">{{ $pageDescription }}</p>
            @endif
            <div class="prose-content">
                @if (($contentHtml ?? null) instanceof Illuminate\Contracts\Support\Htmlable)
                    {!! $contentHtml->toHtml() !!}
                @else
                    {{ $contentHtml ?? '' }}
                @endif
            </div>
        </article>
    </x-public.container>
@endsection
