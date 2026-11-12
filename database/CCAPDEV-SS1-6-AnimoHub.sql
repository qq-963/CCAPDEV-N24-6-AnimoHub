-- AnimoHub (Group 6) - Phase 2 database script
DROP DATABASE IF EXISTS animohub;
CREATE DATABASE animohub CHARACTER SET utf8mb4;
USE animohub;

CREATE TABLE `user` (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  name VARCHAR(100) NOT NULL,
  role ENUM('admin','professor','student') NOT NULL
) ENGINE=InnoDB;

CREATE TABLE admin (
  admin_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL UNIQUE,
  office VARCHAR(100),
  FOREIGN KEY (user_id) REFERENCES `user`(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE professor (
  professor_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL UNIQUE,
  department VARCHAR(100),
  FOREIGN KEY (user_id) REFERENCES `user`(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE student (
  student_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL UNIQUE,
  program VARCHAR(50) NOT NULL DEFAULT 'CCS',
  year_level INT,
  FOREIGN KEY (user_id) REFERENCES `user`(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE org_role (
  role_id INT AUTO_INCREMENT PRIMARY KEY,
  role_name VARCHAR(50) NOT NULL UNIQUE,
  rank_level INT NOT NULL,
  can_manage BOOLEAN NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE organization (
  org_id INT AUTO_INCREMENT PRIMARY KEY,
  created_by INT,
  name VARCHAR(100) NOT NULL,
  description TEXT NOT NULL,
  category ENUM('Academic','Technology','Community','Government') NOT NULL,
  org_type VARCHAR(50) NOT NULL,
  color VARCHAR(7) NOT NULL DEFAULT '#0b6b3a',
  recruiting BOOLEAN NOT NULL DEFAULT 1,
  officer_hiring BOOLEAN NOT NULL DEFAULT 0,
  FOREIGN KEY (created_by) REFERENCES admin(admin_id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE membership (
  membership_id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  org_id INT NOT NULL,
  role_id INT NOT NULL,
  date_joined DATE NOT NULL,
  UNIQUE (student_id, org_id),
  FOREIGN KEY (student_id) REFERENCES student(student_id) ON DELETE CASCADE,
  FOREIGN KEY (org_id) REFERENCES organization(org_id) ON DELETE CASCADE,
  FOREIGN KEY (role_id) REFERENCES org_role(role_id)
) ENGINE=InnoDB;

CREATE TABLE application (
  application_id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  org_id INT NOT NULL,
  role_id INT NOT NULL,
  reason TEXT NOT NULL,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  date_submitted DATE NOT NULL,
  FOREIGN KEY (student_id) REFERENCES student(student_id) ON DELETE CASCADE,
  FOREIGN KEY (org_id) REFERENCES organization(org_id) ON DELETE CASCADE,
  FOREIGN KEY (role_id) REFERENCES org_role(role_id)
) ENGINE=InnoDB;

CREATE TABLE resignation (
  resignation_id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  org_id INT NOT NULL,
  reason TEXT NOT NULL,
  status ENUM('pending','accepted','declined') NOT NULL DEFAULT 'pending',
  date_submitted DATE NOT NULL,
  FOREIGN KEY (student_id) REFERENCES student(student_id) ON DELETE CASCADE,
  FOREIGN KEY (org_id) REFERENCES organization(org_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `event` (
  event_id INT AUTO_INCREMENT PRIMARY KEY,
  org_id INT NOT NULL,
  title VARCHAR(150) NOT NULL,
  `date` DATE NOT NULL,
  `time` TIME,
  place VARCHAR(150) NOT NULL,
  FOREIGN KEY (org_id) REFERENCES organization(org_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE announcement (
  announcement_id INT AUTO_INCREMENT PRIMARY KEY,
  org_id INT NOT NULL,
  title VARCHAR(150) NOT NULL,
  content TEXT NOT NULL,
  publish_date DATE NOT NULL,
  visibility ENUM('public','members') NOT NULL DEFAULT 'public',
  FOREIGN KEY (org_id) REFERENCES organization(org_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE course (
  course_id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(20) NOT NULL UNIQUE,
  name VARCHAR(150) NOT NULL,
  units INT NOT NULL
) ENGINE=InnoDB;

CREATE TABLE room (
  room_id INT AUTO_INCREMENT PRIMARY KEY,
  room_code VARCHAR(20) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE section (
  section_id INT AUTO_INCREMENT PRIMARY KEY,
  course_id INT NOT NULL,
  professor_id INT NOT NULL,
  room_id INT NOT NULL,
  section_code VARCHAR(10) NOT NULL,
  time_slot VARCHAR(15) NOT NULL,
  slots INT NOT NULL,
  max_slots INT NOT NULL,
  UNIQUE (course_id, section_code),
  FOREIGN KEY (course_id) REFERENCES course(course_id) ON DELETE CASCADE,
  FOREIGN KEY (professor_id) REFERENCES professor(professor_id),
  FOREIGN KEY (room_id) REFERENCES room(room_id)
) ENGINE=InnoDB;

CREATE TABLE section_schedule (
  schedule_id INT AUTO_INCREMENT PRIMARY KEY,
  section_id INT NOT NULL,
  day ENUM('M','T','W','H','F','S') NOT NULL,
  mode ENUM('Online','Onsite') NOT NULL,
  FOREIGN KEY (section_id) REFERENCES section(section_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE enlistment (
  enlistment_id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  section_id INT NOT NULL,
  date_enlisted DATE NOT NULL,
  UNIQUE (student_id, section_id),
  FOREIGN KEY (student_id) REFERENCES student(student_id) ON DELETE CASCADE,
  FOREIGN KEY (section_id) REFERENCES section(section_id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `user` (`user_id`, `email`, `password`, `name`, `role`) VALUES
(1, 'admin@dlsu.edu.ph', 'admin123', 'ITS Admin', 'admin'),
(2, 'ccs.admin@dlsu.edu.ph', 'admin123', 'CCS Admin', 'admin'),
(3, 'sa.office@dlsu.edu.ph', 'admin123', 'Student Affairs Admin', 'admin'),
(4, 'registrar@dlsu.edu.ph', 'admin123', 'Registrar Admin', 'admin'),
(5, 'dean.office@dlsu.edu.ph', 'admin123', 'Dean Office Admin', 'admin'),
(6, 'jose.rizal@dlsu.edu.ph', 'pass123', 'Jose Rizal', 'professor'),
(7, 'carlo.reyes@dlsu.edu.ph', 'pass123', 'Carlo Reyes', 'professor'),
(8, 'ana.cruz@dlsu.edu.ph', 'pass123', 'Ana Cruz', 'professor'),
(9, 'miguel.santos@dlsu.edu.ph', 'pass123', 'Miguel Santos', 'professor'),
(10, 'patricia.lim@dlsu.edu.ph', 'pass123', 'Patricia Lim', 'professor'),
(11, 'roberto.tan@dlsu.edu.ph', 'pass123', 'Roberto Tan', 'professor'),
(12, 'joel.villanueva@dlsu.edu.ph', 'pass123', 'Joel Villanueva', 'professor'),
(13, 'elena.garcia@dlsu.edu.ph', 'pass123', 'Elena Garcia', 'professor'),
(14, 'liza.ocampo@dlsu.edu.ph', 'pass123', 'Liza Ocampo', 'professor'),
(15, 'paolo.mendoza@dlsu.edu.ph', 'pass123', 'Paolo Mendoza', 'professor'),
(16, 'juan_delacruz@dlsu.edu.ph', 'pass123', 'Juan Dela Cruz', 'student'),
(17, 'maria_santos@dlsu.edu.ph', 'pass123', 'Maria Santos', 'student'),
(18, 'pedro_reyes@dlsu.edu.ph', 'pass123', 'Pedro Reyes', 'student'),
(19, 'ana_lim@dlsu.edu.ph', 'pass123', 'Ana Lim', 'student'),
(20, 'carlo_bautista@dlsu.edu.ph', 'pass123', 'Carlo Bautista', 'student'),
(21, 'bea_ramos@dlsu.edu.ph', 'pass123', 'Bea Ramos', 'student');

INSERT INTO `admin` (`admin_id`, `user_id`, `office`) VALUES
(1, 1, 'ITS Office'),
(2, 2, 'CCS Dean Office'),
(3, 3, 'Student Affairs'),
(4, 4, 'Registrar'),
(5, 5, 'Office of the Dean');

INSERT INTO `professor` (`professor_id`, `user_id`, `department`) VALUES
(1, 6, 'Software Technology'),
(2, 7, 'Software Technology'),
(3, 8, 'Software Technology'),
(4, 9, 'Software Technology'),
(5, 10, 'Software Technology'),
(6, 11, 'Computer Technology'),
(7, 12, 'Computer Technology'),
(8, 13, 'Network Technology'),
(9, 14, 'Software Technology'),
(10, 15, 'Humanities');

INSERT INTO `student` (`student_id`, `user_id`, `program`, `year_level`) VALUES
(1, 16, 'BS IT', 3),
(2, 17, 'BS CS', 2),
(3, 18, 'BS IT', 2),
(4, 19, 'BS CS', 1),
(5, 20, 'BS IT', 3),
(6, 21, 'BS CS', 2);

INSERT INTO `org_role` (`role_id`, `role_name`, `rank_level`, `can_manage`) VALUES
(1, 'President', 1, 1),
(2, 'Vice President', 2, 1),
(3, 'Secretary', 3, 1),
(4, 'Treasurer', 3, 1),
(5, 'Officer', 4, 1),
(6, 'Member', 5, 0);

INSERT INTO `organization` (`org_id`, `created_by`, `name`, `description`, `category`, `org_type`, `color`, `recruiting`, `officer_hiring`) VALUES
(1, 1, 'Computer Society', 'The home of CCS students who love coding, hackathons and tech talks. We hold workshops every term.', 'Academic', 'Academic Organization', '#0b6b3a', 1, 0),
(2, 1, 'Game Developers Guild', 'We make games together! Game jams, art and sound collabs, and playtesting nights.', 'Technology', 'Interest Organization', '#8e44ad', 1, 1),
(3, 1, 'Cybersecurity Circle', 'CTFs, security talks and ethical hacking training for everyone who is curious.', 'Technology', 'Interest Organization', '#c0392b', 0, 0),
(4, 1, 'Data Science Society', 'Learn data analysis, machine learning and visualization with other students.', 'Academic', 'Academic Organization', '#2471a3', 1, 0),
(5, 1, 'Robotics Club', 'Build robots, join competitions, and tinker with microcontrollers.', 'Technology', 'Interest Organization', '#d68910', 0, 1);

INSERT INTO `membership` (`membership_id`, `student_id`, `org_id`, `role_id`, `date_joined`) VALUES
(1, 1, 1, 1, '2026-08-20'),
(2, 2, 1, 5, '2026-08-22'),
(3, 3, 1, 6, '2026-08-25'),
(4, 2, 2, 1, '2026-08-21'),
(5, 3, 2, 6, '2026-09-01'),
(6, 3, 3, 1, '2026-08-21'),
(7, 1, 4, 6, '2026-09-03'),
(8, 5, 1, 2, '2026-08-23'),
(9, 6, 2, 5, '2026-09-02');

INSERT INTO `application` (`application_id`, `student_id`, `org_id`, `role_id`, `reason`, `status`, `date_submitted`) VALUES
(1, 4, 1, 6, 'I want to learn more about programming.', 'pending', '2026-11-02'),
(2, 4, 2, 6, 'I love making games and want to join the next jam.', 'pending', '2026-11-03'),
(3, 6, 1, 6, 'I want to join Computer Society events.', 'pending', '2026-11-04'),
(4, 5, 4, 6, 'I am interested in data analysis.', 'pending', '2026-11-04'),
(5, 3, 2, 5, 'I want to help lead the game jams.', 'pending', '2026-11-05'),
(6, 4, 4, 6, 'I want to learn machine learning.', 'rejected', '2026-10-15'),
(7, 6, 2, 6, 'I make pixel art and want to join the guild.', 'approved', '2026-08-30');

INSERT INTO `resignation` (`resignation_id`, `student_id`, `org_id`, `reason`, `status`, `date_submitted`) VALUES
(1, 3, 1, 'I have too many subjects this term.', 'pending', '2026-11-05'),
(2, 2, 1, 'I need more time for my thesis.', 'pending', '2026-11-06'),
(3, 3, 2, 'My schedule no longer fits the meetings.', 'pending', '2026-11-06'),
(4, 1, 4, 'I cannot attend the sessions anymore.', 'declined', '2026-10-20'),
(5, 6, 2, 'I will focus on my internship.', 'declined', '2026-10-25'),
(6, 5, 1, 'I am moving to the Cebu campus next term.', 'pending', '2026-11-07');

INSERT INTO `event` (`event_id`, `org_id`, `title`, `date`, `time`, `place`) VALUES
(1, 1, 'Annual Recruitment Week', '2026-10-20', NULL, 'Henry Sy Hall Lobby'),
(2, 1, 'Intro to Git Workshop', '2026-11-05', '14:00:00', 'G302'),
(3, 2, 'Game Jam 2026', '2026-11-14', '09:00:00', 'Gokongwei Hall'),
(4, 3, 'CTF Night', '2026-12-02', '18:00:00', 'Online'),
(5, 5, 'Line Follower Contest', '2026-11-28', '10:00:00', 'Br. Andrew Gonzalez Hall'),
(6, 4, 'Intro to Python for Data', '2026-11-20', '15:00:00', 'Online');

INSERT INTO `announcement` (`announcement_id`, `org_id`, `title`, `content`, `publish_date`, `visibility`) VALUES
(1, 1, 'Welcome to the new term!', 'Computer Society is open for applications. Check our events for the term.', '2026-09-01', 'public'),
(2, 1, 'Officers meeting', 'All officers meet on Friday at G302. Bring your reports.', '2026-11-03', 'members'),
(3, 2, 'Game Jam theme reveal', 'The theme will be announced one hour before the jam starts.', '2026-11-10', 'public'),
(4, 3, 'CTF practice room', 'Members can access the practice challenges this week.', '2026-11-08', 'members'),
(5, 4, 'Python workshop slots', 'Slots are limited, please apply early.', '2026-11-09', 'public'),
(6, 5, 'Parts order', 'Members, submit your parts list to the treasurer by Monday.', '2026-11-07', 'members');

INSERT INTO `course` (`course_id`, `code`, `name`, `units`) VALUES
(1, 'CCPROG3', 'Object-Oriented Programming', 3),
(2, 'CCAPDEV', 'Web Application Development', 3),
(3, 'CSALGCM', 'Algorithms and Complexity', 3),
(4, 'CSARCH1', 'Computer Organization and Architecture', 3),
(5, 'ITNET04', 'Networking 2', 3),
(6, 'CCDSTRU', 'Data Structures and Algorithms', 4),
(7, 'STETHIC', 'Ethics', 3);

INSERT INTO `room` (`room_id`, `room_code`) VALUES
(1, 'G302'),
(2, 'Online'),
(3, 'G304'),
(4, 'G306'),
(5, 'G210'),
(6, 'G201'),
(7, 'G404'),
(8, 'G310'),
(9, 'A1005');

INSERT INTO `section` (`section_id`, `course_id`, `professor_id`, `room_id`, `section_code`, `time_slot`, `slots`, `max_slots`) VALUES
(1, 1, 2, 1, 'S11', '08:00-09:30', 3, 40),
(2, 1, 2, 2, 'S12', '09:45-11:15', 0, 40),
(3, 1, 3, 3, 'S13', '11:30-13:00', 12, 40),
(4, 2, 4, 4, 'S11', '08:00-09:30', 8, 35),
(5, 2, 5, 4, 'S12', '13:15-14:45', 1, 35),
(6, 3, 6, 5, 'S11', '09:45-11:15', 10, 40),
(7, 3, 6, 2, 'S12', '15:00-16:30', 0, 40),
(8, 4, 7, 6, 'S11', '11:30-13:00', 14, 40),
(9, 4, 7, 6, 'S12', '15:00-16:30', 5, 40),
(10, 5, 8, 7, 'S11', '08:00-09:30', 6, 30),
(11, 5, 8, 7, 'S12', '13:15-14:45', 15, 30),
(12, 6, 9, 8, 'S11', '11:30-13:00', 0, 40),
(13, 6, 9, 2, 'S12', '15:00-16:30', 20, 45),
(14, 7, 10, 2, 'S11', '13:15-14:45', 25, 45),
(15, 7, 10, 9, 'S12', '16:45-18:15', 9, 45);

INSERT INTO `section_schedule` (`schedule_id`, `section_id`, `day`, `mode`) VALUES
(1, 1, 'M', 'Onsite'),
(2, 1, 'H', 'Onsite'),
(3, 2, 'T', 'Online'),
(4, 2, 'F', 'Online'),
(5, 3, 'W', 'Onsite'),
(6, 3, 'S', 'Online'),
(7, 4, 'T', 'Onsite'),
(8, 4, 'F', 'Onsite'),
(9, 5, 'M', 'Online'),
(10, 5, 'H', 'Onsite'),
(11, 6, 'M', 'Onsite'),
(12, 6, 'H', 'Online'),
(13, 7, 'W', 'Online'),
(14, 7, 'S', 'Online'),
(15, 8, 'T', 'Onsite'),
(16, 8, 'F', 'Onsite'),
(17, 9, 'M', 'Onsite'),
(18, 9, 'H', 'Online'),
(19, 10, 'W', 'Onsite'),
(20, 10, 'S', 'Onsite'),
(21, 11, 'T', 'Onsite'),
(22, 11, 'F', 'Online'),
(23, 12, 'M', 'Onsite'),
(24, 12, 'H', 'Onsite'),
(25, 13, 'T', 'Online'),
(26, 13, 'F', 'Online'),
(27, 14, 'W', 'Online'),
(28, 14, 'S', 'Online'),
(29, 15, 'T', 'Onsite'),
(30, 15, 'F', 'Onsite');

INSERT INTO `enlistment` (`enlistment_id`, `student_id`, `section_id`, `date_enlisted`) VALUES
(1, 1, 1, '2026-11-01'),
(2, 1, 4, '2026-11-01'),
(3, 1, 10, '2026-11-01'),
(4, 2, 5, '2026-11-02'),
(5, 2, 6, '2026-11-02'),
(6, 5, 8, '2026-11-03'),
(7, 6, 13, '2026-11-03');
