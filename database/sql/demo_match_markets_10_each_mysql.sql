-- Add ten standard football markets to fixtures in International Club Friendlies.
-- This staging-only script uses the actual home/away club names on each fixture.
-- MySQL / MariaDB.
--
-- Questions and odds are active and available in the isolated staging database.
-- No match status, existing question, option, result, or bet is overwritten.
--
-- Run the complete script in one phpMyAdmin SQL request/session. Run
-- staging fixtures must already exist in the selected database.

DROP TEMPORARY TABLE IF EXISTS tmp_demo_market_seed;
DROP TEMPORARY TABLE IF EXISTS tmp_demo_market_option_seed;
DROP TEMPORARY TABLE IF EXISTS tmp_demo_question_map;
DROP TEMPORARY TABLE IF EXISTS tmp_demo_resolved_options;

CREATE TEMPORARY TABLE tmp_demo_market_seed (
    `market_no` TINYINT UNSIGNED NOT NULL PRIMARY KEY,
    `market_name` VARCHAR(120) NOT NULL
) ENGINE=MEMORY;

INSERT INTO tmp_demo_market_seed (`market_no`, `market_name`) VALUES
    (1, 'Match Result (1X2)'),
    (2, 'Double Chance'),
    (3, 'Both Teams To Score'),
    (4, 'Total Goals Over/Under 1.5'),
    (5, 'Total Goals Over/Under 2.5'),
    (6, 'Total Goals Over/Under 3.5'),
    (7, 'First Half Result'),
    (8, 'Draw No Bet'),
    (9, 'Correct Score'),
    (10, 'First Team To Score');

CREATE TEMPORARY TABLE tmp_demo_market_option_seed (
    `market_no` TINYINT UNSIGNED NOT NULL,
    `option_no` TINYINT UNSIGNED NOT NULL,
    `option_label` VARCHAR(120) NOT NULL,
    `ratio` DECIMAL(8, 2) NOT NULL,
    PRIMARY KEY (`market_no`, `option_no`)
) ENGINE=MEMORY;

INSERT INTO tmp_demo_market_option_seed
    (`market_no`, `option_no`, `option_label`, `ratio`) VALUES
    -- Match Result (1X2)
    (1, 1, '@HOME', 2.15),
    (1, 2, 'Draw', 3.20),
    (1, 3, '@AWAY', 3.25),
    -- Double Chance
    (2, 1, '@HOME_OR_DRAW', 1.32),
    (2, 2, '@HOME_OR_AWAY', 1.30),
    (2, 3, '@DRAW_OR_AWAY', 1.62),
    -- Both Teams To Score
    (3, 1, 'Yes', 1.82),
    (3, 2, 'No', 1.90),
    -- Total Goals Over/Under 1.5
    (4, 1, 'Over 1.5', 1.42),
    (4, 2, 'Under 1.5', 2.55),
    -- Total Goals Over/Under 2.5
    (5, 1, 'Over 2.5', 2.05),
    (5, 2, 'Under 2.5', 1.76),
    -- Total Goals Over/Under 3.5
    (6, 1, 'Over 3.5', 3.30),
    (6, 2, 'Under 3.5', 1.30),
    -- First Half Result
    (7, 1, '@HOME', 2.60),
    (7, 2, 'Draw', 2.00),
    (7, 3, '@AWAY', 3.40),
    -- Draw No Bet
    (8, 1, '@HOME', 1.62),
    (8, 2, '@AWAY', 2.18),
    -- Correct Score (sample outcomes)
    (9, 1, '1-0', 7.50),
    (9, 2, '1-1', 6.25),
    (9, 3, '0-1', 8.50),
    -- First Team To Score
    (10, 1, '@HOME', 2.05),
    (10, 2, 'No Goal', 8.00),
    (10, 3, '@AWAY', 2.20);

CREATE TEMPORARY TABLE tmp_demo_question_map (
    `match_id` BIGINT UNSIGNED NOT NULL,
    `market_no` TINYINT UNSIGNED NOT NULL,
    `question_id` BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (`match_id`, `market_no`)
) ENGINE=MEMORY;

CREATE TEMPORARY TABLE tmp_demo_resolved_options (
    `question_id` BIGINT UNSIGNED NOT NULL,
    `option_name` VARCHAR(120) NOT NULL,
    `ratio` DECIMAL(8, 2) NOT NULL,
    PRIMARY KEY (`question_id`, `option_name`)
) ENGINE=MEMORY;

SET @demo_category_id := (
    SELECT MIN(category.`id`)
    FROM `game_categories` category
    WHERE LOWER(TRIM(category.`name`)) = 'football'
      AND category.`status` = 1
);

SET @demo_tournament_id := (
    SELECT MIN(tournament.`id`)
    FROM `game_tournaments` tournament
    WHERE tournament.`category_id` = @demo_category_id
      AND LOWER(TRIM(tournament.`name`)) = 'international club friendlies'
      AND tournament.`status` = 1
);

-- MatchController creates markets using the authenticated admin ID. Use an
-- existing admin ID for the same creator_id field.
SET @demo_creator_id := (SELECT MIN(admin_row.`id`) FROM `admins` admin_row);

START TRANSACTION;

-- Add missing market questions for the selected tournament.
INSERT INTO `game_questions`
    (`match_id`, `creator_id`, `name`, `limit`, `end_time`,
     `is_unlock`, `status`, `created_at`, `updated_at`)
