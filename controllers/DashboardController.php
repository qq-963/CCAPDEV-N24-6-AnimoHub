<?php
// dashboard: stat cards + 2 bar charts + upcoming events
// the numbers are different for each kind of user
class DashboardController extends Controller {

    function index() {
        $user = $this->needLogin();

        $stats = new StatsModel($this->db);
        $enlistments = new EnlistmentModel($this->db);

        $cards = [];
        $title1 = '';
        $labels1 = [];
        $values1 = [];

        if ($user['role'] == 'student') {
            $studentId = $enlistments->getStudentId($user['user_id']);
            $units = $enlistments->totalUnits($studentId);

            $cards[] = ['Courses enlisted', count($enlistments->getEnlisted($studentId))];
            $cards[] = ['Total units', $units];
            $cards[] = ['Units left', 24 - $units];   // 24 is the same max as in EnlistmentController
            $cards[] = ['Organizations joined', $stats->orgsJoined($studentId)];

            // how many classes on each day
            $perDay = ['M' => 0, 'T' => 0, 'W' => 0, 'H' => 0, 'F' => 0, 'S' => 0];
            foreach ($enlistments->getSchedule($studentId) as $row) {
                $perDay[$row['day']] = $perDay[$row['day']] + 1;
            }
            $title1 = 'My classes per day';
            $labels1 = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            $values1 = array_values($perDay);
        }
        else if ($user['role'] == 'professor') {
            $cards[] = ['Courses offered', $stats->countCourses()];
            $cards[] = ['Total sections', $stats->countSections()];
            $cards[] = ['Organizations', $stats->countOrgs()];

            $title1 = 'Sections per course';
            foreach ($stats->sectionsPerCourse() as $row) {
                $labels1[] = $row['code'];
                $values1[] = (int) $row['total'];
            }
        }
        else {
            // admin
            $cards[] = ['Organizations', $stats->countOrgs()];
            $cards[] = ['Registered users', $stats->countUsers()];
            $cards[] = ['Memberships', $stats->countMemberships()];

            $title1 = 'Members per organization';
            foreach ($stats->membersPerOrg() as $row) {
                $labels1[] = substr($row['name'], 0, 14);
                $values1[] = (int) $row['total'];
            }
        }

        // admins and officers/presidents also see how many requests are waiting
        if ($this->users->canManage($user)) {
            $cards[] = ['Pending requests', $stats->pendingRequests($user)];
        }

        // second chart is the same for everybody
        $labels2 = [];
        $values2 = [];
        foreach ($stats->openSlotsPerCourse() as $row) {
            $labels2[] = $row['code'];
            $values2[] = (int) $row['total'];
        }

        $this->view('dashboard', [
            'cards' => $cards,
            'title1' => $title1,
            'labels1' => $labels1,
            'values1' => $values1,
            'labels2' => $labels2,
            'values2' => $values2,
            'events' => $stats->upcomingEvents()
        ]);
    }
}
