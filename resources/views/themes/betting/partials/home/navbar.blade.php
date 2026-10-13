<!-- categories -->
<div class="categories" id="categories">
    <a href="{{route('home')}}" @if(Request::routeIs('home')) class="active" @endif>
        <i class="far fa-globe-americas"></i> <span>{{trans('All Sports')}}</span>
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
