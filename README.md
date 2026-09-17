# Sweet Crumbs — Online Cake Shop

A PHP/MySQL online cake shop for the SENG 21253 Web Application Development project.

## Technology

- HTML
- CSS
- Vanilla JavaScript
- PHP
- MySQL
- Stripe Checkout API

No framework is used.

## Main features

- Sweet Crumbs pastel bakery storefront
- Real bakery photographs from Unsplash
- Cake-themed photographic hero background
- Six product categories
- 75 seeded products
- Search and category filtering
- Customer registration/login
- Password hashing and validation
- Saved addresses
- Cart and cake inscription messages
- Nationwide shipping and local pickup
- Promo codes
- Order history and order details
- Stripe Checkout payment flow with server-side payment verification
- Product reviews and 1–5 star ratings
- LKR pricing
- Responsive layout

## Installation

1. Copy `cake_shop_project` into `C:/xampp/htdocs/`.
2. Start Apache and MySQL in XAMPP.
3. Open phpMyAdmin.
4. Import `database/schema.sql` first.
5. Import `database/seeds.sql` second.
6. Copy `.env.example` and rename it to `.env`.
7. copy and paste this secret key and public key in to the .env file
8. Open `config/config.php` and confirm `BASE_URL` is `http://localhost/cake_shop_project`.
9. Visit `http://localhost/cake_shop_project/`.

The schema file intentionally drops/recreates the project tables, so importing it for a fresh project resets the database. Do not run it against a database containing real production data.

## Images

The seed data contains remote Unsplash image URLs. The browser therefore needs internet access to display the photographs. See `IMAGE_SOURCES.md` for the source pages.

## Stripe

demo card details - 4242 4242 4242 4242
The payment flow uses Stripe Checkout in test mode. Keep the Stripe secret key server-side in `config/config.php` and use the Stripe-hosted checkout page for card payments.

## Team responsibilities

See `TEAM_SPLIT.md`. The project uses normal feature-based folders rather than member-number folders.
