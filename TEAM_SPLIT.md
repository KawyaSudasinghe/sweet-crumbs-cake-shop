# Team File Responsibilities

This is a normal project structure. The folders are organized by feature, not by member.

## Member 1 — SE/2023/027 - Sudasinghe. S. S. K

Work mainly on:
- `auth/register.php`
- `auth/login.php`
- `auth/logout.php`
- `auth/profile.php`
- `auth/addresses.php`
- USER and ADDRESS sections of `database/schema.sql` and related seed data

## Member 2 — SE/2023/051 – Isiwari D.M

Work mainly on:
- `index.php` catalogue/category presentation
- `catalog/products.php`
- `catalog/product.php`
- CATEGORY and PRODUCT sections of `database/schema.sql` and `database/seeds.sql`
- Product/category images and catalogue filtering

## Member 3 — SE/2023/048 - I.D.N.Sewwandi

Work mainly on:
- `cart/cart.php`
- `cart/checkout.php`
- `cart/place_order.php`
- `orders/orders.php`
- `orders/order.php`
- `orders/order_success.php`
- CART, CART_ITEM, ORDER and ORDER_ITEM sections of the database

## Member 4 — SE/2023/030 - MEGALA M.

Work mainly on:
- `payment/payment.php`
- `payment/success.php`
- `payment/cancel.php`
- `reviews/review.php`
- PAYMENT, REVIEW and PROMO_CODE sections of the database
- Stripe integration and promo/review logic

## Shared integration files

All four members should coordinate changes to:
- `config/`
  - `config.php`
  - `database.php`
- `includes/`
  - `auth.php`
  - `footer.php`
  - `functions.php`
  - `header.php`
- `assets/css/style.css`
- `assets/js/app.js`
- `database/schema.sql`
- `database/seeds.sql`
- `.gitignore`
- `.env.example`

### Environment and API Key Security

- `.env.example` contains only placeholder values for required environment variables and **can be committed to Git**.
- `.env` contains actual API keys/secrets and **not committed to Git**.
- Each team member must create their own local `.env` file based on `.env.example`.


