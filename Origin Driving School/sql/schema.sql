SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;

CREATE TABLE branches (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  suburb VARCHAR(100),
  state VARCHAR(50),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE users (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  role ENUM('admin','staff','instructor','student') NOT NULL,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  phone VARCHAR(40),
  password_hash VARCHAR(255) NOT NULL,
  branch_id INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_users_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE students (
  id BIGINT PRIMARY KEY,
  license_status VARCHAR(30) DEFAULT 'learner',
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_students_user FOREIGN KEY (id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE instructors (
  id BIGINT PRIMARY KEY,
  qualifications VARCHAR(255),
  rating DECIMAL(2,1),
  cert_iv_no VARCHAR(80),
  cert_expiry_date DATE,
  wwc_no VARCHAR(80),
  wwc_expiry DATE,
  medical_check_date DATE,
  police_check_date DATE,
  adtav_member_no VARCHAR(80),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_instructors_user FOREIGN KEY (id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE courses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  price DECIMAL(10,2) NOT NULL DEFAULT 0,
  sessions_count INT NOT NULL DEFAULT 1,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE vehicles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  rego VARCHAR(20) NOT NULL UNIQUE,
  make VARCHAR(60),
  model VARCHAR(60),
  year SMALLINT,
  branch_id INT,
  status ENUM('available','maintenance','unavailable') NOT NULL DEFAULT 'available',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_vehicles_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE schedules (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  instructor_id BIGINT NOT NULL,
  vehicle_id INT,
  branch_id INT,
  start_time DATETIME NOT NULL,
  end_time DATETIME NOT NULL,
  status ENUM('Open','Reserved','Completed','Cancelled') NOT NULL DEFAULT 'Open',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_schedules_instructor FOREIGN KEY (instructor_id) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_schedules_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_schedules_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX idx_schedules_instructor_start (instructor_id, start_time),
  INDEX idx_schedules_vehicle_start (vehicle_id, start_time),
  UNIQUE KEY uq_instructor_start (instructor_id, start_time),
  UNIQUE KEY uq_vehicle_start (vehicle_id, start_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE bookings (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  student_id BIGINT NOT NULL,
  schedule_id BIGINT NOT NULL,
  course_id INT,
  status ENUM('Pending','Confirmed','Cancelled','Completed') NOT NULL DEFAULT 'Pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_bookings_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_bookings_schedule FOREIGN KEY (schedule_id) REFERENCES schedules(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_bookings_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL ON UPDATE CASCADE,
  UNIQUE KEY uq_booking_schedule (schedule_id),
  INDEX idx_bookings_student_status (student_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE invoices (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  student_id BIGINT NOT NULL,
  total DECIMAL(10,2) NOT NULL DEFAULT 0,
  status ENUM('Unpaid','Paid','Overdue','Void') NOT NULL DEFAULT 'Unpaid',
  due_date DATE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_invoices_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  INDEX idx_invoices_student_status (student_id, status),
  INDEX idx_invoices_due (due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE invoice_items (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  invoice_id BIGINT NOT NULL,
  description VARCHAR(255) NOT NULL,
  qty INT NOT NULL DEFAULT 1,
  unit_price DECIMAL(10,2) NOT NULL DEFAULT 0,
  CONSTRAINT fk_invoice_items_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX idx_items_invoice (invoice_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE payments (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  invoice_id BIGINT NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  method ENUM('Cash','Card','Bank') NOT NULL,
  paid_at DATETIME NOT NULL,
  ref VARCHAR(120),
  CONSTRAINT fk_payments_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX idx_payments_invoice (invoice_id),
  INDEX idx_payments_paidat (paid_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE notes (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  entity_type ENUM('student','booking','invoice','vehicle','instructor') NOT NULL,
  entity_id BIGINT NOT NULL,
  author_user_id BIGINT NOT NULL,
  text TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_notes_author FOREIGN KEY (author_user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE attachments (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  note_id BIGINT NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  mime VARCHAR(120),
  uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_attachments_note FOREIGN KEY (note_id) REFERENCES notes(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE notifications (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT NOT NULL,
  type VARCHAR(60) NOT NULL,
  payload_json JSON,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX idx_notifications_user (user_id, is_read),
  INDEX idx_notifications_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE messages (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  sender_id BIGINT NOT NULL,
  audience_scope ENUM('all','students','instructors','branch','user') NOT NULL DEFAULT 'all',
  subject VARCHAR(180) NOT NULL,
  body TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_messages_sender FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET foreign_key_checks = 1;
