CREATE DATABASE IF NOT EXISTS unistay;
USE unistay;

-- ─── Core tables ────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    university VARCHAR(150),
    course VARCHAR(100),
    year INT DEFAULT 1,
    university_id VARCHAR(50),
    rating DECIMAL(2,1) DEFAULT 0,
    is_verified TINYINT DEFAULT 0,
    avatar VARCHAR(255),
    dark_mode TINYINT DEFAULT 1,
    push_notifications TINYINT DEFAULT 1,
    language VARCHAR(20) DEFAULT 'English',
    enrollment_letter VARCHAR(255) DEFAULT NULL,
    campus_distance VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS hostels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    address VARCHAR(255),
    distance_from_campus VARCHAR(50),
    rating DECIMAL(2,1) DEFAULT 0,
    price_from DECIMAL(10,2),
    image VARCHAR(255),
    is_verified TINYINT DEFAULT 1,
    amenities JSON,
    latitude DECIMAL(10,7),
    longitude DECIMAL(10,7),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hostel_id INT NOT NULL,
    type VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    description VARCHAR(255),
    available_count INT DEFAULT 1,
    image VARCHAR(255),
    is_active TINYINT DEFAULT 1,
    FOREIGN KEY (hostel_id) REFERENCES hostels(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    hostel_id INT NOT NULL,
    room_id INT NOT NULL,
    move_in DATE NOT NULL,
    move_out DATE NOT NULL,
    status ENUM('pending','confirmed','cancelled','completed') DEFAULT 'pending',
    student_id_file VARCHAR(255),
    monthly_rent DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (hostel_id) REFERENCES hostels(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    booking_id INT,
    description VARCHAR(255),
    amount DECIMAL(10,2) NOT NULL,
    type ENUM('debit','credit') DEFAULT 'debit',
    status ENUM('pending','completed','failed') DEFAULT 'completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ─── Supporting tables ───────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    hostel_id INT NOT NULL,
    booking_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_review (user_id, hostel_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (hostel_id) REFERENCES hostels(id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS wishlists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    hostel_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_wishlist (user_id, hostel_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (hostel_id) REFERENCES hostels(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('booking','payment','system','review') DEFAULT 'system',
    is_read TINYINT DEFAULT 0,
    link VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS support_tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('open','in_progress','closed') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ─── Seed data ───────────────────────────────────────────────────────────────

-- Default admin: admin@unistay.com / admin123
INSERT INTO admins (name, email, password) VALUES
('Admin', 'admin@unistay.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')
ON DUPLICATE KEY UPDATE id=id;

INSERT INTO hostels (name, description, address, distance_from_campus, rating, price_from, image, amenities) VALUES
-- General hostels
('The Scholar\'s Den', 'A premium student living experience located just minutes away from the main library.', '14 Legon Boundary Rd, East Legon, Accra', '2 mins walk from University of Ghana', 4.8, 1200.00, 'https://images.unsplash.com/photo-1555854877-bab0e564b8d5?w=800&h=500&fit=crop', '["WiFi","Laundry","24/7 Security","Gym Access","Shared Kitchen"]'),
('Greenwood Residency', 'Modern student accommodation near KNUST Accra campus with all amenities.', '7 Adenta Road, Madina, Accra', 'Near KNUST Accra Campus Entrance', 4.5, 950.00, 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800&h=500&fit=crop', '["WiFi","Laundry","24/7 Security"]'),
('Urban Nest', 'Contemporary hostel in the heart of the university district, Accra.', '22 Okponglo Link, Okponglo, Accra', 'University of Ghana District, Accra', 4.6, 1400.00, 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800&h=500&fit=crop', '["WiFi","Laundry","24/7 Security","Gym Access"]'),
-- GCTU hostels (source: gctu.edu.gh official accommodation page)
('GCTU Hostel Blocks A & B', 'On-campus hostel blocks managed directly by Ghana Communication Technology University. Double occupancy rooms with essential facilities including a basketball court and mini football park.', 'GCTU Main Campus, Tesano, Accra', 'On campus – GCTU Tesano', 4.2, 625.00, 'https://images.unsplash.com/photo-1571624436279-b272aff752b5?w=800&h=500&fit=crop', '["WiFi","24/7 Security","DSTV","Generator","Food Court","Basketball Court"]'),
('GCTU Hostel Block C', 'Premium on-campus block with added comforts including in-room fridge and television. Managed by GCTU and ideal for students who want to stay close to academic facilities.', 'GCTU Main Campus, Tesano, Accra', 'On campus – GCTU Tesano', 4.3, 750.00, 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800&h=500&fit=crop', '["WiFi","24/7 Security","DSTV","Generator","Food Court","Fridge","TV"]'),
('International Students Hostel', 'Fully furnished ensuite hostel adjacent to Aqua Safe Water Limited, just 250 metres from the GCTU Tesano campus. Features a fully fitted kitchen, study area and spacious veranda.', 'Tesano, Adjacent Aqua Safe Water Limited, Accra', '250m from GCTU Tesano Campus', 4.5, 2200.00, 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=800&h=500&fit=crop', '["WiFi","24/7 Security","DSTV","Generator","Ensuite","Kitchen","Study Area","Water Heater"]'),
('Tesano Palace Hostel', 'Private hostel opposite Awo\'s Boutique in Tesano, offering ensuite rooms with a kitchenette, balcony and study area. Conveniently located between the GCTU Tesano and Abeka campuses.', 'Tesano, Opposite Awo\'s Boutique, Accra', '1.2 km from GCTU Tesano Campus', 4.4, 750.00, 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800&h=500&fit=crop', '["WiFi","24/7 Security","DSTV","Generator","Ensuite","Kitchenette","Balcony","Study Area"]'),
('Abeka Campus Hostel', 'Affordable private hostel close to the GCTU Abeka campus, offering shared and ensuite room options with a kitchenette and study area. A practical choice for students on a budget.', 'Abeka, Near GCTU Abeka Campus, Accra', '2 km from GCTU Tesano Campus', 4.1, 550.00, 'https://images.unsplash.com/photo-1493809842364-78817add7ffb?w=800&h=500&fit=crop', '["Overnight Security","Kitchenette","Study Area","Living Area"]');

INSERT INTO rooms (hostel_id, type, price, description, available_count) VALUES
-- The Scholar's Den (id 1)
(1, 'Premium Single', 1200.00, 'Private Kitchen • Utilities Incl.', 2),
(1, 'Standard Twin', 850.00, 'Shared Bathroom • Utilities Incl.', 0),
-- Greenwood Residency (id 2)
(2, 'Single Studio', 950.00, 'Private Bathroom • Utilities Incl.', 3),
(2, 'Shared Twin', 700.00, 'Shared Bathroom', 1),
-- Urban Nest (id 3)
(3, 'Premium Single', 1400.00, 'Private Kitchen • Utilities Incl.', 1),
(3, 'Shared Twin', 900.00, 'Shared Bathroom', 2),
-- GCTU Blocks A & B (id 4)
(4, 'Double Room', 625.00, 'Double Occupancy • Shared Bathroom • Per Semester', 10),
-- GCTU Block C (id 5)
(5, 'Double Room (Premium)', 750.00, 'Double Occupancy • In-room Fridge & TV • Per Semester', 8),
-- International Students Hostel (id 6)
(6, '4-in-One Room', 2200.00, 'Ensuite • Shared Kitchen • Per Semester', 4),
(6, '3-in-One Room', 2860.00, 'Ensuite • Shared Kitchen • Per Semester', 3),
-- Tesano Palace (id 7)
(7, '2-in-One Ensuite', 850.00, 'Ensuite • Balcony • Per Semester', 5),
(7, '3-in-One Room', 750.00, 'Shared Bathroom • Study Area • Per Semester', 6),
(7, '4-in-One Room', 750.00, 'Shared Bathroom • Per Semester', 8),
(7, '4-in-One with Kitchenette', 800.00, 'Shared Bathroom • Kitchenette • Per Semester', 4),
-- Abeka Campus Hostel (id 8)
(8, '2-in-One Room', 750.00, 'Shared Bathroom • Per Semester', 6),
(8, '2-in-One Ensuite', 625.00, 'Ensuite • Per Semester', 4),
(8, '4-in-One Room', 575.00, 'Shared Bathroom • Per Semester', 10);

-- ─── Update hostel images (run if DB already seeded) ─────────────────────────
UPDATE hostels SET image = 'https://images.unsplash.com/photo-1555854877-bab0e564b8d5?w=800&h=500&fit=crop' WHERE name = 'The Scholar\'s Den';
UPDATE hostels SET image = 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800&h=500&fit=crop' WHERE name = 'Greenwood Residency';
UPDATE hostels SET image = 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800&h=500&fit=crop' WHERE name = 'Urban Nest';
UPDATE hostels SET image = 'https://images.unsplash.com/photo-1571624436279-b272aff752b5?w=800&h=500&fit=crop' WHERE name = 'GCTU Hostel Blocks A & B';
UPDATE hostels SET image = 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800&h=500&fit=crop' WHERE name = 'GCTU Hostel Block C';
UPDATE hostels SET image = 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=800&h=500&fit=crop' WHERE name = 'International Students Hostel';
UPDATE hostels SET image = 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800&h=500&fit=crop' WHERE name = 'Tesano Palace Hostel';
UPDATE hostels SET image = 'https://images.unsplash.com/photo-1493809842364-78817add7ffb?w=800&h=500&fit=crop' WHERE name = 'Abeka Campus Hostel';
