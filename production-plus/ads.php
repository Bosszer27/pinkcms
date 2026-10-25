<?php
require_once __DIR__.'/config.php';
require_once __DIR__.'/functions.php';

header('Content-Type: text/plain; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: public, max-age=300');

$state=siteFileManagerState();
echo trim($state['files']['ads.txt']['content'])."\n";
