-- AASRA v1 -> v2 migration. Run ONCE on an existing aasra_db (back it up first).
USE aasra_db;

-- users: real 'provider' role, Active/Inactive, optional phone/address
ALTER TABLE users MODIFY role ENUM('user','provider','admin') NOT NULL DEFAULT 'user',
  MODIFY status ENUM('Active','Suspended','Inactive') NOT NULL DEFAULT 'Active',
  MODIFY phone VARCHAR(30) NULL, MODIFY address VARCHAR(255) NULL;
UPDATE users SET status='Inactive' WHERE status='Suspended';
ALTER TABLE users MODIFY status ENUM('Active','Inactive') NOT NULL DEFAULT 'Active';
UPDATE users u JOIN providers p ON p.user_id=u.id SET u.role='provider' WHERE u.role='user';

-- providers
ALTER TABLE providers
  MODIFY phone VARCHAR(30) NULL,
  CHANGE address location VARCHAR(190) NULL,
  MODIFY charges DECIMAL(10,2) NULL,
  ADD COLUMN photo VARCHAR(255) NULL AFTER location,
  ADD COLUMN skills TEXT NULL AFTER bio,
  ADD COLUMN verification_note VARCHAR(500) NULL AFTER verification_status,
  MODIFY verification_status ENUM('Not Submitted','Pending','Approved','Rejected','Suspended') NOT NULL DEFAULT 'Not Submitted',
  MODIFY account_status ENUM('Active','Suspended','Inactive') NOT NULL DEFAULT 'Active';
UPDATE providers SET account_status='Inactive' WHERE account_status='Suspended' OR verification_status='Suspended';
UPDATE providers SET verification_status='Approved' WHERE verification_status='Suspended';
UPDATE providers p SET verification_status='Not Submitted'
  WHERE verification_status='Pending' AND NOT EXISTS(SELECT 1 FROM provider_documents d WHERE d.provider_id=p.id);
ALTER TABLE providers
  MODIFY verification_status ENUM('Not Submitted','Pending','Approved','Rejected') NOT NULL DEFAULT 'Not Submitted',
  MODIFY account_status ENUM('Active','Inactive') NOT NULL DEFAULT 'Active';
UPDATE users u JOIN providers p ON p.user_id=u.id SET u.status='Inactive' WHERE p.account_status='Inactive';

-- services: image support
ALTER TABLE services ADD COLUMN image VARCHAR(255) NULL AFTER description;
INSERT IGNORE INTO services(service_name,description,status) VALUES ('Nursing Support','Basic nursing support such as medication reminders and vitals.','Active');

-- complaints: Pending / Under Review / Resolved
ALTER TABLE complaints MODIFY status ENUM('Open','In Progress','Resolved','Rejected','Pending','Under Review') NOT NULL DEFAULT 'Pending';
UPDATE complaints SET status='Pending' WHERE status='Open';
UPDATE complaints SET status='Under Review' WHERE status='In Progress';
UPDATE complaints SET status='Resolved' WHERE status='Rejected';
ALTER TABLE complaints MODIFY status ENUM('Pending','Under Review','Resolved') NOT NULL DEFAULT 'Pending';

-- favorites
CREATE TABLE IF NOT EXISTS favorites(
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  provider_id INT UNSIGNED NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_fav(user_id,provider_id),
  FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY(provider_id) REFERENCES providers(id) ON DELETE CASCADE
) ENGINE=InnoDB;
