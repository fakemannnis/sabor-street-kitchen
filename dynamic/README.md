# Sabor Street Kitchen — Dynamic (PHP + MySQL)

This is the dynamic version of the Sabor Street Kitchen site, converted from the original
static HTML/CSS/JS build. It adds user authentication, role-based access control, and a
MySQL database behind the menu, orders, and contact messages.

**This has been fully built and tested end-to-end** (registration, login, role-based
access control, menu CRUD, checkout with server-side re-pricing, contact form, order
history) using PHP's built-in server.

## 1. Requirements

- PHP 8.0+ with the **PDO MySQL** and **mbstring** extensions (both are enabled by
  default in XAMPP, WAMP, and MAMP nothing extra to install if you're using one of those).
- MySQL or MariaDB.

## 2. Setup (XAMPP example)

1. Copy the whole `sabor-street-kitchen-dynamic` folder into your `htdocs` directory
   (e.g. `C:\xampp\htdocs\sabor-street-kitchen-dynamic` or `/Applications/XAMPP/htdocs/...`).
2. Start **Apache** and **MySQL** from the XAMPP control panel.
3. Open **phpMyAdmin** (`http://localhost/phpmyadmin`), click **Import**, and import
   `sql/schema.sql`. This creates the `sabor_street_kitchen` database, all six tables,
   and seeds the menu with 12 items across 4 categories.
4. Open `includes/db.php` and check the four constants match your setup — the file
   already ships with typical XAMPP/WAMP/MAMP defaults, so most people won't need to
   change anything:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'sabor_street_kitchen');
   define('DB_USER', 'root');   // XAMPP default
   define('DB_PASS', '');       // XAMPP default is an empty password
   ```
   Only change these if your MySQL setup is non-standard; e.g. you set a root
   password yourself, or you're using a university-provided server with its own
   assigned username/password.
5. Visit `http://localhost/sabor-street-kitchen-dynamic/index.php` in your browser.

## 3. Demo accounts

The first time any page runs, two demo accounts are created automatically if the
`users` table is empty:

| Role   | Email                  | Password     |
| ------ | ---------------------- | ------------ |
| Admin  | `admin@sabor.example`  | `Admin123!`  |
| Member | `member@sabor.example` | `Member123!` |

Admins land on `/admin/index.php`; members land on `/account.php`.

## 4. What's dynamic now

| Feature                                                                | Where                                                                |
| ---------------------------------------------------------------------- | -------------------------------------------------------------------- |
| Register / log in / log out                                            | `register.php`, `login.php`, `logout.php`                            |
| Role-based access (admin vs member vs guest)                           | `includes/auth.php` (`require_login()`, `require_admin()`)           |
| Menu pulled live from MySQL                                            | `menu.php`, `index.php` (featured dishes)                            |
| Real order placement (Create)                                          | `checkout.php` inserts into `orders` + `order_items`                 |
| Order history per member (Read)                                        | `account.php`                                                        |
| Profile update / password change (Update)                              | `account.php`                                                        |
| Admin: add / edit / delete menu items (full CRUD)                      | `admin/menu-add.php`, `admin/menu-edit.php`, `admin/menu-delete.php` |
| Admin: view all orders, update status (Read/Update)                    | `admin/orders.php`                                                   |
| Admin: view / mark read / delete contact messages (Read/Update/Delete) | `admin/messages.php`                                                 |
| Contact form saved to database                                         | `contact.php`                                                        |
| Privacy notice                                                         | `privacy.php`                                                        |

The cart itself still lives in the browser's `localStorage` while you're browsing the
menu (for a snappy, no-reload add-to-cart experience); but at checkout, the cart
contents are sent to `checkout.php`, which **re-looks-up every price from the database**
and ignores any price submitted by the browser, before inserting the order. This was
specifically tested by submitting a tampered price of $0.01 for a $4.25 item — the
server correctly ignored it and charged the real price.

## 5. Security measures implemented

- Passwords hashed with `password_hash()` (bcrypt), never stored in plain text
- All SQL queries use PDO prepared statements (no string-concatenated SQL anywhere)
- CSRF tokens on every state-changing form, checked with `hash_equals()`
- Session ID regenerated on login (`session_regenerate_id`) to prevent session fixation
- Session cookies set `HttpOnly` and `SameSite=Lax`
- Role-based access control gates on both membership (`require_login`) and admin
  privilege (`require_admin`) for every sensitive page
- Server-side re-validation on every form, even where JavaScript also validates
  client-side (so the site is still safe if JS is disabled or bypassed)
- Order pricing is recalculated server-side from the database, never trusted from the client
- The checkout confirmation page reads the just-placed order ID from the session (not
  from the URL), so a guest can't view someone else's order by guessing an ID

## 6. What was tested

- Every public page loads with no PHP errors
- A guest is correctly redirected to login when visiting an admin-only page
- Registration succeeds, and a duplicate email is correctly rejected
- Login fails correctly with a wrong password, and succeeds with the right one
- Admin dashboard loads and lists menu items
- Admin can add, edit, and delete a menu item, and the change is immediately visible
  on the public menu page
- Contact form rejects invalid input server-side and saves valid submissions to the database
- Checkout inserts a real order with correctly calculated subtotal/tax/total
- A tampered client-side price is ignored; the server re-prices from the database
- A logged-in member's order is correctly linked to their account and appears in
  their order history
- A member account cannot reach the admin dashboard (role check enforced)
- Admin can update an order's status and manage (read/mark-read/delete) contact messages
- All stored passwords are bcrypt hashes, never plain text

## 7. Known limitations

- No email is actually sent for order confirmations or contact form replies,this is
  a prototype, so those are simulated with on-page messages instead.
- No payment processing is implemented; "Pay by card at pickup" is just a stored
  preference, not a real transaction.
- Basic brute-force protection (e.g. rate-limiting failed logins) was not implemented
