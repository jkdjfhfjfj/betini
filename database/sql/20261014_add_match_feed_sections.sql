-- Legacy schema-only script. For a new install, use
-- 20261015_match_sections_and_public_sports_catalog.sql instead.
-- If this older script was already applied, the consolidated script can still
-- be run: it checks for the country column and section table before adding them.
-- Do not run this legacy script after the consolidated script.

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
