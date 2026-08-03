<section class="{{ implode(' ', $classes) }} pb-faq" data-block-id="{{ $blockId }}" aria-labelledby="{{ $blockId }}-heading">
    <h2 id="{{ $blockId }}-heading">{{ $props['heading'] }}</h2>
    @foreach ($faqItems as $item)<details><summary>{{ $item['question'] }}</summary><div class="pb-rich-text">{{ $item['answer'] }}</div></details>@endforeach
    @if ($faqJson)<script type="application/ld+json">{{ $faqJson }}</script>@endif
</section>
