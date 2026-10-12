-- AASRA database (v2) — fresh install.
-- Import this file in phpMyAdmin. It drops and recreates all AASRA tables.
-- Existing v1 databases: use database/migration_v2.sql instead (keeps your data).
CREATE DATABASE IF NOT EXISTS aasra_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE aasra_db;
SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS favorites,notifications,complaints,ratings,payments,bookings,provider_documents,provider_offerings,provider_services,services,providers,users;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE users(
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  phone VARCHAR(30) NULL,
  address VARCHAR(255) NULL,
  role ENUM('user','provider','admin') NOT NULL DEFAULT 'user',
  status ENUM('Active','Inactive') NOT NULL DEFAULT 'Active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX(role,status)
) ENGINE=InnoDB;

CREATE TABLE providers(
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL UNIQUE,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  phone VARCHAR(30) NULL,
  location VARCHAR(190) NULL,
  photo VARCHAR(255) NULL,
  bio TEXT NULL,
  skills TEXT NULL,
  experience VARCHAR(120) NULL,
  availability VARCHAR(255) NULL,
  charges DECIMAL(10,2) NULL,
  verification_status ENUM('Not Submitted','Pending','Approved','Rejected') NOT NULL DEFAULT 'Not Submitted',
  verification_note VARCHAR(500) NULL,
  account_status ENUM('Active','Inactive') NOT NULL DEFAULT 'Active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX(verification_status,account_status)
) ENGINE=InnoDB;

CREATE TABLE services(
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  service_name VARCHAR(120) NOT NULL UNIQUE,
  description TEXT,
  image VARCHAR(255) NULL,
  status ENUM('Active','Inactive') NOT NULL DEFAULT 'Active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE provider_services(
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  provider_id INT UNSIGNED NOT NULL,
  service_id INT UNSIGNED NOT NULL,
  UNIQUE KEY uq_ps(provider_id,service_id),
  FOREIGN KEY(provider_id) REFERENCES providers(id) ON DELETE CASCADE,
  FOREIGN KEY(service_id) REFERENCES services(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE provider_offerings(
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  provider_id INT UNSIGNED NOT NULL,
  service_id INT UNSIGNED NOT NULL,
  title VARCHAR(150) NOT NULL,
  description TEXT NULL,
  price DECIMAL(10,2) NULL,
  availability VARCHAR(255) NULL,
  experience VARCHAR(120) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_offering_title(provider_id,service_id,title),
  INDEX(service_id),
  CONSTRAINT fk_offering_listing FOREIGN KEY(provider_id,service_id) REFERENCES provider_services(provider_id,service_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE provider_documents(
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  provider_id INT UNSIGNED NOT NULL,
  document_type VARCHAR(80) NOT NULL,
  document_path VARCHAR(255) NOT NULL,
  verification_status ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(provider_id) REFERENCES providers(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE bookings(
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  provider_id INT UNSIGNED NOT NULL,
  service_id INT UNSIGNED NOT NULL,
  booking_date DATE NOT NULL,
  booking_time TIME NOT NULL,
  requirements TEXT,
  charges DECIMAL(10,2) NOT NULL,
  status ENUM('Pending','Accepted','Rejected','Confirmed','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
  cancellation_reason VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY(user_id) REFERENCES users(id),
  FOREIGN KEY(provider_id) REFERENCES providers(id),
  FOREIGN KEY(service_id) REFERENCES services(id),
  INDEX(provider_id,status),INDEX(user_id,status)
) ENGINE=InnoDB;

CREATE TABLE payments(
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  booking_id INT UNSIGNED NOT NULL UNIQUE,
  amount DECIMAL(10,2) NOT NULL,
  payment_method ENUM('Cash','Bank Transfer','Card') NOT NULL DEFAULT 'Cash',
  payment_status ENUM('Pending','Paid','Failed') NOT NULL DEFAULT 'Pending',
  transaction_reference VARCHAR(120),
  paid_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(booking_id) REFERENCES bookings(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE ratings(
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  booking_id INT UNSIGNED NOT NULL UNIQUE,
  user_id INT UNSIGNED NOT NULL,
  provider_id INT UNSIGNED NOT NULL,
  rating TINYINT UNSIGNED NOT NULL,
  review TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
  FOREIGN KEY(user_id) REFERENCES users(id),
  FOREIGN KEY(provider_id) REFERENCES providers(id),
  CHECK(rating BETWEEN 1 AND 5)
) ENGINE=InnoDB;

CREATE TABLE complaints(
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  provider_id INT UNSIGNED NULL,
  booking_id INT UNSIGNED NULL,
  subject VARCHAR(180) NOT NULL,
  description TEXT NOT NULL,
  status ENUM('Pending','Under Review','Resolved') NOT NULL DEFAULT 'Pending',
  admin_response TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  resolved_at DATETIME NULL,
  FOREIGN KEY(user_id) REFERENCES users(id),
  FOREIGN KEY(provider_id) REFERENCES providers(id) ON DELETE SET NULL,
  FOREIGN KEY(booking_id) REFERENCES bookings(id) ON DELETE SET NULL,
  INDEX(status,created_at)
) ENGINE=InnoDB;

CREATE TABLE notifications(
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  provider_id INT UNSIGNED NULL,
  title VARCHAR(180) NOT NULL,
  message TEXT NOT NULL,
  type VARCHAR(50) NOT NULL DEFAULT 'system',
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY(provider_id) REFERENCES providers(id) ON DELETE CASCADE,
  INDEX(user_id,is_read),INDEX(provider_id,is_read)
) ENGINE=InnoDB;

CREATE TABLE favorites(
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  provider_id INT UNSIGNED NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_fav(user_id,provider_id),
  FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY(provider_id) REFERENCES providers(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Initial admin account only
INSERT INTO users (name, email, password, role, status)
VALUES ('AASRA Admin', 'admin@gmail.com', '$2y$10$uW4fvksMr6w6fEqhL7yowO.G5l9Lls4yaELowMwAHIXsNQFgac2ju', 'admin', 'Active');