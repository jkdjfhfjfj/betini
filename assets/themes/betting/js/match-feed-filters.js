(function (root, factory) {
    var filters = factory();

    if (typeof module === 'object' && module.exports) {
        module.exports = filters;
    } else {
        root.MatchFeedFilters = filters;
    }
}(typeof globalThis !== 'undefined' ? globalThis : this, function () {
    function dateKey(value) {
        var date = value instanceof Date ? value : new Date(value);
        if (isNaN(date.getTime())) {
            return null;
        }

        return date.getFullYear() + '-' +
            String(date.getMonth() + 1).padStart(2, '0') + '-' +
            String(date.getDate()).padStart(2, '0');
    }

    function hasOdds(match) {
        var questions = Array.isArray(match.questions) ? match.questions : [];
        return questions.some(function (question) {
            return Array.isArray(question.options) && question.options.length > 0;
        });
    }

    function hasAvailableOdds(match) {
        var questions = Array.isArray(match.questions) ? match.questions : [];
        return questions.some(function (question) {
            var options = Array.isArray(question.options) ? question.options : [];
            return options.some(function (option) {
                return Number(option.is_unlock_question) !== 1 &&
                    Number(option.is_unlock_match) !== 1;
            });
        });
    }

    function filterMatches(items, filters, now) {
        var matches = Array.isArray(items) ? items : [];
        var settings = filters || {};
        var today = dateKey(now || new Date());

        return matches.filter(function (match) {
            if (settings.section === 'countries' && !String(match.country || '').trim()) {
                return false;
            }
            if (settings.country && String(match.country || '').toLowerCase() !== String(settings.country).toLowerCase()) {
                return false;
            }
            if (settings.section && settings.section !== 'countries') {
                var sections = Array.isArray(match.sections) ? match.sections : [];
                if (sections.indexOf(settings.section) === -1) {
                    return false;
                }
            }
            if (settings.dateFilter === 'today' && dateKey(match.start_date) !== today) {
                return false;
            }
            if (settings.marketGroupFilter === 'highlights' && !hasOdds(match)) {
                return false;
            }
            if (settings.onlyAvailableOdds && !hasAvailableOdds(match)) {
                return false;
            }
            return true;
        });
    }

    function selectQuestions(items, marketFilter) {
        var questions = Array.isArray(items) ? items : [];
        if (marketFilter === '1x2') {
            var matchResult = questions.find(function (question) {
                return /1\s*x\s*2|match\s*(winner|result)|full\s*time\s*result/i.test(question.name || '');
            });
            return matchResult ? [matchResult] : questions.slice(0, 1);
        }

        return questions.slice(0, 3);
    }

    return {
        filterMatches: filterMatches,
        selectQuestions: selectQuestions
    };
}));
