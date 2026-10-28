
CREATE DATABASE IF NOT EXISTS studenthub_p13;
USE studenthub_p13;

CREATE TABLE IF NOT EXISTS events (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(120) NOT NULL,
    event_date DATE NOT NULL,
    venue VARCHAR(150) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO events (title, event_date, venue, description)
VALUES
('AI Workshop', '2026-11-15', 'Computer Lab 1',
 'Introduction to practical AI tools.'),
('Web Technology Seminar', '2026-11-20', 'Seminar Hall',
 'A seminar on modern web development.'),
('Coding Club Meetup', '2026-11-25', 'Innovation Room',
 'Discussion and project ideas.');
