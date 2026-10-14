const test = require('node:test');
const assert = require('node:assert/strict');
const {
    defaultFilters,
    filterMatches,
    matchesTournamentSearch,
    selectQuestions
} = require('../../assets/themes/betting/js/match-feed-filters');

function localDate(year, month, day, hour = 12) {
    return new Date(year, month - 1, day, hour).toISOString();
}

function match(id, startDate, options = []) {
    return {
        id,
        start_date: startDate,
        questions: [{ name: 'Match Result', options }]
    };
}

test('default filter state shows all dates, markets, and sections', () => {
    assert.deepEqual(defaultFilters(), {
        section: 'all',
        country: '',
        dateFilter: 'all',
        marketGroupFilter: 'all',
        oddsMarketFilter: 'all',
        onlyAvailableOdds: false
    });
});

test('All section does not require an admin label or country assignment', () => {
    const fixtures = [
        { id: 1, sections: ['jackpots'], country: 'Kenya' },
        { id: 2, sections: [], country: null }
    ];

    assert.deepEqual(
        filterMatches(fixtures, { ...defaultFilters(), section: 'all' }).map(item => item.id),
        [1, 2]
    );
});

test('today filter includes only matches scheduled on the browser local date', () => {
    const today = new Date(2026, 9, 13, 12);
    const fixtures = [
        match(1, localDate(2026, 10, 13)),
        match(2, localDate(2026, 10, 12)),
        match(3, localDate(2026, 10, 14))
    ];

    assert.deepEqual(
        filterMatches(fixtures, { dateFilter: 'today', marketGroupFilter: 'all' }, today).map(item => item.id),
        [1]
    );
    assert.deepEqual(
        filterMatches(fixtures, { dateFilter: 'all', marketGroupFilter: 'all' }, today).map(item => item.id),
        [1, 2, 3]
    );
});

test('All filter values remove date, market-group, and odds restrictions', () => {
    const fixtures = [
        match(1, localDate(2026, 10, 13), [{ option_name: 'Home', ratio: 2.1 }]),
        match(2, localDate(2026, 10, 12), []),
        match(3, localDate(2026, 10, 14), [{ option_name: 'Away', ratio: 3.2 }])
    ];

    assert.deepEqual(
        filterMatches(fixtures, {
            dateFilter: 'all',
            marketGroupFilter: 'all',
            onlyAvailableOdds: false
        }, new Date(2026, 9, 13, 12)).map(item => item.id),
        [1, 2, 3]
    );
    assert.deepEqual(
        selectQuestions(fixtures[0].questions, 'all'),
        fixtures[0].questions
    );
});

test('highlights excludes fixtures without markets while all matches retains them', () => {
    const fixtures = [
        match(1, localDate(2026, 10, 13), [{ option_name: 'Home', ratio: 2.1 }]),
        match(2, localDate(2026, 10, 13), [])
    ];
    const today = new Date(2026, 9, 13, 12);

    assert.deepEqual(
        filterMatches(fixtures, { dateFilter: 'today', marketGroupFilter: 'highlights' }, today).map(item => item.id),
        [1]
    );
    assert.deepEqual(
        filterMatches(fixtures, { dateFilter: 'today', marketGroupFilter: 'all' }, today).map(item => item.id),
        [1, 2]
    );
});

test('available-odds filter removes matches whose odds are all locked', () => {
    const fixtures = [
        match(1, localDate(2026, 10, 13), [
            { is_unlock_question: 0, is_unlock_match: 0 }
        ]),
        match(2, localDate(2026, 10, 13), [
            { is_unlock_question: '1', is_unlock_match: 0 }
        ])
    ];

    assert.deepEqual(
        filterMatches(fixtures, {
            dateFilter: 'all',
            marketGroupFilter: 'all',
            onlyAvailableOdds: true
        }).map(item => item.id),
        [1]
    );
});

test('1x2 selects a match-result market and all markets shows up to three', () => {
    const questions = [
        { name: 'Over/Under' },
        { name: 'Full Time Result' },
        { name: 'Both Teams To Score' },
        { name: 'Correct Score' }
    ];

    assert.deepEqual(selectQuestions(questions, '1x2'), [questions[1]]);
    assert.deepEqual(selectQuestions(questions, 'all'), questions.slice(0, 3));
});

test('1x2 falls back to the first available market when no result market is named', () => {
    const questions = [{ name: 'Total Goals' }, { name: 'Correct Score' }];
    assert.deepEqual(selectQuestions(questions, '1x2'), [questions[0]]);
    assert.deepEqual(selectQuestions([], '1x2'), []);
});

test('countries section includes only country-assigned matches and honors country selection', () => {
    const fixtures = [
        { id: 1, country: 'Kenya', sections: [] },
        { id: 2, country: 'Uganda', sections: [] },
        { id: 3, country: null, sections: [] }
    ];

    assert.deepEqual(
        filterMatches(fixtures, { section: 'countries', dateFilter: 'all', marketGroupFilter: 'all' }).map(item => item.id),
        [1, 2]
    );
    assert.deepEqual(
        filterMatches(fixtures, {
            section: 'countries',
            country: 'Uganda',
            dateFilter: 'all',
            marketGroupFilter: 'all'
        }).map(item => item.id),
        [2]
    );
});

test('admin-assigned sections only return matches tagged for the selected section', () => {
    const fixtures = [
        { id: 1, sections: ['zoom-soccer', 'new'] },
        { id: 2, sections: ['jackpots'] },
        { id: 3, sections: ['turbo'] },
        { id: 4, sections: [] }
    ];

    for (const section of ['zoom-soccer', 'jackpots', 'new', 'turbo']) {
        assert.equal(
            filterMatches(fixtures, { section, dateFilter: 'all', marketGroupFilter: 'all' }).length,
            1,
            `${section} only returns its assigned fixture`
        );
    }
});

test('tournament search is optional and case-insensitive', () => {
    assert.equal(matchesTournamentSearch('Premier League', ''), true);
    assert.equal(matchesTournamentSearch('Premier League', 'PREMIER'), true);
    assert.equal(matchesTournamentSearch('Premier League', 'cup'), false);
});
