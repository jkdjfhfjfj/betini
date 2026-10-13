-- LOCAL PREVIEW ONLY: applies to /tmp/wallet-preview.sqlite.
-- Do not use this file for production or as a replacement for the legacy
-- production schema. It creates the empty legacy tables needed by public
-- home/auth screens and seeds one clearly labeled local demo football fixture.

CREATE TABLE IF NOT EXISTS configures (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    analytic_status INTEGER NOT NULL DEFAULT 0,
    MEASUREMENT_ID TEXT NULL,
    tawk_status INTEGER NOT NULL DEFAULT 0,
    tawk_id TEXT NULL,
    reCaptcha_status_registration INTEGER NOT NULL DEFAULT 0,
    reCaptcha_status_login INTEGER NOT NULL DEFAULT 0,
    created_at DATETIME NULL,
    updated_at DATETIME NULL
);

INSERT OR IGNORE INTO configures (
    id, analytic_status, tawk_status,
    reCaptcha_status_registration, reCaptcha_status_login,
    created_at, updated_at
) VALUES (1, 0, 0, 0, 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

CREATE TABLE IF NOT EXISTS languages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL DEFAULT '',
    code TEXT NOT NULL DEFAULT '',
    short_name TEXT NOT NULL DEFAULT '',
    is_active INTEGER NOT NULL DEFAULT 0,
    created_at DATETIME NULL,
    updated_at DATETIME NULL
);

INSERT OR IGNORE INTO languages (
    id, name, code, short_name, is_active, created_at, updated_at
) VALUES (1, 'English', 'en', 'en', 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

CREATE TABLE IF NOT EXISTS template_media (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    section_name TEXT NOT NULL DEFAULT '',
    description TEXT NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL
);

CREATE TABLE IF NOT EXISTS contents (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL DEFAULT '',
    created_at DATETIME NULL,
    updated_at DATETIME NULL
);

CREATE TABLE IF NOT EXISTS content_details (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    content_id INTEGER NOT NULL DEFAULT 0,
    language_id INTEGER NOT NULL DEFAULT 1,
    description TEXT NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL
);

CREATE TABLE IF NOT EXISTS content_media (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    content_id INTEGER NOT NULL DEFAULT 0,
    description TEXT NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL
);

CREATE TABLE IF NOT EXISTS game_categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL DEFAULT '',
    slug TEXT NOT NULL DEFAULT '',
    icon TEXT NULL,
    status INTEGER NOT NULL DEFAULT 0,
    created_at DATETIME NULL,
    updated_at DATETIME NULL
);

CREATE TABLE IF NOT EXISTS game_tournaments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    category_id INTEGER NOT NULL DEFAULT 0,
    name TEXT NOT NULL DEFAULT '',
    slug TEXT NOT NULL DEFAULT '',
    status INTEGER NOT NULL DEFAULT 0,
    created_at DATETIME NULL,
    updated_at DATETIME NULL
);

CREATE TABLE IF NOT EXISTS game_matches (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    category_id INTEGER NOT NULL DEFAULT 0,
    tournament_id INTEGER NOT NULL DEFAULT 0,
    team1_id INTEGER NOT NULL DEFAULT 0,
    team2_id INTEGER NOT NULL DEFAULT 0,
    name TEXT NOT NULL DEFAULT '',
    start_date DATETIME NULL,
    end_date DATETIME NULL,
    status INTEGER NOT NULL DEFAULT 0,
    is_unlock INTEGER NOT NULL DEFAULT 0,
    created_at DATETIME NULL,
    updated_at DATETIME NULL
);

CREATE TABLE IF NOT EXISTS game_teams (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL DEFAULT '',
    image TEXT NULL,
    status INTEGER NOT NULL DEFAULT 1,
    created_at DATETIME NULL,
    updated_at DATETIME NULL
);

CREATE TABLE IF NOT EXISTS game_questions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    match_id INTEGER NOT NULL DEFAULT 0,
    name TEXT NOT NULL DEFAULT '',
    "limit" INTEGER NOT NULL DEFAULT 0,
    end_time DATETIME NULL,
    is_unlock INTEGER NOT NULL DEFAULT 0,
    status INTEGER NOT NULL DEFAULT 1,
    created_at DATETIME NULL,
    updated_at DATETIME NULL
);

CREATE TABLE IF NOT EXISTS game_options (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    match_id INTEGER NOT NULL DEFAULT 0,
    question_id INTEGER NOT NULL DEFAULT 0,
    creator_id INTEGER NOT NULL DEFAULT 0,
    option_name TEXT NULL,
    invest_amount REAL NOT NULL DEFAULT 0,
    return_amount REAL NOT NULL DEFAULT 0,
    minimum_amount REAL NOT NULL DEFAULT 0,
    ratio TEXT NULL,
    status INTEGER NOT NULL DEFAULT 1,
    created_at DATETIME NULL,
    updated_at DATETIME NULL
);

INSERT OR IGNORE INTO game_categories
    (id, name, slug, icon, status, created_at, updated_at)
VALUES
    (1, 'Football', 'football', 'fas fa-futbol', 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

INSERT OR IGNORE INTO game_tournaments
    (id, category_id, name, slug, status, created_at, updated_at)
VALUES
    (1, 1, 'Local Preview League', 'local-preview-league', 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

INSERT OR IGNORE INTO game_teams (id, name, image, status, created_at, updated_at)
VALUES
    (1, 'Preview City', NULL, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    (2, 'Demo United', NULL, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

INSERT OR IGNORE INTO game_matches
    (id, category_id, tournament_id, team1_id, team2_id, name, start_date, end_date, status, is_unlock, created_at, updated_at)
VALUES
    (1, 1, 1, 1, 2, 'LOCAL DEMO — Preview City vs Demo United',
     datetime('now', '+1 hour'), datetime('now', '+3 hours'), 1, 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

INSERT OR IGNORE INTO game_questions
    (id, match_id, name, "limit", end_time, is_unlock, status, created_at, updated_at)
VALUES
    (1, 1, 'Match Result (demo)', 1000, datetime('now', '+3 hours'), 0, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

INSERT OR IGNORE INTO game_options
    (id, match_id, question_id, creator_id, option_name, invest_amount, return_amount, minimum_amount, ratio, status, created_at, updated_at)
VALUES
    (1, 1, 1, 0, 'Preview City', 0, 0, 1, '1.80', 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    (2, 1, 1, 0, 'Draw', 0, 0, 1, '3.40', 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    (3, 1, 1, 0, 'Demo United', 0, 0, 1, '4.20', 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
