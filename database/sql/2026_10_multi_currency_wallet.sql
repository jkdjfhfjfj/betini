-- Multi-currency wallet setup for MySQL/MariaDB.
-- Back up the database first and run this file once, before deploying the new wallet code.
-- Existing users keep their current balance unchanged and are marked USD.
-- Do not add an application .env file or run this against the Replit preview database.

ALTER TABLE `users`
    ADD COLUMN `wallet_currency` CHAR(3) NOT NULL DEFAULT 'USD';

ALTER TABLE `bet_invests`
    ADD COLUMN `wallet_currency` CHAR(3) NOT NULL DEFAULT 'USD',
    ADD COLUMN `usd_rate` DECIMAL(20,10) NOT NULL DEFAULT 1;

ALTER TABLE `transactions`
    ADD COLUMN `wallet_currency` CHAR(3) NOT NULL DEFAULT 'USD',
    ADD COLUMN `usd_rate` DECIMAL(20,10) NULL DEFAULT NULL;

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

INSERT INTO `currency_rates` (`currency_code`, `usd_to_currency`, `source`, `fetched_at`, `created_at`, `updated_at`)
VALUES ('USD', 1, 'base', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
ON DUPLICATE KEY UPDATE `usd_to_currency` = 1, `source` = 'base', `fetched_at` = CURRENT_TIMESTAMP, `updated_at` = CURRENT_TIMESTAMP;

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
    `last_status_checked_at` TIMESTAMP(6) NULL,
    `credited_at` TIMESTAMP NULL DEFAULT NULL,
    `refunded_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `wallet_provider_transactions_reference_unique` (`reference`),
    KEY `wallet_provider_user_status_idx` (`user_id`, `status`),
    KEY `wallet_provider_ref_idx` (`provider`, `provider_reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
