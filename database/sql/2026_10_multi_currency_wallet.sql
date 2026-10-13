-- Multi-currency wallet schema for MySQL/MariaDB.
--
-- IMPORTANT:
-- 1. Back up the production database and select the correct database first.
-- 2. Run the preflight SELECT by itself. Proceed only when every required
--    table/column check is 1 and selected_database is the intended database.
-- 3. This is additive and safe to rerun for the listed columns. It does not
--    change users.balance: existing balances remain USD.
-- 4. Do not run this against the local preview SQLite database.
--
-- The legacy app owns the base schemas for users, bet_invests, and
-- transactions. This file only adds wallet fields and new wallet tables.

SELECT
    DATABASE() AS selected_database,
    EXISTS (
        SELECT 1 FROM information_schema.tables
        WHERE table_schema = DATABASE() AND table_name = 'users'
    ) AS has_users,
    EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_schema = DATABASE()
          AND table_name = 'users'
          AND column_name = 'balance'
    ) AS has_users_balance,
    EXISTS (
        SELECT 1 FROM information_schema.tables
        WHERE table_schema = DATABASE() AND table_name = 'bet_invests'
    ) AS has_bet_invests,
    EXISTS (
        SELECT 1 FROM information_schema.tables
        WHERE table_schema = DATABASE() AND table_name = 'transactions'
    ) AS has_transactions;

-- Stop here if the preflight query above reports 0 for any required check.
-- Each conditional ALTER is compatible with MySQL/MariaDB versions that do
-- not support ALTER TABLE ... ADD COLUMN IF NOT EXISTS.

SET @wallet_sql = IF(
    EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_schema = DATABASE()
          AND table_name = 'users'
          AND column_name = 'wallet_currency'
    ),
    'SELECT ''users.wallet_currency already exists; skipped'' AS migration_status',
    'ALTER TABLE `users` ADD COLUMN `wallet_currency` CHAR(3) NOT NULL DEFAULT ''USD'''
);
PREPARE wallet_stmt FROM @wallet_sql;
EXECUTE wallet_stmt;
DEALLOCATE PREPARE wallet_stmt;

SET @wallet_sql = IF(
    EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_schema = DATABASE()
          AND table_name = 'bet_invests'
          AND column_name = 'wallet_currency'
    ),
    'SELECT ''bet_invests.wallet_currency already exists; skipped'' AS migration_status',
    'ALTER TABLE `bet_invests` ADD COLUMN `wallet_currency` CHAR(3) NOT NULL DEFAULT ''USD'''
);
PREPARE wallet_stmt FROM @wallet_sql;
EXECUTE wallet_stmt;
DEALLOCATE PREPARE wallet_stmt;

SET @wallet_sql = IF(
    EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_schema = DATABASE()
          AND table_name = 'bet_invests'
          AND column_name = 'usd_rate'
    ),
    'SELECT ''bet_invests.usd_rate already exists; skipped'' AS migration_status',
    'ALTER TABLE `bet_invests` ADD COLUMN `usd_rate` DECIMAL(20,10) NOT NULL DEFAULT 1'
);
PREPARE wallet_stmt FROM @wallet_sql;
EXECUTE wallet_stmt;
DEALLOCATE PREPARE wallet_stmt;

SET @wallet_sql = IF(
    EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_schema = DATABASE()
          AND table_name = 'transactions'
          AND column_name = 'wallet_currency'
    ),
    'SELECT ''transactions.wallet_currency already exists; skipped'' AS migration_status',
    'ALTER TABLE `transactions` ADD COLUMN `wallet_currency` CHAR(3) NOT NULL DEFAULT ''USD'''
);
PREPARE wallet_stmt FROM @wallet_sql;
EXECUTE wallet_stmt;
DEALLOCATE PREPARE wallet_stmt;

SET @wallet_sql = IF(
    EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_schema = DATABASE()
          AND table_name = 'transactions'
          AND column_name = 'usd_rate'
    ),
    'SELECT ''transactions.usd_rate already exists; skipped'' AS migration_status',
    'ALTER TABLE `transactions` ADD COLUMN `usd_rate` DECIMAL(20,10) NULL DEFAULT NULL'
);
PREPARE wallet_stmt FROM @wallet_sql;
EXECUTE wallet_stmt;
DEALLOCATE PREPARE wallet_stmt;

CREATE TABLE IF NOT EXISTS `currency_rates` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `currency_code` CHAR(3) NOT NULL,
    `usd_to_currency` DECIMAL(20,10) NOT NULL,
    `source` VARCHAR(40) NOT NULL DEFAULT 'currencyapi_com',
    `fetched_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `currency_rates_currency_code_unique` (`currency_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `currency_rates`
    (`currency_code`, `usd_to_currency`, `source`, `fetched_at`, `created_at`, `updated_at`)
VALUES
    ('USD', 1, 'base', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
ON DUPLICATE KEY UPDATE
    `usd_to_currency` = 1,
    `source` = 'base',
    `fetched_at` = CURRENT_TIMESTAMP,
    `updated_at` = CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `payment_provider_settings` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `provider` VARCHAR(32) NOT NULL,
    `settings` LONGTEXT NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `payment_provider_settings_provider_unique` (`provider`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wallet_provider_transactions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `reference` VARCHAR(80) NOT NULL,
    `provider` VARCHAR(32) NOT NULL,
    `direction` VARCHAR(16) NOT NULL,
    `currency_code` CHAR(3) NOT NULL,
    `amount` DECIMAL(20,4) NOT NULL,
    `fee` DECIMAL(20,4) NOT NULL DEFAULT 0,
    `net_amount` DECIMAL(20,4) NOT NULL,
    `usd_rate` DECIMAL(20,10) NULL DEFAULT NULL,
    `status` VARCHAR(16) NOT NULL DEFAULT 'pending',
    `provider_reference` VARCHAR(191) NULL,
    `metadata` LONGTEXT NULL,
    `failure_reason` VARCHAR(500) NULL,
    `last_status_checked_at` TIMESTAMP(6) NULL DEFAULT NULL,
    `credited_at` TIMESTAMP NULL DEFAULT NULL,
    `refunded_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `wallet_provider_transactions_reference_unique` (`reference`),
    KEY `wallet_provider_user_status_idx` (`user_id`, `status`),
    KEY `wallet_provider_ref_idx` (`provider`, `provider_reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payment API credentials are saved encrypted through the admin UI; do not
-- insert credentials into this script.
