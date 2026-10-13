-- MySQL 8 / MariaDB. Run this additive script once against the production app
-- database before deploying code that uses match countries/sections and the
-- expanded sports catalog. It can safely be run again.
--
-- This seeds categories, competitions, and teams only. It does NOT create
-- match fixtures, scores, or betting odds. Teams are grouped by sport because
-- the current schema has no team-to-tournament pivot table.

-- Add the match-country field if it is not already present.
SET @has_match_country := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'game_matches'
      AND column_name = 'country'
);
SET @ddl := IF(
    @has_match_country = 0,
    'ALTER TABLE `game_matches` ADD COLUMN `country` VARCHAR(100) NULL',
    'SELECT 1'
);
PREPARE add_match_country FROM @ddl;
EXECUTE add_match_country;
DEALLOCATE PREPARE add_match_country;

-- Store the admin-assigned feed tabs (Countries, Zoom Soccer, Jackpots, New,
-- and Turbo) without changing existing match records.
CREATE TABLE IF NOT EXISTS `game_match_sections` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `match_id` BIGINT UNSIGNED NOT NULL,
    `section` VARCHAR(32) NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `game_match_sections_match_section_unique` (`match_id`, `section`),
    CONSTRAINT `game_match_sections_match_id_foreign`
        FOREIGN KEY (`match_id`) REFERENCES `game_matches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Older imported databases may not have this column even though the app uses
-- it to group teams by sport. Keep it nullable so existing team rows are not
-- assigned to a guessed sport.
SET @has_team_category := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'game_teams'
      AND column_name = 'category_id'
);
SET @ddl := IF(
    @has_team_category = 0,
    'ALTER TABLE `game_teams` ADD COLUMN `category_id` BIGINT UNSIGNED NULL',
    'SELECT 1'
);
PREPARE add_team_category FROM @ddl;
EXECUTE add_team_category;
DEALLOCATE PREPARE add_team_category;

-- Add the category foreign key when it is safe to do so. Do not fail the seed
-- if a legacy database already contains unassigned/orphaned team categories.
SET @has_team_category_foreign_key := (
    SELECT COUNT(*)
    FROM information_schema.key_column_usage
    WHERE table_schema = DATABASE()
      AND table_name = 'game_teams'
      AND column_name = 'category_id'
      AND referenced_table_name = 'game_categories'
      AND referenced_column_name = 'id'
);
SET @orphaned_team_categories := (
    SELECT COUNT(*)
    FROM `game_teams` team
    LEFT JOIN `game_categories` category ON category.`id` = team.`category_id`
    WHERE team.`category_id` IS NOT NULL
      AND category.`id` IS NULL
);
SET @team_category_type := (
    SELECT column_type
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'game_teams'
      AND column_name = 'category_id'
    LIMIT 1
);
SET @game_category_id_type := (
    SELECT column_type
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'game_categories'
      AND column_name = 'id'
    LIMIT 1
);
SET @ddl := IF(
    @has_team_category_foreign_key = 0
        AND @orphaned_team_categories = 0
        AND @team_category_type = @game_category_id_type,
    'ALTER TABLE `game_teams` ADD CONSTRAINT `game_teams_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `game_categories` (`id`) ON DELETE CASCADE',
    'SELECT 1'
);
PREPARE add_team_category_foreign_key FROM @ddl;
EXECUTE add_team_category_foreign_key;
DEALLOCATE PREPARE add_team_category_foreign_key;

-- game_categories stores name/icon/status (it has no slug column in the app schema).
-- Create standard sport categories without overwriting admin-edited records.
INSERT INTO `game_categories`
    (`name`, `icon`, `status`, `created_at`, `updated_at`)
SELECT seeded.`name`, seeded.`icon`, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
FROM (
    SELECT 'Football' AS `name`,
           '<i class="fas fa-futbol" aria-hidden="true"></i>' AS `icon`
    UNION ALL SELECT 'Cricket',
           '<i class="fas fa-cricket" aria-hidden="true"></i>'
    UNION ALL SELECT 'Basketball',
           '<i class="fas fa-basketball" aria-hidden="true"></i>'
    UNION ALL SELECT 'Rugby',
           '<i class="fas fa-football" aria-hidden="true"></i>'
) AS seeded
WHERE NOT EXISTS (
    SELECT 1 FROM `game_categories` current_category
    WHERE LOWER(TRIM(current_category.`name`)) = LOWER(TRIM(seeded.`name`))
);

-- game_tournaments stores category_id/name/status (it has no slug column).
-- A practical starter competition list: African domestic and continental
-- competitions plus widely followed international leagues.
INSERT INTO `game_tournaments`
    (`category_id`, `name`, `status`, `created_at`, `updated_at`)
