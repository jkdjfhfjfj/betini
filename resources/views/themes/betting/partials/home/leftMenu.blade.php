@php
    $segments = request()->segments();
    $last  = end($segments);
@endphp

<ul class="main">
    <li class="px-2 py-2">
        <input type="search"
               class="form-control form-control-sm tournament-search"
               v-model.trim="tournamentSearch"
               aria-label="@lang('Search tournaments')"
               placeholder="@lang('Search tournaments')">
    </li>
    <li>
        <a  @if(Request::routeIs('home')) class="active" @endif
            href="{{route('home')}}">
            <svg class="category-icon" viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="12" cy="12" r="9"></circle>
                <path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"></path>
            </svg>
            <span>{{trans('All Sports')}}</span>
        </a>
    </li>
    @forelse($gameCategories as $gameCategory)
        <li>
            <a
                class="dropdown-toggle "
                data-bs-toggle="collapse"
                href="#collapse{{$gameCategory->id}}"
                role="button"
                aria-expanded="true"
                aria-controls="collapseExample">
                {!! $gameCategory->icon!!}{{$gameCategory->name}}
                <span class="count"><span class="font-italic">({{$gameCategory->game_active_match_count}})</span></span>
            </a>
            <!-- dropdown item -->

            <div class="collapse {{($loop->index == 0) ? 'show' :''}}"
                 :class="{'show': tournamentSearch.trim().length > 0}"
                 id="collapse{{$gameCategory->id}}">
                <ul class="">
                    @forelse($gameCategory->activeTournament as $tItem)
                        <li v-show='tournamentMatches(@json($tItem->name), tournamentSearch)'>
                            <a href="{{route('tournament',[slug($tItem->name) , $tItem->id ])}}" class="sidebar-link {{( Request::routeIs('tournament') && $last == $tItem->id) ? 'active' : '' }}">
                                <i class="far fa-hand-point-right"></i> {{$tItem->name}}</a>
                        </li>
                    @empty
                    @endforelse
                </ul>
            </div>
        </li>
    @empty
    @endforelse
</ul>
