<!-- live match table -->
<div v-if="showType == 'live'"  v-for="(item, index) in visibleMatches(allSports_filter)" class="table-parent table-responsive d-sm-block d-none">

    <table class="table table-striped">
        <thead>
        <tr>
            <th class="text-center">
                <span v-html="item.game_category.icon"></span>
            </th>
            <th class="col-5">
               <span>
                   @{{item.game_tournament.name}} <span v-if="item.name">- @{{item.name}} </span>
               </span>
            </th>

            <th v-if="index <= 2" class="col-2" v-for="(question, index) in visibleQuestions(item.questions)">
                <div class="d-flex justify-content-evenly">
                    <span>@lang('1')</span>
                    <span>@{{question.name}}</span>
                    <span>@lang('2')</span>
                </div>
            </th>

            <template v-if="3 > visibleQuestions(item.questions).length ">
                <th class="col-2" v-for="index in (3 - visibleQuestions(item.questions).length)"
                    :key="index">
                    <div class="d-flex justify-content-evenly">
                        <span>1</span>
                        <span v-if="index == 1">@lang('X')</span>
                        <span v-if="index == 2">@lang('2X')</span>
                        <span v-if="index == 3">@lang('3X')</span>
                        <span>2</span>
                    </div>
                </th>
            </template>
            <th class="col-1 text-center">@lang('More')</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td class="text-center">
            </td>
            <td>
                <p>
                    <span>
                        <img :src="item.team1_img" alt="..">
                        @{{ item.team1 }}
                    </span>
                </p>
                <p>
                    <span>
                        <img :src="item.team2_img" alt="..">
                        @{{ item.team2 }}
                    </span>
                </p>
                <p>
                    <span class="float-end">
                        <a href="" class="me-2 d-none">
                            <i class="fal fa-chart-bar"></i>
                        </a>
                    </span>
                </p>
            </td>

            <td v-if="index <= 2" v-for="(question, index) in visibleQuestions(item.questions)">
                <div class="d-flex justify-content-evenly w-100">
                    <button type="button" :disabled="option.is_unlock_question == 1 || option.is_unlock_match == 1 "
                            :class="{ disabled: (option.is_unlock_question == 1 || option.is_unlock_match == 1) }"
                            v-for="(option, index) in question.options"
                            :title="option.option_name" @click="addToSlip(option)">
                        <i v-if="option.is_unlock_question == 1 || option.is_unlock_match == 1"
                           class="fas fa-lock-alt"></i> @{{ option.ratio}}
                    </button>
                </div>

                <div v-if="(question.options).length == 0" class="d-flex justify-content-evenly w-100">
                    <button type="button" class="disabled downgrade">-</button>
                    <button type="button" class="disabled downgrade">-</button>
                </div>

            </td>

            <template v-if="3 > visibleQuestions(item.questions).length ">
                <td v-for="index in (3 - visibleQuestions(item.questions).length)" :key="index">
                    <div class="d-flex justify-content-evenly w-100">
                        <button type="button" class="disabled downgrade">-</button>
                        <button type="button" class="disabled downgrade">-</button>
                    </div>
                </td>
            </template>
            <td>
                <button type="button" v-if="0 == (item.questions).length" disabled class="disabled">-</button>
                <button @click="goMatch(item)" type="button" v-else>+@{{ (item.questions).length }}</button>
            </td>
        </tr>
    </table>
</div>


<!-- Upcoming match table -->
<div  v-if="showType == 'upcoming'"  v-for="(item, index) in visibleMatches(upcoming_filter)" class="table-parent table-responsive d-sm-block d-none">
    <table class="table table-striped">
        <thead>
        <tr>
            <th class="text-center">
                <span v-html="item.game_category.icon"></span>
            </th>
            <th class="col-5">
               <span>
                   @{{item.game_tournament.name}} <span v-if="item.name">- @{{item.name}} </span>
               </span>
            </th>

            <th v-if="index <= 2" class="col-2" v-for="(question, index) in visibleQuestions(item.questions)">
                <div class="d-flex justify-content-evenly">
                    <span>@lang('1')</span>
                    <span>@{{question.name}}</span>
                    <span>@lang('2')</span>
                </div>
            </th>

            <template v-if="3 > visibleQuestions(item.questions).length ">
                <th class="col-2" v-for="index in (3 - visibleQuestions(item.questions).length)"
                    :key="index">
                    <div class="d-flex justify-content-evenly">
                        <span>@lang('1')</span>
                        <span v-if="index == 1">@lang('X')</span>
                        <span v-if="index == 2">@lang('2X')</span>
                        <span v-if="index == 3">@lang('3X')</span>
                        <span>2</span>
                    </div>
                </th>
            </template>
            <th class="col-1 text-center">@lang('More')</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td class="text-center">
            </td>
            <td>
                <p>
                    <span>
                        <img :src="item.team1_img" alt="..">
                        @{{ item.team1 }}
                    </span>
                </p>
                <p>
                    <span>
                        <img :src="item.team2_img" alt="..">
                        @{{ item.team2 }}
                    </span>
                </p>
                <p>
                    <span class="float-end">
                        <a href="" class="me-2 d-none">
                            <i class="fal fa-chart-bar"></i>
                        </a>
                    </span>
                </p>
            </td>

            <td v-if="index <= 2" v-for="(question, index) in visibleQuestions(item.questions)">
                <div class="d-flex justify-content-evenly w-100">
                    <button type="button" :disabled="option.is_unlock_question == 1 || option.is_unlock_match == 1 "
                            :class="{ disabled: (option.is_unlock_question == 1 || option.is_unlock_match == 1) }"
                            v-for="(option, index) in question.options"
                            :title="option.option_name" @click="addToSlip(option)">
                        <i v-if="option.is_unlock_question == 1 || option.is_unlock_match == 1"
                           class="fas fa-lock-alt"></i> @{{ option.ratio}}
                    </button>
                </div>

                <div v-if="(question.options).length == 0" class="d-flex justify-content-evenly w-100">
                    <button type="button" class="disabled downgrade">-</button>
                    <button type="button" class="disabled downgrade">-</button>
                </div>

            </td>

            <template v-if="3 > visibleQuestions(item.questions).length ">
                <td v-for="index in (3 - visibleQuestions(item.questions).length)" :key="index">
                    <div class="d-flex justify-content-evenly w-100">
                        <button type="button" class="disabled downgrade">-</button>
                        <button type="button" class="disabled downgrade">-</button>
                    </div>
                </td>
            </template>
            <td>
                <button type="button" v-if="0 == (item.questions).length" disabled class="disabled">-</button>
                <button @click="goMatch(item)" type="button" v-else>+@{{ (item.questions).length }}</button>
            </td>
        </tr>
    </table>
