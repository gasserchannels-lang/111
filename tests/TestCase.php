<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Force consistent SQLite connection for all tests
        config(['database.default' => 'testing']);
        config(['database.connections.testing.database' => ':memory:']);
        config(['database.connections.testing.driver' => 'sqlite']);
        config(['database.connections.testing.prefix' => '']);
        config(['database.connections.testing.foreign_key_constraints' => true]);

        // Create tables manually without migrations
        $this->createTablesManually();
    }

    /**
     * Create tables manually for testing.
     */
    protected function createTablesManually(): void
    {
        // Use the testing connection
        \DB::connection('testing')->statement('PRAGMA foreign_keys=OFF;');

        // Get all table names and drop them
        $tables = \DB::connection('testing')->select("SELECT name FROM sqlite_master WHERE type='table';");
        foreach ($tables as $table) {
            \DB::connection('testing')->statement('DROP TABLE IF EXISTS '.$table->name);
        }

        \DB::connection('testing')->statement('PRAGMA foreign_keys=ON;');

        // Create essential tables for testing
        $this->createUsersTable();
        $this->createProductsTable();
        $this->createPriceOffersTable();
        $this->createWishlistsTable();
        $this->createBrandsTable();
        $this->createCategoriesTable();
        $this->createCurrenciesTable();
        $this->createLanguagesTable();
        $this->createPriceAlertsTable();
        $this->createLanguageCurrencyTable();
        $this->createStoresTable();
        $this->createMigrationsTable();
    }

    /**
     * Create users table.
     */
    protected function createUsersTable(): void
    {
        \DB::connection('testing')->statement('
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
    protected function createProductsTable(): void
    {
        \DB::connection('testing')->statement('
            CREATE TABLE products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                price DECIMAL(10,2) NOT NULL,
                image_url VARCHAR(255),
                category_id INTEGER,
                brand_id INTEGER,
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
    protected function createPriceOffersTable(): void
    {
        \DB::connection('testing')->statement('
            CREATE TABLE price_offers (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                product_id INTEGER,
                store_name VARCHAR(255),
                price DECIMAL(10,2),
                url VARCHAR(255),
                availability BOOLEAN DEFAULT 1,
                created_at DATETIME,
                updated_at DATETIME,
                FOREIGN KEY (product_id) REFERENCES products(id)
            )
        ');
    }

    /**
     * Create wishlists table.
     */
    protected function createWishlistsTable(): void
    {
        \DB::connection('testing')->statement('
            CREATE TABLE wishlists (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                product_id INTEGER,
                created_at DATETIME,
                updated_at DATETIME,
                FOREIGN KEY (user_id) REFERENCES users(id),
                FOREIGN KEY (product_id) REFERENCES products(id)
            )
        ');
    }

    /**
     * Create brands table.
     */
    protected function createBrandsTable(): void
    {
        \DB::connection('testing')->statement('
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
    protected function createCategoriesTable(): void
    {
        \DB::connection('testing')->statement('
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
    protected function createCurrenciesTable(): void
    {
        \DB::connection('testing')->statement('
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
    protected function createLanguagesTable(): void
    {
        \DB::connection('testing')->statement('
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
    protected function createPriceAlertsTable(): void
    {
        \DB::connection('testing')->statement('
            CREATE TABLE price_alerts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                product_id INTEGER NOT NULL,
                target_price DECIMAL(10,2) NOT NULL,
                current_price DECIMAL(10,2),
                is_active BOOLEAN DEFAULT 1,
                created_at DATETIME,
                updated_at DATETIME,
                deleted_at DATETIME
            )
        ');
    }

    protected function createLanguageCurrencyTable(): void
    {
        \DB::connection('testing')->statement('
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

    protected function createStoresTable(): void
    {
        \DB::connection('testing')->statement('
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

    protected function createMigrationsTable(): void
    {
        \DB::connection('testing')->statement('
            CREATE TABLE migrations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                migration VARCHAR NOT NULL,
                batch INTEGER NOT NULL
            )
        ');
    }

    /**
     * Tear down the test environment.
     */
    protected function tearDown(): void
    {
        // Rollback any remaining transactions
        while (\DB::transactionLevel() > 0) {
            \DB::rollBack();
        }

        parent::tearDown();
    }
}
