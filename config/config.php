<?php
// Sweet Crumbs - application configuration
// Keep payment secrets server-side.
// NEVER put the Stripe secret key in JavaScript or public repositories.

const APP_NAME = 'Sweet Crumbs';
const CURRENCY = 'LKR';
const COUNTRY = 'Sri Lanka';

const BASE_URL = 'http://localhost/cake_shop_project';

const DB_HOST = '127.0.0.1';
const DB_NAME = 'cake_shop';
const DB_USER = 'root';
const DB_PASS = '';

/*
 * Load environment variables from .env
 */
$envFile = dirname(__DIR__) . '/.env';

if (file_exists($envFile)) {
    $envLines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($envLines as $line) {
        $line = trim($line);

        // Ignore comments
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        // Only process KEY=VALUE lines
        if (strpos($line, '=') === false) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);

        $key = trim($key);
        $value = trim($value);

        // Remove optional surrounding quotes
        $value = trim($value, "\"'");

        if ($key !== '') {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

/*
 * Stripe Test Mode
 */
$stripePublishableKey = getenv('STRIPE_PUBLISHABLE_KEY') ?: '';
$stripeSecretKey = getenv('STRIPE_SECRET_KEY') ?: '';

define('STRIPE_PUBLISHABLE_KEY', $stripePublishableKey);
define('STRIPE_SECRET_KEY', $stripeSecretKey);

const SHIPPING_FEE = 500.00;
const MAX_CART_QTY = 20;