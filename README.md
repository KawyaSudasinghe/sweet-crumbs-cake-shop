# Sweet Crumbs — Online Cake Shop

A PHP/MySQL online cake shop for the SENG 21253 Web Application Development project.

## Live Website

The completed Sweet Crumbs website is available online at:

**https://sweetcrumbs.page.gd/**

You can visit the live website here:

[Sweet Crumbs — Online Cake Shop](https://sweetcrumbs.page.gd/)

## Technology

- HTML
- CSS
- Vanilla JavaScript
- PHP
- MySQL
- Stripe Checkout API

No framework is used.

## Main features

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
7. Copy and paste your secret key and publishable key in to the .env file
8. Open `config/config.php` and confirm `BASE_URL` is `http://localhost/cake_shop_project`.
9. Visit `http://localhost/cake_shop_project/`.

The schema file intentionally drops/recreates the project tables, so importing it for a fresh project resets the database. Do not run it against a database containing real production data.

## Images

The project includes all product and category images in the `assets/images/` directory.

- Category images are stored in `assets/images/categories/`.
- Product images are stored in `assets/images/products/`.
- The images are included locally in the repository, so an internet connection is not required to display the product and category photographs.

See `IMAGE_SOURCES.md` for information about the original sources of the images.

## Stripe

demo card details - 4242 4242 4242 4242
The payment flow uses Stripe Checkout in test mode. Keep the Stripe secret key server-side in `config/config.php` and use the Stripe-hosted checkout page for card payments.

## Team responsibilities

See `TEAM_SPLIT.md`. The project uses normal feature-based folders rather than member-number folders.
