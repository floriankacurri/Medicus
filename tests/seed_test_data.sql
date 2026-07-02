-- Test/demo data for Medicus (run AFTER importing database/schema.sql)
-- Password for ALL accounts below: Test1234!
-- Hash generated with: php -r "echo password_hash('Test1234!', PASSWORD_DEFAULT);"
USE medicus;

INSERT INTO users (name, email, `password`, role) VALUES
('Admin Test', 'admin@medicus.test', '$2y$10$AiHEvMEevImdqy0tIuFN5.oFyFHw9RXxVCJI7E75/KR4QNB1QwHwG', 'admin'),
('Dr. Elira Hoxha', 'doctor1@medicus.test', '$2y$10$AiHEvMEevImdqy0tIuFN5.oFyFHw9RXxVCJI7E75/KR4QNB1QwHwG', 'doctor'),
('Dr. Genc Krasniqi', 'doctor2@medicus.test', '$2y$10$AiHEvMEevImdqy0tIuFN5.oFyFHw9RXxVCJI7E75/KR4QNB1QwHwG', 'doctor'),
('Patient One', 'patient1@medicus.test', '$2y$10$AiHEvMEevImdqy0tIuFN5.oFyFHw9RXxVCJI7E75/KR4QNB1QwHwG', 'patient'),
('Patient Two', 'patient2@medicus.test', '$2y$10$AiHEvMEevImdqy0tIuFN5.oFyFHw9RXxVCJI7E75/KR4QNB1QwHwG', 'patient'),
('Patient Three', 'patient3@medicus.test', '$2y$10$AiHEvMEevImdqy0tIuFN5.oFyFHw9RXxVCJI7E75/KR4QNB1QwHwG', 'patient');

INSERT INTO doctors (user_id, specialization)
SELECT id, 'Kardiologji' FROM users WHERE email = 'doctor1@medicus.test';
INSERT INTO doctors (user_id, specialization)
SELECT id, 'Dermatologji' FROM users WHERE email = 'doctor2@medicus.test';

INSERT INTO patients (user_id) SELECT id FROM users WHERE email = 'patient1@medicus.test';
INSERT INTO patients (user_id) SELECT id FROM users WHERE email = 'patient2@medicus.test';
INSERT INTO patients (user_id) SELECT id FROM users WHERE email = 'patient3@medicus.test';

-- Availability schedule is inserted dynamically by run_api_tests.sh for the
-- correct day_of_week matching the test date (so tests remain valid on any run date).
