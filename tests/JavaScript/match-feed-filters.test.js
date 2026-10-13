const test = require('node:test');
const assert = require('node:assert/strict');
const { filterMatches, selectQuestions } = require('../../assets/themes/betting/js/match-feed-filters');

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
