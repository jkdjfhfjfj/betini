-- MySQL / MariaDB staging fixtures using well-known international clubs.
-- Additive and repeatable: it preserves existing categories, tournaments,
-- teams, and matches. The generated pairings and dates are not an official schedule.
--
-- Run this only against an isolated staging database.
-- The running application schema must include game_matches.name/status/is_unlock.

DROP TEMPORARY TABLE IF EXISTS tmp_demo_fixture_numbers;
DROP TEMPORARY TABLE IF EXISTS tmp_demo_fixture_teams;
DROP TEMPORARY TABLE IF EXISTS tmp_demo_fixture_resolved_teams;
DROP TEMPORARY TABLE IF EXISTS tmp_demo_fixture_resolved_teams_away;

CREATE TEMPORARY TABLE tmp_demo_fixture_numbers (
    `n` TINYINT UNSIGNED NOT NULL PRIMARY KEY
) ENGINE=MEMORY;

INSERT INTO tmp_demo_fixture_numbers (`n`) VALUES
    (1), (2), (3), (4), (5),
    (6), (7), (8), (9), (10),
    (11), (12), (13), (14), (15),
    (16), (17), (18), (19), (20),
    (21), (22), (23), (24), (25);

CREATE TEMPORARY TABLE tmp_demo_fixture_teams (
    `team_number` TINYINT UNSIGNED NOT NULL PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL
) ENGINE=MEMORY;

INSERT INTO tmp_demo_fixture_teams (`team_number`, `name`) VALUES
    (1, 'Arsenal'),
    (2, 'Real Madrid'),
    (3, 'Manchester City'),
    (4, 'FC Barcelona'),
    (5, 'Liverpool'),
    (6, 'Bayern Munich'),
    (7, 'Paris Saint-Germain'),
    (8, 'Al Ahly SC'),
    (9, 'Mamelodi Sundowns FC'),
    (10, 'Flamengo');

CREATE TEMPORARY TABLE tmp_demo_fixture_resolved_teams (
    `team_number` TINYINT UNSIGNED NOT NULL PRIMARY KEY,
    `team_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL
) ENGINE=MEMORY;

-- MySQL cannot reopen the same temporary table twice in one SELECT, so create
-- a separate copy for the away-team join below.
CREATE TEMPORARY TABLE tmp_demo_fixture_resolved_teams_away (
    `team_number` TINYINT UNSIGNED NOT NULL PRIMARY KEY,
    `team_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL
) ENGINE=MEMORY;

START TRANSACTION;

-- Match the existing local preview football category; do not change its values.
INSERT INTO `game_categories`
    (`name`, `icon`, `status`, `created_at`, `updated_at`)
SELECT
    'Football',
    '<i class="fas fa-futbol" aria-hidden="true"></i>',
    1,
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
WHERE NOT EXISTS (
    SELECT 1
    FROM `game_categories`
    WHERE LOWER(TRIM(`name`)) = 'football'
      AND `status` = 1
);

SET @demo_category_id := (
    SELECT MIN(`id`)
    FROM `game_categories`
    WHERE LOWER(TRIM(`name`)) = 'football'
      AND `status` = 1
);

-- Reuse International Club Friendlies if it exists under the selected category.
INSERT INTO `game_tournaments`
    (`category_id`, `name`, `status`, `created_at`, `updated_at`)
SELECT
    @demo_category_id,
    'International Club Friendlies',
    1,
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
WHERE @demo_category_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `game_tournaments`
      WHERE `category_id` = @demo_category_id
        AND LOWER(TRIM(`name`)) = 'international club friendlies'
        AND `status` = 1
  );

SET @demo_tournament_id := (
    SELECT MIN(`id`)
    FROM `game_tournaments`
    WHERE `category_id` = @demo_category_id
      AND LOWER(TRIM(`name`)) = 'international club friendlies'
      AND `status` = 1
);

-- Reuse active team records when available; only create missing records.
INSERT INTO `game_teams`
    (`category_id`, `name`, `image`, `status`, `created_at`, `updated_at`)
SELECT
    @demo_category_id,
    seed.`name`,
    NULL,
    1,
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
FROM `tmp_demo_fixture_teams` seed
WHERE @demo_category_id IS NOT NULL
  AND NOT EXISTS (
    SELECT 1
    FROM `game_teams` existing_team
    WHERE LOWER(TRIM(existing_team.`name`)) = LOWER(TRIM(seed.`name`))
      AND existing_team.`category_id` = @demo_category_id
      AND existing_team.`status` = 1
);

INSERT INTO `tmp_demo_fixture_resolved_teams` (`team_number`, `team_id`, `name`)
SELECT seed.`team_number`, MIN(team.`id`), seed.`name`
FROM `tmp_demo_fixture_teams` seed
JOIN `game_teams` team
  ON LOWER(TRIM(team.`name`)) = LOWER(TRIM(seed.`name`))
 AND team.`category_id` = @demo_category_id
 AND team.`status` = 1
GROUP BY seed.`team_number`, seed.`name`;

INSERT INTO `tmp_demo_fixture_resolved_teams_away`
    (`team_number`, `team_id`, `name`)
SELECT `team_number`, `team_id`, `name`
FROM `tmp_demo_fixture_resolved_teams`;

-- Make 25 uniquely named, future fixtures. No markets or betting options are added.
INSERT INTO `game_matches`
    (`category_id`, `tournament_id`, `team1_id`, `team2_id`, `name`,
     `start_date`, `end_date`, `status`, `is_unlock`, `created_at`, `updated_at`)
SELECT
    @demo_category_id,
    @demo_tournament_id,
    home.`team_id`,
    away.`team_id`,
    CONCAT(home.`name`, ' vs ', away.`name`),
    DATE_ADD(CURRENT_TIMESTAMP, INTERVAL numbers.`n` DAY),
    DATE_ADD(DATE_ADD(CURRENT_TIMESTAMP, INTERVAL numbers.`n` DAY), INTERVAL 2 HOUR),
    1,
    0,
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
FROM `tmp_demo_fixture_numbers` numbers
JOIN `tmp_demo_fixture_resolved_teams` home
  ON home.`team_number` = FLOOR((numbers.`n` - 1) / 9) + 1
JOIN `tmp_demo_fixture_resolved_teams_away` away
  ON away.`team_number` = MOD(home.`team_number` + MOD(numbers.`n` - 1, 9), 10) + 1
WHERE @demo_category_id IS NOT NULL
  AND @demo_tournament_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `game_matches` existing_match
       WHERE existing_match.`team1_id` = home.`team_id`
         AND existing_match.`team2_id` = away.`team_id`
         AND existing_match.`tournament_id` = @demo_tournament_id
  );

COMMIT;

-- Expected result after the first run: 25.
SELECT COUNT(*) AS `fixture_count`
FROM `game_matches`
WHERE `tournament_id` = @demo_tournament_id;

DROP TEMPORARY TABLE IF EXISTS tmp_demo_fixture_resolved_teams;
DROP TEMPORARY TABLE IF EXISTS tmp_demo_fixture_resolved_teams_away;
DROP TEMPORARY TABLE IF EXISTS tmp_demo_fixture_teams;
DROP TEMPORARY TABLE IF EXISTS tmp_demo_fixture_numbers;
