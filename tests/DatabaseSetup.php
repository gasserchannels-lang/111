<?php

namespace Tests;

trait DatabaseSetup
{
    /**
     * Set up the database for testing.
     */
    protected function setUpDatabase(): void
    {
        // Force consistent SQLite connection for all tests
        config(['database.default' => 'testing']);
        config(['database.connections.testing.database' => ':memory:']);
        config(['database.connections.testing.driver' => 'sqlite']);
        config(['database.connections.testing.prefix' => '']);
        config(['database.connections.testing.foreign_key_constraints' => true]);

        // Also configure sqlite_testing connection to be consistent
        config(['database.connections.sqlite_testing.database' => ':memory:']);
        config(['database.connections.sqlite_testing.driver' => 'sqlite']);
        config(['database.connections.sqlite_testing.prefix' => '']);
        config(['database.connections.sqlite_testing.foreign_key_constraints' => true]);

        // Create tables manually without migrations
        $this->createTablesManually();
    }

    /**
     * Create tables manually for testing.
     */
    protected function createTablesManually(): void
    {
        // Create tables in both testing and sqlite_testing connections
        $this->createTablesInConnection('testing');
        $this->createTablesInConnection('sqlite_testing');
    }

    /**
     * Create tables in a specific connection.
     */
    protected function createTablesInConnection(string $connection): void
    {
        // Use the specified connection
        \DB::connection($connection)->statement('PRAGMA foreign_keys=OFF;');
        \DB::connection($connection)->statement('PRAGMA auto_vacuum=0;'); // Disable auto vacuum

        // Get all table names and drop them (excluding sqlite_sequence)
        $tables = \DB::connection($connection)->select("SELECT name FROM sqlite_master WHERE type='table' AND name != 'sqlite_sequence';");
        foreach ($tables as $table) {
            \DB::connection($connection)->statement('DROP TABLE IF EXISTS '.$table->name);
        }

        \DB::connection($connection)->statement('PRAGMA foreign_keys=ON;');

        // Create essential tables for testing
        $this->createUsersTable($connection);
        $this->createProductsTable($connection);
        $this->createPriceOffersTable($connection);
        $this->createWishlistsTable($connection);
        $this->createBrandsTable($connection);
        $this->createCategoriesTable($connection);
        $this->createCurrenciesTable($connection);
        $this->createLanguagesTable($connection);
        $this->createPriceAlertsTable($connection);
        $this->createLanguageCurrencyTable($connection);
        $this->createStoresTable($connection);
        $this->createUserLocaleSettingsTable($connection);
        $this->createReviewsTable($connection);
        $this->createMigrationsTable($connection);
    }

    /**
     * Create users table.
     */
    protected function createUsersTable(string $connection = 'testing'): void
    {
        \DB::connection($connection)->statement('
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                email_verified_at DATETIME,
                password VARCHAR(255) NOT NULL,
                is_admin BOOLEAN DEFAULT 0,
                remember_token VARCHAR(100),
                created_at DATETIME,
                updated_at DATETIME
            )
        ');
    }