SELECT category.`id`, seeded.`name`, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
FROM (
    SELECT 'Football' AS `category_name`, 'English Premier League' AS `name`
    UNION ALL SELECT 'Football', 'Spanish LaLiga'
    UNION ALL SELECT 'Football', 'Italian Serie A'
    UNION ALL SELECT 'Football', 'German Bundesliga'
    UNION ALL SELECT 'Football', 'French Ligue 1'
    UNION ALL SELECT 'Football', 'UEFA Champions League'
    UNION ALL SELECT 'Football', 'CAF Champions League'
    UNION ALL SELECT 'Football', 'CAF Confederation Cup'
    UNION ALL SELECT 'Football', 'FKF Premier League'
    UNION ALL SELECT 'Football', 'Tanzanian Premier League'
    UNION ALL SELECT 'Football', 'Uganda Premier League'
    UNION ALL SELECT 'Football', 'South African Premiership'
    UNION ALL SELECT 'Football', 'Nigeria Premier Football League'
    UNION ALL SELECT 'Football', 'Ghana Premier League'
    UNION ALL SELECT 'Football', 'Egyptian Premier League'
    UNION ALL SELECT 'Football', 'Botola Pro'
    UNION ALL SELECT 'Football', 'Major League Soccer'
    UNION ALL SELECT 'Football', 'Saudi Pro League'
    UNION ALL SELECT 'Football', 'Campeonato Brasileiro Serie A'
    UNION ALL SELECT 'Football', 'J1 League'
    UNION ALL SELECT 'Cricket', 'ICC Men''s T20 Internationals'
    UNION ALL SELECT 'Cricket', 'Indian Premier League'
    UNION ALL SELECT 'Cricket', 'Pakistan Super League'
    UNION ALL SELECT 'Cricket', 'Big Bash League'
    UNION ALL SELECT 'Cricket', 'SA20'
    UNION ALL SELECT 'Cricket', 'Caribbean Premier League'
    UNION ALL SELECT 'Basketball', 'NBA'
    UNION ALL SELECT 'Basketball', 'EuroLeague'
    UNION ALL SELECT 'Basketball', 'Basketball Africa League'
    UNION ALL SELECT 'Rugby', 'Rugby Africa Cup'
    UNION ALL SELECT 'Rugby', 'Six Nations Championship'
    UNION ALL SELECT 'Rugby', 'United Rugby Championship'
    UNION ALL SELECT 'Rugby', 'Super Rugby Pacific'
) AS seeded
JOIN `game_categories` category
  ON LOWER(TRIM(category.`name`)) = LOWER(TRIM(seeded.`category_name`))
WHERE category.`id` = (
    SELECT MIN(category_match.`id`)
    FROM `game_categories` category_match
    WHERE LOWER(TRIM(category_match.`name`)) = LOWER(TRIM(seeded.`category_name`))
)
AND NOT EXISTS (
    SELECT 1
    FROM `game_tournaments` current_tournament
    WHERE current_tournament.`category_id` = category.`id`
      AND LOWER(TRIM(current_tournament.`name`)) = LOWER(TRIM(seeded.`name`))
);

-- Well-known African and international clubs/national sides. Images are left
-- NULL so the app can use its existing fallback and admins can upload artwork.
INSERT INTO `game_teams`
    (`category_id`, `name`, `image`, `status`, `created_at`, `updated_at`)
