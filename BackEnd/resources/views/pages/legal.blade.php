@extends('layouts.public')

@section('content')
    <x-public.container size="narrow" class="content-page">
        <x-public.breadcrumbs :items="[
            ['label' => __('public.navigation.home'), 'url' => $navigation[0]['url']],
            ['label' => $pageTitle, 'current' => true],
        ]" />
        <x-public.heading level="1">{{ $pageTitle }}</x-public.heading>
        <p>{{ __("public.pages.{$currentPage}.placeholder") }}</p>
        <x-public.alert tone="warning">{{ __('public.legal_notice') }}</x-public.alert>

        @if (data_get($site, 'legal_name') || data_get($site, 'tax_code'))
            <dl class="legal-identity">
                @if (data_get($site, 'legal_name'))
                    <div><dt>{{ __('public.legal_identity.name') }}</dt><dd>{{ data_get($site, 'legal_name') }}</dd></div>
                @endif
                @if (data_get($site, 'tax_code'))
                    <div><dt>{{ __('public.legal_identity.tax_code') }}</dt><dd>{{ data_get($site, 'tax_code') }}</dd></div>
                @endif
            </dl>
        @endif
    </x-public.container>
@endsection
