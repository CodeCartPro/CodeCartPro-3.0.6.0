<?php
/**
 * CodeCart distribution preflight (non-destructive).
 * Usage: php tools/release_check.php [--strict-release]
 * Static checks only; not a runtime installation or security certification.
 */

declare(strict_types=1);

$root = dirname(__DIR__);
$strict = in_array('--strict-release', $argv, true);
$errors = 0;
$warnings = 0;

function checkOutput(string $type, string $message): void {
    echo '[' . $type . '] ' . $message . PHP_EOL;
}

$requiredFiles = [
    'README.md', 'composer.json', 'composer.lock',
    'upload/index.php', 'upload/admin/index.php', 'upload/install/index.php',
    'upload/system/startup.php', 'documentation/INSTALL.md',
    'documentation/UPGRADE.md', 'documentation/GITHUB.md'
];

foreach ($requiredFiles as $relative) {
    if (!is_file($root . '/' . $relative)) {
        checkOutput('FAIL', 'Required file missing: ' . $relative);
        $errors++;
    }
}

foreach (['upload/config.php', 'upload/admin/config.php', '.env'] as $relative) {
    if (is_file($root . '/' . $relative)) {
        checkOutput('FAIL', 'Local configuration must not be published: ' . $relative);
        $errors++;
    }
}

foreach (['upload/index.php', 'upload/admin/index.php', 'upload/install/index.php'] as $relative) {
    $content = @file_get_contents($root . '/' . $relative);
    if ($content === false || !preg_match('/define\(\s*[\'\"]VERSION[\'\"]\s*,\s*[\'\"]([^\'\"]+)[\'\"]/', $content, $match)) {
        checkOutput('FAIL', 'Could not read VERSION in ' . $relative);
        $errors++;
    } else {
        $versions[$relative] = $match[1];
    }
}
if (isset($versions) && count(array_unique($versions)) > 1) {
    checkOutput('FAIL', 'Version mismatch between entrypoints');
    $errors++;
} elseif (isset($versions)) {
    checkOutput('OK', 'Entrypoint VERSION: ' . reset($versions));
}

$composerFile = $root . '/composer.json';
$composer = is_file($composerFile) ? json_decode((string)file_get_contents($composerFile), true) : null;
if (!is_array($composer)) {
    checkOutput('FAIL', 'composer.json missing or invalid JSON');
    $errors++;
} else {
    checkOutput('INFO', 'Composer PHP constraint: ' . ($composer['require']['php'] ?? '(not specified)'));
    if (($composer['license'] ?? '') === 'proprietary') {
        checkOutput('WARN', 'composer.json says proprietary; review licensing against inherited OpenCart GPLv3 code before public release');
        $warnings++;
        if ($strict) $errors++;
    }
}
if (!is_file($root . '/LICENSE')) {
    checkOutput('WARN', 'No root LICENSE file; determine and document license obligations before public release');
    $warnings++;
    if ($strict) $errors++;
}

$vendorAutoload = $root . '/upload/system/storage/vendor/autoload.php';
if (is_file($vendorAutoload)) {
    checkOutput('OK', 'Production Composer autoloader present');
} elseif ($strict) {
    checkOutput('FAIL', 'Production Composer vendor missing in strict release mode');
    $errors++;
} else {
    checkOutput('INFO', 'Composer vendor absent (expected in source checkout; production packages need vendor)');
}

checkOutput($errors ? 'FAIL' : 'OK', 'Preflight summary: ' . $errors . ' blocker(s), ' . $warnings . ' warning(s)');
exit($errors ? 1 : 0);
