<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/bootstrap.php';
require_once dirname(__DIR__) . '/lib/store.php';

$projectId = isset($argv[1]) ? (int)$argv[1] : 0;
if ($projectId <= 0) {
  fwrite(STDERR, "Usage: php studio/worker/export-project.php <project_id>\n");
  exit(1);
}

$package = studio_build_export_package($projectId);
echo json_encode([
  'ok' => true,
  'project_json_url' => $package['project_json_url'],
  'entries_json_url' => $package['entries_json_url'],
  'manifest_json_url' => $package['manifest_json_url'],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
