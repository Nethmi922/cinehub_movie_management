-- =========================================================
-- Online Movie Management System - Database Schema
-- Built directly from submitted ER diagram
-- Import order matters (foreign keys) - run this file top to bottom
-- =========================================================

CREATE DATABASE IF NOT EXISTS movie_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE movie_management;

-- ---------------------------------------------------------
-- USER
-- ---------------------------------------------------------
CREATE TABLE user (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('customer','admin','counter_staff') NOT NULL DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------
-- BRANCH
-- ---------------------------------------------------------
CREATE TABLE branch (
    branch_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150),
    location VARCHAR(255) NOT NULL,
    contact_number VARCHAR(20)
);

-- ---------------------------------------------------------
-- HALL  (branch 1 -- N hall)
-- ---------------------------------------------------------
CREATE TABLE hall (
    hall_id INT AUTO_INCREMENT PRIMARY KEY,
    branch_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(150),
    capacity INT NOT NULL,
    type VARCHAR(50),
    FOREIGN KEY (branch_id) REFERENCES branch(branch_id) ON DELETE CASCADE
);

-- ---------------------------------------------------------
-- SEAT  (hall 1 -- N seat)
-- ---------------------------------------------------------
CREATE TABLE seat (
    seat_id INT AUTO_INCREMENT PRIMARY KEY,
    hall_id INT NOT NULL,
    seat_number VARCHAR(10) NOT NULL,
    row_label VARCHAR(5) NOT NULL,
    category ENUM('VIP','Standard') NOT NULL DEFAULT 'Standard',
    FOREIGN KEY (hall_id) REFERENCES hall(hall_id) ON DELETE CASCADE,
    UNIQUE KEY uniq_seat_per_hall (hall_id, seat_number)
);

