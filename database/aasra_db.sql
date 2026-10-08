-- AASRA database (v2) — fresh install.
-- Import this file in phpMyAdmin. It drops and recreates all AASRA tables.
-- Existing v1 databases: use database/migration_v2.sql instead (keeps your data).
CREATE DATABASE IF NOT EXISTS aasra_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE aasra_db;
SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS favorites,notifications,complaints,ratings,payments,bookings,provider_documents,provider_services,services,providers,users;
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

-- ===================== SEED DATA =====================
-- Demo logins:  admin@aasra.com / Admin@123   user@aasra.com / User@123   provider@aasra.com / Provider@123
INSERT INTO users(id,name,email,password,phone,address,role,status) VALUES
(1,'AASRA Admin','admin@aasra.com','$2y$10$YQCSvrpFY7pH9tJ4r3/Vsu7BZftrNw2URDOE5XTg7o4Lmq.BELcQ.','03000000001','Lahore, Punjab','admin','Active'),
(2,'Ahmad Hassan','user@aasra.com','$2y$10$JURXqsCcYbWfkORHFAYpKOQSiz2KyNCKicHUJolJMvtTl/EZ/d2om','03001234567','Johar Town, Lahore','user','Active'),
(3,'Sara Ahmed','sara@aasra.com','$2y$10$JURXqsCcYbWfkORHFAYpKOQSiz2KyNCKicHUJolJMvtTl/EZ/d2om','03002223344','Model Town, Lahore','user','Active'),
(4,'Ahmed Raza','ahmed.raza@aasra.com','$2y$10$JURXqsCcYbWfkORHFAYpKOQSiz2KyNCKicHUJolJMvtTl/EZ/d2om','03003334455','DHA Lahore','user','Active'),
(5,'Fatima Noor','fatima@aasra.com','$2y$10$JURXqsCcYbWfkORHFAYpKOQSiz2KyNCKicHUJolJMvtTl/EZ/d2om','03004445566','Wapda Town, Lahore','user','Active'),
(6,'Usman Malik','usman@aasra.com','$2y$10$JURXqsCcYbWfkORHFAYpKOQSiz2KyNCKicHUJolJMvtTl/EZ/d2om','03005556677','Bahria Town, Lahore','user','Inactive'),
(7,'Hina Shah','hina@aasra.com','$2y$10$JURXqsCcYbWfkORHFAYpKOQSiz2KyNCKicHUJolJMvtTl/EZ/d2om','03001334455','Garden Town, Lahore','user','Active'),
(8,'Ali Khan','provider@aasra.com','$2y$10$no8lyLYoBgK9b6xes4gI0.Z0UdLPqYjOW.QTm.WABY9iN4KM5aM6.','03001112233','Gulberg, Lahore','provider','Active'),
(9,'Nadia Sheikh','provider2@aasra.com','$2y$10$no8lyLYoBgK9b6xes4gI0.Z0UdLPqYjOW.QTm.WABY9iN4KM5aM6.','03006667788','DHA Lahore','provider','Active'),
(10,'Imran Qureshi','provider3@aasra.com','$2y$10$no8lyLYoBgK9b6xes4gI0.Z0UdLPqYjOW.QTm.WABY9iN4KM5aM6.','03007778899','Model Town, Lahore','provider','Active'),
(11,'Rukhsana Bibi','provider4@aasra.com','$2y$10$no8lyLYoBgK9b6xes4gI0.Z0UdLPqYjOW.QTm.WABY9iN4KM5aM6.','03008889900','Johar Town, Lahore','provider','Active'),
(12,'Hamza Tariq','provider5@aasra.com','$2y$10$no8lyLYoBgK9b6xes4gI0.Z0UdLPqYjOW.QTm.WABY9iN4KM5aM6.','03009990011','Gulberg, Lahore','provider','Active'),
(13,'Ayesha Farooq','provider6@aasra.com','$2y$10$no8lyLYoBgK9b6xes4gI0.Z0UdLPqYjOW.QTm.WABY9iN4KM5aM6.','03001010101','Garden Town, Lahore','provider','Active'),
(14,'Kashif Mehmood','provider7@aasra.com','$2y$10$no8lyLYoBgK9b6xes4gI0.Z0UdLPqYjOW.QTm.WABY9iN4KM5aM6.','03002020202','Bahria Town, Lahore','provider','Inactive'),
(15,'Sana Riaz','provider8@aasra.com','$2y$10$no8lyLYoBgK9b6xes4gI0.Z0UdLPqYjOW.QTm.WABY9iN4KM5aM6.','03003030303','Faisal Town, Lahore','provider','Active'),
(16,'Tahir Aziz','provider9@aasra.com','$2y$10$no8lyLYoBgK9b6xes4gI0.Z0UdLPqYjOW.QTm.WABY9iN4KM5aM6.','03004040404','Cantt, Lahore','provider','Active'),
(17,'Maryam Zafar','provider10@aasra.com','$2y$10$no8lyLYoBgK9b6xes4gI0.Z0UdLPqYjOW.QTm.WABY9iN4KM5aM6.',NULL,NULL,'provider','Active');

