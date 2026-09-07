

CREATE DATABASE IF NOT EXISTS sabor_street_kitchen
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE sabor_street_kitchen;

-- ---------------------------------------------------------
-- Users: registered accounts. Roles control access level.
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  full_name     VARCHAR(100)      NOT NULL,
  email         VARCHAR(150)      NOT NULL UNIQUE,
  password_hash VARCHAR(255)      NOT NULL,
  phone         VARCHAR(30)       NULL,
  role          ENUM('admin','member') NOT NULL DEFAULT 'member',
  created_at    TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Menu categories (Tacos, Antojitos, Bebidas, Postres)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
  id    INT AUTO_INCREMENT PRIMARY KEY,
  name  VARCHAR(50) NOT NULL,
  slug  VARCHAR(50) NOT NULL UNIQUE,
  sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Menu items, managed by admins via CRUD screens
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS menu_items (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  category_id   INT NOT NULL,
  name          VARCHAR(100)   NOT NULL,
  description   TEXT           NULL,
  price         DECIMAL(6,2)   NOT NULL,
  image         VARCHAR(255)   NULL,
  tags          VARCHAR(150)   NULL,
  is_featured   TINYINT(1)     NOT NULL DEFAULT 0,
  is_available  TINYINT(1)     NOT NULL DEFAULT 1,
  created_at    TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Orders placed at checkout. user_id is nullable so guests
-- can still order without an account.
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS orders (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  user_id         INT NULL,
  customer_name   VARCHAR(100) NOT NULL,
  customer_phone  VARCHAR(30)  NOT NULL,
  pickup_time     TIME         NOT NULL,
  payment_method  ENUM('card','cash') NOT NULL,
  notes           TEXT         NULL,
  subtotal        DECIMAL(8,2) NOT NULL,
  tax             DECIMAL(8,2) NOT NULL,
  total           DECIMAL(8,2) NOT NULL,
  status          ENUM('pending','confirmed','completed','cancelled') NOT NULL DEFAULT 'pending',
  created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Order line items. menu_item_id kept nullable so historical
-- orders survive even if a menu item is later deleted.
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS order_items (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  order_id      INT NOT NULL,
  menu_item_id  INT NULL,
  item_name     VARCHAR(100) NOT NULL,
  unit_price    DECIMAL(6,2) NOT NULL,
  quantity      INT NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Contact / reservation messages submitted via contact.php
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS messages (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(100) NOT NULL,
  email       VARCHAR(150) NOT NULL,
  phone       VARCHAR(30)  NULL,
  party_size  INT          NULL,
  message     TEXT         NOT NULL,
  is_read     TINYINT(1)   NOT NULL DEFAULT 0,
  created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =========================================================
-- SEED DATA
-- =========================================================

INSERT INTO categories (name, slug, sort_order) VALUES
  ('Tacos', 'tacos', 1),
  ('Antojitos', 'antojitos', 2),
  ('Bebidas', 'bebidas', 3),
  ('Postres', 'postres', 4);

INSERT INTO menu_items (category_id, name, description, price, image, tags, is_featured) VALUES
  (1, 'Carne Asada Tacos', 'Grilled marinated skirt steak, onion, cilantro, salsa roja. Order of two.', 4.25, 'dish-carne-asada.svg', 'Medium heat,Gluten-free', 1),
  (1, 'Al Pastor Tacos', 'Achiote-marinated pork, grilled pineapple, onion, cilantro. Order of two.', 4.50, 'dish-al-pastor.svg', 'Medium heat,Gluten-free', 0),
  (1, 'Baja Fish Tacos', 'Beer-battered cod, cabbage slaw, chipotle crema, pico de gallo. Order of two.', 5.25, 'dish-baja-fish.svg', 'Mild', 0),
  (1, 'Quesabirria', 'Crispy griddled tacos, melted oaxaca cheese, birria, consomm\u00e9 for dipping.', 6.75, 'dish-quesabirria.svg', 'Spicy,New', 1),
  (2, 'Street Corn Elote', 'Charred sweet corn, chili-lime crema, cotija, tajin.', 5.00, 'dish-elote.svg', 'Vegetarian', 1),
  (2, 'Quesadilla', 'Handmade flour tortilla, melted oaxaca cheese, choice of filling.', 6.00, 'dish-quesabirria.svg', 'Vegetarian option', 0),
  (2, 'Guac & Chips', 'Fresh-mashed avocado, lime, cilantro, house-fried tortilla chips.', 6.50, 'dish-guac.svg', 'Vegan,Gluten-free', 0),
  (3, 'Agua Fresca', 'Rotating fresh fruit water \u2014 ask what''s pouring tonight.', 3.50, 'dish-agua-fresca.svg', 'Vegan', 1),
  (3, 'Horchata', 'House-made rice milk, cinnamon, a touch of vanilla.', 3.75, 'dish-horchata.svg', 'Vegetarian', 0),
  (3, 'Jarritos Soda', 'Imported Mexican soda \u2014 ask about tonight''s flavors.', 3.00, 'dish-jarritos.svg', 'Vegetarian', 0),
  (4, 'Churros', 'Fried to order, cinnamon sugar, chocolate dipping sauce. Order of four.', 5.50, 'dish-churros.svg', 'Vegetarian', 0),
  (4, 'Tres Leches Cake', 'Three-milk soaked sponge cake, fresh whipped cream, cinnamon dust.', 5.75, 'dish-tres-leches.svg', 'Vegetarian', 0);

-- Default admin account — email: admin@sabor.example / password: Admin123!
-- Default member account  — email: member@sabor.example / password: Member123!
-- (password_hash values generated with PHP's password_hash(), PASSWORD_DEFAULT)
-- These two INSERTs are also re-created automatically by includes/db.php on
-- first run if the users table is empty, so the hashes always match the
-- PHP version actually installed. See includes/db.php for details.
