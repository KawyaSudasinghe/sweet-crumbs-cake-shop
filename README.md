# Sweet Crumbs — Online Cake Shop

A PHP/MySQL online cake shop developed for the SENG 21253 Web Application Development project.

## Live Website

The completed Sweet Crumbs website is available online at:

**https://sweetcrumbs.page.gd/**

You can visit the live website here:

**Sweet Crumbs — Online Cake Shop**
https://sweetcrumbs.page.gd/

## Technology

* HTML
* CSS
* Vanilla JavaScript
* PHP
* MySQL
* Stripe Checkout API

No framework is used.

## Main Features

### Customer Features

* Six product categories
* 75 seeded cake products
* Product search and category filtering
* Customer registration and login
* Password hashing and validation
* Customer profile management
* Saved delivery addresses
* Set and manage default addresses
* Shopping cart
* Update product quantities in the cart
* Cake inscription/custom message support
* Nationwide shipping
* Local pickup option
* Promo codes
* Order placement and checkout
* Stripe Checkout payment flow
* Server-side payment verification
* Order history
* Order details
* Order cancellation
* Product reviews
* 1–5 star product ratings
* Customers can add reviews for products they have purchased
* Responsive design
* LKR pricing

### Admin Features

* Secure admin login
* Dedicated admin dashboard
* View customer orders
* View individual order details
* Update order status
* Mark orders as processing, shipped, delivered
* Manage the order fulfilment process from the admin interface
* Admin authentication and logout

## Admin Login

The admin panel is available at:

**https://sweetcrumbs.page.gd/admin/login.php**

### Demo Admin Account

**Email:** `admin@sweetcrumbs.lk`
**Password:** `Admin@12345`

> These credentials are provided for demonstration purposes.

For a local installation, use:

**Admin Login:**
`http://localhost/sweet-crumbs/admin/login.php`

## Installation

### 1. Copy the project

Copy the project folder into:

```text
C:/xampp/htdocs/
```

Rename the project folder to:

```text
sweet-crumbs
```

The final folder should therefore be:

```text
C:/xampp/htdocs/sweet-crumbs/
```

### 2. Start XAMPP

Open XAMPP Control Panel and start:

* Apache
* MySQL

### 3. Create the database

Open phpMyAdmin:

```text
http://localhost/phpmyadmin/
```

Create a database for the project, for example:

```text
cake_shop
```

### 4. Import the database schema

Import:

```text
database/schema.sql
```

first.

### 5. Import the seed data and admin data

Import:

```text
database/seeds.sql
database/create_admin.sql
```

second.

### 6. Configure the environment

Copy:

```text
.env.example
```

and rename the copy to:

```text
.env
```

Add your Stripe keys to the `.env` file:

```text
STRIPE_PUBLISHABLE_KEY=your_publishable_key
STRIPE_SECRET_KEY=your_secret_key
```

### 7. Configure the application URL

Open:

```text
config/config.php
```

Confirm that the `BASE_URL` is:

```text
http://localhost/sweet-crumbs
```

### 8. Open the website

Visit:

```text
http://localhost/sweet-crumbs/
```

### 9. Open the admin panel

The local admin login page is:

```text
http://localhost/sweet-crumbs/admin/login.php
```

Use the provided admin credentials to access the admin dashboard.

> The schema file intentionally drops and recreates the project tables. Importing it into an existing database will reset the project database. Do not run it against a database containing real production data.

## Images

The project includes all product and category images in the `assets/images/` directory.

* Category images are stored in `assets/images/categories/`.
* Product images are stored in `assets/images/products/`.
* The brand logo is stored in `assets/images/brand-logo.png`.
* The images are included locally in the repository, so an internet connection is not required to display the product and category photographs.

See `IMAGE_SOURCES.md` for information about the original sources of the images.

## Stripe

The payment flow uses **Stripe Checkout in test mode**.

### Demo Test Card

```text
Card number: 4242 4242 4242 4242
Expiry: Any future date
CVC: Any 3 digits
ZIP/Postal code: Any valid value
```

Keep the Stripe secret key server-side and never expose or commit it to the repository.

The application uses the Stripe-hosted Checkout page for card payments.

## Customer Reviews

Customers can submit product reviews and ratings after purchasing products.

The review system supports:

* Written product reviews
* 1–5 star ratings
* Product-specific reviews
* Review submission from the customer's order/purchase flow

This allows customers to provide feedback about products they have purchased.

## Order Management

Customers can:

* Place orders
* View their order history
* View individual order details
* Cancel eligible orders
* Complete payment through Stripe
* Review purchased products

Administrators can:

* View customer orders
* Open individual order details
* Update order status
* Manage the fulfilment process
* Mark orders as shipped
* Mark orders as delivered

## Project Structure

```text
sweet-crumbs/
│
├── admin/
│   ├── footer.php
│   ├── header.php
│   ├── index.php
│   ├── login.php
│   ├── logout.php
│   ├── order.php
│   ├── orders.php
│   └── update_order.php
│
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── app.js
│   └── images/
│
├── auth/
│
├── cart/
│
├── catalog/
│
├── config/
│
├── database/
│
├── includes/
│
├── orders/
│
├── payment/
│
├── reviews/
│
├── .env.example
├── .gitignore
├── IMAGE_SOURCES.md
├── README.md
└── TEAM_SPLIT.md
```

## Team Responsibilities

See `TEAM_SPLIT.md` for the detailed division of responsibilities among team members.

The project uses normal feature-based folders rather than member-number folders.

## Important Security Notes

* Never commit `.env` to GitHub.
* Never publish the Stripe secret key.
* Use Stripe test keys when demonstrating the project.
* Passwords are stored using password hashing.
* Admin access requires authentication.
* Production installations should use strong, unique admin credentials.
* The demo admin credentials in this README should be changed for a real deployment.
