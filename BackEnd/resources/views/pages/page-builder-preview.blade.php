@extends('layouts.public')

@push('head')
    <style>
        body.page-builder-preview--hide-header .site-header,
        body.page-builder-preview--hide-footer .site-footer { display: none !important; }
        [data-page-builder-preview-root] [data-block-id] { position: relative; }
        [data-page-builder-preview-root] [data-block-id].pb-preview-selected {
            outline: 3px solid #3155b7;
            outline-offset: 3px;
        }
        [data-page-builder-preview-root] [data-block-id]:hover { outline: 2px dashed #3155b7; outline-offset: 2px; }
    </style>
@endpush

@section('content')
    <div data-page-builder-preview-root>{!! $pageHtml !!}</div>
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        (() => {
            'use strict';
            const origin = window.location.origin;
            const token = @json($previewToken);
            const schemaVersion = @json($previewSchemaVersion);
            const channel = 'hongvan.page-builder.preview';
            let selected = null;

            const send = (type, detail = {}) => window.parent.postMessage({ channel, schemaVersion, type, token, ...detail }, origin);
            const blockById = (blockId) => Array.from(document.querySelectorAll('[data-block-id]'))
                .find((element) => element.getAttribute('data-block-id') === blockId) ?? null;

            window.addEventListener('message', (event) => {
                if (event.origin !== origin || event.source !== window.parent) return;
                const message = event.data;
                if (!message || message.channel !== channel || message.schemaVersion !== schemaVersion || message.token !== token) return;
                if (message.type === 'preview.handshake') {
                    send('preview.ready');
                    return;
                }
                if (message.type === 'preview.refresh') {
                    window.location.reload();
                    return;
                }
                if (message.type !== 'preview.scroll-to-block' || typeof message.blockId !== 'string') return;
                selected?.classList.remove('pb-preview-selected');
                selected = blockById(message.blockId);
                selected?.classList.add('pb-preview-selected');
                selected?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });

            document.addEventListener('click', (event) => {
                const target = event.target instanceof Element ? event.target.closest('[data-block-id]') : null;
                if (!target) return;
                event.preventDefault();
                selected?.classList.remove('pb-preview-selected');
                selected = target;
                selected.classList.add('pb-preview-selected');
                send('preview.block-selected', { blockId: selected.getAttribute('data-block-id') });
            });
            document.addEventListener('submit', (event) => event.preventDefault());
            send('preview.ready');
        })();
    </script>
@endsection
