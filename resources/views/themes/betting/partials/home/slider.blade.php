<!-- slider -->
@if(isset($contentDetails['slider']))
    <div class="skitter-large-box home-promo-slider">
        <div class="skitter skitter-large with-dots">
            <ul>
                @foreach($contentDetails['slider'] as $data)
                    @php
                        $slideDescription = optional($data->description);
                        $slideMedia = optional(optional($data->content)->contentMedia)->description;
                        $slideImage = optional($slideMedia)->image;
                        $slidePrimaryUrl = optional($slideMedia)->button_link;
                        $slideSecondaryUrl = optional($slideMedia)->secondary_button_link;
                    @endphp
                    <li class="home-promo-slide">
                        @if($slidePrimaryUrl)
                        <a class="home-promo-art" href="{{ $slidePrimaryUrl }}" aria-label="{{ $slideDescription->name ?: __('Open promotion') }}">
                            <img
                                src="{{ $slideImage ? getFile(config('location.content.path') . $slideImage) : '' }}"
                                class="downBars home-promo-image"
                                alt="{{ $slideDescription->name ?: __('Featured promotion') }}"
                            />
                        </a>
                        @else
                            <img
                                src="{{ $slideImage ? getFile(config('location.content.path') . $slideImage) : '' }}"
                                class="downBars home-promo-image"
                                alt="{{ $slideDescription->name ?: __('Featured promotion') }}"
                            />
                        @endif
                        <div class="label_text home-promo-copy">
                            <div class="home-promo-copy-inner">
                                <h2>{{ $slideDescription->name }}</h2>
                                <p>{{ $slideDescription->short_description }}</p>
                                <div class="home-promo-actions">
                            @if($slideDescription->button_name && $slidePrimaryUrl)
                                <a class="btn-custom home-promo-primary" href="{{ $slidePrimaryUrl }}">{{ $slideDescription->button_name }}</a>
                            @endif
                            @if($slideDescription->secondary_button_name && $slideSecondaryUrl)
                                <a class="btn-custom slider-secondary-cta home-promo-secondary" href="{{ $slideSecondaryUrl }}">{{ $slideDescription->secondary_button_name }}</a>
                            @endif
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
