-- Moonlight Library database setup
-- Import this file through phpMyAdmin or the MySQL command line in XAMPP.

CREATE DATABASE IF NOT EXISTS moonlight_library
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE moonlight_library;

-- Administrator accounts are separate from ordinary library members.
CREATE TABLE IF NOT EXISTS administrators (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    email VARCHAR(150) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_administrator_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Registered library members and their profile details.
CREATE TABLE IF NOT EXISTS members (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    birth_date DATE NOT NULL,
    email VARCHAR(150) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    avatar_filename VARCHAR(100) DEFAULT NULL,
    phone VARCHAR(30) DEFAULT NULL,
    home_address VARCHAR(255) NOT NULL,
    membership_type ENUM('student', 'adult', 'senior') NOT NULL,
    favourite_genre VARCHAR(60) NOT NULL,
    newsletter TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_member_email (email),
    KEY idx_member_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories are stored once and shared by many books.
CREATE TABLE IF NOT EXISTS book_categories (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(80) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_book_category_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Catalogue books and the four statuses managed by administrators.
CREATE TABLE IF NOT EXISTS books (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    category_id INT UNSIGNED NOT NULL,
    title VARCHAR(180) NOT NULL,
    author VARCHAR(150) NOT NULL,
    format VARCHAR(40) NOT NULL,
    status ENUM('available', 'loaned', 'coming_soon', 'unavailable') NOT NULL DEFAULT 'available',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_book_title_author (title, author),
    KEY idx_book_category (category_id),
    KEY idx_book_status (status),
    CONSTRAINT fk_book_category FOREIGN KEY (category_id)
        REFERENCES book_categories (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Events published by an authenticated administrator.
CREATE TABLE IF NOT EXISTS events (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(120) NOT NULL,
    event_date DATE NOT NULL,
    event_time TIME NOT NULL,
    audience VARCHAR(80) NOT NULL,
    venue VARCHAR(120) NOT NULL,
    description TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_event_date (event_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Links members to events and prevents duplicate reservations.
CREATE TABLE IF NOT EXISTS event_reservations (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    member_id INT UNSIGNED NOT NULL,
    event_id INT UNSIGNED NOT NULL,
    reserved_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_member_event (member_id, event_id),
    KEY idx_reservation_event (event_id),
    CONSTRAINT fk_reservation_member FOREIGN KEY (member_id)
        REFERENCES members (id) ON DELETE CASCADE,
    CONSTRAINT fk_reservation_event FOREIGN KEY (event_id)
        REFERENCES events (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Demonstration administrator. Change this password before public deployment.
-- Email: admin@moonlightlibrary.com
-- Password: MoonlightAdmin@2026
INSERT INTO administrators (email, password_hash)
VALUES (
    'admin@moonlightlibrary.com',
    '$2y$10$XV5ov/J8cUcWUCTtjTE54Ogi7tV24T20bbcT4S1NTk.7efHo/5ofe'
)
ON DUPLICATE KEY UPDATE email = VALUES(email);

-- Initial catalogue categories.
INSERT IGNORE INTO book_categories (name) VALUES
    ('Fiction'),
    ('Science & Technology'),
    ('African Writers');

-- Initial catalogue books demonstrate every supported status.
INSERT IGNORE INTO books (category_id, title, author, format, status)
SELECT id, 'The Hobbit', 'J. R. R. Tolkien', 'Paperback', 'available'
FROM book_categories WHERE name = 'Fiction';

INSERT IGNORE INTO books (category_id, title, author, format, status)
SELECT id, 'Pride and Prejudice', 'Jane Austen', 'Hardcover', 'loaned'
FROM book_categories WHERE name = 'Fiction';

INSERT IGNORE INTO books (category_id, title, author, format, status)
SELECT id, 'The Little Prince', 'Antoine de Saint-Exupéry', 'Paperback', 'available'
FROM book_categories WHERE name = 'Fiction';

INSERT IGNORE INTO books (category_id, title, author, format, status)
SELECT id, 'A Brief History of Time', 'Stephen Hawking', 'Paperback', 'available'
FROM book_categories WHERE name = 'Science & Technology';

INSERT IGNORE INTO books (category_id, title, author, format, status)
SELECT id, 'Hidden Figures', 'Margot Lee Shetterly', 'Paperback', 'coming_soon'
FROM book_categories WHERE name = 'Science & Technology';

INSERT IGNORE INTO books (category_id, title, author, format, status)
SELECT id, 'HTML & Web Design Basics', 'Moonlight Library', 'Reference', 'unavailable'
FROM book_categories WHERE name = 'Science & Technology';

INSERT IGNORE INTO books (category_id, title, author, format, status)
SELECT id, 'Things Fall Apart', 'Chinua Achebe', 'Paperback', 'available'
FROM book_categories WHERE name = 'African Writers';

INSERT IGNORE INTO books (category_id, title, author, format, status)
SELECT id, 'Dust', 'Yvonne Adhiambo Owuor', 'Paperback', 'loaned'
FROM book_categories WHERE name = 'African Writers';

INSERT IGNORE INTO books (category_id, title, author, format, status)
SELECT id, 'Weep Not, Child', 'Ngũgĩ wa Thiong''o', 'Paperback', 'available'
FROM book_categories WHERE name = 'African Writers';

INSERT IGNORE INTO books (category_id, title, author, format, status)
SELECT id, 'Born a Crime', 'Trevor Noah', 'Hardcover', 'coming_soon'
FROM book_categories WHERE name = 'African Writers';

-- Sample future events for a newly imported demonstration database.
INSERT INTO events (title, event_date, event_time, audience, venue, description)
SELECT 'Web Development Workshop', '2026-12-12', '12:00:00', 'Ages 12–17', 'Study Area',
       'Bring a laptop with Visual Studio Code installed.'
WHERE NOT EXISTS (SELECT 1 FROM events WHERE title = 'Web Development Workshop');

INSERT INTO events (title, event_date, event_time, audience, venue, description)
SELECT 'Book Reading', '2026-09-09', '10:00:00', 'Adults', 'Grand Reading Hall',
       'Bring a notebook and enjoy a guided reading experience.'
WHERE NOT EXISTS (SELECT 1 FROM events WHERE title = 'Book Reading');
