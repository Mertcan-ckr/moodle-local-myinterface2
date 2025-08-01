index.php
<?php
defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../config.php');
require_login();

$context = context_system::instance();
require_capability('local/myinterface:view', $context);

$PAGE->set_url(new moodle_url('/local/myinterface/index.php'));
$PAGE->set_context($context);
$PAGE->set_title('Ders Seçim Ekranı');
$PAGE->set_heading('Ders Seçim Ekranı');

echo $OUTPUT->header();

global $DB, $USER;

$userid = $USER->id;

// Dersleri çek
$courses = $DB->get_records('course', ['visible' => 1]);

// Kullanıcının seçtiği dersleri işleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['selectedcourses'])) {
    $selectedcourses = $_POST['selectedcourses'];

    // Önce kullanıcı ders kayıtlarını sil veya güncelle
    $DB->delete_records('local_myinterface_user_courses', ['userid' => $userid]);

    foreach ($selectedcourses as $courseid) {
        $record = new stdClass();
        $record->userid = $userid;
        $record->courseid = (int)$courseid;
        $DB->insert_record('local_myinterface_user_courses', $record);
    }

    echo html_writer::tag('p', 'Ders seçiminiz kaydedildi.', ['class' => 'alert alert-success']);
}

// Kullanıcının mevcut derslerini al
$usercourses = $DB->get_records('local_myinterface_user_courses', ['userid' => $userid]);
$usercourseids = array_keys($usercourses);

// Formu göster
echo html_writer::start_tag('form', ['method' => 'POST']);

echo html_writer::start_tag('ul');
foreach ($courses as $course) {
    $checked = in_array($course->id, $usercourseids) ? 'checked' : '';
    echo html_writer::start_tag('li');
    echo html_writer::empty_tag('input', [
        'type' => 'checkbox',
        'name' => 'selectedcourses[]',
        'value' => $course->id,
        'id' => 'course_'.$course->id,
        'checked' => $checked,
    ]);
    echo html_writer::tag('label', format_string($course->fullname), ['for' => 'course_'.$course->id]);
    echo html_writer::end_tag('li');
}
echo html_writer::end_tag('ul');

echo html_writer::empty_tag('input', ['type' => 'submit', 'value' => 'Dersleri Kaydet']);

echo html_writer::end_tag('form');

echo $OUTPUT->footer();
