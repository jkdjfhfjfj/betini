-- Run once on the production MySQL/MariaDB database before deploying the
-- application code. Do not run this file if the Laravel migration has already
-- been applied.

ALTER TABLE game_matches
    ADD COLUMN country VARCHAR(100) NULL;

CREATE TABLE game_match_sections (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    match_id BIGINT UNSIGNED NOT NULL,
    section VARCHAR(32) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY game_match_sections_match_section_unique (match_id, section),
    CONSTRAINT game_match_sections_match_id_foreign
        FOREIGN KEY (match_id) REFERENCES game_matches (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
