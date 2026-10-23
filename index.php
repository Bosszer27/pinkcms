<?php
require_once 'config.php';
require_once 'functions.php';
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';
$q = isset($_GET['q']) ? $_GET['q'] : '';
$accent = setting('theme_accent', '#ff2d78');
$navLabels = json_decode(setting('theme_navLabels', '["หน้าหลัก","ค่าย","นักแสดง","แท็ก"]'), true);
if (!$navLabels) $navLabels = ['หน้าหลัก','ค่าย','นักแสดง','แท็ก'];
$publishedPages = [];
$res = mysqli_query($conn, "SELECT * FROM pages WHERE status='publish'");
while ($r = mysqli_fetch_assoc($res)) $publishedPages[] = $r;
$activeBanners = [];
$res = mysqli_query($conn, "SELECT * FROM banners WHERE active=1");
while ($r = mysqli_fetch_assoc($res)) {
    if (trim($r['image'])==='' && trim($r['link'])!=='') {
        $bannerQuery=parse_url($r['link'],PHP_URL_QUERY);
        $bannerParams=[];
        if ($bannerQuery) parse_str($bannerQuery,$bannerParams);
        $bannerPostSlug=isset($bannerParams['page'],$bannerParams['slug']) && $bannerParams['page']==='post' ? trim($bannerParams['slug']) : '';
        if ($bannerPostSlug!=='') {
            $bannerPostStmt=mysqli_prepare($conn,"SELECT title,content,cover,posterUrl,videoUrl,status FROM posts WHERE slug=? LIMIT 1");
            if ($bannerPostStmt) {
                mysqli_stmt_bind_param($bannerPostStmt,'s',$bannerPostSlug);
                mysqli_stmt_execute($bannerPostStmt);
                $bannerPostResult=mysqli_stmt_get_result($bannerPostStmt);
                $bannerPost=$bannerPostResult?mysqli_fetch_assoc($bannerPostResult):null;
                mysqli_stmt_close($bannerPostStmt);
                if ($bannerPost && $bannerPost['status']==='publish') {
                    $bannerVideo=parseVideo($bannerPost['videoUrl']);
                    $r['image']=trim($bannerPost['posterUrl'])?:trim($bannerPost['cover'])?:$bannerVideo['thumb'];
                    if (trim($r['title'])==='') $r['title']=$bannerPost['title'];
                    if (trim($r['text'])==='') $r['text']=mb_substr(stripHtml($bannerPost['content']),0,170,'UTF-8');
                }
            }
        }
    }
    $activeBanners[] = $r;
}
$metaTitle = setting('siteName').' - '.setting('siteDesc');
$metaDesc = setting('siteDesc');
$metaKeywords = implode(', ', array_filter([
    setting('seo_primary_keyword', ''),
    setting('seo_secondary_keywords', ''),
    setting('seo_keywords', '')
]));
$requestHost=isset($_SERVER['HTTP_HOST'])?preg_replace('/[^A-Za-z0-9.:-]/','',$_SERVER['HTTP_HOST']):'localhost';
$requestScheme=(isset($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off')?'https':'http';
$installPath=str_replace('\\','/',dirname(isset($_SERVER['SCRIPT_NAME'])?$_SERVER['SCRIPT_NAME']:'/pinkcms-php/index.php'));
$canonicalBase=$requestScheme.'://'.$requestHost.rtrim($installPath,'/');
$canonicalUrl=$canonicalBase.'/';
$robotsContent='index, follow';
$videoSchema=null;
$pageSchema=null;
$extractCategories=function($value) {
    $categories=preg_split('/[,،;|，；\r\n]+/u',(string)$value);
    $categories=array_map('trim',$categories?:[]);
    return array_values(array_unique(array_filter($categories,function($category){return $category!=='';})));
};
$publicTagNames=[];
$tagResult=mysqli_query($conn,"SELECT id,name FROM tags");
while($tagRow=mysqli_fetch_assoc($tagResult))$publicTagNames[(int)$tagRow['id']]=$tagRow['name'];
$categoriesForPost=function($post) use ($extractCategories,$publicTagNames) {
    $categories=$extractCategories(isset($post['category'])?$post['category']:'');
    $tagIds=json_decode(isset($post['tagIds'])?$post['tagIds']:'[]',true);
    foreach(is_array($tagIds)?$tagIds:[] as $tagId) {
        $tagId=(int)$tagId;
        if(isset($publicTagNames[$tagId]))$categories[]=$publicTagNames[$tagId];
    }
    return array_values(array_unique($categories));
};
$loadCategoryIndex=function($sourcePosts=null) use ($categoriesForPost) {
    $counts=[];
    foreach(is_array($sourcePosts)?$sourcePosts:publishedPosts() as $post) {
        foreach($categoriesForPost($post) as $category) {
            $categoryKey='category:'.$category;
            if(!isset($counts[$categoryKey]))$counts[$categoryKey]=['name'=>$category,'count'=>0];
            $counts[$categoryKey]['count']++;
        }
    }
    uksort($counts,function($a,$b){return strnatcasecmp(substr($a,9),substr($b,9));});
    $index=[];$usedSlugs=[];
    foreach($counts as $entry) {
        $category=$entry['name'];
        $baseSlug=slugify($category);$categorySlug=$baseSlug;$suffix=2;
        while(isset($usedSlugs[$categorySlug]))$categorySlug=$baseSlug.'-'.$suffix++;
        $usedSlugs[$categorySlug]=true;
        $index[$categorySlug]=['name'=>$category,'count'=>$entry['count']];
    }
    return $index;
};
$renderPagination=function($route,$currentPage,$totalPages,$extra=[]) {
    if($totalPages<=1)return '';
    $start=max(1,$currentPage-2);$end=min($totalPages,$currentPage+2);
    $html='<nav class="archive-pagination" aria-label="หน้ารายการ">';
    if($currentPage>1) {
        $query=http_build_query(array_merge(['page'=>$route,'pg'=>$currentPage-1],$extra));
        $html.='<a href="index.php?'.esc($query).'" rel="prev">← ก่อนหน้า</a>';
    }
    for($pageNumber=$start;$pageNumber<=$end;$pageNumber++) {
        if($pageNumber===$currentPage)$html.='<span aria-current="page">'.$pageNumber.'</span>';
        else {
            $query=http_build_query(array_merge(['page'=>$route,'pg'=>$pageNumber],$extra));
            $html.='<a href="index.php?'.esc($query).'">'.$pageNumber.'</a>';
        }
    }
    if($currentPage<$totalPages) {
        $query=http_build_query(array_merge(['page'=>$route,'pg'=>$currentPage+1],$extra));
        $html.='<a href="index.php?'.esc($query).'" rel="next">ถัดไป →</a>';
    }
    return $html.'</nav>';
};

// ---- Route handling ----
$contentHtml = '';
if ($page === 'post' && $slug) {
    $slugE = mysqli_real_escape_string($conn, $slug);
    $p = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM posts WHERE slug='$slugE' AND status='publish'"));
    if ($p) {
        $categoryPosts=publishedPosts();
        $categoryIndex=$loadCategoryIndex($categoryPosts);
        mysqli_query($conn, "UPDATE posts SET views=views+1 WHERE id=".$p['id']);
        $am = autoMeta($p);
        $metaTitle = $am['title'].' | '.setting('siteName');
        $metaDesc = $am['desc'];
        $metaKeywords = implode(', ', array_filter([
            $p['focusKeyphrase'],
            $p['keywords'],
            setting('seo_primary_keyword', ''),
            setting('seo_secondary_keywords', ''),
            setting('seo_keywords', '')
        ]));
        $playerInput=!empty($p['embedUrl'])?$p['embedUrl']:$p['videoUrl'];
        $v = parseVideo($playerInput);
        $sourceVideo=parseVideo($p['videoUrl']);
        $canonicalUrl=rtrim($canonicalBase,'/').'/?page=post&slug='.rawurlencode($p['slug']);
        $thumbnail=(isset($p['posterUrl'])?$p['posterUrl']:'')?:($p['cover']?:$sourceVideo['thumb']);
        if($p['videoUrl']!=='' && $v['error']==='') {
            $schemaThumbnail=preg_match('/^https?:\/\//i',$thumbnail)?$thumbnail:($thumbnail!=='' && $thumbnail[0]==='/'?$canonicalBase.$thumbnail:'');
            $videoSchema=[
                '@context'=>'https://schema.org',
                '@type'=>'VideoObject',
                'name'=>$am['title'],
                'description'=>$am['desc']?:stripHtml($p['content']),
                'uploadDate'=>date('c',strtotime($p['date']?:'now'))
            ];
            $durationIso=isoVideoDuration($p['videoDuration']);
            if($durationIso!=='')$videoSchema['duration']=$durationIso;
            if($schemaThumbnail!=='' && validImageReference($schemaThumbnail))$videoSchema['thumbnailUrl']=$schemaThumbnail;
            if($v['embedUrl']!=='')$videoSchema['embedUrl']=$v['embedUrl'];
            if($v['contentUrl']!=='')$videoSchema['contentUrl']=$v['contentUrl'];
            $videoSchema['publisher']=['@type'=>'Organization','name'=>setting('siteName'),'url'=>$canonicalBase.'/'];
        }
        $studioIds = parseTaxonomyIds($p['studioId']);
        $studios = array_map('getStudio', $studioIds);
        $studio = $studios ? $studios[0] : getStudio(0);
        $actorIds = parseTaxonomyIds($p['actorIds']);
        $tagIds = parseTaxonomyIds($p['tagIds']);
        $gallery = json_decode($p['gallery'], true) ?: [];
        $actorsHtml = '';
        foreach ($actorIds as $aid) {
            $a = getActor($aid);
            if ($a && $a['id']) {
                if ($actorsHtml !== '') $actorsHtml .= '<span class="post-detail-separator">, </span>';
                $actorsHtml .= '<a class="post-detail-link" href="index.php?page=actor&amp;slug='.rawurlencode($a['slug']).'">'.esc($a['name']).'</a>';
            }
        }
        $categoryHtml='';
        foreach($extractCategories(isset($p['category'])?$p['category']:'') as $postCategory) {
            foreach($categoryIndex as $categorySlug=>$categoryItem) {
                if($categoryItem['name']===$postCategory) {
                    if ($categoryHtml !== '') $categoryHtml .= '<span class="post-detail-separator">, </span>';
                    $categoryHtml.='<a class="post-detail-link" href="index.php?page=category&amp;slug='.rawurlencode($categorySlug).'">'.esc($postCategory).'</a>';
                    break;
                }
            }
        }
        $studioHtml = '';
        foreach ($studios as $postStudio) {
            if (!$postStudio['id']) continue;
            if ($studioHtml !== '') $studioHtml .= '<span class="post-detail-separator">, </span>';
            $studioHtml .= '<a class="post-detail-link" href="index.php?page=studio&amp;slug='.rawurlencode($postStudio['slug']).'">'.esc($postStudio['name']).'</a>';
        }
        if ($studioHtml === '') $studioHtml = '<span>ไม่ระบุ</span>';
        $postPoster=(isset($p['posterUrl'])?$p['posterUrl']:'')?:($p['cover']?:$sourceVideo['thumb']);
        $posterHtml=$postPoster!==''?'<button class="post-poster poster-lightbox" type="button" data-poster-src="'.esc($postPoster).'" aria-label="ขยายโปสเตอร์"><img src="'.esc($postPoster).'" alt="โปสเตอร์ '.esc($p['title']).'" loading="lazy" onerror="this.parentElement.style.display=\'none\'"><span>ขยายภาพ</span></button>':'<div class="post-poster post-poster-empty" aria-hidden="true">ไม่มีโปสเตอร์</div>';
        $detailsHtml='<section class="post-detail-layout" aria-label="ข้อมูลโพสต์">'.$posterHtml.'<div class="post-detail-panel"><h2>ข้อมูลเรื่อง</h2><div class="post-detail-row"><strong>หมวดหมู่</strong><div>'.($categoryHtml!==''?$categoryHtml:'<span>ไม่ระบุ</span>').'</div></div><div class="post-detail-row"><strong>ค่าย</strong><div>'.$studioHtml.'</div></div><div class="post-detail-row"><strong>นักแสดง</strong><div>'.($actorsHtml!==''?$actorsHtml:'<span>ไม่ระบุ</span>').'</div></div></div></section>';
        $shareUrl=rawurlencode($canonicalUrl);
        $shareTitle=rawurlencode($p['title']);
        $shareHtml='<div class="post-share-actions" aria-label="แชร์โพสต์"><strong>แชร์เรื่องนี้</strong><div class="share-social-links">'.
            '<a class="share-social share-facebook" href="https://www.facebook.com/sharer/sharer.php?u='.$shareUrl.'" target="_blank" rel="noopener noreferrer" aria-label="แชร์ผ่าน Facebook" title="Facebook"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M13.4 21v-8h2.7l.4-3.1h-3.1v-2c0-.9.3-1.5 1.6-1.5h1.7V3.6c-.3 0-1.3-.1-2.5-.1-2.5 0-4.2 1.5-4.2 4.3v2.1H7.2V13H10v8h3.4Z"/></svg></a>'.
            '<a class="share-social share-x" href="https://twitter.com/intent/tweet?url='.$shareUrl.'&text='.$shareTitle.'" target="_blank" rel="noopener noreferrer" aria-label="แชร์ผ่าน X" title="X"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18.9 2H22l-6.8 7.8L23.2 22h-6.3L12 14.6 5.6 22H2.4l7.3-8.4L1.9 2h6.5l4.4 6.9L18.9 2Zm-1.1 18h1.7L7.4 3.9H5.6L17.8 20Z"/></svg></a>'.
            '<a class="share-social share-bluesky" href="https://bsky.app/intent/compose?text='.$shareTitle.'%20'.$shareUrl.'" target="_blank" rel="noopener noreferrer" aria-label="แชร์ผ่าน Bluesky" title="Bluesky"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5.2 3.5C8 5.6 11 9.8 12 12c1-2.2 4-6.4 6.8-8.5 2-1.5 5.2-2.7 5.2 1.1 0 .8-.5 6.7-.8 7.7-.9 3.2-4.1 4-7 3.5 5 .8 6.3 3.4 3.6 6-5.1 4.9-7.3-1.2-7.9-3.1-.1-.4-.2-.6-.3-.4-.1-.2-.2 0-.3.4-.6 1.9-2.8 8-7.9 3.1-2.7-2.6-1.4-5.2 3.6-6-2.9.5-6.1-.3-7-3.5C-.3 11.3-.8 5.4-.8 4.6c0-3.8 3.2-2.6 5.2-1.1Z" transform="translate(1 0) scale(.92)"/></svg></a>'.
            '<a class="share-social share-telegram" href="https://t.me/share/url?url='.$shareUrl.'&text='.$shareTitle.'" target="_blank" rel="noopener noreferrer" aria-label="แชร์ผ่าน Telegram" title="Telegram"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21.7 3.4 18.5 20c-.2 1.2-.9 1.5-1.8.9l-5-3.7-2.4 2.3c-.3.3-.6.6-1.2.6l.4-5.1 9.3-8.4c.4-.4-.1-.6-.6-.2L5.7 13.7.8 12.2c-1.1-.3-1.1-1.1.2-1.6L20.2 3c.9-.3 1.7.2 1.5.4Z"/></svg></a>'.
            '<a class="share-social share-line" href="https://social-plugins.line.me/lineit/share?url='.$shareUrl.'&text='.$shareTitle.'" target="_blank" rel="noopener noreferrer" aria-label="แชร์ผ่าน LINE" title="LINE"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3C6.5 3 2 6.7 2 11.2c0 4 3.5 7.4 8.2 8.1.3.1.7.2.8.5.1.2 0 .6 0 .8l-.1 1c0 .3-.2 1.1 1 .6 1.2-.5 6.1-3.6 8.3-6.2 1.5-1.7 2.3-3.6 2.3-5.2C22 6.7 17.5 3 12 3ZM7.8 13.7H5.9a.5.5 0 0 1-.5-.5V9.5a.5.5 0 0 1 1 0v3.2h1.4a.5.5 0 0 1 0 1Zm2 0a.5.5 0 0 1-1 0V9.5a.5.5 0 0 1 1 0v4.2Zm4.6-.5a.5.5 0 0 1-.9.3l-2-2.7v2.4a.5.5 0 0 1-1 0V9.5a.5.5 0 0 1 .9-.3l2 2.7V9.5a.5.5 0 0 1 1 0v3.7Zm3.7.5h-2.3a.5.5 0 0 1-.5-.5V9.5a.5.5 0 0 1 .5-.5h2.2a.5.5 0 0 1 0 1h-1.7v1.1h1.5a.5.5 0 0 1 0 1h-1.5v.6h1.8a.5.5 0 0 1 0 1Z"/></svg></a>'.
            '<a class="share-social share-reddit" href="https://www.reddit.com/submit?url='.$shareUrl.'&title='.$shareTitle.'" target="_blank" rel="noopener noreferrer" aria-label="แชร์ผ่าน Reddit" title="Reddit"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3a2 2 0 0 0-1.9 2.7c-2.5.3-4.6 1.4-5.8 3.1a2 2 0 1 0-1.1 3.7c-.1.4-.2.8-.2 1.2 0 3.3 4 6 9 6s9-2.7 9-6c0-.4-.1-.8-.2-1.2a2 2 0 1 0-1.1-3.7c-1.2-1.7-3.3-2.8-5.8-3.1A2 2 0 0 0 12 3Zm-3 8a1.3 1.3 0 1 1 0 2.6A1.3 1.3 0 0 1 9 11Zm6 0a1.3 1.3 0 1 1 0 2.6 1.3 1.3 0 0 1 0-2.6Zm-6.5 4.1a.6.6 0 0 1 .8-.1c.7.5 1.7.8 2.7.8s2-.3 2.7-.8a.6.6 0 0 1 .7 1c-.9.7-2.1 1-3.4 1s-2.5-.3-3.4-1a.6.6 0 0 1-.1-.9Z"/></svg></a>'.
            '<a class="share-social share-vk" href="https://vk.com/share.php?url='.$shareUrl.'" target="_blank" rel="noopener noreferrer" aria-label="แชร์ผ่าน VK" title="VK"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3.5 7.5h3c.2 3.2 1.5 4.6 2.5 4.9V7.5h2.8v2.8c1-.1 2.1-1.4 2.5-2.8h2.8a7.7 7.7 0 0 1-2.4 4c1.1.5 2.4 1.8 3 4h-3.1c-.5-1.4-1.5-2.5-2.8-2.6v2.6H11c-4.5 0-7.1-3.1-7.5-8Z"/></svg></a>'.
            '<a class="share-social share-email" href="mailto:?subject='.$shareTitle.'&body='.$shareUrl.'" aria-label="แชร์ทางอีเมล" title="อีเมล"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 5h18a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Zm9 7L3.5 7h17L12 12Zm-9 5h18V8.5l-9 5.3-9-5.3V17Z"/></svg></a>'.
            '<button type="button" class="share-social share-copy-link" data-copy-url="'.esc($canonicalUrl).'" aria-label="คัดลอกลิงก์เรื่องนี้" title="คัดลอกลิงก์"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10.6 13.4a1 1 0 0 0 1.4 1.4l4-4a3 3 0 0 0-4.2-4.2l-2.3 2.3a1 1 0 1 0 1.4 1.4l2.3-2.3a1 1 0 0 1 1.4 1.4l-4 4Zm2.8-2.8a1 1 0 0 0-1.4-1.4l-4 4a3 3 0 0 0 4.2 4.2l2.3-2.3a1 1 0 1 0-1.4-1.4l-2.3 2.3a1 1 0 0 1-1.4-1.4l4-4Z"/></svg></button></div></div>';
        $metaLine = '<span class="post-meta-date-group"><time class="post-meta-date" datetime="'.esc(date('c',strtotime($p['date']))).'">📅 เผยแพร่ '.fmtDate($p['date']).'</time>'.(themeOn('showViews')?'<span class="post-meta-views">👁 '.$p['views'].' views</span>':'').'</span>'.($p['videoDuration']?'<span class="badge badge-gold">'.clockIcon().' '.esc(formatVideoDuration($p['videoDuration'])).'</span>':'');
        $galleryHtml = '';
        if ($gallery) {
            $galleryHtml = '<h3 style="font-size:16px;font-weight:700;margin:24px 0 12px;">แกลเลอรี่</h3><div class="gallery-carousel" data-gallery-carousel tabindex="0" aria-label="แกลเลอรี่ ใช้ปุ่มลูกศรซ้ายขวาเพื่อเลื่อนดูภาพ"><button class="gallery-scroll-button is-prev" type="button" data-gallery-scroll="-1" aria-label="เลื่อนแกลเลอรี่ไปทางซ้าย">‹</button><div class="gallery-viewport"><div class="gallery-grid">';
            foreach ($gallery as $i=>$src) $galleryHtml .= '<button class="gallery-item" type="button" data-gallery-image="'.(int)$i.'" data-gallery-src="'.esc($src).'" aria-label="เปิดภาพที่ '.($i+1).' จาก '.count($gallery).'"><img src="'.esc($src).'" alt="ภาพที่ '.($i+1).'" loading="lazy" onerror="this.parentElement.style.display=\'none\'"></button>';
            $galleryHtml .= '</div></div><button class="gallery-scroll-button is-next" type="button" data-gallery-scroll="1" aria-label="เลื่อนแกลเลอรี่ไปทางขวา">›</button></div>';
        }
        $relatedHtml = '';
        if (themeOn('showRelated')) {
            $rel = [];
            $allPub = $categoryPosts;
            foreach ($allPub as $r) {
                if ($r['id'] != $p['id'] && ($r['studioId']==$p['studioId'] || count(array_intersect(json_decode($r['tagIds'],true)?:[], $tagIds))>0)) $rel[] = $r;
            }
            $rel = array_slice($rel, 0, 3);
            if ($rel) {
                $relatedHtml = '<h3 style="font-size:16px;font-weight:700;margin:28px 0 12px;">โพสต์ที่เกี่ยวข้อง</h3><div class="grid" style="grid-template-columns:repeat(auto-fill,minmax(200px,1fr));margin-bottom:24px;">';
                foreach ($rel as $r) {
                    $rv = parseVideo($r['videoUrl']);
                    $thumb = $r['cover'] ?: $rv['thumb'];
                    $relatedHtml .= '<div class="related-card" onclick="location.href=\'index.php?page=post&slug='.$r['slug'].'\'"><div class="post-thumb" style="aspect-ratio:16/10;">'.($thumb?'<img src="'.esc($thumb).'" style="width:100%;height:100%;object-fit:cover;" loading="lazy">':'').($r['videoDuration']?'<span class="dur-badge">'.clockIcon().esc(formatVideoDuration($r['videoDuration'])).'</span>':'').'</div><div style="padding:10px;font-size:12px;font-weight:600;line-height:1.4;">'.esc($r['title']).'</div></div>';
                }
                $relatedHtml .= '</div>';
            }
        }
        $cmts = [];
        $res = mysqli_query($conn, "SELECT * FROM comments WHERE postId=".$p['id']." AND approved=1");
        while ($r = mysqli_fetch_assoc($res)) $cmts[] = $r;
        $commentsHtml = '<h3 style="font-size:16px;font-weight:700;margin:28px 0 12px;">คอมเมนต์ ('.count($cmts).')</h3>';
        foreach ($cmts as $c) $commentsHtml .= '<div class="comment-item"><span class="c-name">'.esc($c['name']).'</span><span class="c-date">'.fmtDate($c['date']).'</span><div class="c-text">'.esc($c['text']).'</div></div>';
        $commentsHtml .= '<div class="card mt"><h4 style="font-size:14px;font-weight:700;margin-bottom:10px;">แสดงความคิดเห็น</h4><form method="post" action="index.php?page=post&slug='.esc($p['slug']).'"><input type="hidden" name="postId" value="'.$p['id'].'"><input name="cmtName" placeholder="ชื่อของคุณ" style="margin-bottom:8px;"><textarea name="cmtText" rows="2" placeholder="เขียนคอมเมนต์..." style="margin-bottom:8px;"></textarea><button class="btn btn-primary btn-sm" type="submit">ส่งคอมเมนต์</button></form></div>';
        $contentHtml = '<nav style="padding:16px 0;font-size:12px;color:var(--muted);"><a href="index.php" style="color:var(--accent);">หน้าหลัก</a> › <a href="index.php?page=studio&slug='.$studio['slug'].'" style="color:var(--accent);">'.esc($studio['name']).'</a> › <span>'.esc($p['title']).'</span></nav>'.
            '<h1 style="font-size:clamp(20px,3.5vw,28px);font-weight:900;line-height:1.3;margin-bottom:12px;">'.esc($p['title']).'</h1>'.
            '<div class="post-meta-line">'.$metaLine.'</div>'.
            ($p['videoUrl']?'<div class="video-wrap glow-pink" style="margin-bottom:12px;">'.$v['embed'].'</div>':'').
            $shareHtml.$detailsHtml.
            '<div class="card" style="margin-bottom:20px;"><div style="line-height:1.8;font-size:14px;">'.$p['content'].'</div></div>'.
            $galleryHtml.$relatedHtml.$commentsHtml.
            '<div style="text-align:center;margin:24px 0;"><a class="btn btn-ghost" href="index.php">← กลับหน้าหลัก</a></div>';
    } else {
        $contentHtml = '<div class="card" style="text-align:center;padding:40px;margin-top:20px;">ไม่พบโพสต์นี้ <a href="index.php" style="color:var(--accent);">กลับหน้าหลัก</a></div>';
    }
} elseif ($page === 'page' && $slug) {
    $slugE = mysqli_real_escape_string($conn, $slug);
    $pg = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM pages WHERE slug='$slugE' AND status='publish'"));
    if ($pg) {
        $metaTitle = $pg['title'].' | '.setting('siteName');
        $metaDesc = substr(stripHtml($pg['content']), 0, 150);
        $contentHtml = '<nav style="padding:16px 0;font-size:12px;color:var(--muted);"><a href="index.php" style="color:var(--accent);">หน้าหลัก</a> › '.esc($pg['title']).'</nav><h1 style="font-size:26px;font-weight:900;margin-bottom:16px;">'.esc($pg['title']).'</h1><div class="card" style="margin-bottom:24px;line-height:1.8;">'.$pg['content'].'</div>';
    } else $contentHtml = '<div class="card" style="text-align:center;padding:40px;margin-top:20px;">ไม่พบหน้านี้ <a href="index.php" style="color:var(--accent);">กลับหน้าหลัก</a></div>';
} elseif (($page==='studio'||$page==='actor'||$page==='tag') && $slug) {
    $table = $page==='studio'?'studios':($page==='actor'?'actors':'tags');
    $slugE = mysqli_real_escape_string($conn, $slug);
    $item = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM $table WHERE slug='$slugE'"));
    if ($item) {
        $label = $page==='studio'?'ค่าย':($page==='actor'?'นักแสดง':'แท็ก');
        $nameField = $page==='tag' ? '#'.$item['name'] : $item['name'];
        $descField = $page==='studio' ? $item['desc'] : ($page==='actor' ? $item['bio'] : '');
        $metaTitle = !empty($item['metaTitle']) ? $item['metaTitle'] : $label.' '.$item['name'];
        $metaDesc = !empty($item['metaDesc']) ? $item['metaDesc'] : $descField;
        if ($page==='studio') $posts = mysqli_query($conn, "SELECT * FROM posts WHERE status='publish' AND studioId=".$item['id']." ORDER BY date DESC");
        elseif ($page==='actor') { $posts = []; $all = publishedPosts(); foreach ($all as $pp) if (in_array($item['id'], json_decode($pp['actorIds'],true)?:[])) $posts[] = $pp; }
        else { $posts = []; $all = publishedPosts(); foreach ($all as $pp) if (in_array($item['id'], json_decode($pp['tagIds'],true)?:[])) $posts[] = $pp; }
        $list = [];
        if ($page==='studio') { while ($r=mysqli_fetch_assoc($posts)) $list[]=$r; } else $list = $posts;
        $contentHtml = '<nav style="padding:16px 0;font-size:12px;color:var(--muted);"><a href="index.php" style="color:var(--accent);">หน้าหลัก</a> › '.esc($nameField).'</nav><h1 style="font-size:24px;font-weight:900;margin-bottom:6px;">'.esc($nameField).'</h1>'.($descField?'<p style="color:var(--muted);font-size:13px;margin-bottom:14px;max-width:600px;">'.esc($descField).'</p>':'').'<p style="color:var(--muted);font-size:13px;margin-bottom:20px;">พบทั้งหมด '.count($list).' รายการ</p>';
        $contentHtml .= count($list) ? '<div class="grid" style="grid-template-columns:repeat(auto-fill,minmax(240px,1fr));margin-bottom:32px;">'.implode('', array_map('postCardHtml', $list)).'</div>' : '<div class="card" style="text-align:center;color:var(--muted);padding:40px;">ยังไม่มีคอนเทนต์ในหมวดนี้</div>';
    } else $contentHtml = '<div class="card" style="text-align:center;padding:40px;margin-top:20px;">ไม่พบหมวดหมู่นี้ <a href="index.php" style="color:var(--accent);">กลับหน้าหลัก</a></div>';
} elseif ($page==='all') {
    $pageSize=12;
    $totalResult=mysqli_query($conn,"SELECT COUNT(*) AS total FROM posts WHERE status='publish'");
    $totalPosts=(int)mysqli_fetch_assoc($totalResult)['total'];
    $totalPages=max(1,(int)ceil($totalPosts/$pageSize));
    $currentPage=min(max(1,(int)(isset($_GET['pg'])?$_GET['pg']:1)),$totalPages);
    $offset=($currentPage-1)*$pageSize;
    $posts=[];$result=mysqli_query($conn,"SELECT * FROM posts WHERE status='publish' ORDER BY date DESC,id DESC LIMIT $offset,$pageSize");
    while($row=mysqli_fetch_assoc($result))$posts[]=$row;
    $metaTitle='เรื่องทั้งหมด | '.setting('siteName');
    $metaDesc='รวมเรื่องที่เผยแพร่ทั้งหมด '.number_format($totalPosts).' เรื่อง เลือกอ่านตามวันที่อัปเดตล่าสุด';
    $canonicalUrl=$canonicalBase.'/?page=all'.($currentPage>1?'&pg='.$currentPage:'');
    $pageSchema=['@context'=>'https://schema.org','@type'=>'CollectionPage','name'=>$metaTitle,'description'=>$metaDesc,'url'=>$canonicalUrl,'isPartOf'=>['@type'=>'WebSite','name'=>setting('siteName'),'url'=>$canonicalBase.'/'],'inLanguage'=>'th'];
    $contentHtml='<section class="archive-hero"><span class="archive-eyebrow">PINKCMS · คลังเรื่อง</span><h1>เรื่องทั้งหมด</h1><p>รวมเรื่องที่เผยแพร่แล้ว เรียงตามวันที่อัปเดตล่าสุด</p><div class="archive-count"><strong>'.number_format($totalPosts).'</strong> เรื่อง</div></section>'.
        '<div class="archive-heading"><h2>รายการล่าสุด</h2><span>หน้า '.$currentPage.' / '.$totalPages.'</span></div>'.
        (count($posts)?'<div class="grid archive-post-grid">'.implode('',array_map('postCardHtml',$posts)).'</div>':'<div class="card archive-empty">ยังไม่มีเรื่องที่เผยแพร่</div>').
        $renderPagination('all',$currentPage,$totalPages);
} elseif ($page==='categories') {
    $categoryIndex=$loadCategoryIndex();
    $categoryTotal=array_sum(array_column($categoryIndex,'count'));
    $metaTitle='หมวดหมู่ทั้งหมด | '.setting('siteName');
    $metaDesc='เลือกดูเรื่องที่เผยแพร่ตามหมวดหมู่และแท็ก รวม '.count($categoryIndex).' หัวข้อ';
    $canonicalUrl=$canonicalBase.'/?page=categories';
    $pageSchema=['@context'=>'https://schema.org','@type'=>'CollectionPage','name'=>$metaTitle,'description'=>$metaDesc,'url'=>$canonicalUrl,'isPartOf'=>['@type'=>'WebSite','name'=>setting('siteName'),'url'=>$canonicalBase.'/'],'inLanguage'=>'th'];
    $categoryCards='';
    foreach($categoryIndex as $categorySlug=>$category) {
        $categoryCards.='<a class="category-card" href="index.php?page=category&amp;slug='.rawurlencode($categorySlug).'"><span class="category-card-icon" aria-hidden="true">✦</span><span class="category-card-name">'.esc($category['name']).'</span><span class="category-card-count">'.number_format($category['count']).' เรื่อง</span><span class="category-card-arrow" aria-hidden="true">→</span></a>';
    }
    $contentHtml='<nav class="archive-breadcrumb" aria-label="เส้นทางนำทาง"><a href="index.php">หน้าหลัก</a><span>›</span><span>หมวดหมู่</span></nav>'.
        '<section class="archive-hero"><span class="archive-eyebrow">PINKCMS · สำรวจตามหมวด</span><h1>หมวดหมู่ทั้งหมด</h1><p>เลือกหมวดหมู่หรือแท็กที่สนใจเพื่อดูเรื่องที่เกี่ยวข้อง</p><div class="archive-count"><strong>'.count($categoryIndex).'</strong> หัวข้อ <i>·</i> <strong>'.number_format($categoryTotal).'</strong> รายการ</div></section>'.
        (count($categoryIndex)?'<div class="category-grid">'.$categoryCards.'</div>':'<div class="card archive-empty">ยังไม่มีหมวดหมู่ — เพิ่มหมวดหมู่ในข้อมูลโพสต์ที่เผยแพร่แล้ว</div>');
} elseif ($page==='category' && $slug!=='') {
    $categoryIndex=$loadCategoryIndex();
    if(isset($categoryIndex[$slug])) {
        $category=$categoryIndex[$slug];$pageSize=12;
        $matchedPosts=[];
        foreach(publishedPosts() as $post)if(in_array($category['name'],$categoriesForPost($post),true))$matchedPosts[]=$post;
        usort($matchedPosts,function($a,$b){$dateOrder=strcmp((string)$b['date'],(string)$a['date']);return $dateOrder!==0?$dateOrder:((int)$b['id']-(int)$a['id']);});
        $totalPosts=count($matchedPosts);$totalPages=max(1,(int)ceil($totalPosts/$pageSize));
        $currentPage=min(max(1,(int)(isset($_GET['pg'])?$_GET['pg']:1)),$totalPages);
        $posts=array_slice($matchedPosts,($currentPage-1)*$pageSize,$pageSize);
        $metaTitle=!empty($category['name'])?$category['name'].' | '.setting('siteName'):setting('siteName');
        $metaDesc='รวมเรื่องในหมวด '. $category['name'].' จำนวน '.number_format($totalPosts).' เรื่อง เรียงตามวันที่อัปเดตล่าสุด';
        $canonicalUrl=$canonicalBase.'/?page=category&slug='.rawurlencode($slug).($currentPage>1?'&pg='.$currentPage:'');
        $pageSchema=['@context'=>'https://schema.org','@type'=>'CollectionPage','name'=>$metaTitle,'description'=>$metaDesc,'url'=>$canonicalUrl,'isPartOf'=>['@type'=>'WebSite','name'=>setting('siteName'),'url'=>$canonicalBase.'/'],'inLanguage'=>'th'];
        $contentHtml='<nav class="archive-breadcrumb" aria-label="เส้นทางนำทาง"><a href="index.php">หน้าหลัก</a><span>›</span><a href="index.php?page=categories">หมวดหมู่</a><span>›</span><span>'.esc($category['name']).'</span></nav>'.
            '<section class="archive-hero"><span class="archive-eyebrow">PINKCMS · หมวดหมู่</span><h1>'.esc($category['name']).'</h1><p>เรื่องทั้งหมดในหมวดนี้ เรียงตามวันที่อัปเดตล่าสุด</p><div class="archive-count"><strong>'.number_format($totalPosts).'</strong> เรื่อง</div></section>'.
            '<div class="archive-heading"><h2>เรื่องในหมวด '.esc($category['name']).'</h2><span>หน้า '.$currentPage.' / '.$totalPages.'</span></div>'.
            (count($posts)?'<div class="grid archive-post-grid">'.implode('',array_map('postCardHtml',$posts)).'</div>':'<div class="card archive-empty">ยังไม่มีเรื่องในหมวดนี้</div>').
            $renderPagination('category',$currentPage,$totalPages,['slug'=>$slug]);
    } else {
        $metaTitle='ไม่พบหมวดหมู่ | '.setting('siteName');
        $canonicalUrl='';
        $robotsContent='noindex, follow';
        $contentHtml='<div class="card archive-empty"><h1>ไม่พบหมวดหมู่นี้</h1><p>หมวดหมู่อาจถูกลบหรือยังไม่มีเรื่องที่เผยแพร่</p><a class="btn btn-ghost" href="index.php?page=categories">ดูหมวดหมู่ทั้งหมด</a></div>';
    }
} elseif ($page==='search') {
    $qE = mysqli_real_escape_string($conn, $q);
    $posts = [];
    $res = mysqli_query($conn, "SELECT * FROM posts WHERE status='publish' AND (title LIKE '%$qE%' OR content LIKE '%$qE%') ORDER BY date DESC");
    while ($r = mysqli_fetch_assoc($res)) $posts[] = $r;
    $metaTitle = 'ค้นหา: '.$q;
    $contentHtml = '<h1 style="font-size:22px;font-weight:900;margin:16px 0;">ผลการค้นหา: "'.esc($q).'"</h1><p style="color:var(--muted);font-size:13px;margin-bottom:20px;">พบ '.count($posts).' รายการ</p>';
    $contentHtml .= count($posts) ? '<div class="grid" style="grid-template-columns:repeat(auto-fill,minmax(240px,1fr));margin-bottom:32px;">'.implode('', array_map('postCardHtml', $posts)).'</div>' : '<div class="card" style="text-align:center;color:var(--muted);padding:40px;">ไม่พบผลการค้นหา</div>';
} elseif ($page==='taxlist' && in_array($slug, ['studio','actor','tag'])) {
    $table = $slug==='studio'?'studios':($slug==='actor'?'actors':'tags');
    $label = $slug==='studio'?'ค่ายทั้งหมด':($slug==='actor'?'นักแสดงทั้งหมด':'แท็กทั้งหมด');
    $items = [];
    $res = mysqli_query($conn, "SELECT * FROM $table");
    while ($r = mysqli_fetch_assoc($res)) $items[] = $r;
    $allPub = publishedPosts();
    $contentHtml = '<h1 style="font-size:22px;font-weight:900;margin:16px 0 20px;">'.$label.'</h1><div class="grid" style="grid-template-columns:repeat(auto-fill,minmax(160px,1fr));margin-bottom:32px;gap:10px;">';
    foreach ($items as $it) {
        $count = 0;
        foreach ($allPub as $p) {
            if ($slug==='studio' && $p['studioId']==$it['id']) $count++;
            elseif ($slug==='actor' && in_array($it['id'], json_decode($p['actorIds'],true)?:[])) $count++;
            elseif ($slug==='tag' && in_array($it['id'], json_decode($p['tagIds'],true)?:[])) $count++;
        }
        $contentHtml .= '<div class="card" style="cursor:pointer;text-align:center;padding:16px;" onclick="location.href=\'index.php?page='.$slug.'&slug='.$it['slug'].'\'"><div style="font-weight:700;font-size:14px;margin-bottom:4px;">'.esc($it['name']).'</div><div style="font-size:11px;color:var(--muted);">'.$count.' รายการ</div></div>';
    }
    $contentHtml .= '</div>';
} else {
    // HOME
    $posts = publishedPosts();
    $hero = ['title1'=>setting('hero_title1','คอนเทนต์ใหม่'),'title2'=>setting('hero_title2','อัปเดตล่าสุด'),'sub'=>setting('hero_sub','PINKCMS'),'desc'=>setting('hero_desc','ค้นหาและชมคอนเทนต์คุณภาพ อัปเดตทุกวัน')];
    $bannerHtml = '';
    if (themeOn('showBanners') && $activeBanners) {
        $bannerHtml = '<div id="bannerBox" class="banner-box"><section class="hero-panel hero-featured" id="bannerSlide" data-hero-slider data-count="'.count($activeBanners).'" aria-label="บทความรหัสหนังใหม่" aria-live="polite"><div class="hero-arrows"><button type="button" data-hero-prev aria-label="บทความก่อนหน้า">←</button><button type="button" data-hero-next aria-label="บทความถัดไป">→</button></div></section><div class="banner-dots" id="bannerDots">'.implode('', array_map(function($i){return '<button type="button" aria-label="แสดงบทความที่ '.($i+1).'" '.($i===0?'class="active"':'').' aria-pressed="'.($i===0?'true':'false').'" data-banner-index="'.$i.'"></button>';}, array_keys($activeBanners))).'</div></div>';
    }
    $contentHtml = $bannerHtml.
        '<div class="hero-glow home-hero" style="border:1px solid var(--border);border-radius:18px;padding:28px 22px;margin:12px auto 24px;position:relative;overflow:hidden;">'.
        '<div style="position:absolute;top:-30px;right:-30px;width:180px;height:180px;border-radius:50%;background:radial-gradient(circle,var(--accent-soft),transparent 70%);"></div>'.
        '<div style="position:relative;z-index:1;"><p style="font-size:11px;letter-spacing:.25em;text-transform:uppercase;color:var(--accent);font-weight:600;margin-bottom:10px;">'.esc($hero['sub']).'</p>'.
        '<h1 style="font-size:clamp(24px,4vw,36px);font-weight:900;line-height:1.2;margin-bottom:10px;">'.esc($hero['title1']).'<br><span style="color:var(--accent);">'.esc($hero['title2']).'</span></h1>'.
        '<p style="color:var(--muted);font-size:13px;max-width:400px;">'.esc($hero['desc']).'</p></div></div>'.
        '<h2 style="font-size:18px;font-weight:800;margin-bottom:14px;">โพสต์ล่าสุด</h2>'.
        (count($posts) ? '<div class="grid" style="grid-template-columns:repeat(auto-fill,minmax(240px,1fr));margin-bottom:32px;">'.implode('', array_map('postCardHtml', $posts)).'</div>' : '<div class="card" style="text-align:center;color:var(--muted);padding:40px;">ยังไม่มีโพสต์ — <a href="admin/" style="color:var(--accent);">ไปเขียนโพสต์แรกในหลังบ้าน</a></div>');
}

function postCardHtml($p) {
    $v = parseVideo($p['videoUrl']);
    $thumb = $p['cover'] ?: $v['thumb'];
    $actorIds = parseTaxonomyIds($p['actorIds']);
    $tagIds = parseTaxonomyIds($p['tagIds']);
    $actorNames = array_map(function($aid){return getActor($aid)['name'];}, $actorIds);
    $actors = implode(', ', array_slice($actorNames, 0, 2));
    $studioNames = array_map(function($studioId){return getStudio($studioId)['name'];}, parseTaxonomyIds($p['studioId']));
    $dur = (themeOn('showDuration') && $p['videoDuration']) ? '<span class="dur-badge">'.clockIcon().esc(formatVideoDuration($p['videoDuration'])).'</span>' : '';
    $categoryNames=preg_split('/[,،;|，；\r\n]+/u',isset($p['category'])?$p['category']:'');
    $categoryNames=array_values(array_unique(array_filter(array_map('trim',$categoryNames?:[]),function($name){return $name!=='';})));
    $categoryHtml='';
    foreach(array_slice($categoryNames,0,2) as $categoryName)$categoryHtml.='<span class="badge badge-pink">'.esc($categoryName).'</span> ';
    $tagsHtml = themeOn('showTags') ? '<div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:6px;">'.implode('', array_map(function($tid){return '<span class="badge badge-gray">#'.esc(getTag($tid)['name']).'</span>';}, array_slice($tagIds,0,3))).'</div>' : '';
    return '<article class="post-card" onclick="location.href=\'index.php?page=post&slug='.$p['slug'].'\'">'.
        '<div class="post-thumb">'.($thumb?'<img src="'.esc($thumb).'" alt="'.esc($p['title']).'" loading="lazy" onerror="this.style.display=\'none\'">':'').
        ($p['videoUrl']?'<div class="play-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="#fff"><polygon points="6 3 20 12 6 21 6 3"/></svg></div>':'').$dur.
        (themeOn('showStudio') && $studioNames?'<span style="position:absolute;top:8px;left:8px;" class="badge badge-pink">'.esc(implode(', ', $studioNames)).'</span>':'').
        (!empty($p['code'])?'<span class="code-badge">'.esc($p['code']).'</span>':'').'</div>'.
        '<div style="padding:12px;"><h3 style="font-size:14px;font-weight:700;line-height:1.4;margin-bottom:6px;min-height:38px;">'.esc($p['title']).'</h3>'.
        ($categoryHtml!==''?'<div class="post-category-list">'.$categoryHtml.'</div>':'').
        $tagsHtml.
        '<div style="display:flex;justify-content:space-between;align-items:center;font-size:11px;color:var(--muted);">'.(themeOn('showActors')?'<span>'.esc($actors).'</span>':'<span>📅 '.fmtDate($p['date']).'</span>').(themeOn('showViews')?'<span>👁 '.$p['views'].'</span>':'').'</div></div></article>';
}

// Handle comment submit
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['cmtText'])) {
    $postId = intval($_POST['postId']);
    $name = trim($_POST['cmtName']) ?: 'ผู้เยี่ยมชม';
    $text = trim($_POST['cmtText']);
    if ($text) {
        $nameE = mysqli_real_escape_string($conn, $name);
        $textE = mysqli_real_escape_string($conn, $text);
        mysqli_query($conn, "INSERT INTO comments (postId,name,text,date,approved) VALUES ($postId,'$nameE','$textE',CURDATE(),0)");
        logActivity('มีคอมเมนต์ใหม่จาก '.$name);
        echo '<script>alert("ส่งคอมเมนต์แล้ว รอการอนุมัติ");</script>';
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo esc($metaTitle); ?></title>
<meta name="description" content="<?php echo esc($metaDesc); ?>">
<meta name="keywords" content="<?php echo esc($metaKeywords); ?>">
<meta name="robots" content="<?php echo esc($robotsContent); ?>">
<?php if($canonicalUrl!==''): ?><link rel="canonical" href="<?php echo esc($canonicalUrl); ?>"><?php endif; ?>
<meta property="og:type" content="<?php echo $videoSchema?'video.other':'website'; ?>">
<meta property="og:title" content="<?php echo esc($metaTitle); ?>">
<meta property="og:description" content="<?php echo esc($metaDesc); ?>">
<?php if($canonicalUrl!==''): ?><meta property="og:url" content="<?php echo esc($canonicalUrl); ?>"><?php endif; ?>
<?php if($videoSchema && !empty($videoSchema['thumbnailUrl'])): ?><meta property="og:image" content="<?php echo esc($videoSchema['thumbnailUrl']); ?>"><?php endif; ?>
<?php if($videoSchema): ?><script type="application/ld+json"><?php echo json_encode($videoSchema,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT|JSON_INVALID_UTF8_SUBSTITUTE); ?></script><?php endif; ?>
<?php if($pageSchema): ?><script type="application/ld+json"><?php echo json_encode($pageSchema,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT|JSON_INVALID_UTF8_SUBSTITUTE); ?></script><?php endif; ?>
<link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
:root{--bg:#0a0a0f;--card:#14141c;--card2:#1a1a24;--text:#fff;--muted:#8b8b9a;--accent:<?php echo $accent; ?>;--accent-soft:<?php echo $accent; ?>26;--accent2:#a855f7;--accent3:#22d3ee;--gold:#f5c542;--border:rgba(255,255,255,.08);--danger:#ef4444;--success:#22c55e}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Kanit',sans-serif;background:var(--bg);color:var(--text);line-height:1.5;font-size:14px}
a{color:inherit;text-decoration:none;cursor:pointer}
button{cursor:pointer;font-family:inherit}
input,textarea,select{font-family:inherit;font-size:13px;background:var(--bg);color:var(--text);border:1px solid var(--border);border-radius:8px;padding:9px 12px;width:100%}
input:focus,textarea:focus,select:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 2px rgba(255,45,120,.2)}
.btn{padding:9px 18px;border-radius:8px;border:none;font-weight:600;font-size:13px;transition:all .2s;display:inline-block}
.btn-primary{background:var(--accent);color:#fff;box-shadow:0 0 12px rgba(255,45,120,.4)}
.btn-ghost{background:var(--card);color:var(--text);border:1px solid var(--border)}
.site-primary-nav{display:flex;align-items:center;gap:clamp(8px,1.5vw,20px);margin-left:auto}
.site-primary-nav a{display:inline-flex;align-items:center;gap:6px;padding:7px 4px;color:var(--muted);font-size:12px;font-weight:700;white-space:nowrap;transition:color .2s,transform .2s}
.site-primary-nav a:hover,.site-primary-nav a[aria-current="page"]{color:var(--text);transform:translateY(-1px)}
.site-primary-nav svg{width:17px;height:17px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
.site-primary-nav a:nth-child(1) svg{color:#fb7185}
.site-primary-nav a:nth-child(2) svg{color:#c084fc}
.site-primary-nav a:nth-child(3) svg{color:#38bdf8}
.site-primary-nav a:nth-child(4) svg{color:#fbbf24}
.badge{display:inline-block;padding:2px 8px;border-radius:999px;font-size:11px;font-weight:600}
.badge-pink{background:var(--accent-soft);color:var(--accent);border:1px solid rgba(255,45,120,.3)}
.badge-gold{background:rgba(245,197,66,.15);color:var(--gold);border:1px solid rgba(245,197,66,.3)}
.badge-gray{background:rgba(255,255,255,.06);color:var(--muted);border:1px solid var(--border)}
.card{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:18px}
.grid{display:grid;gap:12px}
.glow-pink{box-shadow:0 0 20px rgba(255,45,120,.35)}
.post-card{background:var(--card);border:1px solid var(--border);border-radius:14px;overflow:hidden;cursor:pointer;transition:all .25s}
.post-card:hover{transform:translateY(-3px);border-color:var(--accent);box-shadow:0 0 20px rgba(255,45,120,.3)}
.post-thumb{aspect-ratio:16/10;background:linear-gradient(135deg,rgba(255,45,120,.2),rgba(168,85,247,.15));display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden;isolation:isolate}
.post-thumb img{width:100%;height:100%;object-fit:cover}
.post-thumb:after{content:"";position:absolute;z-index:1;right:0;bottom:0;left:0;height:28px;background:linear-gradient(0deg,var(--card),rgba(16,16,23,0));pointer-events:none}
.code-badge{position:absolute;z-index:2;left:8px;bottom:8px;max-width:60%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;padding:3px 8px;border:1px solid rgba(255,255,255,.2);border-radius:999px;background:rgba(10,10,15,.82);color:#fff;font-size:10px;font-weight:800}
.post-category-list{display:flex;gap:4px;flex-wrap:wrap;margin-bottom:6px}
.play-icon{position:absolute;z-index:2;width:52px;height:52px;border-radius:50%;background:rgba(255,45,120,.9);display:flex;align-items:center;justify-content:center;box-shadow:0 0 20px rgba(255,45,120,.6)}
.dur-badge{position:absolute;z-index:2;bottom:8px;right:8px;background:rgba(0,0,0,.75);color:#fff;font-size:11px;font-weight:600;padding:2px 8px;border-radius:6px;display:flex;align-items:center;gap:4px}
.chip{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:500;margin:2px;background:var(--accent-soft);color:var(--accent);border:1px solid rgba(255,45,120,.3);cursor:pointer}
.hero-glow{background:radial-gradient(ellipse at 80% 20%,rgba(255,45,120,.25),transparent 55%),radial-gradient(ellipse at 10% 90%,rgba(168,85,247,.18),transparent 50%),var(--card)}
.home-hero{width:100%;max-width:none;text-align:center}
.home-hero>div:last-child p:last-child{margin-left:auto;margin-right:auto}
.video-wrap{position:relative;width:100%;aspect-ratio:16/9;border-radius:14px;overflow:hidden;background:#000}
.video-wrap iframe,.video-wrap video{width:100%;height:100%;border:none}
.post-share-actions{display:flex;align-items:center;gap:10px 14px;flex-wrap:wrap;margin:0 auto 14px;max-width:920px}
.post-share-actions>strong{width:100%;margin-right:4px;font-size:13px;font-weight:800}
.share-social-links{display:flex;gap:8px;flex-wrap:wrap}
.share-social{display:grid;width:40px;height:40px;place-items:center;border:2px solid rgba(255,255,255,.25);border-radius:11px;background:#101116;color:#fff;font-size:19px;font-weight:800;line-height:1;box-shadow:0 2px 0 rgba(0,0,0,.25);transition:transform .18s,border-color .18s,filter .18s}
.share-social:hover{transform:translateY(-2px);filter:brightness(1.12)}
.share-social svg,.share-copy-link svg{width:19px;height:19px;fill:currentColor}
.share-facebook{background:#1877f2;border-color:#55a0ff}
.share-x{background:#090a0d;border-color:#777}
.share-bluesky{background:#087ff5;border-color:#73b8ff}
.share-telegram{background:#229ed9;border-color:#65ceff}
.share-line{background:#06c755;border-color:#6be99b;font-size:11px}
.share-reddit{background:#ff4500;border-color:#ff875f}
.share-vk{background:#4c75a3;border-color:#8eb5e2}
.share-email{background:#6b6b70;border-color:#aaa}
.share-copy-link{appearance:none;-webkit-appearance:none;padding:0;border-color:#ff78d2;background:linear-gradient(135deg,#ff4ec4,#c6158b);cursor:pointer}
.post-detail-layout{display:grid;grid-template-columns:minmax(150px,220px) minmax(0,1fr);gap:16px;align-items:stretch;max-width:920px;margin:0 auto 20px}
.post-poster{position:relative;display:block;width:100%;min-height:180px;aspect-ratio:2/3;overflow:hidden;padding:0;border:1px solid var(--border);border-radius:14px;background:linear-gradient(145deg,var(--card),var(--card2));cursor:zoom-in}
.post-poster img{display:block;width:100%;height:100%;object-fit:cover}
.post-poster>span{position:absolute;right:8px;bottom:8px;padding:4px 9px;border-radius:999px;background:rgba(0,0,0,.72);color:#fff;font-size:10px}
.post-poster-empty{display:grid;place-items:center;color:var(--muted);font-size:12px;cursor:default}
.post-detail-panel{display:grid;grid-template-columns:max-content minmax(0,1fr);padding:16px;border:1px solid var(--border);border-radius:14px;background:var(--card)}
.post-detail-panel h2{grid-column:1/-1;margin:0 0 8px;font-size:16px;font-weight:800}
.post-detail-row{display:contents;font-size:12px}
.post-detail-row>strong{width:max-content;padding:10px 0;border-top:1px solid var(--border);color:var(--muted);font-weight:600}
.post-detail-row>div{display:flex;width:100%;min-width:0;gap:6px;flex-wrap:wrap;align-content:flex-start;padding:10px 0;border-top:1px solid var(--border)}
.post-detail-link{display:inline-flex;align-items:center;padding:3px 9px;border:1px solid rgba(255,45,120,.25);border-radius:999px;background:var(--accent-soft);color:var(--accent);font-size:11px}
.post-detail-row>div .post-detail-link{width:auto}
.post-detail-separator{color:var(--muted);white-space:pre}
.post-detail-link:hover{border-color:var(--accent);color:var(--text)}
.post-meta-line{display:flex;align-items:center;gap:8px;flex-wrap:nowrap;min-width:0;overflow-x:auto;margin-bottom:16px;font-size:12px;scrollbar-width:none}
.post-meta-line::-webkit-scrollbar{display:none}
.post-meta-date-group{display:inline-flex;align-items:center;gap:8px;flex:0 0 auto;white-space:nowrap}
.post-meta-date,.post-meta-views,.post-meta-line .badge-gold{flex:0 0 auto;white-space:nowrap}
.related-card{background:var(--card);border:1px solid var(--border);border-radius:10px;overflow:hidden;cursor:pointer}
.related-card:hover{border-color:var(--accent)}
.bottom-nav{position:sticky;bottom:0;background:rgba(10,10,15,.95);backdrop-filter:blur(10px);border-top:1px solid var(--border);padding:8px 16px;display:grid;grid-template-columns:repeat(4,1fr);gap:8px;z-index:50}
.bottom-btn{background:transparent;border:1px solid transparent;border-radius:10px;padding:8px;color:var(--muted);font-size:11px;font-weight:500;display:flex;flex-direction:column;align-items:center;gap:3px;cursor:pointer}
.bottom-btn svg{transition:color .2s,filter .2s}
.bottom-btn:nth-child(1) svg{color:#ff4f91}
.bottom-btn:nth-child(2) svg{color:#a78bfa}
.bottom-btn:nth-child(3) svg{color:#38bdf8}
.bottom-btn:nth-child(4) svg{color:#fbbf24}
.bottom-btn.active{border-color:var(--accent);color:var(--accent);background:rgba(255,45,120,.08)}
.bottom-btn:hover svg,.bottom-btn.active svg{filter:drop-shadow(0 0 6px currentColor)}
.gallery-carousel{position:relative;min-width:0}
.gallery-viewport{overflow-x:auto;scroll-snap-type:x mandatory;scroll-behavior:auto;scrollbar-width:none;-ms-overflow-style:none}
.gallery-viewport::-webkit-scrollbar{display:none}
.gallery-grid{display:flex;gap:8px}
.gallery-item{position:relative;display:block;flex:0 0 calc((100% - 24px)/4);aspect-ratio:1;overflow:hidden;padding:0;border:1px solid var(--border);border-radius:8px;background:var(--card);cursor:zoom-in;scroll-snap-align:start}
.gallery-item img{display:block;width:100%;height:100%;object-fit:cover}
.gallery-scroll-button,.lightbox-nav{position:absolute;z-index:2;top:50%;display:grid;width:38px;height:38px;place-items:center;transform:translateY(-50%);border:1px solid rgba(255,255,255,.35);border-radius:50%;background:rgba(8,9,13,.82);color:#fff;font-size:27px;line-height:1;cursor:pointer;box-shadow:0 3px 12px rgba(0,0,0,.38)}
.gallery-scroll-button:hover,.lightbox-nav:hover{border-color:var(--accent);background:var(--accent)}
.gallery-scroll-button.is-prev{left:8px}
.gallery-scroll-button.is-next{right:8px}
.gallery-scroll-button:disabled{opacity:.35;cursor:default}
.lightbox{position:fixed;inset:0;background:rgba(0,0,0,.94);z-index:1000;display:none;align-items:center;justify-content:center;padding:20px}
.lightbox.active{display:flex}
.lightbox img{max-width:min(88vw,1200px);max-height:85vh;border-radius:12px;object-fit:contain}
.lightbox-close{position:absolute;top:16px;right:20px;z-index:3;display:grid;width:42px;height:42px;place-items:center;border:1px solid rgba(255,255,255,.3);border-radius:50%;background:rgba(0,0,0,.45);font-size:30px;color:#fff;cursor:pointer}
.lightbox-nav.is-prev{left:20px}
.lightbox-nav.is-next{right:20px}
.announce-bar{background:var(--accent);color:#fff;text-align:center;padding:6px;font-size:12px;font-weight:600;overflow:hidden;white-space:nowrap}
.announce-bar span{display:inline-block;animation:scrollL 18s linear infinite;padding-left:100%}
@keyframes scrollL{to{transform:translateX(-100%)}}
.banner-box{margin:16px 0 20px}
.hero-panel{position:relative;width:100%;min-height:310px;overflow:hidden;border:1px solid rgba(255,45,120,.55);border-radius:16px;background:radial-gradient(ellipse at 15% 10%,rgba(255,45,120,.12),transparent 45%),linear-gradient(115deg,#08090d 0%,#101017 54%,#211222 100%);color:var(--text);box-shadow:0 12px 32px rgba(0,0,0,.28)}
.hero-slide{display:none;grid-template-columns:minmax(0,.92fr) minmax(0,1.08fr);align-items:stretch;min-height:310px;animation:heroSlideIn .35s ease both}
.hero-slide.is-active{display:grid}
.hero-copy{align-self:center;min-width:0;padding:32px 30px 64px}
.hero-eyebrow{display:block;margin-bottom:12px;color:var(--accent);font-size:12px;font-weight:800;letter-spacing:.04em}
.hero-code{display:block;margin-bottom:8px;color:var(--accent);font-size:clamp(19px,2.6vw,28px);font-weight:900;letter-spacing:.04em}
.hero-copy h2{margin:0;color:var(--text);font-size:clamp(19px,2.4vw,27px);font-weight:900;line-height:1.4}
.hero-copy p{display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:2;overflow:hidden;margin:10px 0 16px;color:var(--muted);font-size:14px;line-height:1.6}
.hero-read-link{display:inline-flex;align-items:center;gap:9px;padding:10px 17px;border-radius:8px;background:var(--accent);color:#fff;font-size:13px;font-weight:800}
.hero-read-link:hover{filter:brightness(1.1)}
.hero-cover{position:relative;display:block;width:100%;height:100%;min-height:310px;overflow:hidden;background:linear-gradient(135deg,var(--accent-soft),rgba(168,85,247,.18))}
.hero-cover:before{position:absolute;inset:0;z-index:1;background:linear-gradient(90deg,#101017 0%,rgba(16,16,23,.45) 14%,transparent 42%);content:"";pointer-events:none}
.hero-cover img{position:absolute;inset:0;display:block;width:100%;height:100%;min-height:0;object-fit:cover}
.hero-cover-placeholder{display:grid;min-height:310px;place-items:center;color:var(--accent);font-size:40px}
.hero-arrows{position:absolute;z-index:3;bottom:18px;left:30px;display:flex;gap:8px}
.hero-featured[data-count="1"] .hero-arrows{display:none}
.hero-arrows button{width:38px;height:34px;border:1px solid rgba(255,255,255,.2);border-radius:8px;background:rgba(8,9,13,.75);color:#fff;font-size:20px;line-height:1;cursor:pointer;transition:background .2s,border-color .2s}
.hero-arrows button:hover{border-color:var(--accent);background:var(--accent)}
.hero-arrows button:focus-visible,.banner-dots button:focus-visible{outline:2px solid #fff;outline-offset:3px}
.banner-dots{display:flex;gap:6px;justify-content:center;margin-top:8px}
.banner-dots button{width:8px;height:8px;padding:0;border:0;border-radius:50%;background:#555;cursor:pointer;transition:width .2s,background .2s}
.banner-dots button.active{width:20px;border-radius:99px;background:var(--accent)}
@keyframes heroSlideIn{from{opacity:.4;transform:translateY(4px)}to{opacity:1;transform:translateY(0)}}
@media(max-width:700px){.hero-panel,.hero-slide{min-height:250px}.hero-copy{padding:22px 18px 62px}.hero-cover,.hero-cover-placeholder{min-height:250px}.hero-eyebrow{font-size:10px}.hero-copy h2{font-size:clamp(14px,3.5vw,19px)}.hero-copy p{font-size:11px}.hero-read-link{padding:8px 11px;font-size:11px}.hero-arrows{left:18px;bottom:14px}}
@media(max-width:820px){.site-primary-nav{display:none}}
@media(max-width:420px){.hero-panel,.hero-slide{min-height:215px}.hero-slide{grid-template-columns:minmax(0,1fr) 38%}.hero-copy{padding:18px 10px 58px 14px}.hero-eyebrow{font-size:9px;margin-bottom:8px}.hero-code{font-size:15px}.hero-copy h2{font-size:13px}.hero-copy p{font-size:10px;margin:7px 0 10px}.hero-cover,.hero-cover-placeholder{min-height:215px}.hero-cover:before{background:linear-gradient(90deg,#101017 0%,rgba(16,16,23,.25) 22%,transparent 60%)}.hero-arrows{left:14px;bottom:12px}.hero-arrows button{width:33px;height:30px;font-size:17px}}
@media(max-width:560px){.post-detail-layout{grid-template-columns:minmax(110px,38%) minmax(0,1fr);gap:10px;align-items:start}.post-poster{min-height:150px}.post-detail-panel{min-width:0;padding:11px;column-gap:7px}.post-detail-panel h2{font-size:14px}.post-detail-row>strong,.post-detail-row>div{padding:8px 0}.post-detail-row>strong{white-space:nowrap}.post-detail-row>div{min-width:0;gap:4px}.post-detail-row>div .post-detail-link{width:auto;max-width:100%;justify-content:flex-start;white-space:normal;overflow-wrap:anywhere}.post-detail-separator{flex:0 0 auto}.post-meta-line{gap:6px;font-size:11px}.post-meta-date-group{gap:5px}.post-meta-line .badge-gold{padding:2px 6px;font-size:10px}.post-share-actions{gap:8px}.post-share-actions>strong{width:100%}.share-social-links{gap:5px}.share-social{width:34px;height:34px}.share-social svg,.share-copy-link svg{width:16px;height:16px}}
@media(prefers-reduced-motion:reduce){.hero-slide{animation:none}}
.comment-item{background:var(--card2);border:1px solid var(--border);border-radius:10px;padding:12px;margin-bottom:10px}
.comment-item .c-name{font-weight:700;font-size:13px;color:var(--accent)}
.comment-item .c-date{font-size:10px;color:var(--muted);margin-left:8px}
.comment-item .c-text{font-size:13px;margin-top:6px;line-height:1.6}
.toast{position:fixed;bottom:24px;left:50%;transform:translateX(-50%);background:var(--accent);color:#fff;padding:10px 22px;border-radius:999px;font-size:13px;font-weight:600;box-shadow:0 0 20px rgba(255,45,120,.5);z-index:999;animation:toastIn .3s ease}
@keyframes toastIn{from{opacity:0;transform:translate(-50%,20px)}to{opacity:1;transform:translate(-50%,0)}}
.mt{margin-top:16px}
.archive-nav{display:flex;gap:8px;flex-wrap:wrap;margin:14px 0 4px}
.archive-nav a{padding:6px 12px;border:1px solid var(--border);border-radius:999px;background:var(--card);color:var(--muted);font-size:11px;font-weight:600;transition:color .2s,border-color .2s}
.archive-nav a:hover,.archive-nav a[aria-current="page"]{border-color:var(--accent);color:var(--accent)}
.archive-breadcrumb{display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin:16px 0 12px;color:var(--muted);font-size:11px}
.archive-breadcrumb a{color:var(--accent)}
.archive-hero{position:relative;overflow:hidden;margin:12px 0 22px;padding:22px;border:1px solid var(--border);border-radius:18px;background:radial-gradient(ellipse at 90% 10%,var(--accent-soft),transparent 48%),linear-gradient(135deg,var(--card),var(--card2))}
.archive-hero h1{margin:3px 0 5px;font-size:clamp(23px,4vw,32px);font-weight:900;line-height:1.2}
.archive-hero p{max-width:640px;color:var(--muted);font-size:12px}
.archive-eyebrow{color:var(--accent);font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase}
.archive-count{display:inline-flex;gap:6px;align-items:center;margin-top:12px;padding:5px 10px;border:1px solid var(--border);border-radius:999px;background:rgba(0,0,0,.18);color:var(--muted);font-size:11px}
.archive-count strong{color:var(--text);font-size:13px}
.archive-count i{font-style:normal;color:var(--accent)}
.archive-heading{display:flex;align-items:baseline;justify-content:space-between;gap:10px;margin:0 0 12px}
.archive-heading h2{font-size:17px;font-weight:800}
.archive-heading span{color:var(--muted);font-size:11px}
.archive-post-grid{grid-template-columns:repeat(auto-fill,minmax(min(100%,210px),1fr));margin-bottom:20px}
.category-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(min(100%,190px),1fr));gap:10px;margin:0 0 24px}
.category-card{position:relative;display:flex;min-height:108px;flex-direction:column;justify-content:center;gap:4px;overflow:hidden;padding:16px;border:1px solid var(--border);border-radius:14px;background:linear-gradient(135deg,var(--card),var(--card2));transition:transform .2s,border-color .2s,box-shadow .2s}
.category-card:hover{transform:translateY(-2px);border-color:var(--accent);box-shadow:0 8px 22px rgba(0,0,0,.22)}
.category-card-icon{color:var(--accent);font-size:15px}
.category-card-name{font-size:14px;font-weight:800;line-height:1.35}
.category-card-count{color:var(--muted);font-size:11px}
.category-card-arrow{position:absolute;right:14px;bottom:14px;color:var(--accent);font-size:16px}
.archive-pagination{display:flex;justify-content:center;align-items:center;gap:6px;flex-wrap:wrap;margin:22px 0 30px}
.archive-pagination a,.archive-pagination span{display:grid;min-width:34px;height:34px;place-items:center;padding:0 10px;border:1px solid var(--border);border-radius:9px;background:var(--card);color:var(--muted);font-size:11px}
.archive-pagination a:hover,.archive-pagination span[aria-current="page"]{border-color:var(--accent);background:var(--accent-soft);color:var(--accent)}
.archive-empty{margin:16px 0 24px;text-align:center;color:var(--muted)}
.archive-empty h1{margin-bottom:8px;color:var(--text);font-size:22px}
.archive-empty .btn{margin-top:14px}
@media(max-width:520px){.archive-hero{padding:18px}.archive-post-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:9px}.archive-post-grid .post-card .post-thumb{aspect-ratio:1.15}.archive-post-grid .post-card>div:nth-child(2){padding:9px!important}.archive-post-grid .post-card h3{font-size:12px!important}.category-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}.category-card{min-height:98px;padding:12px}.archive-pagination{gap:4px}.archive-pagination a,.archive-pagination span{min-width:30px;height:32px;padding:0 8px}}
@media(max-width:900px){.gallery-item{flex-basis:calc((100% - 16px)/3)}}
@media(max-width:560px){.gallery-grid{gap:6px}.gallery-item{flex-basis:calc((100% - 6px)/2)}.gallery-scroll-button{width:34px;height:34px;font-size:24px}.lightbox{padding:12px}.lightbox-nav.is-prev{left:8px}.lightbox-nav.is-next{right:8px}}
html,body{scrollbar-width:none;-ms-overflow-style:none}
*::-webkit-scrollbar{width:0;height:0}
</style>
</head>
<body>
<?php if (themeOn('showAnnouncement') && setting('announcement')): ?>
<div class="announce-bar"><span><?php echo esc(setting('announcement')); ?></span></div>
<?php endif; ?>
<header style="background:var(--bg);border-bottom:1px solid var(--border);position:sticky;top:0;z-index:50;">
<div style="max-width:1100px;margin:0 auto;padding:0 16px;display:flex;align-items:center;justify-content:space-between;height:60px;gap:10px;flex-wrap:wrap;">
<a href="index.php" style="font-size:22px;font-weight:900;white-space:nowrap;"><?php $logo=explode(' ',setting('logoText','PINK CMS')); echo esc($logo[0]).'<span style="color:var(--accent);">'.(isset($logo[1])?esc($logo[1]):'CMS').'</span>'; ?></a>
<nav class="site-primary-nav" aria-label="เมนูหลัก">
<a href="index.php?page=taxlist&amp;slug=actor"<?php echo ($page==='actor'||($page==='taxlist'&&$slug==='actor'))?' aria-current="page"':''; ?>><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-7 8-7s8 3 8 7"/></svg><span>นักแสดง</span></a>
<a href="index.php?page=taxlist&amp;slug=studio"<?php echo ($page==='studio'||($page==='taxlist'&&$slug==='studio'))?' aria-current="page"':''; ?>><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-5h6v5M9 9h.01M15 9h.01"/></svg><span>ค่าย</span></a>
<a href="index.php?page=all"<?php echo $page==='all'?' aria-current="page"':''; ?>><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M7 8h10M7 12h7M7 16h5"/></svg><span>รหัสเรื่อง</span></a>
<a href="index.php?page=categories"<?php echo ($page==='categories'||$page==='category')?' aria-current="page"':''; ?>><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16M4 12h16M4 19h16"/><circle cx="7" cy="5" r="1"/><circle cx="7" cy="12" r="1"/><circle cx="7" cy="19" r="1"/></svg><span>บทความ</span></a>
</nav>
<?php if (themeOn('showSearch')): ?>
<div style="display:flex;gap:0;background:var(--card);border:1px solid var(--border);border-radius:10px;overflow:hidden;">
<form method="get" action="index.php" style="display:flex;margin:0;"><input type="hidden" name="page" value="search"><input type="text" name="q" placeholder="ค้นหา..." style="border:none;background:transparent;padding:8px 12px;width:150px;font-size:13px;" value="<?php echo esc($q); ?>"><button type="submit" style="background:var(--accent);border:none;color:#fff;padding:0 14px;font-weight:600;">ค้นหา</button></form>
</div>
<?php endif; ?>
<a href="admin/" class="btn btn-ghost" style="padding:7px 14px;font-size:12px;white-space:nowrap;">หลังบ้าน</a>
</div>
</header>
<main style="max-width:1100px;margin:0 auto;padding:0 16px 20px;">
<nav class="archive-nav" aria-label="สำรวจเนื้อหา">
<a href="index.php?page=all"<?php echo $page==='all'?' aria-current="page"':''; ?>>เรื่องทั้งหมด</a>
<a href="index.php?page=categories"<?php echo $page==='categories'||$page==='category'?' aria-current="page"':''; ?>>หมวดหมู่</a>
</nav>
<?php echo $contentHtml; ?>
</main>
<footer style="background:var(--card);border-top:1px solid var(--border);padding:24px 16px;margin-top:16px;">
<div style="max-width:1100px;margin:0 auto;text-align:center;">
<div style="font-size:18px;font-weight:900;margin-bottom:10px;"><?php $logo=explode(' ',setting('logoText','PINK CMS')); echo esc($logo[0]).'<span style="color:var(--accent);">'.(isset($logo[1])?esc($logo[1]):'CMS').'</span>'; ?></div>
<div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap;margin-bottom:12px;font-size:12px;color:var(--muted);">
<?php foreach ($publishedPages as $pg): ?><a href="index.php?page=page&slug=<?php echo $pg['slug']; ?>"><?php echo esc($pg['title']); ?></a><?php endforeach; ?>
</div>
<p style="font-size:11px;color:var(--muted);"><?php echo esc(setting('footerText')); ?></p>
</div>
</footer>
<?php if (themeOn('showBottomNav')): ?>
<nav class="bottom-nav">
<?php
$navActions = ['index.php', 'index.php?page=taxlist&slug=studio', 'index.php?page=taxlist&slug=actor', 'index.php?page=taxlist&slug=tag'];
$navIcons = ['<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>','<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/></svg>','<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/></svg>','<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/></svg>'];
foreach ($navLabels as $i=>$label): ?>
<a class="bottom-btn" href="<?php echo $navActions[$i]; ?>"><?php echo $navIcons[$i]; ?><span><?php echo esc($label); ?></span></a>
<?php endforeach; ?>
</nav>
<?php endif; ?>
<div id="lightbox" class="lightbox" role="dialog" aria-modal="true" aria-label="ดูภาพแกลเลอรี่" onclick="if(event.target===this)closeLightbox()"><button class="lightbox-close" type="button" aria-label="ปิดภาพ" onclick="closeLightbox()">×</button><button class="lightbox-nav is-prev" type="button" data-lightbox-step="-1" aria-label="ภาพก่อนหน้า">‹</button><img id="lightboxImg" src="" alt=""><button class="lightbox-nav is-next" type="button" data-lightbox-step="1" aria-label="ภาพถัดไป">›</button></div>
<script>
var banners = <?php echo json_encode($activeBanners, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?>;
var bannerIdx = 0;
var bannerTimer = null;
function showBanner(i){
  if(!banners.length||i<0||i>=banners.length)return;
  bannerIdx=i;
  var b=banners[i],el=document.getElementById('bannerSlide');
  if(!el)return;
  el.querySelectorAll('[data-hero-slide]').forEach(function(slide,idx){
    var active=idx===i;
    slide.classList.toggle('is-active',active);
    slide.setAttribute('aria-hidden',active?'false':'true');
  });
  document.querySelectorAll('#bannerDots button').forEach(function(button,idx){button.classList.toggle('active',idx===i);button.setAttribute('aria-pressed',idx===i?'true':'false');});
}
function buildBannerSlides(){
  var slider=document.getElementById('bannerSlide');
  if(!slider||!banners.length)return;
  banners.forEach(function(b,i){
    var target;
    try{target=new URL(b.link||'',location.href);}catch(e){target=null;}
    var safeLink=target&&(target.protocol==='http:'||target.protocol==='https:')?target.href:'';
    var article=document.createElement('article');article.className='hero-slide';article.setAttribute('data-hero-slide','');article.setAttribute('aria-hidden','true');
    var copy=document.createElement('div');copy.className='hero-copy';
    var eyebrow=document.createElement('span');eyebrow.className='hero-eyebrow';eyebrow.textContent='แนะนำบทความรหัสหนังใหม่';copy.appendChild(eyebrow);
    var titleText=(b.title||'').trim(),codeMatch=titleText.match(/^\[?([A-Z0-9][A-Z0-9-]{2,})\]?\s*(?:[-:|]\s*)?/i),headingText=titleText;
    if(codeMatch){var code=document.createElement('strong');code.className='hero-code';code.textContent=codeMatch[1];copy.appendChild(code);headingText=titleText.slice(codeMatch[0].length).trim()||titleText;}
    var heading=document.createElement('h2');heading.textContent=headingText||'บทความแนะนำ';copy.appendChild(heading);
    if(b.text){var excerpt=document.createElement('p');excerpt.textContent=b.text;copy.appendChild(excerpt);}
    if(safeLink){var readLink=document.createElement('a');readLink.className='hero-read-link';readLink.href=safeLink;readLink.textContent='อ่านบทความ　→';copy.appendChild(readLink);}
    article.appendChild(copy);
    var cover=document.createElement(safeLink?'a':'div');cover.className='hero-cover';if(safeLink){cover.href=safeLink;cover.setAttribute('aria-label','อ่าน '+titleText);}
    if(b.image){
      try{
        var imageUrl=new URL(b.image,location.href);
        if(imageUrl.protocol==='http:'||imageUrl.protocol==='https:'){
          var image=document.createElement('img');image.src=imageUrl.href;image.alt=titleText;image.width=800;image.height=500;image.loading=i===0?'eager':'lazy';image.decoding='async';if(i===0)image.fetchPriority='high';cover.appendChild(image);
        }
      }catch(e){}
    }
    if(!cover.firstChild){var placeholder=document.createElement('span');placeholder.className='hero-cover-placeholder';placeholder.setAttribute('aria-hidden','true');placeholder.textContent='▶';cover.appendChild(placeholder);}
    article.appendChild(cover);slider.appendChild(article);
  });
  showBanner(0);
}
document.querySelectorAll('#bannerDots button[data-banner-index]').forEach(function(button){button.addEventListener('click',function(){showBanner(parseInt(button.getAttribute('data-banner-index'),10));startBannerTimer();});});
var bannerPrevious=document.querySelector('[data-hero-prev]');
var bannerNext=document.querySelector('[data-hero-next]');
function startBannerTimer(){
  if(bannerTimer)clearInterval(bannerTimer);
  bannerTimer=null;
  if(banners.length>1&&!window.matchMedia('(prefers-reduced-motion: reduce)').matches){
    bannerTimer=setInterval(function(){showBanner((bannerIdx+1)%banners.length);},3000);
  }
}
if(bannerPrevious)bannerPrevious.addEventListener('click',function(){showBanner((bannerIdx-1+banners.length)%banners.length);startBannerTimer();});
if(bannerNext)bannerNext.addEventListener('click',function(){showBanner((bannerIdx+1)%banners.length);startBannerTimer();});
var bannerPanel=document.getElementById('bannerSlide');
if(bannerPanel){
  bannerPanel.addEventListener('mouseenter',function(){if(bannerTimer){clearInterval(bannerTimer);bannerTimer=null;}});
  bannerPanel.addEventListener('mouseleave',startBannerTimer);
  var bannerPointerStart=null;
  bannerPanel.addEventListener('pointerdown',function(event){bannerPointerStart=event.clientX;});
  bannerPanel.addEventListener('pointerup',function(event){
    if(bannerPointerStart===null)return;
    var swipeDistance=event.clientX-bannerPointerStart;bannerPointerStart=null;
    if(Math.abs(swipeDistance)>55){showBanner((bannerIdx+(swipeDistance<0?1:-1)+banners.length)%banners.length);startBannerTimer();}
  });
  bannerPanel.addEventListener('pointercancel',function(){bannerPointerStart=null;});
}
document.addEventListener('visibilitychange',function(){if(document.hidden){if(bannerTimer){clearInterval(bannerTimer);bannerTimer=null;}}else startBannerTimer();});
if(banners.length){buildBannerSlides();startBannerTimer();}
var lightboxImages=[],lightboxIndex=0,lastGalleryFocus=null;
function renderLightboxImage(){
  if(!lightboxImages.length)return;
  var image=document.getElementById('lightboxImg');
  image.src=lightboxImages[lightboxIndex].src;
  image.alt=lightboxImages[lightboxIndex].alt;
  var navigation=document.querySelectorAll('[data-lightbox-step]');
  navigation.forEach(function(button){button.hidden=lightboxImages.length<2;});
}
function openLightbox(src,images,index){
  lightboxImages=images&&images.length?images:[{src:src,alt:''}];
  lightboxIndex=Math.max(0,Math.min(index||0,lightboxImages.length-1));
  lastGalleryFocus=document.activeElement;
  renderLightboxImage();
  var lightbox=document.getElementById('lightbox');lightbox.classList.add('active');document.body.style.overflow='hidden';
  lightbox.querySelector('.lightbox-close').focus();
}
function closeLightbox(){
  var lightbox=document.getElementById('lightbox');lightbox.classList.remove('active');document.body.style.overflow='';
  document.getElementById('lightboxImg').removeAttribute('src');lightboxImages=[];
  if(lastGalleryFocus&&typeof lastGalleryFocus.focus==='function')lastGalleryFocus.focus();
}
function stepLightbox(direction){
  if(lightboxImages.length<2)return;
  lightboxIndex=(lightboxIndex+direction+lightboxImages.length)%lightboxImages.length;renderLightboxImage();
}
document.querySelectorAll('[data-gallery-carousel]').forEach(function(carousel){
  var viewport=carousel.querySelector('.gallery-viewport'),items=Array.from(carousel.querySelectorAll('.gallery-item'));
  var images=items.map(function(item){var image=item.querySelector('img');return {src:item.getAttribute('data-gallery-src'),alt:image?image.alt:''};});
  var updateButtons=function(){
    var maxScroll=viewport.scrollWidth-viewport.clientWidth;
    carousel.querySelector('[data-gallery-scroll="-1"]').disabled=viewport.scrollLeft<=1;
    carousel.querySelector('[data-gallery-scroll="1"]').disabled=viewport.scrollLeft>=maxScroll-1;
  };
  var scrollGallery=function(direction){
    viewport.scrollTo({left:viewport.scrollLeft+direction*viewport.clientWidth*.85,behavior:'auto'});
  };
  carousel.querySelectorAll('[data-gallery-scroll]').forEach(function(button){
    button.addEventListener('click',function(){scrollGallery(Number(button.getAttribute('data-gallery-scroll')));});
  });
  viewport.addEventListener('scroll',updateButtons,{passive:true});
  window.addEventListener('resize',updateButtons);updateButtons();
  carousel.addEventListener('keydown',function(event){
    if(event.key==='ArrowLeft'||event.key==='ArrowRight'){
      event.preventDefault();scrollGallery(event.key==='ArrowRight'?1:-1);
    }
  });
  items.forEach(function(item,index){item.addEventListener('click',function(){openLightbox(images[index].src,images,index);});});
});
document.querySelectorAll('[data-lightbox-step]').forEach(function(button){
  button.addEventListener('click',function(event){event.stopPropagation();stepLightbox(Number(button.getAttribute('data-lightbox-step')));});
});
document.addEventListener('keydown',function(event){
  var lightbox=document.getElementById('lightbox');
  if(!lightbox.classList.contains('active'))return;
  if(event.key==='Escape'){event.preventDefault();closeLightbox();}
  else if(event.key==='ArrowLeft'){event.preventDefault();stepLightbox(-1);}
  else if(event.key==='ArrowRight'){event.preventDefault();stepLightbox(1);}
});
function showActionToast(message){
  var oldToast=document.getElementById('actionToast');
  if(oldToast)oldToast.remove();
  var toast=document.createElement('div');toast.id='actionToast';toast.className='toast';toast.setAttribute('role','status');toast.textContent=message;
  document.body.appendChild(toast);window.setTimeout(function(){toast.remove();},2600);
}
async function copyPostUrl(url){
  try{
    if(navigator.clipboard&&window.isSecureContext){await navigator.clipboard.writeText(url);}
    else{
      var field=document.createElement('textarea');field.value=url;field.setAttribute('readonly','');field.style.position='fixed';field.style.opacity='0';document.body.appendChild(field);field.select();
      var copied=document.execCommand('copy');field.remove();if(!copied)throw new Error('Clipboard copy was not available');
    }
    showActionToast('คัดลอกลิงก์แล้ว');
  }catch(error){window.prompt('คัดลอกลิงก์นี้',url);}
}
document.querySelectorAll('[data-copy-url]').forEach(function(button){button.addEventListener('click',function(){copyPostUrl(button.getAttribute('data-copy-url'));});});
document.querySelectorAll('[data-native-share]').forEach(function(button){button.addEventListener('click',async function(){
  var url=button.getAttribute('data-share-url'),title=button.getAttribute('data-share-title');
  if(!navigator.share){await copyPostUrl(url);return;}
  try{await navigator.share({title:title,url:url});}
  catch(error){if(error.name!=='AbortError')await copyPostUrl(url);}
});});
document.querySelectorAll('.poster-lightbox').forEach(function(button){button.addEventListener('click',function(){openLightbox(button.getAttribute('data-poster-src'),null,0);});});
</script>
</body>
</html>
