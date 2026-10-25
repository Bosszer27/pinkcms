<?php
require_once __DIR__.'/config.php';
require_once __DIR__.'/functions.php';

header('Content-Type: text/plain; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: public, max-age=300');

$state=siteFileManagerState();
$content=trim($state['files']['robots.txt']['content']);
if(empty($state['indexing_enabled'])) {
    $content="User-agent: *\nDisallow: /";
} elseif($content==='') {
    $content="User-agent: *\nAllow: /";
}

$sitemap=siteCanonicalBase().'/sitemap_index.xml';
if(!preg_match('/^\s*Sitemap:\s*'.preg_quote($sitemap,'/').'\s*$/im',$content)) {
    $content=rtrim($content)."\nSitemap: ".$sitemap;
}

echo rtrim($content)."\n";
