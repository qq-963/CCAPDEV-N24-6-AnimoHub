-- extra rows to test the 24 unit cap and the schedule conflict
-- run this after the main script: sudo mysql < test_data.sql
USE animohub;

-- two extra courses, 3 units each
INSERT INTO course (course_id, code, name, units) VALUES
(8, 'CSSWENG', 'Software Engineering', 3),
(9, 'CCINFOM', 'Information Management', 3);

-- CSSWENG S11 is MH 08:00-09:30, same as CCPROG3 S11 (conflict test)
-- CCINFOM S11 is WS 15:00-16:30
INSERT INTO section (section_id, course_id, professor_id, room_id, section_code, time_slot, slots, max_slots) VALUES
(16, 8, 3, 1, 'S11', '08:00-09:30', 10, 40),
(17, 9, 6, 5, 'S11', '15:00-16:30', 10, 40);

INSERT INTO section_schedule (section_id, day, mode) VALUES
(16, 'M', 'Onsite'),
(16, 'H', 'Onsite'),
(17, 'W', 'Onsite'),
(17, 'S', 'Online');

-- Ana Lim (student 4) enlisted in 7 courses = 22 units, no conflicts
-- she can not add a 3 unit course because 22 + 3 is more than 24
INSERT INTO enlistment (student_id, section_id, date_enlisted) VALUES
(4, 1, '2026-11-05'),
(4, 4, '2026-11-05'),
(4, 6, '2026-11-05'),
(4, 8, '2026-11-05'),
(4, 10, '2026-11-05'),
(4, 13, '2026-11-05'),
(4, 14, '2026-11-05');

-- take the slots of those enlistments
UPDATE section SET slots = slots - 1 WHERE section_id IN (1, 4, 6, 8, 10, 13, 14);
