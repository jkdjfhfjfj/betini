<!-- categories -->
<div class="categories" id="categories">
    <a href="{{route('home')}}" @if(Request::routeIs('home')) class="active" @endif>
        <svg class="category-icon" viewBox="0 0 24 24" aria-hidden="true">
            <circle cx="12" cy="12" r="9"></circle>
            <path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"></path>
        </svg>
        <span>{{trans('All Sports')}}</span>
    </a>

    @php
        $segments = request()->segments();
        $last  = end($segments);
    @endphp

    @forelse($gameCategories as $gameCategory)

    <a href="{{route('category',[slug($gameCategory->name), $gameCategory->id])}}"
       class="{{( Request::routeIs('category') && $last == $gameCategory->id) ? 'active' : '' }}">
        @php echo $gameCategory->icon @endphp <span>{{$gameCategory->name}}</span>
    </a>
    @empty
    @endforelse

    <button type="button" class="category-more"
            aria-label="@lang('More sports')"
            onclick="toggleSidebar('leftbar')">
        <i class="fal fa-ellipsis-h" aria-hidden="true"></i>
        <span>@lang('More')</span>
    </button>
</div>
