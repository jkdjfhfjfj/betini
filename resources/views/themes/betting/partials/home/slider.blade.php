@if(isset($contentDetails['slider']) && $contentDetails['slider']->isNotEmpty())
    @php($promoSlides = $contentDetails['slider'])
    <section class="home-promo-carousel" aria-label="@lang('Featured promotions')" data-home-promo-carousel>
        <div class="home-promo-track" data-home-promo-track>
            @foreach($promoSlides as $data)
                @php
                    $slideDescription = optional($data->description);
                    $slideMedia = optional(optional($data->content)->contentMedia)->description;
                    $slideImage = optional($slideMedia)->image;
                    $slidePrimaryUrl = optional($slideMedia)->button_link;
                    $slideSecondaryUrl = optional($slideMedia)->secondary_button_link;
                    $slideTitle = trim((string) ($slideDescription->name ?? ''));
                    $slideText = trim((string) ($slideDescription->short_description ?? ''));
                    $hasPlaceholderCopy = preg_match('/lorem ipsum|dolor sit amet|consectetur adipisicing/i', $slideTitle . ' ' . $slideText);

                    if ($hasPlaceholderCopy) {
                        $slideTitle = __('Featured matches');
                        $slideText = __('Explore upcoming fixtures, follow live action, and find markets for your next bet.');
                    } elseif ($slideTitle === '' && $slideText === '') {
                        $slideTitle = __('Featured matches');
                        $slideText = __('Explore upcoming fixtures and live sports markets.');
                    } elseif ($slideTitle === '') {
                        $slideTitle = __('Featured matches');
                    }
                @endphp

                <article class="home-promo-slide"
                         role="group"
                         aria-roledescription="@lang('slide')"
                         aria-label="@lang('Slide :current of :total', ['current' => $loop->iteration, 'total' => $promoSlides->count()])">
                    @if($slideImage)
                        <img
                            class="home-promo-image"
                            src="{{ getFile(config('location.content.path') . $slideImage) }}"
                            alt="{{ $slideTitle }}"
                            @if($loop->first) fetchpriority="high" @else loading="lazy" @endif
                        >
                    @endif

                    <div class="home-promo-copy">
                        <div class="home-promo-copy-inner">
                            <h2>{{ $slideTitle }}</h2>
                            @if($slideText !== '')
                                <p>{{ $slideText }}</p>
                            @endif

                            <div class="home-promo-actions">
                                @if($slidePrimaryUrl)
                                    <a class="btn-custom home-promo-primary" href="{{ $slidePrimaryUrl }}">
                                        {{ $slideDescription->button_name ?: __('Explore') }}
                                    </a>
                                @endif
                                @if($slideSecondaryUrl && $slideDescription->secondary_button_name)
                                    <a class="btn-custom home-promo-secondary" href="{{ $slideSecondaryUrl }}">
                                        {{ $slideDescription->secondary_button_name }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        @if($promoSlides->count() > 1)
            <div class="home-promo-controls" aria-label="@lang('Promotion carousel controls')">
                <button type="button" class="home-promo-arrow" data-promo-previous aria-label="@lang('Previous promotion')">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 5-7 7 7 7"></path></svg>
                </button>
                <div class="home-promo-dots">
                    @foreach($promoSlides as $slide)
                        <button type="button"
                                class="home-promo-dot{{ $loop->first ? ' is-active' : '' }}"
                                data-promo-slide="{{ $loop->index }}"
                                aria-label="@lang('Show promotion :number', ['number' => $loop->iteration])"
                                aria-current="{{ $loop->first ? 'true' : 'false' }}"></button>
                    @endforeach
                </div>
                <button type="button" class="home-promo-arrow" data-promo-next aria-label="@lang('Next promotion')">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 5 7 7-7 7"></path></svg>
                </button>
            </div>
        @endif
    </section>
@endif