INSERT INTO services(id,service_name,description,status) VALUES
(1,'Elderly Care','Daily personal care and support for older adults, delivered with patience and dignity.','Active'),
(2,'Personal Assistance','Help with everyday personal activities such as dressing, grooming and meals.','Active'),
(3,'Daily Activity Assistance','Support with routines, errands, appointments and daily tasks.','Active'),
(4,'Companion Service','Friendly companionship, conversation and supervised activities.','Active'),
(5,'Transportation Assistance','Assistance with safe local transportation to clinics, markets and family visits.','Active'),
(6,'Home Assistance','Light household support, tidying and help around the home.','Active'),
(7,'Nursing Support','Basic nursing support such as medication reminders, vitals and wound dressing.','Active');

INSERT INTO providers(id,user_id,name,email,phone,location,bio,skills,experience,availability,charges,verification_status,verification_note,account_status) VALUES
(1,8,'Ali Khan','provider@aasra.com','03001112233','Gulberg, Lahore','Experienced caregiver who focuses on calm, respectful daily support for elderly clients and their families.','Elderly care, mobility support, medication reminders','8 years','Mon-Sat, 8 AM - 6 PM',1800,'Approved',NULL,'Active'),
(2,9,'Nadia Sheikh','provider2@aasra.com','03006667788','DHA, Lahore','Warm and organised companion who helps with personal routines, outings and conversation.','Companionship, personal grooming, meal preparation','6 years','Mon-Fri, 9 AM - 5 PM',1500,'Approved',NULL,'Active'),
(3,10,'Imran Qureshi','provider3@aasra.com','03007778899','Model Town, Lahore','Reliable driver and helper for clinic visits, grocery runs and family visits across Lahore.','Safe driving, wheelchair assistance, errands','5 years','Daily, 10 AM - 8 PM',1200,'Approved',NULL,'Active'),
(4,11,'Rukhsana Bibi','provider4@aasra.com','03008889900','Johar Town, Lahore','Home helper with a gentle approach to housekeeping and elderly support.','Housekeeping, cooking, elderly care','10 years','Mon-Sat, 8 AM - 4 PM',1000,'Approved',NULL,'Active'),
(5,12,'Hamza Tariq','provider5@aasra.com','03009990011','Gulberg, Lahore','Registered nursing assistant offering medication management and post-hospital support at home.','Vitals monitoring, wound dressing, medication management','7 years','Daily, 7 AM - 3 PM',2200,'Approved',NULL,'Active'),
(6,13,'Ayesha Farooq','provider6@aasra.com','03001010101','Garden Town, Lahore','Patient companion and activity helper for seniors and differently-abled adults.','Activity planning, companionship, communication support','4 years','Tue-Sun, 10 AM - 6 PM',1400,'Approved',NULL,'Active'),
(7,14,'Kashif Mehmood','provider7@aasra.com','03002020202','Bahria Town, Lahore','Transport and home support helper.','Driving, errands, light repairs','3 years','Mon-Fri, 9 AM - 5 PM',1300,'Approved',NULL,'Inactive'),
(8,15,'Sana Riaz','provider8@aasra.com','03003030303','Faisal Town, Lahore','Compassionate caregiver supporting elderly clients with daily routines and companionship.','Elderly care, companionship, light physiotherapy exercises','9 years','Mon-Sat, 9 AM - 7 PM',1600,'Approved',NULL,'Active'),
(9,16,'Tahir Aziz','provider9@aasra.com','03004040404','Cantt, Lahore','Home assistance and personal care helper.','Home care, lifting and transfers','2 years','Weekends, 9 AM - 5 PM',1100,'Pending',NULL,'Active'),
(10,17,'Maryam Zafar','provider10@aasra.com',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Not Submitted',NULL,'Active');

INSERT INTO provider_services(provider_id,service_id) VALUES
(1,1),(1,7),(2,2),(2,4),(3,5),(3,3),(4,6),(4,1),(5,7),(5,2),(6,4),(6,3),(7,5),(7,6),(8,1),(8,4),(9,6),(9,2);

INSERT INTO provider_documents(provider_id,document_type,document_path,verification_status,uploaded_at) VALUES
(1,'CNIC','uploads/documents/demo-cnic.pdf','Approved',NOW()-INTERVAL 40 DAY),
(2,'CNIC','uploads/documents/demo-cnic.pdf','Approved',NOW()-INTERVAL 38 DAY),
(3,'CNIC','uploads/documents/demo-cnic.pdf','Approved',NOW()-INTERVAL 35 DAY),
(4,'CNIC','uploads/documents/demo-cnic.pdf','Approved',NOW()-INTERVAL 30 DAY),
(5,'CNIC','uploads/documents/demo-cnic.pdf','Approved',NOW()-INTERVAL 28 DAY),
(5,'Experience Certificate','uploads/documents/demo-cnic.pdf','Approved',NOW()-INTERVAL 28 DAY),
(6,'CNIC','uploads/documents/demo-cnic.pdf','Approved',NOW()-INTERVAL 20 DAY),
(7,'CNIC','uploads/documents/demo-cnic.pdf','Approved',NOW()-INTERVAL 18 DAY),
(8,'CNIC','uploads/documents/demo-cnic.pdf','Approved',NOW()-INTERVAL 15 DAY),
(9,'CNIC','uploads/documents/demo-cnic.pdf','Pending',NOW()-INTERVAL 2 DAY),
(9,'Experience Certificate','uploads/documents/demo-cnic.pdf','Pending',NOW()-INTERVAL 2 DAY);

INSERT INTO bookings(id,user_id,provider_id,service_id,booking_date,booking_time,requirements,charges,status) VALUES
(1,2,1,1,CURDATE()-INTERVAL 20 DAY,'10:00:00','Morning assistance for my father',1800,'Completed'),
(2,2,2,4,CURDATE()+INTERVAL 5 DAY,'11:00:00','Companion support for afternoon walk',1500,'Pending'),
(3,2,5,7,CURDATE()+INTERVAL 7 DAY,'14:00:00','Medication reminders and vitals',2200,'Accepted'),
(4,3,4,6,CURDATE()-INTERVAL 12 DAY,'09:30:00','Light housekeeping',1000,'Completed'),
(5,4,3,5,CURDATE()-INTERVAL 10 DAY,'12:00:00','Clinic visit transport',1200,'Completed'),
(6,5,6,4,CURDATE()-INTERVAL 9 DAY,'15:00:00','Companion for evening activities',1400,'Completed'),
(7,7,8,1,CURDATE()-INTERVAL 6 DAY,'10:30:00','Elderly care for mother',1600,'Completed'),
(8,3,1,7,CURDATE()+INTERVAL 9 DAY,'13:00:00','Dressing change',1800,'Confirmed'),
(9,4,2,2,CURDATE()-INTERVAL 4 DAY,'09:00:00','Personal assistance',1500,'Cancelled'),
(10,5,5,7,CURDATE()-INTERVAL 14 DAY,'08:00:00','Post-hospital care',2200,'Completed'),
(11,7,1,1,CURDATE()-INTERVAL 25 DAY,'09:00:00','Daily assistance',1800,'Completed');

INSERT INTO payments(booking_id,amount,payment_method,payment_status,paid_at) VALUES
(1,1800,'Cash','Paid',NOW()-INTERVAL 20 DAY),(4,1000,'Cash','Paid',NOW()-INTERVAL 12 DAY),(5,1200,'Bank Transfer','Paid',NOW()-INTERVAL 10 DAY),
(6,1400,'Cash','Pending',NULL),(7,1600,'Cash','Pending',NULL),(10,2200,'Card','Paid',NOW()-INTERVAL 14 DAY);

INSERT INTO ratings(booking_id,user_id,provider_id,rating,review) VALUES
(1,2,1,5,'Professional, patient and very helpful with my father.'),
(11,7,1,5,'Arrived on time and was wonderful with my grandmother.'),
(4,3,4,4,'Good work around the house and very respectful.'),
(5,4,3,5,'Safe driver and helped us at the clinic.'),
(6,5,6,4,'Great conversation and good energy.'),
(7,7,8,5,'Very supportive and punctual.'),
(10,5,5,5,'Skilled and caring, handled medication perfectly.');

INSERT INTO complaints(id,user_id,provider_id,booking_id,subject,description,status,admin_response,created_at) VALUES
(1,2,1,1,'Arrival time concern','The provider arrived about 30 minutes later than agreed. Please review the service timing.','Pending',NULL,NOW()-INTERVAL 2 DAY),
(2,3,4,4,'Cleaning scope','Some of the agreed tasks were not completed during the visit.','Under Review',NULL,NOW()-INTERVAL 5 DAY),
(3,5,6,6,'Positive feedback','The provider was helpful and punctual. Sharing this for the team.','Resolved','Thank you for the feedback. We have shared it with the provider.',NOW()-INTERVAL 8 DAY),
(4,4,2,9,'Cancellation fee','I was asked for a cancellation fee that was not mentioned during booking.','Pending',NULL,NOW()-INTERVAL 1 DAY);
UPDATE complaints SET resolved_at=NOW()-INTERVAL 6 DAY WHERE id=3;

INSERT INTO notifications(user_id,provider_id,title,message,type,is_read) VALUES
(2,NULL,'Booking accepted','Your booking #3 is now Accepted.','booking',0),
(2,NULL,'Welcome to AASRA','Browse verified providers and book support in a few clicks.','system',1),
(NULL,1,'New booking request','A new booking request has been submitted.','booking',0),
(NULL,9,'Verification pending','Your documents are waiting for admin review.','verification',0);

INSERT INTO favorites(user_id,provider_id) VALUES (2,1),(2,5),(2,8);
