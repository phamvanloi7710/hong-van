@extends('layouts.public')

@section('content')
    <header class="page-banner">
        <x-public.container>
            <x-public.breadcrumbs :items="[
                ['label' => __('public.navigation.home'), 'url' => $homeUrl],
                ['label' => $pageTitle, 'current' => true],
            ]" />
            <x-public.heading level="1">{{ $pageTitle }}</x-public.heading>
            <p>{{ __('public.templates.contact_description') }}</p>
        </x-public.container>
    </header>

    <x-public.container class="contact-page">
        <section aria-labelledby="contact-information-heading">
            <x-public.heading level="2" id="contact-information-heading">{{ __('public.templates.contact_information') }}</x-public.heading>
            <address class="contact-page__details">
                @if (data_get($site, 'address'))<span>{{ data_get($site, 'address') }}</span>@endif
                @if (data_get($site, 'phone'))<a href="tel:{{ preg_replace('/[^0-9+]/', '', data_get($site, 'phone')) }}">{{ data_get($site, 'phone') }}</a>@endif
                @if (data_get($site, 'email'))<a href="mailto:{{ data_get($site, 'email') }}">{{ data_get($site, 'email') }}</a>@endif
            </address>
        </section>
        <section class="contact-page__form-placeholder" aria-labelledby="contact-form-heading">
            <x-public.heading level="2" id="contact-form-heading">{{ __('public.templates.contact_form') }}</x-public.heading>
            <x-public.alert tone="info">{{ __('public.templates.contact_form_pending') }}</x-public.alert>
        </section>
    </x-public.container>
@endsection