</div>

<div class="match-feed-mobile" v-if="showType == 'live'">
    <article class="match-card" v-for="(item, index) in visibleMatches(allSports_filter)" :key="'live-mobile-' + item.id">
        <div class="match-card-meta">
            <span class="match-card-league"><span v-html="item.game_category.icon"></span> @{{ item.game_tournament.name }}<span v-if="item.name"> · @{{ item.name }}</span></span>
            <span class="match-card-time">@lang('Live')</span>
        </div>
        <div class="match-card-body">
            <div class="match-card-teams">
                <div class="match-card-team"><img :src="item.team1_img" alt=""><span>@{{ item.team1 }}</span></div>
                <div class="match-card-team"><img :src="item.team2_img" alt=""><span>@{{ item.team2 }}</span></div>
            </div>
            <div class="match-card-odds" v-if="item.questions.length && slicedArray(item.questions).options.length">
                <button v-for="(option, optionIndex) in slicedArray(item.questions).options"
                        :key="'live-odd-' + option.id"
                        type="button"
                        :disabled="option.is_unlock_question == 1 || option.is_unlock_match == 1"
                        :title="option.option_name"
                        :aria-label="option.option_name + ' @ ' + option.ratio"
                        @click="addToSlip(option)">
                    <i v-if="option.is_unlock_question == 1 || option.is_unlock_match == 1" class="fas fa-lock-alt" aria-hidden="true"></i>
                    @{{ option.ratio }}
                </button>
            </div>
            <div class="match-card-odds" v-else>
                <button type="button" disabled aria-label="@lang('Odds unavailable')">-</button>
                <button type="button" disabled aria-label="@lang('Odds unavailable')">-</button>
            </div>
        </div>
        <div class="match-card-more" v-if="item.questions.length">
            <button type="button" @click="goMatch(item)">+@{{ item.questions.length }} @lang('Markets')</button>
        </div>
    </article>
    <div class="feed-empty" v-if="visibleMatches(allSports_filter).length == 0">@lang('No matches match these filters')</div>
</div>

<div class="match-feed-mobile" v-if="showType == 'upcoming'">
    <article class="match-card" v-for="(item, index) in visibleMatches(upcoming_filter)" :key="'upcoming-mobile-' + item.id">
        <div class="match-card-meta">
            <span class="match-card-league"><span v-html="item.game_category.icon"></span> @{{ item.game_tournament.name }}<span v-if="item.name"> · @{{ item.name }}</span></span>
            <span class="match-card-time">@lang('Upcoming')</span>
        </div>
        <div class="match-card-body">
            <div class="match-card-teams">
                <div class="match-card-team"><img :src="item.team1_img" alt=""><span>@{{ item.team1 }}</span></div>
                <div class="match-card-team"><img :src="item.team2_img" alt=""><span>@{{ item.team2 }}</span></div>
            </div>
            <div class="match-card-odds" v-if="item.questions.length && slicedArray(item.questions).options.length">
                <button v-for="(option, optionIndex) in slicedArray(item.questions).options"
                        :key="'upcoming-odd-' + option.id"
                        type="button"
                        :disabled="option.is_unlock_question == 1 || option.is_unlock_match == 1"
                        :title="option.option_name"
                        :aria-label="option.option_name + ' @ ' + option.ratio"
                        @click="addToSlip(option)">
                    <i v-if="option.is_unlock_question == 1 || option.is_unlock_match == 1" class="fas fa-lock-alt" aria-hidden="true"></i>
                    @{{ option.ratio }}
                </button>
            </div>
            <div class="match-card-odds" v-else>
                <button type="button" disabled aria-label="@lang('Odds unavailable')">-</button>
                <button type="button" disabled aria-label="@lang('Odds unavailable')">-</button>
            </div>
        </div>
        <div class="match-card-more" v-if="item.questions.length">
            <button type="button" @click="goMatch(item)">+@{{ item.questions.length }} @lang('Markets')</button>
        </div>
    </article>
    <div class="feed-empty" v-if="visibleMatches(upcoming_filter).length == 0">@lang('No matches match these filters')</div>
</div>
