<!-- categories -->
<div class="sports-category-bar" id="sportsCategoryBar">
    <div class="categories" id="categories">
        <a href="{{ route('home') }}" @if(Request::routeIs('home')) class="active" @endif>
            <svg class="category-icon" viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="12" cy="12" r="9"></circle>
                <path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"></path>
            </svg>
            <span>{{ trans('All Sports') }}</span>
        </a>

        @php
            $segments = request()->segments();
            $last = end($segments);
            $visibleCategories = $gameCategories->take(5);
        @endphp

        @foreach($visibleCategories as $gameCategory)
            <a href="{{ route('category', [slug($gameCategory->name), $gameCategory->id]) }}"
               class="{{ (Request::routeIs('category') && $last == $gameCategory->id) ? 'active' : '' }}">
                <span class="category-icon-wrap">@php echo $gameCategory->icon @endphp</span>
                <span>{{ $gameCategory->name }}</span>
            </a>
        @endforeach

        <button type="button" class="category-more"
                aria-label="@lang('More sports')"
                aria-controls="moreSportsMenu"
                aria-expanded="false"
                onclick="window.toggleSportsMore(this)">
            <i class="fal fa-ellipsis-h" aria-hidden="true"></i>
            <span>@lang('More')</span>
        </button>
    </div>

    <div class="category-more-menu" id="moreSportsMenu" role="menu" hidden>
        <h6>@lang('All sports')</h6>
        @forelse($gameCategories as $gameCategory)
            <a role="menuitem"
               href="{{ route('category', [slug($gameCategory->name), $gameCategory->id]) }}"
               class="{{ (Request::routeIs('category') && $last == $gameCategory->id) ? 'active' : '' }}">
                <span class="category-icon-wrap">@php echo $gameCategory->icon @endphp</span>
                <span>{{ $gameCategory->name }}</span>
                @if((int)$gameCategory->game_active_match_count > 0)
                    <small>{{ $gameCategory->game_active_match_count }}</small>
                @endif
            </a>
        @empty
            <p class="category-more-empty">@lang('No sports are available right now.')</p>
        @endforelse
    </div>
</div>
