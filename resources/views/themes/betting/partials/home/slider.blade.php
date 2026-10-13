<!-- slider -->
@if(isset($contentDetails['slider']))
    <div class="skitter-large-box">
        <div class="skitter skitter-large with-dots">
            <ul>
                @foreach($contentDetails['slider'] as $data)
                    <li>
                        <a href="{{optional($data->content->contentMedia->description)->button_link}}" aria-label="{{optional($data->description)->name}}">
                            <img
                                src="{{getFile(config('location.content.path').@$data->content->contentMedia->description->image)}}"
                                class="downBars"
                                alt="{{optional($data->description)->name}}"
                            />
                        </a>
                        <div class="label_text">
                            <h2>{{optional($data->description)->name}}</h2>
                            <p class="mb-4">
                                {{optional($data->description)->short_description}}
                            </p>
                            @if(optional($data->description)->button_name && optional($data->content->contentMedia->description)->button_link)
                                <a class="btn-custom" href="{{optional($data->content->contentMedia->description)->button_link}}">{{optional($data->description)->button_name}}</a>
                            @endif
                            @if(optional($data->description)->secondary_button_name && optional($data->content->contentMedia->description)->secondary_button_link)
                                <a class="btn-custom slider-secondary-cta" href="{{optional($data->content->contentMedia->description)->secondary_button_link}}">{{optional($data->description)->secondary_button_name}}</a>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
