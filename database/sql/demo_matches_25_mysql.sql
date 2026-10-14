-- MySQL / MariaDB demo fixtures for the local-preview data set.
-- Additive and repeatable: it preserves existing categories, tournaments,
-- teams, and matches. Fixtures are marked LOCAL DEMO, locked, and have no odds,
-- so they cannot be used to place bets.
--
-- Run this in the app's remote database only if you want demo fixtures there.
-- The running application schema must include game_matches.name/status/is_unlock.

DROP TEMPORARY TABLE IF EXISTS tmp_demo_fixture_numbers;
DROP TEMPORARY TABLE IF EXISTS tmp_demo_fixture_teams;
DROP TEMPORARY TABLE IF EXISTS tmp_demo_fixture_resolved_teams;

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
    (1, 'Preview City'),
    (2, 'Demo United'),
    (3, 'Eastside Athletic'),
    (4, 'Lakeside Rovers'),
    (5, 'Nairobi Comets'),
    (6, 'Mombasa Mariners'),
    (7, 'Kisumu Stars'),
    (8, 'Rift Valley FC'),
    (9, 'Coastline United'),
    (10, 'Highlands FC');

CREATE TEMPORARY TABLE tmp_demo_fixture_resolved_teams (
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

-- Reuse Local Preview League if it exists under the selected category.
INSERT INTO `game_tournaments`
    (`category_id`, `name`, `status`, `created_at`, `updated_at`)
SELECT
    @demo_category_id,
    'Local Preview League',
    1,
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
WHERE @demo_category_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `game_tournaments`
      WHERE `category_id` = @demo_category_id
        AND LOWER(TRIM(`name`)) = 'local preview league'
        AND `status` = 1
  );

SET @demo_tournament_id := (
    SELECT MIN(`id`)
    FROM `game_tournaments`
    WHERE `category_id` = @demo_category_id
      AND LOWER(TRIM(`name`)) = 'local preview league'
      AND `status` = 1
);

-- Reuse demo team names if present; only create missing active records.
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

-- Make 25 uniquely named, future fixtures. No markets or betting options are added.
INSERT INTO `game_matches`
    (`category_id`, `tournament_id`, `team1_id`, `team2_id`, `name`,
     `start_date`, `end_date`, `status`, `is_unlock`, `created_at`, `updated_at`)
SELECT
    @demo_category_id,
    @demo_tournament_id,
    home.`team_id`,
    away.`team_id`,
    CONCAT('LOCAL DEMO MATCH ', LPAD(numbers.`n`, 2, '0')),
    DATE_ADD(CURRENT_TIMESTAMP, INTERVAL numbers.`n` DAY),
    DATE_ADD(DATE_ADD(CURRENT_TIMESTAMP, INTERVAL numbers.`n` DAY), INTERVAL 2 HOUR),
    1,
    1,
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
FROM `tmp_demo_fixture_numbers` numbers
JOIN `tmp_demo_fixture_resolved_teams` home
  ON home.`team_number` = FLOOR((numbers.`n` - 1) / 9) + 1
JOIN `tmp_demo_fixture_resolved_teams` away
  ON away.`team_number` = MOD(home.`team_number` + MOD(numbers.`n` - 1, 9), 10) + 1
WHERE @demo_category_id IS NOT NULL
  AND @demo_tournament_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM `game_matches` existing_match
      WHERE existing_match.`name` = CONCAT('LOCAL DEMO MATCH ', LPAD(numbers.`n`, 2, '0'))
        AND existing_match.`tournament_id` = @demo_tournament_id
  );

COMMIT;

-- Expected result after the first run: 25.
SELECT COUNT(*) AS `local_demo_match_count`
FROM `game_matches`
WHERE `tournament_id` = @demo_tournament_id
  AND `name` LIKE 'LOCAL DEMO MATCH %';

DROP TEMPORARY TABLE IF EXISTS tmp_demo_fixture_resolved_teams;
DROP TEMPORARY TABLE IF EXISTS tmp_demo_fixture_teams;
DROP TEMPORARY TABLE IF EXISTS tmp_demo_fixture_numbers;