SELECT category.`id`, seeded.`name`, NULL, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
FROM (
    SELECT 'Football' AS `category_name`, 'Al Ahly SC' AS `name`
    UNION ALL SELECT 'Football', 'Zamalek SC'
    UNION ALL SELECT 'Football', 'Pyramids FC'
    UNION ALL SELECT 'Football', 'Mamelodi Sundowns FC'
    UNION ALL SELECT 'Football', 'Orlando Pirates FC'
    UNION ALL SELECT 'Football', 'Kaizer Chiefs FC'
    UNION ALL SELECT 'Football', 'Simba SC'
    UNION ALL SELECT 'Football', 'Young Africans SC'
    UNION ALL SELECT 'Football', 'Gor Mahia FC'
    UNION ALL SELECT 'Football', 'AFC Leopards'
    UNION ALL SELECT 'Football', 'TP Mazembe'
    UNION ALL SELECT 'Football', 'AS Vita Club'
    UNION ALL SELECT 'Football', 'Esperance de Tunis'
    UNION ALL SELECT 'Football', 'Wydad AC'
    UNION ALL SELECT 'Football', 'Raja CA'
    UNION ALL SELECT 'Football', 'Enyimba FC'
    UNION ALL SELECT 'Football', 'Asante Kotoko SC'
    UNION ALL SELECT 'Football', 'Hearts of Oak SC'
    UNION ALL SELECT 'Football', 'Manchester City'
    UNION ALL SELECT 'Football', 'Arsenal'
    UNION ALL SELECT 'Football', 'Liverpool'
    UNION ALL SELECT 'Football', 'Chelsea'
    UNION ALL SELECT 'Football', 'Manchester United'
    UNION ALL SELECT 'Football', 'Tottenham Hotspur'
    UNION ALL SELECT 'Football', 'Real Madrid'
    UNION ALL SELECT 'Football', 'FC Barcelona'
    UNION ALL SELECT 'Football', 'Atletico Madrid'
    UNION ALL SELECT 'Football', 'Bayern Munich'
    UNION ALL SELECT 'Football', 'Borussia Dortmund'
    UNION ALL SELECT 'Football', 'Inter Milan'
    UNION ALL SELECT 'Football', 'AC Milan'
    UNION ALL SELECT 'Football', 'Juventus'
    UNION ALL SELECT 'Football', 'Paris Saint-Germain'
    UNION ALL SELECT 'Football', 'Flamengo'
    UNION ALL SELECT 'Football', 'Palmeiras'
    UNION ALL SELECT 'Football', 'River Plate'
    UNION ALL SELECT 'Football', 'Boca Juniors'
    UNION ALL SELECT 'Football', 'Al Hilal'
    UNION ALL SELECT 'Football', 'Al Nassr'
    UNION ALL SELECT 'Cricket', 'Kenya'
    UNION ALL SELECT 'Cricket', 'South Africa'
    UNION ALL SELECT 'Cricket', 'India'
    UNION ALL SELECT 'Cricket', 'Pakistan'
    UNION ALL SELECT 'Cricket', 'Australia'
    UNION ALL SELECT 'Cricket', 'England'
    UNION ALL SELECT 'Cricket', 'New Zealand'
    UNION ALL SELECT 'Cricket', 'West Indies'
    UNION ALL SELECT 'Cricket', 'Sri Lanka'
    UNION ALL SELECT 'Cricket', 'Bangladesh'
    UNION ALL SELECT 'Cricket', 'Afghanistan'
    UNION ALL SELECT 'Cricket', 'Zimbabwe'
    UNION ALL SELECT 'Cricket', 'Mumbai Indians'
    UNION ALL SELECT 'Cricket', 'Chennai Super Kings'
    UNION ALL SELECT 'Cricket', 'Royal Challengers Bengaluru'
    UNION ALL SELECT 'Cricket', 'Sydney Sixers'
    UNION ALL SELECT 'Cricket', 'Perth Scorchers'
    UNION ALL SELECT 'Basketball', 'Boston Celtics'
    UNION ALL SELECT 'Basketball', 'Los Angeles Lakers'
    UNION ALL SELECT 'Basketball', 'Golden State Warriors'
    UNION ALL SELECT 'Basketball', 'Chicago Bulls'
    UNION ALL SELECT 'Basketball', 'Miami Heat'
    UNION ALL SELECT 'Basketball', 'Denver Nuggets'
    UNION ALL SELECT 'Basketball', 'Real Madrid Baloncesto'
    UNION ALL SELECT 'Basketball', 'Olympiacos BC'
    UNION ALL SELECT 'Basketball', 'Al Ahly Basketball'
    UNION ALL SELECT 'Basketball', 'Petro de Luanda'
    UNION ALL SELECT 'Basketball', 'Zamalek Basketball'
    UNION ALL SELECT 'Basketball', 'US Monastir'
    UNION ALL SELECT 'Rugby', 'Kenya Simbas'
    UNION ALL SELECT 'Rugby', 'South Africa Springboks'
    UNION ALL SELECT 'Rugby', 'New Zealand All Blacks'
    UNION ALL SELECT 'Rugby', 'England Rugby'
    UNION ALL SELECT 'Rugby', 'Ireland Rugby'
    UNION ALL SELECT 'Rugby', 'France Rugby'
    UNION ALL SELECT 'Rugby', 'Namibia Rugby'
    UNION ALL SELECT 'Rugby', 'Zimbabwe Sables'
) AS seeded
JOIN `game_categories` category
  ON LOWER(TRIM(category.`name`)) = LOWER(TRIM(seeded.`category_name`))
WHERE category.`id` = (
    SELECT MIN(category_match.`id`)
    FROM `game_categories` category_match
    WHERE LOWER(TRIM(category_match.`name`)) = LOWER(TRIM(seeded.`category_name`))
)
AND NOT EXISTS (
    SELECT 1
    FROM `game_teams` current_team
    WHERE current_team.`category_id` = category.`id`
      AND LOWER(TRIM(current_team.`name`)) = LOWER(TRIM(seeded.`name`))
);
