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

    if (strpos($php, "getInstallDates('" . $type . "')") === false || strpos($php, "'code'      => \$extension") === false) {
        $errors[] = $type . ': no installation date metadata in controller';
    }
    if (strpos($twig, 'ccp-extension-main') === false || strpos($twig, 'ccp-extension-sort') === false) {
        $errors[] = $type . ': no sortable rows or headers';
    }
    if (substr_count($twig, 'data-ccp-sort=') < 3) {
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
    foreach (array('column_installed','column_install_date','text_installed','text_not_installed','text_date_unknown') as $key) {
        if (strpos($content, chr(36) . "_['" . $key . "']") === false) {
            $errors[] = $locale . ': missing ' . $key;
        }
    }
}

$model = file_get_contents($root . '/upload/admin/model/setting/extension.php');
foreach (array('getInstallDates', 'codecart_extension_install', 'installed_at_') as $needle) {
    if (strpos($model, $needle) === false) {
        $errors[] = 'installation tracking: missing ' . $needle;
    }
}

if ($errors) {
    fwrite(STDERR, implode("\n", $errors) . "\n");
    exit(1);
}

print 'Extension sorting checks passed: ' . count($types) . " types, three languages, installation timestamps.\n";
