<?php

global $mydb;

require_once(dirname(__FILE__) . '/style.php');

/* Small helper: always returns an array. */
function ss_rows($sql)
{
    global $mydb;
    $mydb->setQuery($sql);
    $rows = $mydb->loadResultList();
    return $rows ? $rows : array();
}

/* Loaded once; used by the Add and Edit modals and the JS below. */
$allScheduleSections = ss_rows("
    SELECT s.SECTION_ID, s.SECTION_NAME, s.COURSE_ID, s.SY_ID, s.YEAR_LEVEL, sy.SCHOOL_YEAR
    FROM tblsections s
    LEFT JOIN tblschoolyear sy ON sy.SY_ID = s.SY_ID
    ORDER BY s.COURSE_ID ASC, s.YEAR_LEVEL ASC, s.SECTION_NAME ASC
");

$allScheduleSubjects = ss_rows("
    SELECT SUBJECT_ID, SUBJECT_CODE, SUBJECT_NAME, UNITS, COURSE_ID, YEAR_LEVEL, SEMESTER
    FROM tblsubjects
    ORDER BY COURSE_ID ASC, YEAR_LEVEL ASC, SEMESTER ASC, SUBJECT_CODE ASC
");

$departments = ss_rows("SELECT id, name FROM tbldepartment ORDER BY name ASC");
$courses     = ss_rows("SELECT COURSE_ID, COURSE_CODE, COURSE_NAME FROM tblcourses ORDER BY COURSE_CODE ASC");
$classrooms  = ss_rows("SELECT id, name FROM tblclassroom ORDER BY name ASC");
$days        = ss_rows("SELECT id, name FROM tblscheduleday ORDER BY id ASC");
$times       = ss_rows("SELECT id, time_start, time_end FROM tblscheduletime ORDER BY time_start ASC");
$instructors = ss_rows("SELECT id, instructor_id, name FROM tblinstructor ORDER BY name ASC");

/* Option lists (HTML) */
$optDepartment = '<option value="">Select Department</option>';
foreach ($departments as $r) {
    $optDepartment .= '<option value="' . (int)$r->id . '">' . htmlspecialchars($r->name) . '</option>';
}

$optCourse = '<option value="">Select Course</option>';
foreach ($courses as $r) {
    $optCourse .= '<option value="' . (int)$r->COURSE_ID . '">'
        . htmlspecialchars($r->COURSE_CODE) . ' - ' . htmlspecialchars($r->COURSE_NAME) . '</option>';
}

$optClassroom = '<option value="">Select Classroom</option>';
foreach ($classrooms as $r) {
    $optClassroom .= '<option value="' . (int)$r->id . '">' . htmlspecialchars($r->name) . '</option>';
}

$optDay = '<option value="">Select Schedule Day</option>';
foreach ($days as $r) {
    $optDay .= '<option value="' . (int)$r->id . '">' . htmlspecialchars($r->name) . '</option>';
}

$optTime = '<option value="">Select Schedule Time</option>';
foreach ($times as $r) {
    $optTime .= '<option value="' . (int)$r->id . '">'
        . date('h:i A', strtotime($r->time_start)) . ' - ' . date('h:i A', strtotime($r->time_end)) . '</option>';
}

$optInstructor = '<option value="">Select Instructor</option>';
foreach ($instructors as $r) {
    $optInstructor .= '<option value="' . (int)$r->id . '">' . htmlspecialchars($r->name)
        . (!empty($r->instructor_id) ? ' - ' . htmlspecialchars($r->instructor_id) : '') . '</option>';
}

$optSemester = '<option value="">Select Semester</option>'
    . '<option value="1st Semester">1st Semester</option>'
    . '<option value="2nd Semester">2nd Semester</option>'
    . '<option value="Summer">Summer</option>';


/* One field (icon + label + select) in a half-width column. */
function ss_field($label, $name, $id, $options, $required = true, $disabled = false, $attrs = '', $icon = 'fa-list')
{
    echo '<div class="col-md-6"><div class="form-group">'
        . '<label>' . $label . '</label>'
        . '<div class="ss-input"><i class="fa ' . $icon . '"></i>'
        . '<select name="' . $name . '" id="' . $id . '" class="form-control"'
        . ($required ? ' required' : '') . ($disabled ? ' disabled' : '') . ($attrs ? ' ' . $attrs : '') . '>'
        . $options . '</select></div></div></div>';
}

function ss_group($title, $icon)
{
    echo '<div class="col-12"><div class="ss-group"><i class="fa ' . $icon . '"></i>' . $title . '</div></div>';
}

/* The Add / Edit modal. $p is the id prefix ('' or 'EDIT_'). */
function ss_modal($modalId, $title, $action, $buttonName, $p, $withId)
{
    global $optDepartment, $optCourse, $optClassroom, $optDay, $optTime, $optInstructor, $optSemester;
    ?>
<div class="modal fade ss-modal" id="<?php echo $modalId; ?>">
    <div class="modal-dialog modal-lg">
        <form action="controller.php?action=<?php echo $action; ?>" method="POST">
            <div class="modal-content">

                <div class="modal-header">
                    <h4 class="modal-title"><span class="ss-badge"><i class="fa <?php echo $withId ? 'fa-edit' : 'fa-plus'; ?>"></i></span><?php echo $title; ?></h4>
                    <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal"><span>&times;</span></button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <?php if ($withId): ?>
                            <input type="hidden" name="ID" id="EDIT_ID">
                        <?php endif; ?>

                        <?php
                        ss_group('Class Details', 'fa-graduation-cap');
                        ss_field('Department',    'DEPARTMENT_ID', $p . 'DEPARTMENT_ID', $optDepartment, true, false, '', 'fa-building');
                        ss_field('Course',        'COURSE_ID',     $p . 'COURSE_ID',     $optCourse, true, false, '', 'fa-book');
                        ss_field('Section',       'SECTION_ID',    $p . 'SECTION_ID',    '<option value="">Select Course First</option>', true, false, '', 'fa-users');
                        ss_field('Subject',       'SUBJECT_ID',    $p . 'SUBJECT_ID',    '<option value="">Select Course First</option>', true, false, '', 'fa-bookmark');

                        ss_group('Schedule', 'fa-clock-o');
                        ss_field('Classroom',     'CLASSROOM_ID',  $p . 'CLASSROOM_ID',  $optClassroom, true, false, '', 'fa-map-marker');
                        ss_field('Schedule Day',  'DAY_ID',        $p . 'DAY_ID',        $optDay, true, false, '', 'fa-calendar');
                        ss_field('Schedule Time', 'TIME_ID',       $p . 'TIME_ID',       $optTime, true, false, '', 'fa-clock-o');
                        ss_field('Instructor',    'INSTRUCTOR_ID', $p . 'INSTRUCTOR_ID', $optInstructor, false, false, '', 'fa-user');

                        ss_group('Term', 'fa-calendar-check-o');
                        ss_field('Semester',      'SEMESTER',      $p . 'SEMESTER',      $optSemester, true, false, '', 'fa-flag');
                        ss_field('School Year',   'SCHOOL_YEAR',   $p . 'SCHOOL_YEAR',   '<option value="">Select Section First</option>', true, true, '', 'fa-calendar-o');
                        ?>
                    </div>
                </div>

                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="<?php echo $buttonName; ?>"><i class="fa fa-save"></i> Save changes</button>
                </div>

            </div>
        </form>
    </div>
</div>
    <?php
}

?>

<section class="content">

    <div class="container-fluid">

        <?php check_message(); ?>

        <div class="row">

            <div class="col-12">

                <div class="card ss-card">

                    <div class="card-header">
                        <h3 class="card-title"><i class="fa fa-calendar-check-o"></i>Set Schedule List</h3>
                    </div>

                    <div class="card-body">

                        <table id="tblsetschedule" class="table table-bordered table-striped" style="width:100%;">

                            <thead>
                                <tr>
                                    <th width="4%">#</th>
                                    <th width="13%">TIME</th>
                                    <th width="9%">DAY</th>
                                    <th width="9%">CODE</th>
                                    <th width="23%">SUBJECT DESCRIPTION</th>
                                    <th width="6%">UNIT</th>
                                    <th width="10%">ROOM</th>
                                    <th width="13%">INSTRUCTOR</th>
                                    <th width="10%">SECTION</th>
                                    <th width="10%">Action</th>
                                </tr>
                            </thead>

                            <tbody></tbody>

                        </table>

                        <div class="ss-actions mt-3">

                            <button
                                type="button"
                                class="btn btn-add"
                                data-toggle="modal"
                                data-target="#AddNewEntry"
                                data-bs-toggle="modal"
                                data-bs-target="#AddNewEntry"
                            >
                                <i class="fa fa-plus"></i> Add New
                            </button>

                            <button
                                type="button"
                                class="btn btn-prt"
                                data-toggle="modal"
                                data-target="#PrintClassModal"
                                data-bs-toggle="modal"
                                data-bs-target="#PrintClassModal"
                            >
                                <i class="fa fa-print"></i> Print Class Schedule
                            </button>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>

<?php
ss_modal('AddNewEntry', 'Add New Schedule', 'add',  'save', '',      false);
ss_modal('editEntry',   'Modify Schedule',  'edit', 'edit', 'EDIT_', true);
?>

<!-- =========================================================
     PRINT CLASS SCHEDULE MODAL
     Course -> Section -> School Year -> Semester (+ optional Subject)
     ========================================================= -->

<div class="modal fade ss-modal" id="PrintClassModal">
    <div class="modal-dialog modal-lg">
        <form action="<?php echo WEB_ROOT; ?>module/setschedule/print.php" method="GET" target="_blank">
            <div class="modal-content">

                <div class="modal-header">
                    <h4 class="modal-title"><span class="ss-badge"><i class="fa fa-print"></i></span>Print Class Schedule</h4>
                    <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal"><span>&times;</span></button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <?php
                        ss_group('Class', 'fa-graduation-cap');
                        ss_field('Course',      'course_id',   'PRINT_COURSE_ID',   $optCourse, true, false, '', 'fa-book');
                        ss_field('Section',     'section_id',  'PRINT_SECTION_ID',  '<option value="">Select Course First</option>', true, false, '', 'fa-users');
                        ss_group('Term', 'fa-calendar-check-o');
                        ss_field('School Year', 'school_year', 'PRINT_SCHOOL_YEAR', '<option value="">Select Section First</option>', true, true, '', 'fa-calendar-o');
                        ss_field('Semester',    'semester',    'PRINT_SEMESTER',    $optSemester, true, false, '', 'fa-flag');
                        ss_group('Filter', 'fa-filter');
                        ss_field('Subject (optional)', 'subject_id', 'PRINT_SUBJECT_ID',
                                 '<option value="">All Subjects</option>', false, false, 'data-placeholder="All Subjects"', 'fa-bookmark');
                        ?>
                    </div>
                    <div class="ss-hint"><i class="fa fa-info-circle"></i>Leave Subject on "All Subjects" to print the whole class schedule.</div>
                </div>

                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success"><i class="fa fa-print"></i> Print</button>
                </div>

            </div>
        </form>
    </div>
</div>

<!-- =========================================================
     COURSE / SECTION / SUBJECT / SEMESTER / SCHOOL YEAR
     Client-side dropdown logic
     ========================================================= -->

<script>
(function () {
    'use strict';

    var allSections = <?php echo json_encode(array_map(function ($s) {
        return array(
            'SECTION_ID'   => (int)$s->SECTION_ID,
            'SECTION_NAME' => (string)$s->SECTION_NAME,
            'COURSE_ID'    => (int)$s->COURSE_ID,
            'YEAR_LEVEL'   => (string)$s->YEAR_LEVEL,
            'SCHOOL_YEAR'  => (string)$s->SCHOOL_YEAR
        );
    }, $allScheduleSections), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

    var allSubjects = <?php echo json_encode(array_map(function ($s) {
        return array(
            'SUBJECT_ID'   => (int)$s->SUBJECT_ID,
            'SUBJECT_CODE' => (string)$s->SUBJECT_CODE,
            'SUBJECT_NAME' => (string)$s->SUBJECT_NAME,
            'UNITS'        => (int)$s->UNITS,
            'COURSE_ID'    => (int)$s->COURSE_ID,
            'YEAR_LEVEL'   => (string)$s->YEAR_LEVEL,
            'SEMESTER'     => (string)$s->SEMESTER
        );
    }, $allScheduleSubjects), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;


    /* ---------- helpers ---------- */

    function val(v) {
        return String(v === null || typeof v === 'undefined' ? '' : v).trim();
    }

    function same(a, b) {
        return val(a).toLowerCase() === val(b).toLowerCase();
    }

    function findById(list, key, id) {
        id = val(id);
        for (var i = 0; i < list.length; i++) {
            if (val(list[i][key]) === id) return list[i];
        }
        return null;
    }

    function getSection(id) { return findById(allSections, 'SECTION_ID', id); }
    function getSubject(id) { return findById(allSubjects, 'SUBJECT_ID', id); }

    function setOnly(select, text, disabled) {
        select.innerHTML = '';
        var o = document.createElement('option');
        o.value = '';
        o.textContent = text;
        select.appendChild(o);
        select.disabled = !!disabled;
    }

    function fields(prefix) {
        var course = document.getElementById(prefix + 'COURSE_ID');
        return {
            form:       course ? course.form : null,
            course:     course,
            section:    document.getElementById(prefix + 'SECTION_ID'),
            subject:    document.getElementById(prefix + 'SUBJECT_ID'),
            semester:   document.getElementById(prefix + 'SEMESTER'),
            schoolYear: document.getElementById(prefix + 'SCHOOL_YEAR')
        };
    }


    /* ---------- School Year (years available for the chosen Section) ---------- */

    function buildSchoolYears(f, sec, wantedYear) {
        var sy = f.schoolYear;
        if (!sy) return;

        if (!sec) {
            setOnly(sy, 'Select Section First', true);
            return;
        }

        var years = [];
        allSections.forEach(function (r) {
            if (val(r.COURSE_ID) !== val(sec.COURSE_ID)) return;
            if (!same(r.SECTION_NAME, sec.SECTION_NAME)) return;
            if (!same(r.YEAR_LEVEL, sec.YEAR_LEVEL)) return;
            if (val(r.SCHOOL_YEAR) && years.indexOf(val(r.SCHOOL_YEAR)) === -1) {
                years.push(val(r.SCHOOL_YEAR));
            }
        });
        years.sort();

        sy.innerHTML = '<option value="">Select School Year</option>';
        years.forEach(function (y) {
            var o = document.createElement('option');
            o.value = y;
            o.textContent = y;
            sy.appendChild(o);
        });

        sy.disabled = years.length === 0;

        /* Preselect the wanted year, otherwise the only year available. */
        var match = years.filter(function (y) { return wantedYear && same(y, wantedYear); });
        if (match.length) {
            sy.value = match[0];
        } else if (years.length === 1) {
            sy.value = years[0];
        }

        syncSectionToYear(f);
    }

    /* The same Section name has a different SECTION_ID per School Year.
       Change the selected option's value; assigning select.value to an id
       that is not an option would blank the dropdown. */
    function syncSectionToYear(f) {
        if (!f.section || !f.schoolYear || !f.section.value || !f.schoolYear.value) return;

        var opt = f.section.options[f.section.selectedIndex];
        var current = getSection(f.section.value);
        if (!opt || !current) return;

        for (var i = 0; i < allSections.length; i++) {
            var r = allSections[i];
            if (val(r.COURSE_ID) !== val(current.COURSE_ID)) continue;
            if (!same(r.SECTION_NAME, current.SECTION_NAME)) continue;
            if (!same(r.YEAR_LEVEL, current.YEAR_LEVEL)) continue;
            if (!same(r.SCHOOL_YEAR, f.schoolYear.value)) continue;
            opt.value = String(r.SECTION_ID);
            break;
        }
    }


    /* ---------- Section (depends on Course only) ---------- */

    function buildSections(f, selectedId, wantedYear) {
        var courseID = val(f.course.value);

        if (!courseID) {
            setOnly(f.section, 'Select Course First', true);
            buildSchoolYears(f, null);
            return;
        }

        var groups = {}, order = [];

        allSections.forEach(function (s) {
            if (val(s.COURSE_ID) !== courseID) return;
            var key = val(s.SECTION_NAME).toLowerCase() + '||' + val(s.YEAR_LEVEL).toLowerCase();
            if (!groups[key]) {
                groups[key] = { name: val(s.SECTION_NAME), yearLevel: val(s.YEAR_LEVEL), rows: [] };
                order.push(key);
            }
            groups[key].rows.push(s);
        });

        if (!order.length) {
            setOnly(f.section, 'No sections for this course', true);
            buildSchoolYears(f, null);
            return;
        }

        f.section.innerHTML = '<option value="">Select Section</option>';
        var selectedObj = null;

        order.forEach(function (key) {
            var g = groups[key];
            var chosen = g.rows[0];

            /* If the wanted section is in this group, use its exact id. */
            g.rows.forEach(function (r) {
                if (selectedId && val(r.SECTION_ID) === val(selectedId)) chosen = r;
            });

            var o = document.createElement('option');
            o.value = String(chosen.SECTION_ID);
            o.textContent = g.name + (g.yearLevel ? ' - ' + g.yearLevel : '');

            if (selectedId && val(chosen.SECTION_ID) === val(selectedId)) {
                o.selected = true;
                selectedObj = chosen;
            }
            f.section.appendChild(o);
        });

        f.section.disabled = false;
        buildSchoolYears(f, selectedObj, wantedYear);
    }


    /* ---------- Subject (Course + Section year level + Semester) ---------- */

    function buildSubjects(f, selectedId) {
        var courseID = val(f.course.value);

        if (!courseID) {
            setOnly(f.subject, 'Select Course First', true);
            return;
        }

        var sec = f.section.value ? getSection(f.section.value) : null;
        var yearLevel = sec ? val(sec.YEAR_LEVEL) : '';
        var semester = f.semester ? val(f.semester.value) : '';

        f.subject.innerHTML = '<option value="">' + (f.subject.getAttribute('data-placeholder') || 'Select Subject') + '</option>';
        var count = 0;

        allSubjects.forEach(function (s) {
            if (val(s.COURSE_ID) !== courseID) return;
            if (yearLevel && val(s.YEAR_LEVEL) && !same(s.YEAR_LEVEL, yearLevel)) return;
            if (semester && val(s.SEMESTER) && !same(s.SEMESTER, semester)) return;

            var o = document.createElement('option');
            o.value = String(s.SUBJECT_ID);
            o.textContent = val(s.SUBJECT_CODE) + ' - ' + val(s.SUBJECT_NAME) +
                (s.UNITS > 0 ? ' (' + s.UNITS + ' unit' + (s.UNITS === 1 ? '' : 's') + ')' : '');

            if (selectedId && val(selectedId) === val(s.SUBJECT_ID)) o.selected = true;

            f.subject.appendChild(o);
            count++;
        });

        if (!count) {
            setOnly(f.subject, 'No subjects for this selection', true);
        } else {
            f.subject.disabled = false;
        }
    }


    /* ---------- wiring ---------- */

    function wire(f) {
        if (!f.course || !f.section || !f.subject) return;

        function reset() {
            buildSections(f, null, null);
            buildSubjects(f, null);
        }

        reset();

        f.course.addEventListener('change', reset);

        f.section.addEventListener('change', function () {
            buildSchoolYears(f, getSection(f.section.value), null);
            /* Re-filter Subject by the Section's year level, but KEEP the
               current Subject when it is still valid. */
            buildSubjects(f, f.subject.value);
        });

        if (f.schoolYear) {
            f.schoolYear.addEventListener('change', function () {
                syncSectionToYear(f);
            });
        }

        if (f.semester) {
            f.semester.addEventListener('change', function () {
                buildSubjects(f, f.subject.value);
            });
        }

        f.subject.addEventListener('change', function () {
            /* controller.php requires Subject.SEMESTER == Semester, so
               follow the chosen Subject. */
            var s = getSubject(f.subject.value);
            if (s && f.semester && val(s.SEMESTER) && !same(f.semester.value, s.SEMESTER)) {
                f.semester.value = s.SEMESTER;
            }
        });

        /* A disabled field is not submitted, so enable School Year first. */
        if (f.form) {
            f.form.addEventListener('submit', function () {
                if (f.schoolYear) f.schoolYear.disabled = false;
            });
        }
    }

    function init() {
        var add = fields('');
        var edit = fields('EDIT_');

        wire(add);
        wire(edit);
        wire(fields('PRINT_'));

        /* Fresh Add form every time its modal is opened. */
        var addBtn = document.querySelector('[data-target="#AddNewEntry"], [data-bs-target="#AddNewEntry"]');
        if (addBtn && add.form) {
            addBtn.addEventListener('click', function () {
                add.form.reset();
                buildSections(add, null, null);
                buildSubjects(add, null);
            });
        }

        /* Called by index.php after it loads a record for the Edit modal. */
        window.SetScheduleForm = {
            fillEdit: function (data) {
                if (!edit.course) return;

                edit.course.value = val(data.course_id);
                if (edit.semester) edit.semester.value = val(data.semester);

                buildSections(edit, data.section_id, data.school_year);
                buildSubjects(edit, data.subject_id);
            }
        };
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>