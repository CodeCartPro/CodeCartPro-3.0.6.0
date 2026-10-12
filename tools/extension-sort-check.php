<?php
/**
 * Regression checks for the CodeCart extensions sortable table.
 * Run from project root: php tools/extension-sort-check.php
 */
$root = dirname(__DIR__);
$types = array('advertise', 'analytics', 'captcha', 'currency', 'dashboard', 'feed', 'fraud', 'menu', 'module', 'payment', 'report', 'shipping', 'theme', 'total');
$errors = array();

foreach ($types as $type) {
    $controller = $root . '/upload/admin/controller/extension/extension/' . $type . '.php';
    $view = $root . '/upload/admin/view/template/extension/extension/' . $type . '.twig';
    $php = file_get_contents($controller);
    $twig = file_get_contents($view);

    if (strpos($php, "getInstallDates('" . $type . "')") === false || strpos($php, "getUploadDates('" . $type . "')") === false || strpos($php, "'code'      => \$extension") === false) {
        $errors[] = $type . ': no installation date metadata in controller';
    }
    if (strpos($twig, 'ccp-extension-main') === false || strpos($twig, 'ccp-extension-sort') === false) {
        $errors[] = $type . ': no sortable rows or headers';
    }
    if (substr_count($twig, 'data-ccp-sort=') < 5 || strpos($twig, 'data-sort-upload') === false) {
        $errors[] = $type . ': fewer than three sort choices';
    }
    if (preg_match('/<thead>(.*?)<\/thead>/s', $twig, $header)) {
        $columns = substr_count($header[1], '<td');
        if (preg_match('/colspan="(\d+)"\s*>\{\{ text_no_results/', $twig, $match) && (int)$match[1] !== $columns) {
            $errors[] = $type . ': empty-state columns differ from table header';
        }
    }
}

foreach (array('uk-ua', 'ru-ru', 'en-gb') as $locale) {
    $path = $root . '/upload/admin/language/' . $locale . '/marketplace/extension.php';
    $content = file_get_contents($path);
    foreach (array('column_installed','column_install_date','column_upload_date','text_installed','text_not_installed','text_date_unknown','text_upload_date_unknown') as $key) {
        if (strpos($content, chr(36) . "_['" . $key . "']") === false) {
            $errors[] = $locale . ': missing ' . $key;
        }
    }
}

$model = file_get_contents($root . '/upload/admin/model/setting/extension.php');
foreach (array('getInstallDates', 'getUploadDates', 'extension_path', 'extension_install', 'codecart_extension_install', 'installed_at_') as $needle) {
    if (strpos($model, $needle) === false) {
        $errors[] = 'installation tracking: missing ' . $needle;
    }
}

if ($errors) {
    fwrite(STDERR, implode("\n", $errors) . "\n");
    exit(1);
}

// Run date-mapping regression tests without requiring a live database.
if (!defined('DB_PREFIX')) { define('DB_PREFIX', 'oc_'); }
if (!class_exists('Model')) { class Model { public $db; } }
require_once $root . '/upload/admin/model/setting/extension.php';
class DateSortMockDatabase {
    public $rows;
    public $sql = '';
    public function __construct($rows) { $this->rows = $rows; }
    public function escape($value) { return addslashes($value); }
    public function query($sql) {
        $this->sql = $sql;
        return (object) array('rows' => $this->rows);
    }
}
$dates_db = new DateSortMockDatabase(array(
    array('path' => 'admin/controller/extension/module/sample.php', 'date_added' => '2026-10-12 12:00:00'),
    array('path' => 'admin/controller/extension/module/sample.php', 'date_added' => '2026-09-12 12:00:00'),
    array('path' => 'admin/controller/module/legacy.php', 'date_added' => '2025-02-03 01:02:03'),
    array('path' => 'admin/controller/extension/module/helpers/invalid.php', 'date_added' => '2026-10-12 11:00:00'),
    array('path' => 'admin/controller/extension/module/invalid.twig', 'date_added' => '2026-10-12 11:00:00'),
));
$dates_model = new ModelSettingExtension();
$dates_model->db = $dates_db;
$dates = $dates_model->getUploadDates('module');
if ($dates !== array('sample' => '2026-10-12 12:00:00', 'legacy' => '2025-02-03 01:02:03')) {
    fwrite(STDERR, 'Upload date mapping failed.\n');
    exit(1);
}
if (strpos($dates_db->sql, 'extension_path') === false || strpos($dates_db->sql, 'extension_install') === false) {
    fwrite(STDERR, 'Upload date query does not use installer records.\n');
    exit(1);
}
if ($dates_model->getUploadDates('unexpected_type') !== array()) {
    fwrite(STDERR, 'Unrecognized extension type was accepted.\n');
    exit(1);
}

print 'Extension sorting checks passed: ' . count($types) . " types, three languages, upload and installation timestamps.\n";