    /**
     * Create products table.
     */
    protected function createProductsTable(string $connection = 'testing'): void
    {
        \DB::connection($connection)->statement('
            CREATE TABLE products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255) NOT NULL,
                slug VARCHAR(255) UNIQUE NOT NULL,
                description TEXT,
                price DECIMAL(10,2) NOT NULL,
                image VARCHAR(255),
                image_url VARCHAR(255),
                category_id INTEGER,
                brand_id INTEGER,
                store_id INTEGER,
                is_active BOOLEAN DEFAULT 1,
                created_at DATETIME,
                updated_at DATETIME,
                deleted_at DATETIME
            )
        ');
    }

    /**
     * Create price_offers table.
     */
    protected function createPriceOffersTable(string $connection = 'testing'): void
    {
        \DB::connection($connection)->statement('
            CREATE TABLE price_offers (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                product_id INTEGER,
                product_sku VARCHAR(255),
                store_id INTEGER,
                price DECIMAL(10,2),
                currency VARCHAR(3) DEFAULT "USD",
                product_url VARCHAR(255),
                affiliate_url VARCHAR(255),
                in_stock BOOLEAN DEFAULT 1,
                stock_quantity INTEGER,
                condition VARCHAR(255) DEFAULT "new",
                rating DECIMAL(3,1),
                reviews_count INTEGER DEFAULT 0,
                image_url VARCHAR(255),
                specifications TEXT,
                is_available BOOLEAN DEFAULT 1,
                original_price DECIMAL(10,2),
                created_at DATETIME,
                updated_at DATETIME,
                FOREIGN KEY (product_id) REFERENCES products(id),
                FOREIGN KEY (store_id) REFERENCES stores(id)
            )
        ');
    }

    /**
     * Create wishlists table.
     */
    protected function createWishlistsTable(string $connection = 'testing'): void
    {
        \DB::connection($connection)->statement('
            CREATE TABLE wishlists (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                product_id INTEGER,
                notes TEXT,
                created_at DATETIME,
                updated_at DATETIME,
                deleted_at DATETIME,
                FOREIGN KEY (user_id) REFERENCES users(id),
                FOREIGN KEY (product_id) REFERENCES products(id)
            )
        ');
    }

    /**
     * Create brands table.
     */
    protected function createBrandsTable(string $connection = 'testing'): void
    {
        \DB::connection($connection)->statement('
            CREATE TABLE brands (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255) NOT NULL,
                slug VARCHAR(255) UNIQUE NOT NULL,
                description TEXT,
                logo_url VARCHAR(255),
                website_url VARCHAR(255),
                is_active BOOLEAN DEFAULT 1,
                created_at DATETIME,
                updated_at DATETIME,
                deleted_at DATETIME
            )
        ');
    }

    /**
     * Create categories table.
     */
    protected function createCategoriesTable(string $connection = 'testing'): void
    {
        \DB::connection($connection)->statement('
            CREATE TABLE categories (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255) NOT NULL,
                slug VARCHAR(255) UNIQUE NOT NULL,
                description TEXT,
                parent_id INTEGER,
                level INTEGER DEFAULT 0,
                is_active BOOLEAN DEFAULT 1,
                created_at DATETIME,
                updated_at DATETIME,
                deleted_at DATETIME
            )
        ');
    }

    /**
     * Create currencies table.
     */
    protected function createCurrenciesTable(string $connection = 'testing'): void
    {
        \DB::connection($connection)->statement('
            CREATE TABLE currencies (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                code VARCHAR(3) UNIQUE NOT NULL,
                name VARCHAR(255) NOT NULL,
                symbol VARCHAR(10),
                is_active BOOLEAN DEFAULT 1,
                is_default BOOLEAN DEFAULT 0,
                exchange_rate DECIMAL(10,4) DEFAULT 1.0000,
                decimal_places INTEGER DEFAULT 2,
                created_at DATETIME,
                updated_at DATETIME
            )
        ');
    }

    /**
     * Create languages table.
     */
    protected function createLanguagesTable(string $connection = 'testing'): void
    {
        \DB::connection($connection)->statement('
            CREATE TABLE languages (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                code VARCHAR(5) UNIQUE NOT NULL,
                name VARCHAR(255) NOT NULL,
                native_name VARCHAR(255),
                direction VARCHAR(3) DEFAULT "ltr",
                is_active BOOLEAN DEFAULT 1,
                is_default BOOLEAN DEFAULT 0,
                sort_order INTEGER DEFAULT 0,
                created_at DATETIME,
                updated_at DATETIME
            )
        ');
    }

    /**
     * Create price_alerts table.
     */
    protected function createPriceAlertsTable(string $connection = 'testing'): void
    {
        \DB::connection($connection)->statement('
            CREATE TABLE price_alerts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                product_id INTEGER NOT NULL,
                target_price DECIMAL(10,2) NOT NULL,
                current_price DECIMAL(10,2),
                repeat_alert BOOLEAN DEFAULT 0,
                is_active BOOLEAN DEFAULT 1,
                created_at DATETIME,
                updated_at DATETIME,
                deleted_at DATETIME
            )
        ');
    }

    protected function createLanguageCurrencyTable(string $connection = 'testing'): void
    {
        \DB::connection($connection)->statement('
            CREATE TABLE language_currency (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                language_id INTEGER NOT NULL,
                currency_id INTEGER NOT NULL,
                is_default BOOLEAN DEFAULT 0,
                created_at DATETIME,
                updated_at DATETIME
            )
        ');
    }

    protected function createStoresTable(string $connection = 'testing'): void
    {
        \DB::connection($connection)->statement('
            CREATE TABLE stores (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255) NOT NULL,
                slug VARCHAR(255) UNIQUE NOT NULL,
                description TEXT,
                logo_url VARCHAR(255),
                website_url VARCHAR(255),
                country_code VARCHAR(2),
                supported_countries TEXT,
                is_active BOOLEAN DEFAULT 1,
                priority INTEGER DEFAULT 0,
                affiliate_base_url VARCHAR(255),
                affiliate_code VARCHAR(255),
                api_config TEXT,
                currency_id INTEGER,
                created_at DATETIME,
                updated_at DATETIME,
                deleted_at DATETIME
            )
        ');
    }

    protected function createMigrationsTable(string $connection = 'testing'): void
    {
        \DB::connection($connection)->statement('
            CREATE TABLE migrations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                migration VARCHAR NOT NULL,
                batch INTEGER NOT NULL
            )
        ');
    }

    protected function createUserLocaleSettingsTable(string $connection = 'testing'): void
    {
        \DB::connection($connection)->statement('
            CREATE TABLE user_locale_settings (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                session_id VARCHAR(255),
                language_id INTEGER NOT NULL,
                currency_id INTEGER NOT NULL,
                ip_address VARCHAR(45),
                country_code VARCHAR(2),
                created_at DATETIME,
                updated_at DATETIME,
                FOREIGN KEY (user_id) REFERENCES users(id),
                FOREIGN KEY (language_id) REFERENCES languages(id),
                FOREIGN KEY (currency_id) REFERENCES currencies(id)
            )
        ');
    }

    /**
     * Create reviews table.
     */
    protected function createReviewsTable(string $connection = 'testing'): void
    {
        \DB::connection($connection)->statement('
            CREATE TABLE IF NOT EXISTS reviews (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                product_id INTEGER NOT NULL,
                title VARCHAR(255),
                content TEXT NOT NULL,
                rating INTEGER NOT NULL CHECK (rating >= 1 AND rating <= 5),
                is_verified_purchase BOOLEAN DEFAULT 0,
                is_approved BOOLEAN DEFAULT 1,
                helpful_votes TEXT,
                helpful_count INTEGER DEFAULT 0,
                created_at DATETIME,
                updated_at DATETIME,
                FOREIGN KEY (user_id) REFERENCES users(id),
                FOREIGN KEY (product_id) REFERENCES products(id)
            )
        ');
    }

    /**
     * Create personal_access_tokens table for Sanctum.
     */
    protected function createPersonalAccessTokensTable(string $connection = 'testing'): void
    {
        \DB::connection($connection)->statement('
            CREATE TABLE personal_access_tokens (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                tokenable_type VARCHAR(255) NOT NULL,
                tokenable_id INTEGER NOT NULL,
                name VARCHAR(255) NOT NULL,
                token VARCHAR(64) NOT NULL UNIQUE,
                abilities TEXT,
                last_used_at DATETIME,
                expires_at DATETIME,
                created_at DATETIME,
                updated_at DATETIME
            )
        ');
    }
}
