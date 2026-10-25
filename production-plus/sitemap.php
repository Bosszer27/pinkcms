<?php
require_once __DIR__.'/config.php';
require_once __DIR__.'/functions.php';

header('Content-Type: application/xml; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: public, max-age=300');

$base=siteCanonicalBase();
$type=isset($_GET['type'])&&is_string($_GET['type'])?$_GET['type']:'index';
$part=isset($_GET['part'])&&is_string($_GET['part'])&&ctype_digit($_GET['part'])?(int)$_GET['part']:1;
$pageSize=5000;
$entities=[
    'studios'=>['table'=>'studios','page'=>'studio'],
    'actors'=>['table'=>'actors','page'=>'actor'],
    'tags'=>['table'=>'tags','page'=>'tag']
];
$xmlEscape=function($value) {
    return htmlspecialchars((string)$value,ENT_XML1|ENT_COMPAT,'UTF-8');
};
$fail=function() {
    error_log('PinkCMS sitemap query failed: '.mysqli_error($GLOBALS['conn']));
    http_response_code(500);
    echo '<?xml version="1.0" encoding="UTF-8"?><error>Sitemap generation failed</error>';
    exit;
};
$countRows=function($table,$publishedOnly=false) use ($conn,$fail) {
    $where=$publishedOnly?" WHERE status='publish'":'';
    $result=mysqli_query($conn,"SELECT COUNT(*) AS total FROM `$table`".$where);
    if(!$result)$fail();
    $row=mysqli_fetch_assoc($result);
    return isset($row['total'])?(int)$row['total']:0;
};
$writeUrl=function($url,$lastmod=null) use ($xmlEscape) {
    echo '<url><loc>'.$xmlEscape($url).'</loc>';
    if($lastmod!==null)echo '<lastmod>'.$xmlEscape($lastmod).'</lastmod>';
    echo '</url>';
};

echo '<?xml version="1.0" encoding="UTF-8"?>';
if($type==='index') {
    echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    echo '<sitemap><loc>'.$xmlEscape($base.'/sitemap-static.xml').'</loc></sitemap>';
    $maps=[['posts',$countRows('posts',true)],['pages',$countRows('pages',true)]];
    foreach($entities as $entity=>$config)$maps[]=[$entity,$countRows($config['table'])];
    foreach($maps as $map) {
        $parts=max(1,(int)ceil($map[1]/$pageSize));
        for($number=1;$number<=$parts;$number++) {
            $name='sitemap-'.$map[0].'-'.$number.'.xml';
            echo '<sitemap><loc>'.$xmlEscape($base.'/'.$name).'</loc></sitemap>';
        }
    }
    echo '</sitemapindex>';
    exit;
}

if($type==='static') {
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach([
        $base.'/',
        $base.'/?page=all',
        $base.'/?page=categories',
        $base.'/?page=taxlist&slug=studio',
        $base.'/?page=taxlist&slug=actor',
        $base.'/?page=taxlist&slug=tag'
    ] as $url)$writeUrl($url);
    echo '</urlset>';
    exit;
}

$validTypes=['posts','pages'];
if(in_array($type,$validTypes,true)) {
    $table=$type;
    $where=" WHERE status='publish'";
    $total=$countRows($table,true);
    $maxPart=max(1,(int)ceil($total/$pageSize));
    if($part<1||$part>$maxPart) {
        http_response_code(404);
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>';
        exit;
    }
    $offset=($part-1)*$pageSize;
    $result=mysqli_query($conn,"SELECT slug,date FROM `$table`".$where." ORDER BY id LIMIT ".$offset.",".$pageSize);
    if(!$result)$fail();
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    while($row=mysqli_fetch_assoc($result)) {
        $route=$type==='posts'?'?page=post&slug=':'?page=page&slug=';
        $url=$base.'/'.$route.rawurlencode($row['slug']);
        $lastmod=!empty($row['date'])&&strtotime($row['date'])!==false?date('Y-m-d',strtotime($row['date'])):null;
        $writeUrl($url,$lastmod);
    }
    echo '</urlset>';
    exit;
}

if($type==='terms') {
    $entity=isset($_GET['entity'])&&is_string($_GET['entity'])?$_GET['entity']:'';
    if(!isset($entities[$entity])) {
        http_response_code(404);
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>';
        exit;
    }
    $table=$entities[$entity]['table'];
    $total=$countRows($table);
    $maxPart=max(1,(int)ceil($total/$pageSize));
    if($part<1||$part>$maxPart) {
        http_response_code(404);
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>';
        exit;
    }
    $offset=($part-1)*$pageSize;
    $result=mysqli_query($conn,"SELECT slug FROM `$table` ORDER BY id LIMIT ".$offset.",".$pageSize);
    if(!$result)$fail();
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    while($row=mysqli_fetch_assoc($result)) {
        $writeUrl($base.'/?page='.$entities[$entity]['page'].'&slug='.rawurlencode($row['slug']));
    }
    echo '</urlset>';
    exit;
}

http_response_code(404);
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>';