SELECT
    match_row.`id`,
    @demo_creator_id,
    market.`market_name`,
    1000,
    match_row.`end_date`,
    0,
    1,
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
FROM `game_matches` match_row
CROSS JOIN `tmp_demo_market_seed` market
WHERE @demo_tournament_id IS NOT NULL
  AND @demo_creator_id IS NOT NULL
  AND match_row.`tournament_id` = @demo_tournament_id
  AND match_row.`end_date` > CURRENT_TIMESTAMP
  AND NOT EXISTS (
      SELECT 1
      FROM `game_questions` existing_question
      WHERE existing_question.`match_id` = match_row.`id`
        AND existing_question.`name` = market.`market_name`
        AND existing_question.`status` = 1
        AND existing_question.`end_time` > CURRENT_TIMESTAMP
  );

-- Resolve one question ID per match and market. MIN also tolerates pre-existing
-- duplicate question names without inserting duplicate mapping rows.
INSERT INTO `tmp_demo_question_map` (`match_id`, `market_no`, `question_id`)
SELECT
    match_row.`id`,
    market.`market_no`,
    MIN(question.`id`)
FROM `game_matches` match_row
CROSS JOIN `tmp_demo_market_seed` market
JOIN `game_questions` question
  ON question.`match_id` = match_row.`id`
 AND question.`name` = market.`market_name`
 AND question.`status` = 1
 AND question.`end_time` > CURRENT_TIMESTAMP
WHERE @demo_tournament_id IS NOT NULL
  AND match_row.`tournament_id` = @demo_tournament_id
  AND match_row.`end_date` > CURRENT_TIMESTAMP
GROUP BY match_row.`id`, market.`market_no`;

-- Replace team placeholders with the actual names from each fixture.
INSERT INTO `tmp_demo_resolved_options` (`question_id`, `option_name`, `ratio`)
SELECT
    question_map.`question_id`,
    CASE option_seed.`option_label`
        WHEN '@HOME' THEN home_team.`name`
        WHEN '@AWAY' THEN away_team.`name`
        WHEN '@HOME_OR_DRAW' THEN CONCAT(home_team.`name`, ' or Draw (1X)')
        WHEN '@HOME_OR_AWAY' THEN CONCAT(home_team.`name`, ' or ', away_team.`name`, ' (12)')
        WHEN '@DRAW_OR_AWAY' THEN CONCAT('Draw or ', away_team.`name`, ' (X2)')
        ELSE option_seed.`option_label`
    END,
    option_seed.`ratio`
FROM `tmp_demo_question_map` question_map
JOIN `tmp_demo_market_option_seed` option_seed
  ON option_seed.`market_no` = question_map.`market_no`
JOIN `game_matches` match_row
  ON match_row.`id` = question_map.`match_id`
JOIN `game_teams` home_team
  ON home_team.`id` = match_row.`team1_id`
JOIN `game_teams` away_team
  ON away_team.`id` = match_row.`team2_id`;

-- Add active staged odds. Amount fields start at zero with minimum one.
INSERT INTO `game_options`
    (`match_id`, `question_id`, `creator_id`, `option_name`,
     `invest_amount`, `return_amount`, `minimum_amount`, `ratio`,
     `status`, `created_at`, `updated_at`)
SELECT
    question_map.`match_id`,
    resolved.`question_id`,
    @demo_creator_id,
    resolved.`option_name`,
    0,
    0,
    1,
    CAST(resolved.`ratio` AS CHAR),
    1,
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
FROM `tmp_demo_resolved_options` resolved
JOIN `tmp_demo_question_map` question_map
  ON question_map.`question_id` = resolved.`question_id`
WHERE @demo_creator_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `game_options` existing_option
      WHERE existing_option.`question_id` = resolved.`question_id`
        AND existing_option.`option_name` = resolved.`option_name`
  );

COMMIT;

-- Show the market and outcome counts for the selected fixtures.
SELECT
    match_row.`id`,
    match_row.`name`,
    COUNT(DISTINCT question.`id`) AS `market_count`,
    COUNT(DISTINCT option_row.`id`) AS `active_odds_count`
FROM `game_matches` match_row
CROSS JOIN `tmp_demo_market_seed` market
LEFT JOIN `game_questions` question
  ON question.`match_id` = match_row.`id`
 AND question.`name` = market.`market_name`
LEFT JOIN `game_options` option_row
  ON option_row.`question_id` = question.`id`
 AND option_row.`status` = 1
WHERE @demo_tournament_id IS NOT NULL
  AND match_row.`tournament_id` = @demo_tournament_id
GROUP BY match_row.`id`, match_row.`name`
ORDER BY match_row.`id`;

-- A NULL creator ID means no admin row was found, so the guarded inserts
-- were skipped; check the admins table before trying again.
SELECT
    @demo_tournament_id AS `tournament_id`,
    @demo_creator_id AS `market_creator_id`;

DROP TEMPORARY TABLE IF EXISTS tmp_demo_resolved_options;
DROP TEMPORARY TABLE IF EXISTS tmp_demo_question_map;
DROP TEMPORARY TABLE IF EXISTS tmp_demo_market_option_seed;
DROP TEMPORARY TABLE IF EXISTS tmp_demo_market_seed;