-- ---------------------------------------------------------
-- MOVIE
-- ---------------------------------------------------------
CREATE TABLE movie (
    movie_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    duration INT NOT NULL,
    imdb_rate DECIMAL(3,1),
    genre VARCHAR(100),
    description TEXT,
    language VARCHAR(50),
    poster_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------
-- ACTOR
-- ---------------------------------------------------------
CREATE TABLE actor (
    actor_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    photo_path VARCHAR(255)
);

-- ---------------------------------------------------------
-- MOVIE_ACTOR  (movie M -- N actor)
-- ---------------------------------------------------------
CREATE TABLE movie_actor (
    movie_id INT NOT NULL,
    actor_id INT NOT NULL,
    character_type ENUM('main','sub') NOT NULL DEFAULT 'main',
    PRIMARY KEY (movie_id, actor_id),
    FOREIGN KEY (movie_id) REFERENCES movie(movie_id) ON DELETE CASCADE,
    FOREIGN KEY (actor_id) REFERENCES actor(actor_id) ON DELETE CASCADE
);

-- ---------------------------------------------------------
-- SHOW_TIME  (movie 1 -- N show_time -- N 1 hall)
-- ---------------------------------------------------------
CREATE TABLE show_time (
    show_id INT AUTO_INCREMENT PRIMARY KEY,
    movie_id INT NOT NULL,
    hall_id INT NOT NULL,
    experience_type VARCHAR(50),
    date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    FOREIGN KEY (movie_id) REFERENCES movie(movie_id) ON DELETE CASCADE,
    FOREIGN KEY (hall_id) REFERENCES hall(hall_id) ON DELETE CASCADE
);

-- ---------------------------------------------------------
-- OFFER  (created before booking, which references it)
-- ---------------------------------------------------------
CREATE TABLE offer (
    offer_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    discount_pct DECIMAL(5,2) NOT NULL,
    start_date DATE NOT NULL,
    expiry_date DATE NOT NULL,
    is_active TINYINT(1) DEFAULT 1
);

-- ---------------------------------------------------------
-- BOOKING  (user 1 -- N booking, booking_offer N -- 1 offer)
-- ---------------------------------------------------------
CREATE TABLE booking (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    booking_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('Pending','Confirmed','Cancelled') NOT NULL DEFAULT 'Pending',
    offer_id INT NULL,
    FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE,
    FOREIGN KEY (offer_id) REFERENCES offer(offer_id) ON DELETE SET NULL
);

-- ---------------------------------------------------------
-- SHOWTIME_SEAT  (bridge: show_time N -- N seat, per-showtime status)
-- ---------------------------------------------------------
CREATE TABLE showtime_seat (
    showtime_seat_id INT AUTO_INCREMENT PRIMARY KEY,
    show_id INT NOT NULL,
    seat_id INT NOT NULL,
    booking_id INT NULL,
    status ENUM('Available','Hold','Booked','CheckedIn') NOT NULL DEFAULT 'Available',
    hold_expires_at DATETIME NULL,
    FOREIGN KEY (show_id) REFERENCES show_time(show_id) ON DELETE CASCADE,
    FOREIGN KEY (seat_id) REFERENCES seat(seat_id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES booking(booking_id) ON DELETE SET NULL,
    UNIQUE KEY uniq_seat_per_showtime (show_id, seat_id)
);

-- ---------------------------------------------------------
-- PAYMENT  (booking 1 -- 1 payment)
-- ---------------------------------------------------------
CREATE TABLE payment (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL UNIQUE,
    amount DECIMAL(10,2) NOT NULL,
    method VARCHAR(50) NOT NULL,
    transaction_id VARCHAR(100),
    payment_date DATE,
    payment_time TIME,
    FOREIGN KEY (booking_id) REFERENCES booking(booking_id) ON DELETE CASCADE
);

-- ---------------------------------------------------------
-- TICKET  (one ticket per booking, single QR covers all seats)
-- ---------------------------------------------------------
CREATE TABLE ticket (
    ticket_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL UNIQUE,
    unique_qr_code VARCHAR(100) NOT NULL UNIQUE,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES booking(booking_id) ON DELETE CASCADE
);

-- ---------------------------------------------------------
-- REVIEW  (user N -- N movie via movie_review)
-- ---------------------------------------------------------
CREATE TABLE review (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    movie_id INT NOT NULL,
    rating TINYINT NOT NULL,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE,
    FOREIGN KEY (movie_id) REFERENCES movie(movie_id) ON DELETE CASCADE,
    UNIQUE KEY uniq_user_movie_review (user_id, movie_id),
    CONSTRAINT chk_rating CHECK (rating BETWEEN 1 AND 5)
);

-- ---------------------------------------------------------
-- LOYALTY  (user M -- N loyalty transactions)
-- ---------------------------------------------------------
CREATE TABLE loyalty (
    transaction_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    points INT NOT NULL,
    type ENUM('Earn','Redeem') NOT NULL,
    time TIME,
    date DATE,
    booking_id INT NULL,
    FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES booking(booking_id) ON DELETE SET NULL
);

-- =========================================================
-- SEED DATA (sample data so the site isn't empty on first run)
-- =========================================================

INSERT INTO user (name, email, password, phone, role) VALUES
('Admin User', 'admin@cinema.com', '$2y$10$Risi6H0bP5rmfkp3iK314uVVGdrxSD0QkwhU0ThL6vGq7WAasQSKG', '0770000000', 'admin');
-- Login: admin@cinema.com / admin123 (demo credentials — change in production)

INSERT INTO user (name, email, password, phone, role) VALUES
('Counter Staff', 'staff@cinema.com', '$2y$10$isT.fe7DdJXTgqvWsCo8fuR6oIKAdkYJPVi2ztfHhl8VmLQe0uToS', '0770000002', 'counter_staff');
-- Login: staff@cinema.com / staff123 (demo credentials — change in production)

INSERT INTO branch (name, email, location, contact_number) VALUES
('Cinemax City Centre', 'citycentre@cinemax.com', '123 Main Street, Colombo', '0112345678'),
('Cinemax Galle Road', 'galleroad@cinemax.com', '45 Galle Road, Colombo', '0112223344');

INSERT INTO hall (branch_id, name, location, capacity, type) VALUES
(1, 'Hall 1', 'Ground Floor', 40, '2D'),
(1, 'Hall 2 - IMAX', '1st Floor', 60, 'IMAX'),
(2, 'Hall 1', 'Ground Floor', 35, '2D');

-- Seats for Hall 1 (4 rows A-D, 10 seats each = 40 seats, row D = VIP)
INSERT INTO seat (hall_id, seat_number, row_label, category)
SELECT 1, CONCAT(r.row_label, n.num), r.row_label, IF(r.row_label='D','VIP','Standard')
FROM (SELECT 'A' AS row_label UNION SELECT 'B' UNION SELECT 'C' UNION SELECT 'D') r
CROSS JOIN (SELECT 1 num UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5
            UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10) n;

-- Seats for Hall 2 - IMAX (6 rows A-F, 10 seats each = 60 seats, row F = VIP)
INSERT INTO seat (hall_id, seat_number, row_label, category)
SELECT 2, CONCAT(r.row_label, n.num), r.row_label, IF(r.row_label='F','VIP','Standard')
FROM (SELECT 'A' AS row_label UNION SELECT 'B' UNION SELECT 'C' UNION SELECT 'D' UNION SELECT 'E' UNION SELECT 'F') r
CROSS JOIN (SELECT 1 num UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5
            UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10) n;

-- Seats for Hall 1 @ Branch 2 (Galle Road) (5 rows A-E, 7 seats each = 35 seats, row E = VIP)
INSERT INTO seat (hall_id, seat_number, row_label, category)
SELECT 3, CONCAT(r.row_label, n.num), r.row_label, IF(r.row_label='E','VIP','Standard')
FROM (SELECT 'A' AS row_label UNION SELECT 'B' UNION SELECT 'C' UNION SELECT 'D' UNION SELECT 'E') r
CROSS JOIN (SELECT 1 num UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5
            UNION SELECT 6 UNION SELECT 7) n;

INSERT INTO movie (title, duration, imdb_rate, genre, description, language, poster_path) VALUES
('Nebula Rising', 128, 8.2, 'Sci-Fi, Action', 'A stranded pilot must navigate a dying star system to bring a warning home.', 'English', 'assets/uploads/nebula_rising.jpg'),
('The Quiet Harbor', 104, 7.6, 'Drama', 'Three siblings return to their coastal hometown to confront a decades-old secret.', 'English', 'assets/uploads/quiet_harbor.jpg'),
('Laugh Track', 96, 6.9, 'Comedy', 'A washed-up sitcom writer gets one last shot when a sitcom is revived for streaming.', 'English', 'assets/uploads/laugh_track.jpg');

INSERT INTO actor (name) VALUES ('Elena Marsh'), ('Tobias Reign'), ('Priya Nandan'), ('Cole Whitfield');

INSERT INTO movie_actor (movie_id, actor_id, character_type) VALUES
(1, 1, 'main'), (1, 2, 'sub'),
(2, 3, 'main'), (2, 4, 'sub'),
(3, 2, 'main');

INSERT INTO show_time (movie_id, hall_id, experience_type, date, start_time, end_time) VALUES
(1, 2, 'IMAX', CURDATE(), '14:00:00', '16:08:00'),
(1, 2, 'IMAX', CURDATE(), '19:00:00', '21:08:00'),
(2, 1, '2D', CURDATE(), '17:30:00', '19:14:00'),
(3, 3, '2D', CURDATE(), '20:00:00', '21:36:00');

-- Populate showtime_seat for every showtime x every seat in that showtime's hall
INSERT INTO showtime_seat (show_id, seat_id, status)
SELECT st.show_id, s.seat_id, 'Available'
FROM show_time st
JOIN seat s ON s.hall_id = st.hall_id;

INSERT INTO offer (title, discount_pct, start_date, expiry_date, is_active) VALUES
('Weekday Special', 10.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 1),
('Student Discount', 15.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 60 DAY), 1);
