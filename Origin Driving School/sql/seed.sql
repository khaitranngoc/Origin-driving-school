-- /sql/seed.sql

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;

-- Branches
INSERT INTO branches (id, name, suburb, state) VALUES
(1,'CBD Branch','Melbourne CBD','VIC'),
(2,'Bayside Branch','St Kilda','VIC');

-- Users
INSERT INTO users (id, role, name, email, phone, password_hash, branch_id, created_at) VALUES
(1,'admin','Admin User','admin@origin-driving.au','0400000000', 
  '$2y$10$Q2EAXk3zGqX5xTkVj2fWIecLgROmW1gHVRQvOLNycE/0mvJIsvRVe',1,NOW()), -- password: admin123
(2,'instructor','Alex Turner','alex@origin-driving.au','0411111111',
  '$2y$10$X2joVhuM5upbDNHckhjMCuYtfLdnkzKrWrudXkU5Cu3CjAAx7gZg2',1,NOW()), -- password: instructor123
(3,'student','Minh Tran','minh@student.au','0422222222',
  '$2y$10$7ZPb99ipL5Z0k2k9OVJNE.1lSgCzO8fSg90/.gA1jc4h7jAzGzT3O',2,NOW()); -- password: student123

-- Student + Instructor details
INSERT INTO students (id, license_status, notes) VALUES
(3,'learner','Prefers automatic car');

INSERT INTO instructors (id, qualifications, rating, cert_iv_no, adtav_member_no) VALUES
(2,'Cert IV in Transport and Logistics',4.7,'CIV12345','ADTAV5678');

-- Courses
INSERT INTO courses (id, title, price, sessions_count, description) VALUES
(1,'Starter Pack',180,3,'3 lessons for beginners'),
(2,'Test Ready',280,5,'5 lessons + mock test'),
(3,'Refresher',70,1,'Single lesson refresher'),
(4,'Overseas Licence Conversion',90,1,'Help overseas licence holders prepare for VicRoads test');

-- Vehicles
INSERT INTO vehicles (id, rego, make, model, year, branch_id, status) VALUES
(1,'TOY-123','Toyota','Corolla',2022,1,'available'),
(2,'VIC-456','Hyundai','i30',2021,2,'available');

-- Schedules
INSERT INTO schedules (id, instructor_id, vehicle_id, branch_id, start_time, end_time, status) VALUES
(1,2,1,1,'2025-09-20 09:00:00','2025-09-20 10:00:00','Reserved'),
(2,2,2,2,'2025-09-23 14:30:00','2025-09-23 15:30:00','Open');

-- Bookings
INSERT INTO bookings (id, student_id, schedule_id, course_id, status, created_at) VALUES
(1,3,1,1,'Confirmed',NOW());

-- Invoices
INSERT INTO invoices (id, student_id, total, status, due_date, created_at) VALUES
(1,3,180,'Unpaid','2025-09-22',NOW());

-- Invoice Items
INSERT INTO invoice_items (id, invoice_id, description, qty, unit_price) VALUES
(1,1,'Starter Pack - 3 lessons',1,180);

-- Notifications
INSERT INTO notifications (id, user_id, type, payload_json, is_read, created_at) VALUES
(1,3,'reminder','{\"text\":\"Your next lesson is on 20 Sep at 9:00am.\"}',0,NOW()),
(2,3,'invoice','{\"text\":\"Invoice #1 is due on 22 Sep.\"}',0,NOW());

SET foreign_key_checks = 1;
