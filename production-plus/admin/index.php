<?php
require_once '../config.php';
require_once '../functions.php';
header('X-Robots-Tag: noindex, nofollow, noarchive', true);
header('Cache-Control: no-store, private', true);
header('X-Content-Type-Options: nosniff', true);
header('Referrer-Policy: same-origin', true);
$accent = setting('theme_accent', '#ff2d78');
$msg = isset($_SESSION['pinkcms_admin_notice']) ? $_SESSION['pinkcms_admin_notice'] : '';
unset($_SESSION['pinkcms_admin_notice']);

// ---- LOGIN / LOGOUT ----
if (isset($_GET['logout'])) { session_destroy(); header('Location: index.php'); exit; }
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['login_pass'])) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $msg = 'แบบฟอร์มหมดอายุ กรุณาลองใหม่';
    } else {
    $username = trim(isset($_POST['username']) ? $_POST['username'] : '');
    $stmt = mysqli_prepare($conn, "SELECT id, password FROM users WHERE username=? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $userId, $storedPassword);
    $foundUser = mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    $passwordValid = $foundUser && (password_verify($_POST['login_pass'], $storedPassword) || hash_equals($storedPassword, md5($_POST['login_pass'])));
    if ($passwordValid) {
        if (password_needs_rehash($storedPassword, PASSWORD_DEFAULT) || hash_equals($storedPassword, md5($_POST['login_pass']))) {
            $newHash = password_hash($_POST['login_pass'], PASSWORD_DEFAULT);
            $update = mysqli_prepare($conn, "UPDATE users SET password=? WHERE id=?");
            mysqli_stmt_bind_param($update, 'si', $newHash, $userId);
            mysqli_stmt_execute($update);
            mysqli_stmt_close($update);
        }
        session_regenerate_id(true);
        $_SESSION['pinkcms_admin'] = 1;
        $_SESSION['pinkcms_admin_username'] = $username;
        mysqli_query($conn, "UPDATE users SET lastLogin=NOW() WHERE id=".(int)$userId);
        logActivity('เข้าสู่ระบบ');
        header('Location: index.php?page=dashboard'); exit;
    } else $msg = 'รหัสผ่านไม่ถูกต้อง';
    }
}
if (!isAdmin()) {
    echo '<!DOCTYPE html><html lang="th"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>เข้าสู่ระบบหลังบ้าน - PINKCMS</title><link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet"><style>
*{box-sizing:border-box}html,body{margin:0;min-height:100%;scrollbar-width:none;-ms-overflow-style:none}*::-webkit-scrollbar{width:0;height:0}body{font-family:Kanit,sans-serif;background:#080810;color:#fff}.login-shell{min-height:100vh;display:grid;grid-template-columns:minmax(320px,.92fr) 1.08fr}.brand-panel{position:relative;overflow:hidden;display:flex;flex-direction:column;justify-content:space-between;padding:38px 7%;background:radial-gradient(circle at 80% 20%,rgba(255,255,255,.18),transparent 30%),linear-gradient(145deg,#ff2d78 0%,#a855f7 56%,#5726b8 100%)}.brand-panel:after{content:"";position:absolute;width:340px;height:340px;border:1px solid rgba(255,255,255,.15);border-radius:50%;right:-120px;bottom:-170px;box-shadow:0 0 0 38px rgba(255,255,255,.035),0 0 0 78px rgba(255,255,255,.025)}.brand-top,.brand-copy,.brand-foot{position:relative;z-index:1}.brand-top{display:flex;align-items:center;gap:13px}.brand-mark{width:46px;height:46px;border-radius:15px;background:rgba(255,255,255,.18);display:grid;place-items:center;font-size:14px;font-weight:900;backdrop-filter:blur(10px)}.brand-name{font-size:15px;font-weight:800}.brand-caption{font-size:11px;opacity:.8;margin-top:2px}.brand-copy{max-width:520px}.brand-copy h1{font-size:clamp(28px,3.3vw,44px);line-height:1.25;margin:0 0 14px;font-weight:900}.brand-copy p{font-size:14px;line-height:1.8;opacity:.86;margin:0;max-width:420px}.brand-foot{font-size:11px;opacity:.68}.login-panel{display:flex;align-items:center;justify-content:center;padding:36px}.login-content{width:min(100%,430px)}.login-heading{padding-bottom:20px;border-bottom:1px solid rgba(255,255,255,.1);margin-bottom:25px}.login-heading strong{font-size:clamp(27px,3vw,38px);font-weight:900;letter-spacing:-.03em;background:linear-gradient(90deg,#ff70a3,#be8cff);-webkit-background-clip:text;background-clip:text;color:transparent}.login-heading p{font-size:13px;color:#9a9aab;margin:5px 0 0}.login-title{font-size:21px;font-weight:800;margin:0 0 5px}.login-subtitle{font-size:12px;color:#9191a3;margin:0 0 22px}.login-field{margin-bottom:16px}.login-field label{display:block;font-size:12px;font-weight:600;color:#d1d1dc;margin-bottom:7px}.login-input-wrap{position:relative}.login-input{width:100%;height:48px;padding:0 14px;border:1px solid #292936;border-radius:11px;background:#0c0c15;color:#fff;font:14px Kanit,sans-serif;outline:none;transition:border .2s,box-shadow .2s}.login-input:focus{border-color:#ff4f91;box-shadow:0 0 0 3px rgba(255,45,120,.13)}.password-input{padding-right:48px}.password-toggle{position:absolute;right:8px;top:7px;width:34px;height:34px;border:0;border-radius:8px;color:#9a9aab;background:transparent}.password-toggle:hover{background:rgba(255,255,255,.07);color:#fff}.login-error{padding:10px 12px;border:1px solid rgba(239,68,68,.28);border-radius:10px;background:rgba(239,68,68,.09);color:#ff9a9a;font-size:12px;margin-bottom:15px}.login-submit{width:100%;height:48px;border:0;border-radius:11px;background:linear-gradient(100deg,#ff2d78,#a855f7);color:#fff;font:700 14px Kanit,sans-serif;box-shadow:0 8px 24px rgba(255,45,120,.2);transition:transform .2s,filter .2s}.login-submit:hover{transform:translateY(-1px);filter:brightness(1.08)}.login-bottom{display:flex;justify-content:space-between;gap:12px;align-items:center;margin-top:17px;font-size:11px;color:#898998}.login-bottom a{color:#c4a4ff;text-decoration:none}.login-bottom a:hover{color:#fff}.security-note{margin-top:23px;padding-top:16px;border-top:1px solid rgba(255,255,255,.08);color:#777786;font-size:10px;line-height:1.7}@media(max-width:760px){.login-shell{grid-template-columns:1fr}.brand-panel{min-height:190px;padding:22px 24px}.brand-copy{margin:28px 0 18px}.brand-copy h1{font-size:25px}.brand-copy p,.brand-foot{display:none}.login-panel{padding:28px 22px 42px}.login-content{max-width:460px}.login-heading{margin-bottom:22px}}</style></head><body><div class="login-shell"><section class="brand-panel"><div class="brand-top"><div class="brand-mark">PC</div><div><div class="brand-name">PINKCMS หลังบ้าน</div><div class="brand-caption">ระบบจัดการเว็บไซต์</div></div></div><div class="brand-copy"><h1>ทุกโพสต์และทุกหน้า<br>จัดการได้ในที่เดียว</h1><p>พื้นที่ดูแลเว็บไซต์สำหรับผู้ดูแลระบบ จัดการเนื้อหา สถานะการเผยแพร่ และข้อมูล SEO ได้อย่างเป็นระเบียบ</p></div><div class="brand-foot">สำหรับผู้ดูแลที่ได้รับอนุญาตเท่านั้น · ระบบบันทึกกิจกรรมการใช้งาน</div></section><main class="login-panel"><div class="login-content"><div class="login-heading"><strong>PINKCMS</strong><p>ระบบจัดการเว็บไซต์</p></div><h2 class="login-title">เข้าสู่ระบบหลังบ้าน</h2><p class="login-subtitle">กรอกชื่อผู้ใช้และรหัสผ่านเพื่อดำเนินการต่อ</p>'.($msg?'<div class="login-error" role="alert">'.esc($msg).'</div>':'').'<form method="post" autocomplete="on"><input type="hidden" name="csrf_token" value="'.esc($_SESSION['csrf_token']).'"><div class="login-field"><label for="loginUsername">ชื่อผู้ใช้</label><input class="login-input" id="loginUsername" name="username" type="text" autocomplete="username" required autofocus></div><div class="login-field"><label for="loginPassword">รหัสผ่าน</label><div class="login-input-wrap"><input class="login-input password-input" id="loginPassword" name="login_pass" type="password" autocomplete="current-password" required><button class="password-toggle" type="button" aria-label="แสดงรหัสผ่าน" onclick="var p=document.getElementById(\'loginPassword\');var show=p.type===\'password\';p.type=show?\'text\':\'password\';this.setAttribute(\'aria-label\',show?\'ซ่อนรหัสผ่าน\':\'แสดงรหัสผ่าน\');">◉</button></div></div><button class="login-submit" type="submit">เข้าสู่ระบบ</button></form><div class="login-bottom"><span>เข้าใช้งานเฉพาะผู้ดูแล</span><a href="../">กลับไปหน้าบ้าน</a></div><div class="security-note">อย่าแชร์ข้อมูลเข้าสู่ระบบ และควรเปลี่ยนรหัสผ่านเริ่มต้นก่อนเปิดให้เข้าถึงจากเครือข่ายภายนอก</div></div></main></div></body></html>';
    exit;
}
if ($_SERVER['REQUEST_METHOD']==='POST' && (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']))) {
    http_response_code(403);
    exit('คำขอไม่ถูกต้องหรือหมดอายุ กรุณาโหลดหน้าใหม่');
}

// ---- ACTION HANDLING (POST) ----
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    if ($action==='save_managed_file' || $action==='restore_managed_file') {
        $state=siteFileManagerState();
        $fileName=isset($_POST['managed_file'])&&is_string($_POST['managed_file'])?$_POST['managed_file']:'';
        if(!in_array($fileName,['robots.txt','ads.txt'],true)) {
            $msg='เลือกไฟล์ที่อนุญาตเท่านั้น';
        } elseif($action==='save_managed_file') {
            $content=isset($_POST['content'])&&is_string($_POST['content'])?$_POST['content']:null;
            $siteUrl=isset($_POST['site_url'])&&is_string($_POST['site_url'])?trim($_POST['site_url']):'';
            $effectiveSiteUrl=$siteUrl!==''?$siteUrl:siteCanonicalBase();
            $indexingEnabled=isset($_POST['indexing_enabled']);
            if($content===null||strlen($content)>20000||strpos($content,"\0")!==false||preg_match('/[\x01-\x08\x0B\x0C\x0E-\x1F]/',$content)) {
                $msg='เนื้อหาไฟล์ไม่ถูกต้องหรือเกิน 20 KB';
            } elseif($siteUrl!==''&&!validSiteCanonicalUrl($siteUrl)) {
                $msg='URL เว็บไซต์ต้องเป็น http(s) และห้ามมี query, fragment หรือข้อมูลล็อกอิน';
            } elseif($fileName==='robots.txt'&&$indexingEnabled&&stripos($effectiveSiteUrl,'https://')!==0&&!preg_match('~^http://(?:localhost|127\.0\.0\.1)(?::[0-9]+)?(?:/|$)~i',$effectiveSiteUrl)) {
                $msg='ก่อนเปิดให้จัดทำดัชนี โปรดกำหนด URL เว็บไซต์เป็น HTTPS';
            } else {
                $content=str_replace(["\r\n","\r"],"\n",$content);
                $oldContent=$state['files'][$fileName]['content'];
                if($oldContent!==''&&$oldContent!==$content) {
                    array_unshift($state['files'][$fileName]['backups'],['content'=>$oldContent,'saved_at'=>date('Y-m-d H:i:s')]);
                    $state['files'][$fileName]['backups']=array_slice($state['files'][$fileName]['backups'],0,3);
                }
                $state['files'][$fileName]['content']=$content;
                if($siteUrl!=='')$state['site_url']=rtrim($siteUrl,'/');
                if($fileName==='robots.txt')$state['indexing_enabled']=$indexingEnabled;
                if(saveSetting('site_file_manager_state',json_encode($state,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE))) {
                    logActivity('แก้ไขไฟล์ที่จัดการได้: '.$fileName);
                    $msg='บันทึก '.$fileName.' แล้ว';
                } else {
                    $msg='บันทึกไม่สำเร็จ กรุณาตรวจสอบการเชื่อมต่อฐานข้อมูลและ error log';
                }
            }
        } else {
            $backupIndex=isset($_POST['backup_index'])&&ctype_digit((string)$_POST['backup_index'])?(int)$_POST['backup_index']:-1;
            $backups=$state['files'][$fileName]['backups'];
            if(!isset($backups[$backupIndex])||!isset($backups[$backupIndex]['content'])||!is_string($backups[$backupIndex]['content'])) {
                $msg='ไม่พบข้อมูลสำรองที่เลือก';
            } else {
                $current=$state['files'][$fileName]['content'];
                if($current!=='')array_unshift($state['files'][$fileName]['backups'],['content'=>$current,'saved_at'=>date('Y-m-d H:i:s')]);
                $state['files'][$fileName]['content']=$backups[$backupIndex]['content'];
                $state['files'][$fileName]['backups']=array_slice($state['files'][$fileName]['backups'],0,3);
                if(saveSetting('site_file_manager_state',json_encode($state,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE))) {
                    logActivity('กู้คืนไฟล์ที่จัดการได้: '.$fileName);
                    $msg='กู้คืน '.$fileName.' จากข้อมูลสำรองแล้ว';
                } else {
                    $msg='กู้คืนไม่สำเร็จ กรุณาตรวจสอบการเชื่อมต่อฐานข้อมูลและ error log';
                }
            }
        }
    }
    if ($action==='save_post_keyword_notes') {
        $primaryNotes=trim(isset($_POST['primary_keyword_notes'])?$_POST['primary_keyword_notes']:'');
        $secondaryNotes=trim(isset($_POST['secondary_keyword_notes'])?$_POST['secondary_keyword_notes']:'');
        if (mb_strlen($primaryNotes,'UTF-8')>20000 || mb_strlen($secondaryNotes,'UTF-8')>30000) {
            $msg='ชุดคีย์ยาวเกินกำหนด กรุณาลดจำนวนข้อความแล้วบันทึกอีกครั้ง';
        } else {
            saveSetting('post_primary_keyword_notes',$primaryNotes);
            saveSetting('post_secondary_keyword_notes',$secondaryNotes);
            logActivity('แก้ไขชุดคีย์เวิร์ดโน้ต');
            $_SESSION['pinkcms_admin_notice']='บันทึกชุดคีย์เวิร์ดแล้ว';
            header('Location: index.php?page=posts#post-keyword-notes');
            exit;
        }
    }
    if ($action==='save_post') {
        $id = intval($_POST['id']);
        $title = trim($_POST['title']);
        $videoInput=normalizeVideoInput(isset($_POST['videoUrl'])?$_POST['videoUrl']:'');
        $coverUpload=storeImageUpload('coverUpload');
        $coverValue=$coverUpload['path']!==''?$coverUpload['path']:trim(isset($_POST['cover'])?$_POST['cover']:'');
        $posterUpload=storeImageUpload('posterUpload');
        $posterValue=$posterUpload['path']!==''?$posterUpload['path']:trim(isset($_POST['poster'])?$_POST['poster']:'');
        $galleryUploads=storeMultipleImageUploads('galleryUploads');
        $duration = trim(isset($_POST['videoDuration']) ? $_POST['videoDuration'] : '');
        if ($coverUpload['error']!=='') { $msg=$coverUpload['error']; }
        elseif ($posterUpload['error']!=='') { $msg=$posterUpload['error']; }
        elseif ($galleryUploads['error']!=='') { $msg=$galleryUploads['error']; }
        elseif (!validImageReference($coverValue)) { $msg='ลิงก์รูปภาพต้องเป็น URL http(s) หรือพาธรูปภายในเว็บไซต์'; }
        elseif (!validImageReference($posterValue)) { $msg='ลิงก์โปสเตอร์ต้องเป็น URL http(s) หรือพาธรูปภายในเว็บไซต์'; }
        elseif ($videoInput['error']!=='') { $msg=$videoInput['error']; }
        elseif (!$title) { $msg='กรุณากรอกชื่อเรื่อง'; }
        elseif ($duration!=='' && !preg_match('/^\d{1,5}:[0-5]\d:[0-5]\d$/', $duration)) { $msg='กรุณาระบุความยาววิดีโอเป็นชั่วโมง:นาที:วินาที เช่น 02:15:22'; }
        else {
            $slug = trim($_POST['slug']) ?: slugify($title);
            $slugE = mysqli_real_escape_string($conn, $slug);
            $titleE = mysqli_real_escape_string($conn, $title);
            $contentE = mysqli_real_escape_string($conn, $_POST['content']);
            $coverE = mysqli_real_escape_string($conn, $coverValue);
            $posterE = mysqli_real_escape_string($conn, $posterValue);
            $videoSource=$videoInput['url'];
            $videoE = mysqli_real_escape_string($conn, $videoSource);
            $remoteResult=$videoSource!==''?videoApiRemote($videoSource):['url'=>'','error'=>''];
            $embedE=mysqli_real_escape_string($conn,$remoteResult['url']);
            $durationParts=videoDurationParts($duration);
            $duration=$durationParts===null?'':sprintf('%02d:%02d:%02d',$durationParts[0],$durationParts[1],$durationParts[2]);
            $durE = mysqli_real_escape_string($conn, $duration);
            $galleryInput=isset($_POST['gallery'])?(string)$_POST['gallery']:'';
            $gallery=array_filter(array_map('trim',preg_split('/\r\n|\r|\n/',$galleryInput)));
            $gallery=array_values(array_unique(array_merge($gallery,$galleryUploads['paths'])));
            $galleryE = mysqli_real_escape_string($conn, json_encode($gallery));
            $taxonomyNames=function($field) {
                if (!isset($_POST[$field])) return [];
                $names=json_decode((string)$_POST[$field],true);
                if (!is_array($names)) return [];
                $result=[];$seen=[];
                foreach($names as $name) {
                    $name=trim((string)$name);
                    if ($name==='' || mb_strlen($name,'UTF-8')>100) continue;
                    $key=mb_strtolower($name,'UTF-8');
                    if (isset($seen[$key])) continue;
                    $seen[$key]=true;$result[]=$name;
                }
                return $result;
            };
            $findTaxonomyId=function($table,$name) use ($conn) {
                $stmt=mysqli_prepare($conn,"SELECT id FROM `$table` WHERE name=? LIMIT 1");
                if (!$stmt) return 0;
                mysqli_stmt_bind_param($stmt,'s',$name);
                if (!mysqli_stmt_execute($stmt)) { mysqli_stmt_close($stmt);return 0; }
                mysqli_stmt_bind_result($stmt,$foundId);
                $found=mysqli_stmt_fetch($stmt);
                mysqli_stmt_close($stmt);
                return $found?(int)$foundId:0;
            };
            $createTaxonomyId=function($table,$name) use ($conn) {
                $slugBase=slugify($name);$slug=$slugBase;$suffix=2;
                while (true) {
                    $check=mysqli_prepare($conn,"SELECT id FROM `$table` WHERE slug=? LIMIT 1");
                    if (!$check) return 0;
                    mysqli_stmt_bind_param($check,'s',$slug);
                    if (!mysqli_stmt_execute($check)) { mysqli_stmt_close($check);return 0; }
                    mysqli_stmt_bind_result($check,$existingId);
                    $exists=mysqli_stmt_fetch($check);
                    mysqli_stmt_close($check);
                    if (!$exists) break;
                    $slug=$slugBase.'-'.$suffix++;
                }
                $stmt=mysqli_prepare($conn,"INSERT INTO `$table` (name,slug) VALUES (?,?)");
                if (!$stmt) return 0;
                mysqli_stmt_bind_param($stmt,'ss',$name,$slug);
                $ok=mysqli_stmt_execute($stmt);$id=$ok?(int)mysqli_insert_id($conn):0;
                mysqli_stmt_close($stmt);
                return $id;
            };
            $studioName=trim(isset($_POST['studioName'])?$_POST['studioName']:'');
            $studioId=$studioName!==''?$findTaxonomyId('studios',$studioName):0;
            if ($studioName!=='' && !$studioId) $studioId=$createTaxonomyId('studios',$studioName);
            if ($studioName!=='' && !$studioId) $msg='บันทึกชื่อค่ายไม่สำเร็จ กรุณาตรวจสอบฐานข้อมูลแล้วลองใหม่';
            $actorIds=array_map('intval',isset($_POST['actorIds'])&&is_array($_POST['actorIds'])?$_POST['actorIds']:[]);
            foreach($taxonomyNames('newActorNames') as $actorName) {
                $actorId=$findTaxonomyId('actors',$actorName);
                if (!$actorId) $actorId=$createTaxonomyId('actors',$actorName);
                if (!$actorId) {$msg='บันทึกชื่อนักแสดง "'.esc($actorName).'" ไม่สำเร็จ';break;}
                $actorIds[]=$actorId;
            }
            $tagIds=array_map('intval',isset($_POST['tagIds'])&&is_array($_POST['tagIds'])?$_POST['tagIds']:[]);
            if ($msg==='') foreach($taxonomyNames('newTagNames') as $tagName) {
                $tagId=$findTaxonomyId('tags',$tagName);
                if (!$tagId) $tagId=$createTaxonomyId('tags',$tagName);
                if (!$tagId) {$msg='บันทึกแท็ก "'.esc($tagName).'" ไม่สำเร็จ';break;}
                $tagIds[]=$tagId;
            }
            $actorIds=json_encode(array_values(array_unique($actorIds)));
            $tagIds=json_encode(array_values(array_unique($tagIds)));
            $mtE = mysqli_real_escape_string($conn, trim($_POST['metaTitle']));
            $mdE = mysqli_real_escape_string($conn, trim($_POST['metaDesc']));
            $kwE = mysqli_real_escape_string($conn, trim($_POST['keywords']));
            $status = isset($_POST['post_intent']) && $_POST['post_intent']==='publish'?'publish':(isset($_POST['post_intent']) && $_POST['post_intent']==='draft'?'draft':($_POST['status']==='publish'?'publish':'draft'));
            $dateValue = isset($_POST['date']) ? $_POST['date'] : '';
            $dateObject = DateTime::createFromFormat('!Y-m-d', $dateValue);
            $thaiNow = new DateTimeImmutable('now', new DateTimeZone('Asia/Bangkok'));
            $date = $status==='publish' && isset($_POST['post_intent']) && $_POST['post_intent']==='publish' ? $thaiNow->format('Y-m-d') : ($dateObject && $dateObject->format('Y-m-d')===$dateValue ? $dateValue : $thaiNow->format('Y-m-d'));
            $dateE = mysqli_real_escape_string($conn, $date);
            $authorName=trim(isset($_SESSION['pinkcms_admin_username'])?$_SESSION['pinkcms_admin_username']:'');
            if($authorName==='')$authorName='admin';
            $authorE=mysqli_real_escape_string($conn,mb_substr($authorName,0,100,'UTF-8'));
            $createdAtE=mysqli_real_escape_string($conn,$thaiNow->format('Y-m-d H:i:s'));
            $codeE = mysqli_real_escape_string($conn, trim(isset($_POST['code'])?$_POST['code']:''));
            $actorENE = mysqli_real_escape_string($conn, trim(isset($_POST['actorEN'])?$_POST['actorEN']:''));
            $categoryE = mysqli_real_escape_string($conn, trim(isset($_POST['category'])?$_POST['category']:''));
            $focusE = mysqli_real_escape_string($conn, trim(isset($_POST['focusKeyphrase'])?$_POST['focusKeyphrase']:''));
            if ($msg!=='') {
                error_log('PinkCMS taxonomy save failed: '.$msg);
            } elseif ($id) {
                mysqli_query($conn, "UPDATE posts SET code='$codeE',title='$titleE',slug='$slugE',content='$contentE',cover='$coverE',posterUrl='$posterE',videoUrl='$videoE',embedUrl='$embedE',videoDuration='$durE',gallery='$galleryE',studioId=$studioId,actorIds='$actorIds',actorEN='$actorENE',tagIds='$tagIds',category='$categoryE',focusKeyphrase='$focusE',metaTitle='$mtE',metaDesc='$mdE',keywords='$kwE',status='$status',date='$dateE' WHERE id=$id");
                logActivity('แก้ไขโพสต์: '.$title);
            } else {
                mysqli_query($conn, "INSERT INTO posts (code,title,slug,content,cover,posterUrl,videoUrl,embedUrl,videoDuration,gallery,studioId,actorIds,actorEN,tagIds,category,focusKeyphrase,metaTitle,metaDesc,keywords,status,date,author,createdAt,views) VALUES ('$codeE','$titleE','$slugE','$contentE','$coverE','$posterE','$videoE','$embedE','$durE','$galleryE',$studioId,'$actorIds','$actorENE','$tagIds','$categoryE','$focusE','$mtE','$mdE','$kwE','$status','$dateE','$authorE','$createdAtE',0)");
                logActivity('สร้างโพสต์: '.$title);
            }
            if ($remoteResult['error']!=='') {
                error_log('PinkCMS Remote API warning for post '.$id.': '.$remoteResult['error']);
                $_SESSION['pinkcms_admin_notice']=$remoteResult['error'];
            }
            header('Location: index.php?page=posts'); exit;
        }
    }
    if ($action==='delete_post') { mysqli_query($conn, "DELETE FROM posts WHERE id=".intval($_POST['id'])); logActivity('ลบโพสต์'); header('Location: index.php?page=posts'); exit; }
    if ($action==='add_tax') {
        $type = $_POST['tax_type']; $table = $type==='studio'?'studios':($type==='actor'?'actors':'tags');
        $imageField=$type==='studio'?'logoUpload':($type==='actor'?'photoUpload':'');
        $imageUpload=$imageField!==''?storeImageUpload($imageField):['path'=>'','error'=>''];
        $imageValue=$imageUpload['path']!==''?$imageUpload['path']:trim(isset($_POST['logo'])?$_POST['logo']:(isset($_POST['photo'])?$_POST['photo']:''));
        $imageError=$imageUpload['error']!==''?$imageUpload['error']:(!validImageReference($imageValue)?'ลิงก์รูปภาพต้องเป็น URL http(s) หรือพาธรูปภายในเว็บไซต์':'');
        $name = trim($_POST['name']); if ($imageError!=='') { $msg=$imageError; }
        elseif ($name) {
            $nameE = mysqli_real_escape_string($conn, $name);
            $slugE = mysqli_real_escape_string($conn, slugify($name));
            if ($type==='studio') { $logoE=mysqli_real_escape_string($conn,$imageValue); $descE=mysqli_real_escape_string($conn,trim($_POST['desc'])); mysqli_query($conn, "INSERT INTO studios (name,slug,logo,desc) VALUES ('$nameE','$slugE','$logoE','$descE')"); }
            elseif ($type==='actor') { $photoE=mysqli_real_escape_string($conn,$imageValue); $bioE=mysqli_real_escape_string($conn,trim($_POST['bio'])); mysqli_query($conn, "INSERT INTO actors (name,slug,photo,bio) VALUES ('$nameE','$slugE','$photoE','$bioE')"); }
            else mysqli_query($conn, "INSERT INTO tags (name,slug) VALUES ('$nameE','$slugE')");
            logActivity('เพิ่ม'.($type==='studio'?'ค่าย':($type==='actor'?'นักแสดง':'แท็ก')).': '.$name);
        }
        if ($msg==='') { header('Location: index.php?page='.($type==='studio'?'studios':($type==='actor'?'actors':'tags'))); exit; }
    }
    if ($action==='delete_tax') { $type=$_POST['tax_type']; $table=$type==='studio'?'studios':($type==='actor'?'actors':'tags'); mysqli_query($conn, "DELETE FROM $table WHERE id=".intval($_POST['id'])); logActivity('ลบ'.($type==='studio'?'ค่าย':($type==='actor'?'นักแสดง':'แท็ก'))); header('Location: index.php?page='.($type==='studio'?'studios':($type==='actor'?'actors':'tags'))); exit; }
    if ($action==='save_page') {
        $id=intval($_POST['id']); $title=trim($_POST['title']); $slug=trim($_POST['slug'])?:slugify($title);
        $titleE=mysqli_real_escape_string($conn,$title); $slugE=mysqli_real_escape_string($conn,$slug); $contentE=mysqli_real_escape_string($conn,$_POST['content']);
        $status=$_POST['status']==='publish'?'publish':'draft';
        if ($id) mysqli_query($conn,"UPDATE pages SET title='$titleE',slug='$slugE',content='$contentE',status='$status' WHERE id=$id");
        else mysqli_query($conn,"INSERT INTO pages (title,slug,content,status,date) VALUES ('$titleE','$slugE','$contentE','$status',CURDATE())");
        logActivity('บันทึกหน้าเว็บ: '.$title); header('Location: index.php?page=pages'); exit;
    }
    if ($action==='delete_page') { mysqli_query($conn,"DELETE FROM pages WHERE id=".intval($_POST['id'])); header('Location: index.php?page=pages'); exit; }
    if ($action==='save_banner') {
        $upload=storeImageUpload('bannerImageUpload');
        $imageValue=$upload['path']!==''?$upload['path']:trim(isset($_POST['image'])?$_POST['image']:'');
        if ($upload['error']!=='') $msg=$upload['error'];
        elseif (!validImageReference($imageValue)) $msg='ลิงก์รูปภาพต้องเป็น URL http(s) หรือพาธรูปภายในเว็บไซต์';
        else {
            $id=intval($_POST['id']); $titleE=mysqli_real_escape_string($conn,trim($_POST['title'])); $linkE=mysqli_real_escape_string($conn,trim($_POST['link'])); $imgE=mysqli_real_escape_string($conn,$imageValue); $textE=mysqli_real_escape_string($conn,trim($_POST['text'])); $active=isset($_POST['active'])?1:0;
            if ($id) mysqli_query($conn,"UPDATE banners SET title='$titleE',link='$linkE',image='$imgE',text='$textE',active=$active WHERE id=$id");
            else mysqli_query($conn,"INSERT INTO banners (title,link,image,text,active) VALUES ('$titleE','$linkE','$imgE','$textE',$active)");
            logActivity('บันทึกแบนเนอร์'); header('Location: index.php?page=banners'); exit;
        }
    }
    if ($action==='upload_media') {
        $upload=storeImageUpload('mediaUpload');
        if ($upload['error']!=='') $msg=$upload['error'];
        else { logActivity('อัปโหลดรูปภาพ'); header('Location: index.php?page=media'); exit; }
    }
    if ($action==='delete_banner') { mysqli_query($conn,"DELETE FROM banners WHERE id=".intval($_POST['id'])); header('Location: index.php?page=banners'); exit; }
    if ($action==='toggle_cmt') { $id=intval($_POST['id']); $r=mysqli_fetch_assoc(mysqli_query($conn,"SELECT approved FROM comments WHERE id=$id")); $new=$r['approved']?0:1; mysqli_query($conn,"UPDATE comments SET approved=$new WHERE id=$id"); header('Location: index.php?page=comments'); exit; }
    if ($action==='delete_cmt') { mysqli_query($conn,"DELETE FROM comments WHERE id=".intval($_POST['id'])); header('Location: index.php?page=comments'); exit; }
    if ($action==='save_site_content') {
        $menuConfigs=[];$menuError='';
        $featuredLimitValue=isset($_POST['homeFeaturedLimit'])&&is_string($_POST['homeFeaturedLimit'])?$_POST['homeFeaturedLimit']:'12';
        $bannerIntervalValue=isset($_POST['bannerInterval'])&&is_string($_POST['bannerInterval'])?$_POST['bannerInterval']:'3';
        $featuredIntervalValue=isset($_POST['featuredInterval'])&&is_string($_POST['featuredInterval'])?$_POST['featuredInterval']:'6';
        $featuredLimit=ctype_digit($featuredLimitValue)?(int)$featuredLimitValue:0;
        $bannerInterval=ctype_digit($bannerIntervalValue)?(int)$bannerIntervalValue:0;
        $featuredInterval=ctype_digit($featuredIntervalValue)?(int)$featuredIntervalValue:0;
        if(!in_array($featuredLimit,[4,6,8,10,12,16,20,24],true)||$bannerInterval<3||$bannerInterval>30||$featuredInterval<3||$featuredInterval>30) {
            $menuError='ค่าจำนวนการ์ดหรือความเร็วสไลด์อยู่นอกช่วงที่กำหนด';
        }
        foreach(['header'=>6,'explore'=>6,'bottom'=>4] as $menuName=>$slotCount) {
            $labels=isset($_POST['menu_'.$menuName.'_label'])&&is_array($_POST['menu_'.$menuName.'_label'])?$_POST['menu_'.$menuName.'_label']:[];
            $destinations=isset($_POST['menu_'.$menuName.'_destination'])&&is_array($_POST['menu_'.$menuName.'_destination'])?$_POST['menu_'.$menuName.'_destination']:[];
            $customUrls=isset($_POST['menu_'.$menuName.'_custom'])&&is_array($_POST['menu_'.$menuName.'_custom'])?$_POST['menu_'.$menuName.'_custom']:[];
            $menuConfigs[$menuName]=[];
            for($slot=0;$slot<$slotCount;$slot++) {
                $labelValue=isset($labels[$slot])?$labels[$slot]:'';
                $destinationValue=isset($destinations[$slot])?$destinations[$slot]:'';
                $customValue=isset($customUrls[$slot])?$customUrls[$slot]:'';
                if(!is_string($labelValue)||!is_string($destinationValue)||!is_string($customValue)) {
                    $menuError='ข้อมูลเมนูไม่ถูกต้อง กรุณาตรวจสอบรายการเมนู';
                    break 2;
                }
                $label=trim($labelValue);$destination=trim($destinationValue);$customUrl=trim($customValue);
                if($label===''&&$destination===''&&$customUrl==='')continue;
                if($label===''||$destination==='') {
                    $menuError='กรุณาระบุชื่อและปลายทางให้ครบในแต่ละเมนู';
                    break 2;
                }
                $type=$destination;$value='';
                if(preg_match('/^(category|tag):([a-z0-9ก-๙-]+)$/iu',$destination,$match)) {
                    $type=$match[1];$value=$match[2];
                } elseif($destination==='custom') {
                    if(!(validAdDestinationUrl($customUrl)||preg_match('/^(?:index\.php\?|\/(?!\/))[^\s<>"]*$/u',$customUrl))) {
                        $menuError='ลิงก์ Custom ต้องเป็น URL http(s) ที่ถูกต้อง หรือพาธภายในเว็บไซต์ เช่น index.php?page=all';
                        break 2;
                    }
                    $value=$customUrl;
                } elseif(!in_array($destination,['home','all','categories','studio_list','actor_list','tag_list'],true)) {
                    $menuError='พบประเภทเมนูที่ไม่รองรับ กรุณาเลือกปลายทางจากรายการ';
                    break 2;
                }
                $menuConfigs[$menuName][]=['label'=>mb_substr($label,0,60,'UTF-8'),'type'=>$type,'value'=>$value];
            }
        }
        if($menuError==='') {
            $siteFields=[
                'site_home_featured_title'=>['homeFeaturedTitle','แนะนำ'],
                'site_home_featured_all_label'=>['homeFeaturedAllLabel','ดูทั้งหมด'],
                'site_home_latest_title'=>['homeLatestTitle','โพสต์ล่าสุด'],
                'site_banner_accessibility'=>['bannerAccessibility','บทความรหัสหนังใหม่'],
                'site_ad_label'=>['siteAdLabel','โฆษณา'],
                'site_ad_close_label'=>['siteAdCloseLabel','ปิดโฆษณา'],
                'site_search_placeholder'=>['siteSearchPlaceholder','ค้นหา...'],
                'site_search_button'=>['siteSearchButton','ค้นหา'],
                'site_home_featured_limit'=>['homeFeaturedLimit',(string)$featuredLimit],
                'site_banner_interval_seconds'=>['bannerInterval',(string)$bannerInterval],
                'site_home_featured_interval_seconds'=>['featuredInterval',(string)$featuredInterval]
            ];
            foreach($siteFields as $settingKey=>$field) {
                $value=isset($_POST[$field[0]])&&is_string($_POST[$field[0]])?trim($_POST[$field[0]]):'';
                saveSetting($settingKey,mb_substr($value,0,120,'UTF-8'));
            }
            foreach($menuConfigs as $menuName=>$menuItems)saveSetting('site_menu_'.$menuName,json_encode($menuItems,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
            logActivity('แก้ไขเนื้อหาและเมนูหน้าเว็บ');$msg='บันทึกเนื้อหาและเมนูหน้าเว็บแล้ว';
        } else {
            $msg=$menuError;
        }
    }
    if ($action==='save_theme') {
        saveSetting('logoText', trim($_POST['logoText']));
        saveSetting('theme_accent', trim($_POST['accent']));
        foreach (['showViews','showStudio','showTags','showActors','showDuration','showBottomNav','showSearch','showRelated','showBanners','showAnnouncement'] as $k) saveSetting('theme_'.$k, isset($_POST[$k])?'1':'0');
        logActivity('แก้ไขสไตล์เว็บ'); $msg='บันทึกสไตล์แล้ว';
    }
    if ($action==='save_seo') {
        saveSetting('siteName', trim($_POST['siteName']));
        saveSetting('siteDesc', trim($_POST['siteDesc']));
        saveSetting('seo_primary_keyword', trim($_POST['primaryKeyword']));
        saveSetting('seo_secondary_keywords', trim($_POST['secondaryKeywords']));
        saveSetting('seo_keywords', trim($_POST['keywords']));
        saveSetting('seo_gaId', trim($_POST['gaId']));
        saveSetting('seo_robots', trim($_POST['robots']));
        logActivity('แก้ไขตั้งค่า SEO'); $msg='บันทึก SEO แล้ว';
    }
    if ($action==='save_system') {
        $videoAdSlots=[];$videoAdError='';
        foreach(['top'=>4,'inside'=>1,'bottom'=>3] as $position=>$slotCount) {
            $destinationKey='videoAd'.ucfirst($position).'Destination';
            $imageKey='videoAd'.ucfirst($position).'Image';
            $destinations=isset($_POST[$destinationKey])&&is_array($_POST[$destinationKey])?$_POST[$destinationKey]:[];
            $images=isset($_POST[$imageKey])&&is_array($_POST[$imageKey])?$_POST[$imageKey]:[];
            for($slot=0;$slot<$slotCount;$slot++) {
                $destinationValue=isset($destinations[$slot])?$destinations[$slot]:'';
                $imageValue=isset($images[$slot])?$images[$slot]:'';
                if(!is_string($destinationValue) || !is_string($imageValue)) {
                    $videoAdError='ข้อมูลโฆษณาไม่ถูกต้อง กรุณาตรวจ URL ปลายทางและ URL รูปภาพอีกครั้ง';
                    break 2;
                }
                $destination=trim($destinationValue);
                $image=trim($imageValue);
                if(substr($image,0,2)==='//')$image='https:'.$image;
                if($destination==='' && $image==='')continue;
                if(!validAdDestinationUrl($destination) || $image==='' || !validImageReference($image)) {
                    $videoAdError='กรุณากรอก URL ปลายทางแบบ http(s) และ URL รูปภาพที่ถูกต้องให้ครบในแต่ละป้ายโฆษณา';
                    break 2;
                }
                $videoAdSlots[$position][]=['destination'=>$destination,'image'=>$image];
            }
        }
        if($videoAdError==='') {
        saveSetting('siteName', trim($_POST['siteName']));
        saveSetting('siteDesc', trim($_POST['siteDesc']));
        saveSetting('footerText', trim($_POST['footerText']));
        saveSetting('announcement', trim($_POST['announcement']));
        if (trim($_POST['newPass'])) { $p=password_hash(trim($_POST['newPass']), PASSWORD_DEFAULT); $stmt=mysqli_prepare($conn,"UPDATE users SET password=? WHERE username='admin'"); mysqli_stmt_bind_param($stmt,'s',$p); mysqli_stmt_execute($stmt); mysqli_stmt_close($stmt); }
        saveSetting('video_ad_slots',json_encode($videoAdSlots,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
        logActivity('แก้ไขตั้งค่าระบบ'); $msg='บันทึกตั้งค่าแล้ว';
        } else {
            $msg=$videoAdError;
        }
    }
    if ($action==='export') {
        $data = ['settings'=>$SETTINGS];
        foreach (['studios','actors','tags','posts','banners','pages','comments','users','activity'] as $t) {
            $res=mysqli_query($conn,"SELECT * FROM $t"); $data[$t]=[];
            while($r=mysqli_fetch_assoc($res)) $data[$t][]=$r;
        }
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="pinkcms-backup.json"');
        echo json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE); exit;
    }
    if ($action==='reset') {
        foreach (['posts','studios','actors','tags','banners','pages','comments','activity'] as $t) mysqli_query($conn,"TRUNCATE TABLE $t");
        logActivity('รีเซ็ตข้อมูล'); header('Location: index.php?page=settings'); exit;
    }
}
if (isset($_GET['export'])) { $_POST['action']='export'; /* handled above on next load */ }

// ---- DATA LOADING ----
$studios=[];$res=mysqli_query($conn,"SELECT * FROM studios");while($r=mysqli_fetch_assoc($res))$studios[]=$r;
$actors=[];$res=mysqli_query($conn,"SELECT * FROM actors");while($r=mysqli_fetch_assoc($res))$actors[]=$r;
$tags=[];$res=mysqli_query($conn,"SELECT * FROM tags");while($r=mysqli_fetch_assoc($res))$tags[]=$r;
$posts=[];$res=mysqli_query($conn,"SELECT * FROM posts ORDER BY date DESC");while($r=mysqli_fetch_assoc($res))$posts[]=$r;
$pubCount=count(array_filter($posts,function($p){return $p['status']==='publish';}));
$draftCount=count($posts)-$pubCount;
$totalViews=array_sum(array_column($posts,'views'));
$pendingCmts=mysqli_num_rows(mysqli_query($conn,"SELECT * FROM comments WHERE approved=0"));

// ---- MENU ----
$menus = [
    'dashboard'=>'📊 ภาพรวม','posts'=>'📝 โพสต์ทั้งหมด','post-new'=>'➕ เขียนโพสต์ใหม่',
    'studios'=>'🎬 ค่าย','actors'=>'👤 นักแสดง','tags'=>'🏷️ แท็ก','media'=>'🖼️ สื่อ/แกลเลอรี่',
    'pages'=>'📄 หน้าเว็บ','banners'=>'📢 แบนเนอร์/ประกาศ','comments'=>'💬 คอมเมนต์'.($pendingCmts?' <span class="badge badge-red">'.$pendingCmts.'</span>':''),
    'users'=>'👥 ผู้ใช้งาน','reports'=>'📈 รายงาน/สถิติ','theme'=>'🎨 สไตล์ & เมนู','site-content'=>'🧩 เนื้อหาเว็บ & เมนู','seo'=>'🔍 ตั้งค่า SEO','file-manager'=>'📂 ไฟล์ & Sitemap','settings'=>'⚙️ ตั้งค่าระบบ'
];

function adminContent($page) {
    global $conn,$posts,$studios,$actors,$tags,$pubCount,$draftCount,$totalViews,$pendingCmts,$SETTINGS;
    if($page==='file-manager') {
        $state=siteFileManagerState();
        $siteUrl=siteCanonicalBase();
        $indexingEnabled=!empty($state['indexing_enabled']);
        $fileHtml='';
        foreach(['robots.txt'=>'robots.php','ads.txt'=>'ads.php'] as $name=>$endpoint) {
            $fileState=$state['files'][$name];
            $content=$fileState['content'];
            if($name==='robots.txt'&&$content==='')$content="User-agent: *\nAllow: /";
            $backups=$fileState['backups'];
            $fileHtml.='<section class="card mb"><h2 class="section-title">'.esc($name).'</h2>'.
                '<p style="font-size:12px;color:var(--muted);line-height:1.7;margin-bottom:12px;">ไฟล์นี้จัดเก็บในฐานข้อมูลและให้บริการผ่าน <a href="../'.esc($endpoint).'" target="_blank" rel="noopener" style="color:var(--accent);">ตัวอย่างปลายทาง</a> ไม่ได้เปิดให้แก้ไขไฟล์ระบบบนเซิร์ฟเวอร์</p>'.
                '<form method="post"><input type="hidden" name="action" value="save_managed_file"><input type="hidden" name="managed_file" value="'.esc($name).'">'.
                ($name==='robots.txt'?'<div class="form-group"><label>URL หลักของเว็บไซต์</label><input type="url" name="site_url" value="'.esc(isset($state['site_url'])&&$state['site_url']!==''?$state['site_url']:$siteUrl).'" placeholder="https://example.com" required><small style="color:var(--muted);">ใช้สร้าง canonical URL และลิงก์ Sitemap; ควรเป็นโดเมน HTTPS จริง</small></div><label style="display:flex;align-items:flex-start;gap:9px;margin:12px 0;color:var(--text);"><input type="checkbox" name="indexing_enabled" value="1"'.($indexingEnabled?' checked':'').'> <span>อนุญาตให้เครื่องมือค้นหาจัดทำดัชนีเว็บ<br><small style="color:var(--muted);">เมื่อปิด ระบบส่ง noindex และ robots.txt จะบล็อก crawler แต่ไม่ใช่ระบบยืนยันตัวตน/ป้องกันข้อมูล</small></span></label><input type="hidden" name="indexing_toggle" value="1">':'').
                '<div class="form-group"><label>เนื้อหา (สูงสุด 20 KB)</label><textarea name="content" rows="9" maxlength="20000" style="font-family:monospace;font-size:12px;" spellcheck="false">'.esc($content).'</textarea></div><button class="btn btn-primary" type="submit">💾 บันทึก '.esc($name).'</button></form>'.
                '<div style="margin-top:16px;"><strong style="font-size:12px;">ข้อมูลสำรองล่าสุด (สูงสุด 3 ชุด)</strong>'.
                ($backups?'<div style="display:grid;gap:8px;margin-top:8px;">'.implode('',array_map(function($backup,$index)use($name){return '<form method="post" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;"><input type="hidden" name="action" value="restore_managed_file"><input type="hidden" name="managed_file" value="'.esc($name).'"><input type="hidden" name="backup_index" value="'.(int)$index.'"><span style="color:var(--muted);font-size:11px;">'.esc(isset($backup['saved_at'])?$backup['saved_at']:'').'</span><button class="btn btn-ghost btn-sm" type="submit">กู้คืนข้อมูลสำรอง #'.((int)$index+1).'</button></form>';},$backups,array_keys($backups))).'</div>':'<p style="font-size:11px;color:var(--muted);margin-top:6px;">ยังไม่มีข้อมูลสำรอง — จะสร้างเมื่อบันทึกเนื้อหาที่เปลี่ยน</p>').'</div></section>';
        }
        $sitemapUrl=$siteUrl.'/sitemap_index.xml';
        return '<h1 style="font-size:22px;font-weight:900;margin-bottom:8px;">📂 File Manager และ Sitemap</h1>'.
            '<p style="font-size:12px;color:var(--muted);line-height:1.7;margin-bottom:18px;">การจัดการไฟล์จำกัดเฉพาะ robots.txt และ ads.txt เท่านั้น ไม่สามารถแก้ PHP, config, .htaccess, SQL หรือไฟล์อื่นได้ การอัปเดต GitHub ไม่ได้เปลี่ยนไฟล์บนโฮสต์โดยอัตโนมัติ เว้นแต่ตั้งระบบ deploy แยกไว้</p>'.
            '<div class="card mb"><div class="section-title">🗺️ XML Sitemap</div><p style="font-size:12px;color:var(--muted);line-height:1.7;">Sitemap index แบ่งไฟล์อัตโนมัติไม่เกิน 5,000 URL ต่อไฟล์ และรวมเฉพาะโพสต์/หน้าเผยแพร่กับค่าย นักแสดง และแท็ก</p><p style="margin:10px 0;"><a href="'.esc($sitemapUrl).'" target="_blank" rel="noopener" style="color:var(--accent);word-break:break-all;">'.esc($sitemapUrl).'</a></p><span class="badge '.($indexingEnabled?'badge-green':'badge-gold').'">'.($indexingEnabled?'เปิด indexing':'ปิด indexing — noindex / robots block').'</span><ol style="font-size:12px;color:var(--muted);line-height:1.8;padding-left:20px;margin-top:12px;"><li>ตรวจว่า URL หลักถูกต้องและเปิด HTTPS แล้ว</li><li>เปิด indexing หลังเว็บพร้อมเผยแพร่เท่านั้น</li><li>ยืนยันโดเมนใน Google Search Console ด้วยบัญชีที่คุณควบคุม</li><li>ไปที่เมนู Sitemaps แล้วส่ง <code>sitemap_index.xml</code></li></ol><p style="font-size:11px;color:var(--muted);margin-top:10px;">ระบบนี้สร้างไฟล์ Sitemap แต่ไม่ส่งข้อมูลล็อกอินหรือส่งคำขอไปยัง Google แทนคุณ</p></div>'.
            $fileHtml;
    }
    if ($page==='dashboard') {
        $recentAct=[];$res=mysqli_query($conn,"SELECT * FROM activity ORDER BY time DESC LIMIT 8");while($r=mysqli_fetch_assoc($res))$recentAct[]=$r;
        $chartData=[800,1200,950,1500,1100,1800,$totalViews%2000+500];$max=max($chartData);
        $h='<h1 style="font-size:24px;font-weight:900;margin-bottom:20px;">📊 ภาพรวมระบบ</h1>'.
        '<div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(120px,1fr));margin-bottom:24px;">'.
        '<div class="stat-card"><div class="stat-num">'.count($posts).'</div><div class="stat-label">โพสต์ทั้งหมด</div></div>'.
        '<div class="stat-card"><div class="stat-num" style="color:var(--success);">'.$pubCount.'</div><div class="stat-label">เผยแพร่</div></div>'.
        '<div class="stat-card"><div class="stat-num" style="color:var(--gold);">'.$draftCount.'</div><div class="stat-label">แบบร่าง</div></div>'.
        '<div class="stat-card"><div class="stat-num" style="color:var(--accent2);">'.count($studios).'</div><div class="stat-label">ค่าย</div></div>'.
        '<div class="stat-card"><div class="stat-num" style="color:var(--accent3);">'.count($actors).'</div><div class="stat-label">นักแสดง</div></div>'.
        '<div class="stat-card"><div class="stat-num" style="color:var(--gold);">'.number_format($totalViews).'</div><div class="stat-label">ยอดวิวรวม</div></div>'.
        '<div class="stat-card"><div class="stat-num" style="color:var(--danger);">'.$pendingCmts.'</div><div class="stat-label">คอมเมนต์รออนุมัติ</div></div></div>'.
        '<div class="grid dashboard-chart-grid" style="grid-template-columns:2fr 1fr;gap:12px;margin-bottom:24px;">'.
        '<div class="card"><div class="section-title">📈 ยอดวิว 7 วันล่าสุด</div><div class="bar-chart">'.implode('',array_map(function($v)use($max){return '<div class="bar" style="height:'.($v/$max*100).'%;" title="'.$v.'"><span>'.($v>999?round($v/1000,1).'k':$v).'</span></div>';},$chartData)).'</div></div>'.
        '<div class="card"><div class="section-title">🕐 กิจกรรมล่าสุด</div><div style="font-size:12px;line-height:2;max-height:140px;overflow:auto;">'.implode('',array_map(function($a){return '<div style="border-bottom:1px solid var(--border);padding:4px 0;"><span style="color:var(--accent);">●</span> '.esc($a['action']).'<br><span style="color:var(--muted);font-size:10px;">'.fmtTime($a['time']).'</span></div>';},$recentAct)).'</div></div></div>'.
        '<div class="card"><div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;flex-wrap:wrap;gap:8px;"><h2 style="font-size:16px;font-weight:700;">โพสต์ล่าสุด</h2><a class="btn btn-primary btn-sm" href="index.php?page=post-new">+ เขียนใหม่</a></div>'.
        '<div style="overflow-x:auto;"><table><thead><tr><th>ชื่อเรื่อง</th><th>ค่าย</th><th>ความยาว</th><th>สถานะ</th><th>วิว</th><th>วันที่</th><th></th></tr></thead><tbody>'.
        implode('',array_map(function($p){return '<tr><td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-weight:600;">'.esc($p['title']).($p['code']?'<div style="font-size:10px;color:var(--muted);font-weight:400;">'.esc($p['code']).'</div>':'').'</td><td>'.esc(getStudio($p['studioId'])['name']).'</td><td>'.esc(formatVideoDuration($p['videoDuration'])?:'-').'</td><td><span class="post-status '.($p['status']==='publish'?'is-published':'is-draft').'">'.($p['status']==='publish'?'เผยแพร่':'ฉบับร่าง').'</span></td><td>'.$p['views'].'</td><td>'.fmtDate($p['date']).'</td><td><a class="btn btn-ghost btn-sm" href="index.php?page=post-edit&id='.$p['id'].'">แก้ไข</a></td></tr>';},array_slice($posts,0,5))).
        '</tbody></table></div></div>';
        return $h;
    }
    if ($page==='posts') {
        $statusCounts=[];
        foreach($posts as $post)$statusCounts[$post['status']]=isset($statusCounts[$post['status']])?$statusCounts[$post['status']]+1:1;
        $statusLabels=['publish'=>'เผยแพร่','draft'=>'ฉบับร่าง','pending'=>'รอตรวจ','private'=>'ส่วนตัว'];
        $statusCards='<div class="posts-stat-card"><strong>'.count($posts).'</strong><span>โพสต์ทั้งหมด</span></div>';
        foreach($statusCounts as $status=>$count) {
            $label=isset($statusLabels[$status])?$statusLabels[$status]:$status;
            $class=$status==='publish'?'is-published':($status==='draft'?'is-draft':'is-pending');
            $statusCards.='<div class="posts-stat-card"><strong class="'.$class.'">'.$count.'</strong><span>'.esc($label).'</span></div>';
        }
        $tableRows='';$postCards='';
        foreach($posts as $post) {
            $studio=getStudio($post['studioId']);
            $duration=formatVideoDuration($post['videoDuration']);
            $status=$post['status']==='publish'?'เผยแพร่':($post['status']==='draft'?'ฉบับร่าง':(isset($statusLabels[$post['status']])?$statusLabels[$post['status']]:$post['status']));
            $statusClass=$post['status']==='publish'?'is-published':($post['status']==='draft'?'is-draft':'is-pending');
            $actorsForPost=array_map(function($actorId){return getActor($actorId)['name'];},parseTaxonomyIds($post['actorIds']));
            $tagNamesForPost=array_map(function($tagId){return getTag($tagId)['name'];},parseTaxonomyIds($post['tagIds']));
            $categoriesForPost=preg_split('/[,،;|，；\r\n]+/u',(string)(isset($post['category'])?$post['category']:''))?:[];
            $categoriesForPost=array_values(array_unique(array_filter(array_map('trim',$categoriesForPost),function($category){return $category!=='';})));
            $postAuthor=trim(isset($post['author'])?$post['author']:'');
            $postDateTime=isset($post['createdAt'])?$post['createdAt']:'';
            $postBylineDate=$postDateTime!==''?fmtThaiDateTime($postDateTime):(isset($post['date'])&&$post['date']!==''?fmtThaiDate($post['date']).' · เวลาไม่ระบุ':'วันเวลาไม่ระบุ');
            $cover=!empty($post['cover'])?$post['cover']:'';
            $actions='<a class="btn btn-ghost btn-sm" href="index.php?page=post-edit&amp;id='.(int)$post['id'].'">แก้ไข</a><form method="post" class="post-delete-form"><input type="hidden" name="action" value="delete_post"><input type="hidden" name="id" value="'.(int)$post['id'].'"><button class="btn btn-danger btn-sm" onclick="return confirm(\'ลบโพสต์นี้?\')">ลบ</button></form>';
            $tableRows.='<tr><td class="post-table-title">'.($cover?'<img src="'.esc($cover).'" alt="" loading="lazy">':'<span class="post-table-placeholder">ไม่มีภาพ</span>').'<span><strong>'.esc($post['title']).'</strong>'.(!empty($post['code'])?'<small>'.esc($post['code']).'</small>':'').'</span></td><td>'.esc($studio['name']).'</td><td>'.esc($duration?:'-').'</td><td><span class="post-status '.$statusClass.'">'.esc($status).'</span></td><td>'.number_format((int)$post['views']).'</td><td>'.fmtDate($post['date']).'</td><td class="post-table-actions">'.$actions.'</td></tr>';
            $taxonomyPills='';
            foreach(array_slice($categoriesForPost,0,3) as $categoryName)$taxonomyPills.='<span class="post-summary-pill is-category">'.esc($categoryName).'</span>';
            if(!empty($studio['id']))$taxonomyPills.='<span class="post-summary-pill is-studio">'.esc($studio['name']).'</span>';
            foreach(array_slice($actorsForPost,0,3) as $actorName)$taxonomyPills.='<span class="post-summary-pill is-actor">'.esc($actorName).'</span>';
            foreach(array_slice($tagNamesForPost,0,5) as $tagName)$taxonomyPills.='<span class="post-summary-pill is-tag">#'.esc($tagName).'</span>';
            if($taxonomyPills==='')$taxonomyPills='<span class="post-summary-pill is-empty">ไม่ระบุ</span>';
            $postCards.='<article class="post-summary-card"><div class="post-summary-cover">'.($cover?'<img src="'.esc($cover).'" alt="" loading="lazy">':'<span>ไม่มีภาพปก</span>').'<span class="post-status '.$statusClass.'">'.esc($status).'</span></div><div class="post-summary-body"><div class="post-summary-heading"><div>'.(!empty($post['code'])?'<span class="post-summary-code">'.esc($post['code']).'</span>':'').'<h2>'.esc($post['title']).'</h2></div><div class="post-summary-views">👁 '.number_format((int)$post['views']).'</div></div><div class="post-summary-byline"><span>👤 '.esc($postAuthor!==''?$postAuthor:'ไม่ระบุ').'</span><span>◷ '.esc($postBylineDate).'</span></div><div class="post-summary-meta">'.($duration?'<span>🎞 '.esc($duration).'</span>':'').'</div><div class="post-summary-taxonomy">'.$taxonomyPills.'</div><div class="post-summary-actions">'.$actions.'</div></div></article>';
        }
        $savedPrimary=trim(setting('post_primary_keyword_notes',''));
        $savedSecondary=trim(setting('post_secondary_keyword_notes',''));
        $defaultPrimary="jav|EN|1\nหนังav|TH|2\nหนังโป๊ญี่ปุ่น|TH|3\nav japan|EN|4\nxxxav|EN\nหนังเอวี|TH|6\nav xxx|EN\nหนัง av|TH\navjapan|EN|9\navxxx|EN\navญี่ปุ่น|TH\nหนังโป๊|TH\nxxx av|EN\nav ญี่ปุ่น|TH\nxxxญี่ปุ่น|TH\nหนัง x ญี่ปุ่น|TH\nดูหนังโป๊ญี่ปุ่น|TH\nxxxjapan|EN\nav18+|EN\nหนังโป๊av|TH\nav hd|EN\njapan av|EN|22\nporn av|EN\nคลิปโป๊ญี่ปุ่น|TH\njapanxxx|EN\nเอวี|TH\nav porn|EN\nหนังxญี่ปุ่น|TH";
        $defaultSecondary="หนังโป๊ญี่ปุ่นนมใหญ่\nav นมใหญ่\nเย็ดญี่ปุ่น\nหนังเอวีนมใหญ่\njav นมใหญ่\nนางเอกเอวีนมใหญ่\nหนังโป๊ญี่ปุ่นสวย\nav สวย\nหนังเอวีหน้าใส\njav สวย\nนางเอกavสวย\nหนังโป๊ญี่ปุ่นใหม่\nav ใหม่ล่าสุด\nหนังเอวีใหม่\njav ใหม่\nดูหนังเอวีฟรี\nดูjavฟรี\nหนังโป๊ญี่ปุ่นhd\nav hd\njav hd\nหนังเอวีชัด\nดูavออนไลน์\nดูjavออนไลน์\nหนังโป๊ญี่ปุ่นเต็มเรื่อง\nav เต็มเรื่อง\njav เต็มเรื่อง\nหนังเอวีแนวโรแมนติก\nav คู่รัก\njav ความรัก\nหนังโป๊ญี่ปุ่นน่ารัก\nav น่ารัก\njav น่ารัก\nนางเอกเอวีน่ารัก\nหนังโป๊ญี่ปุ่นยอดนิยม\nav ยอดนิยม\njav ยอดนิยม\nหนังเอวีเรื่องดัง\nดูหนังav\nหนังavญี่ปุ่น\nav ญี่ปุ่น\njav ญี่ปุ่น\nนักแสดงavญี่ปุ่น\nนักแสดงjav\nav สตรีมมิ่ง\nดูหนังเอวี\nหนังโป๊ญี่ปุ่นxxx\nav xxx\nหนังโป๊ญี่ปุ่น24ชม\nav ครบทุกเรื่อง\njav ครบทุกเรื่อง\nหนังเอวีสด\nav สด\nถ่ายทอดสดav\nหนังเอวีคุณภาพสูง\nav 4k\njav 1080p\nหนังเอวีฟูลเอชดี\nหนังโป๊ญี่ปุ่นชัดไม่เบลอ\nav คมชัด\njav คมชัด";
        if(!array_key_exists('post_primary_keyword_notes',$SETTINGS))$savedPrimary=$defaultPrimary;
        if(!array_key_exists('post_secondary_keyword_notes',$SETTINGS))$savedSecondary=$defaultSecondary;
        $primaryChipHtml='';$primaryRankMap=[1=>1,2=>2,3=>3,4=>4,6=>6,9=>9,22=>22];
        foreach(preg_split('/\r\n|\r|\n/',$savedPrimary) as $index=>$line) {
            $parts=array_map('trim',explode('|',$line));$keyword=isset($parts[0])?$parts[0]:'';
            if($keyword==='')continue;
            $language=isset($parts[1])&&in_array(strtoupper($parts[1]),['EN','TH'],true)?strtoupper($parts[1]):(preg_match('/[ก-๙]/u',$keyword)?'TH':'EN');
            $rank=isset($parts[2])&&ctype_digit($parts[2])?(int)$parts[2]:(isset($primaryRankMap[$index+1])?$primaryRankMap[$index+1]:0);
            $primaryChipHtml.='<button type="button" class="keyword-note-chip" data-copy-keyword="'.esc($keyword).'" title="คลิกเพื่อคัดลอก: '.esc($keyword).'"><span class="keyword-note-index">#'.($index+1).'</span><span>'.esc($keyword).'</span>'.($rank?'<span class="keyword-note-rank">rank '.$rank.'</span>':'').'<span class="keyword-note-language is-'.strtolower($language).'">'.$language.'</span></button>';
        }
        $secondaryChipHtml='';foreach(preg_split('/\r\n|\r|\n/',$savedSecondary) as $index=>$keyword){$keyword=trim($keyword);if($keyword!=='')$secondaryChipHtml.='<button type="button" class="keyword-note-chip is-secondary" data-copy-keyword="'.esc($keyword).'" title="คลิกเพื่อคัดลอก: '.esc($keyword).'"><span class="keyword-note-index">#'.($index+1).'</span><span>'.esc($keyword).'</span></button>';}
        return '<div class="posts-page" data-posts-page><div class="posts-page-heading"><div><h1>📝 จัดการโพสต์ทั้งหมด</h1><p>ค้นหาและเลือกมุมมองให้เหมาะกับงานและขนาดหน้าจอ</p></div><a class="btn btn-primary" href="index.php?page=post-new">+ เขียนโพสต์ใหม่</a></div>'.
        '<div class="posts-stats">'.$statusCards.'</div>'.
        '<details class="post-keyword-notes" id="post-keyword-notes"><summary class="keyword-notes-summary"><span>🔑 ชุดคีย์เวิร์ดส่วนตัว</span><small>คลิกเพื่อเปิดเต็มจอ · แตะคีย์เพื่อคัดลอกเฉพาะคำ</small></summary><div class="keyword-notes-content"><form method="post" class="keyword-notes-form" data-keyword-form><input type="hidden" name="action" value="save_post_keyword_notes"><div class="keyword-notes-toolbar"><div><strong>ชุดคีย์ยอดนิยม · Top Focus Keys</strong><small>'.count(array_filter(preg_split('/\r\n|\r|\n/',$savedPrimary),'trim')).' คีย์</small></div><button class="btn btn-ghost btn-sm" type="button" data-keyword-edit>✏️ แก้ไขชุดคีย์</button></div><div class="keyword-notes-read" data-keyword-read><section><h3>Primary head keys <span>คลิกคีย์เพื่อคัดลอก</span></h3><div class="keyword-note-preview">'.$primaryChipHtml.'</div></section><section><h3>Long-tail Keywords <span>'.count(array_filter(preg_split('/\r\n|\r|\n/',$savedSecondary),'trim')).' คีย์ · คลิกคีย์เพื่อคัดลอก</span></h3><div class="keyword-note-preview">'.$secondaryChipHtml.'</div></section></div><div class="keyword-notes-edit" data-keyword-editor hidden><div class="keyword-notes-grid"><section><label for="primaryKeywordNotes">Primary head keys <span>หนึ่งคีย์ต่อบรรทัด · คีย์|ภาษา|อันดับ (เช่น jav|EN|1)</span></label><textarea id="primaryKeywordNotes" name="primary_keyword_notes" rows="12">'.esc($savedPrimary).'</textarea></section><section><label for="secondaryKeywordNotes">Long-tail / Secondary keys <span>หนึ่งคีย์ต่อบรรทัด</span></label><textarea id="secondaryKeywordNotes" name="secondary_keyword_notes" rows="12">'.esc($savedSecondary).'</textarea></section></div><div class="keyword-notes-footer"><small>ชุดคีย์นี้เก็บเป็นโน้ตส่วนตัวของหลังบ้าน ไม่ถูกนำไปใส่ในโพสต์อัตโนมัติ</small><div><button class="btn btn-ghost btn-sm" type="button" data-keyword-cancel>ยกเลิก</button> <button class="btn btn-primary btn-sm" type="submit">💾 บันทึกชุดคีย์</button></div></div></div></form></div></details>'.
        '<div class="posts-toolbar"><strong>รายการโพสต์ <span>'.count($posts).' เรื่อง</span></strong><div class="posts-view-switch" role="group" aria-label="เปลี่ยนมุมมองโพสต์"><button type="button" data-post-view="normal" aria-pressed="true">☷ ปกติ</button><button type="button" data-post-view="rows" aria-pressed="false">▤ แถวรายละเอียด</button><button type="button" data-post-view="columns" aria-pressed="false">▦ คอลัมน์</button></div></div>'.
        '<section class="posts-view posts-view-normal is-active" data-post-view-panel="normal"><div class="card posts-table-card"><div class="posts-table-scroll"><table class="posts-table"><thead><tr><th>โพสต์</th><th>ค่าย</th><th>ความยาว</th><th>สถานะ</th><th>วิว</th><th>วันที่</th><th>จัดการ</th></tr></thead><tbody>'.($tableRows!==''?$tableRows:'<tr><td colspan="7" class="posts-empty">ยังไม่มีโพสต์</td></tr>').'</tbody></table></div></div></section>'.
        '<section class="posts-view posts-view-rows" data-post-view-panel="rows">'.$postCards.'</section><section class="posts-view posts-view-columns" data-post-view-panel="columns">'.$postCards.'</section></div>';
    }
    if ($page==='post-new' || $page==='post-edit') {
        $p = ['id'=>0,'code'=>'','title'=>'','slug'=>'','content'=>'','cover'=>'','posterUrl'=>'','videoUrl'=>'','embedUrl'=>'','videoDuration'=>'','gallery'=>'[]','studioId'=>$studios?$studios[0]['id']:0,'actorIds'=>'[]','actorEN'=>'','tagIds'=>'[]','category'=>'','focusKeyphrase'=>'','metaTitle'=>'','metaDesc'=>'','keywords'=>'','status'=>'draft','date'=>date('Y-m-d')];
        if ($page==='post-edit' && isset($_GET['id'])) {
            $id=intval($_GET['id']);
            $r=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM posts WHERE id=$id"));
            if ($r) $p=$r;
        }
        $actorIds=json_decode($p['actorIds'],true)?:[];
        $tagIds=json_decode($p['tagIds'],true)?:[];
        $gallery=json_decode($p['gallery'],true)?:[];
        $galleryPreviewHtml='';
        foreach($gallery as $galleryImage)$galleryPreviewHtml.='<div class="editor-gallery-item"><img src="'.esc($galleryImage).'" alt="" loading="lazy"></div>';
        $playerInput=!empty($p['embedUrl'])?$p['embedUrl']:$p['videoUrl'];
        $v=parseVideo($playerInput);
        $durationParts=trim((string)$p['videoDuration'])!==''?videoDurationParts($p['videoDuration']):null;
        $durationHours=$durationParts===null?'':(string)$durationParts[0];
        $durationMinutes=$durationParts===null?'':(string)$durationParts[1];
        $durationSeconds=$durationParts===null?'':sprintf('%02d',$durationParts[2]);
        $studioUsage=[];$actorUsage=[];$tagUsage=[];
        foreach($studios as $studio)$studioUsage[(int)$studio['id']]=0;
        foreach($actors as $actor)$actorUsage[(int)$actor['id']]=0;
        foreach($tags as $tag)$tagUsage[(int)$tag['id']]=0;
        foreach($posts as $usagePost) {
            $studioUsage[(int)$usagePost['studioId']]=isset($studioUsage[(int)$usagePost['studioId']])?$studioUsage[(int)$usagePost['studioId']]+1:1;
            foreach(json_decode($usagePost['actorIds'],true)?:[] as $usageActorId)if(isset($actorUsage[(int)$usageActorId]))$actorUsage[(int)$usageActorId]++;
            foreach(json_decode($usagePost['tagIds'],true)?:[] as $usageTagId)if(isset($tagUsage[(int)$usageTagId]))$tagUsage[(int)$usageTagId]++;
        }
        $popularTerms=function($items,$usage) {
            usort($items,function($a,$b)use($usage){$countA=isset($usage[(int)$a['id']])?$usage[(int)$a['id']]:0;$countB=isset($usage[(int)$b['id']])?$usage[(int)$b['id']]:0;return $countB===$countA?strcasecmp($a['name'],$b['name']):$countB-$countA;});
            return array_slice(array_values(array_filter($items,function($item)use($usage){return !empty($usage[(int)$item['id']]);})),0,10);
        };
        $categoryUsage=[];
        foreach($posts as $categoryPost) {
            foreach(preg_split('/[,،;|，；\r\n]+/u',(string)(isset($categoryPost['category'])?$categoryPost['category']:''))?:[] as $categoryName) {
                $categoryName=trim($categoryName);
                if($categoryName!=='')$categoryUsage[$categoryName]=isset($categoryUsage[$categoryName])?$categoryUsage[$categoryName]+1:1;
            }
        }
        arsort($categoryUsage,SORT_NUMERIC);
        $categoryOptions='';$categoryRecommendations='';
        foreach($categoryUsage as $categoryName=>$categoryCount) {
            $categoryOptions.='<option value="'.esc($categoryName).'" data-count="'.(int)$categoryCount.'">';
            $categoryRecommendations.='<button class="taxonomy-recommendation" type="button" data-category-name="'.esc($categoryName).'">'.esc($categoryName).'<span>'.(int)$categoryCount.' เรื่อง</span></button>';
        }
        $popularStudios=$popularTerms($studios,$studioUsage);
        $popularActors=$popularTerms($actors,$actorUsage);
        $popularTags=$popularTerms($tags,$tagUsage);
        $actorOptions=implode('',array_map(function($actor)use($actorIds,$actorUsage){$count=isset($actorUsage[(int)$actor['id']])?$actorUsage[(int)$actor['id']]:0;return '<option value="'.$actor['id'].'" data-count="'.$count.'"'.(in_array($actor['id'],$actorIds)?' selected':'').'>'.esc($actor['name']).'</option>';},$actors));
        $tagOptions=implode('',array_map(function($tag)use($tagIds,$tagUsage){$count=isset($tagUsage[(int)$tag['id']])?$tagUsage[(int)$tag['id']]:0;return '<option value="'.$tag['id'].'" data-count="'.$count.'"'.(in_array($tag['id'],$tagIds)?' selected':'').'>'.esc($tag['name']).'</option>';},$tags));
        $studioOptions=implode('',array_map(function($studio)use($studioUsage){$count=isset($studioUsage[(int)$studio['id']])?$studioUsage[(int)$studio['id']]:0;return '<option value="'.esc($studio['name']).'" data-count="'.$count.'">';},$studios));
        $popularStudioHtml=implode('',array_map(function($studio)use($studioUsage){return '<button class="taxonomy-recommendation" type="button" data-studio-name="'.esc($studio['name']).'">'.esc($studio['name']).'<span>'.(int)$studioUsage[(int)$studio['id']].' เรื่อง</span></button>';},$popularStudios));
        $popularActorHtml=implode('',array_map(function($actor)use($actorUsage){return '<button class="taxonomy-recommendation" type="button" data-term-id="'.$actor['id'].'" data-term-name="'.esc($actor['name']).'">'.esc($actor['name']).'<span>'.(int)$actorUsage[(int)$actor['id']].' เรื่อง</span></button>';},$popularActors));
        $popularTagHtml=implode('',array_map(function($tag)use($tagUsage){return '<button class="taxonomy-recommendation" type="button" data-term-id="'.$tag['id'].'" data-term-name="'.esc($tag['name']).'">#'.esc($tag['name']).'<span>'.(int)$tagUsage[(int)$tag['id']].' เรื่อง</span></button>';},$popularTags));
        $newActorsJson=json_encode([],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);
        $newTagsJson=json_encode([],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);
        $autoTitle=$p['metaTitle']?'':'<div class="auto-hint">💡 เว้นว่าง = ใช้ชื่อเรื่องอัตโนมัติ: <strong>'.esc($p['title']).'</strong></div>';
        $autoDesc=$p['metaDesc']?'':'<div class="auto-hint">💡 เว้นว่าง = ใช้เนื้อหาอัตโนมัติ (ไม่เกิน 140 ตัวอักษร): <strong>'.esc(mb_substr(stripHtml($p['content']),0,80,'UTF-8')).'...</strong></div>';
        return '<h1 style="font-size:22px;font-weight:900;margin-bottom:16px;">'.($p['id']?'✏️ แก้ไขโพสต์':'➕ เขียนโพสต์ใหม่').'</h1>'.
        '<div class="editor-preview-layout"><form id="postEditorForm" class="editor-form" method="post" enctype="multipart/form-data"><input type="hidden" name="action" value="save_post"><input type="hidden" name="id" value="'.$p['id'].'">'.
        '<div class="editor-columns"><div class="editor-side-column"><details class="seo-audit seo-audit-sticky" id="seoAudit" data-site-name="'.esc(setting('siteName')).'" aria-live="polite"><summary class="seo-audit-summary"><span class="seo-audit-light" data-seo-light></span><strong data-seo-overall>กำลังตรวจ SEO</strong><span class="seo-audit-score" data-seo-score>0/100</span></summary><ul class="seo-audit-list" data-seo-checks></ul><small class="seo-audit-note">รายการตรวจช่วยเตือนเบื้องต้น ไม่ใช่การรับรองอันดับจาก Google</small></details><aside class="card editor-preview-panel"><div class="section-title">👁️ ตัวอย่างโพสต์ (อัปเดตขณะพิมพ์)</div><span id="postPreviewStatus" class="post-status '.($p['status']==='publish'?'is-published':'is-draft').'">'.($p['status']==='publish'?'เผยแพร่':'ฉบับร่าง').'</span><h2 id="postPreviewTitle" style="font-size:19px;line-height:1.5;margin:12px 0 10px;">'.esc($p['title']?:'ชื่อเรื่องจะแสดงที่นี่').'</h2><div id="postPreviewMeta" style="font-size:12px;color:var(--muted);margin-bottom:14px;">'.esc(implode(' · ',array_filter([isset($p['code'])?$p['code']:'',getStudio($p['studioId'])['name'],isset($p['actorEN'])?$p['actorEN']:'',isset($p['category'])?$p['category']:'' ]))).'</div><div id="postPreviewContent" class="post-preview-content">'.esc(stripHtml($p['content'])?:'เนื้อหาตัวอย่างจะแสดงที่นี่').'</div><div id="postPreviewSEO" style="border-top:1px solid var(--border);margin-top:16px;padding-top:12px;font-size:12px;color:var(--muted);">Focus Keyphrase: '.esc(isset($p['focusKeyphrase'])?$p['focusKeyphrase']:'-').' | Meta Description: '.esc($p['metaDesc']?:'ระบบจะสร้างจากเนื้อหาอัตโนมัติ').'</div></aside>'.
        '<div class="editor-cover-preview" id="editorCoverPreview"'.((!empty($p['cover'])||!empty($p['posterUrl']))?'':' hidden').'><img id="editorCoverPreviewImage" src="'.esc(!empty($p['cover'])?$p['cover']:(isset($p['posterUrl'])?$p['posterUrl']:'')).'" alt="ตัวอย่างรูปปกโพสต์"><span>ตัวอย่างรูปปกโพสต์</span></div>'.
        '<div class="card mb editor-image-card"><div class="section-title" style="color:var(--accent2);">🖼️ รูปปกและโปสเตอร์</div>'.
        '<div class="form-group"><label>รูปปก: ใส่ลิงก์หรือเลือกไฟล์</label><input name="cover" value="'.esc($p['cover']).'" placeholder="https://... (หากเลือกไฟล์ ไฟล์จะถูกใช้แทนลิงก์)"><input type="file" name="coverUpload" accept="image/jpeg,image/png,image/gif,image/webp" style="margin-top:8px;"><small class="field-help">ใช้เป็นภาพปกการ์ดและภาพสำรอง · JPG, PNG, GIF, WebP ไม่เกิน 5 MB · ระบบสร้าง alt จากชื่อเรื่อง</small></div>'.
        '<div class="form-group"><label>โปสเตอร์หน้าโพสต์: ใส่ลิงก์หรือเลือกไฟล์</label><input name="poster" value="'.esc(isset($p['posterUrl'])?$p['posterUrl']:'').'" placeholder="ไม่ระบุ = ใช้รูปปกเป็นโปสเตอร์"><input type="file" name="posterUpload" accept="image/jpeg,image/png,image/gif,image/webp" style="margin-top:8px;"><small class="field-help">โปสเตอร์มีลำดับเหนือรูปปกในหน้าโพสต์ · JPG, PNG, GIF, WebP ไม่เกิน 5 MB · alt ใช้ชื่อเรื่อง</small></div></div>'.
        '<div class="card mb editor-video-card"><div class="section-title" style="color:var(--accent);">🎬 วิดีโอและ Remote Player</div>'.
        '<div class="form-group"><label for="videoUrlInput">ลิงก์วิดีโอ หรือวาง iframe เต็ม</label><textarea id="videoUrlInput" name="videoUrl" rows="3" placeholder="https://player.example.com/embed/ID&#10;หรือวางโค้ด &lt;iframe src=&quot;https://...&quot;&gt;&lt;/iframe&gt;">'.esc($p['videoUrl']).'</textarea><small class="video-input-help">รองรับลิงก์ http(s), URL วิดีโอ MP4/WebM/OGG, YouTube, Google Drive, Fembed, javzero, ggcdn และโค้ด iframe เต็ม ระบบจะดึงเฉพาะ src แล้วสร้าง iframe ที่ปลอดภัยให้</small></div>'.
        '<div class="remote-api-status '.(getenv('PINKCMS_VDOHIDE_TOKEN')?'is-ready':'is-off').'"><span class="remote-api-dot"></span><span><strong>'.(getenv('PINKCMS_VDOHIDE_TOKEN')?'Remote Player API พร้อมใช้งาน':'Remote Player API ยังไม่ได้ตั้งค่า').'</strong><small>'.(getenv('PINKCMS_VDOHIDE_TOKEN')?'ระบบจะสร้าง Player URL ตอนบันทึกโพสต์':'ลิงก์วิดีโอและ iframe ที่รองรับยังใช้งานได้โดยตรง ตั้งค่า token ที่ฝั่งเซิร์ฟเวอร์เพื่อเปิด API').'</small></span></div>'.
        '<div id="videoInputPreview" class="video-input-preview">'.($v['embed']?'<div class="video-wrap">'.$v['embed'].'</div>':'<span>ตัวอย่างเครื่องเล่นจะแสดงที่นี่</span>').'</div></div>'.
        '<div class="card mb editor-seo-card"><div class="section-title" style="color:var(--gold);">🔍 SEO ต่อโพสต์ (เว้นว่าง = อัตโนมัติ)</div>'.
        '<div class="form-group"><label>Meta Title</label><input name="metaTitle" value="'.esc($p['metaTitle']).'" data-preview="metaTitle" maxlength="70">'.$autoTitle.'</div>'.
        '<div class="form-group"><label>Meta Description (ไม่เกิน 140 ตัวอักษร)</label><textarea name="metaDesc" rows="2" data-preview="metaDesc" maxlength="140">'.esc($p['metaDesc']).'</textarea>'.$autoDesc.'</div>'.
        '<div class="form-group"><label>Keywords (คั่นด้วย ,)</label><input name="keywords" value="'.esc($p['keywords']).'"></div></div>'.
        '</div><div class="editor-primary-column">'.
        '<div class="card mb editor-basic-card"><div class="section-title">📌 ข้อมูลพื้นฐาน</div>'.
        '<div class="form-row code-keyphrase-row"><div class="form-group"><label>รหัสเรื่อง (Code)</label><input class="code-input" name="code" value="'.esc(isset($p['code'])?$p['code']:'').'" data-preview="code"></div>'.
        '<div class="form-group"><label>Focus Keyphrase</label><input name="focusKeyphrase" value="'.esc(isset($p['focusKeyphrase'])?$p['focusKeyphrase']:'').'" data-preview="focusKeyphrase"></div></div>'.
        '<div class="form-group"><label>ชื่อเรื่อง *</label><input name="title" value="'.esc($p['title']).'" data-preview="title" maxlength="70"></div>'.
        '<div class="form-row-3"><div class="form-group"><label>Slug (URL)</label><input name="slug" value="'.esc($p['slug']).'" data-preview="slug"></div>'.
        '<div class="form-group"><label>วันที่เผยแพร่</label><input type="date" name="date" value="'.$p['date'].'"></div>'.
        '<div class="form-group"><label>ความยาววิดีโอ</label><div class="duration-input"><input id="videoHours" type="text" inputmode="numeric" maxlength="8" aria-label="ชั่วโมง" placeholder="ชม." value="'.esc($durationHours).'"><span class="duration-separator">:</span><input id="videoMinutes" type="text" inputmode="numeric" maxlength="8" aria-label="นาที" placeholder="นาที" value="'.esc($durationMinutes).'"><span class="duration-separator">:</span><input id="videoSeconds" type="text" inputmode="numeric" maxlength="8" aria-label="วินาที" placeholder="วินาที" value="'.esc($durationSeconds).'"></div><input type="hidden" name="videoDuration" id="videoDuration" value=""><small id="durationReadable" class="field-help">ระบุชั่วโมง นาที และวินาที</small></div></div>'.
        '<div class="form-group"><label>สถานะ</label><select name="status" data-preview="status"><option value="publish"'.($p['status']==='publish'?' selected':'').'>เผยแพร่</option><option value="draft"'.($p['status']==='draft'?' selected':'').'>แบบร่าง</option></select></div></div>'.
        '<div class="card mb editor-content-card"><div class="section-title">✍️ เนื้อหาโพสต์</div><div class="form-group"><label>เนื้อหา (รองรับ HTML)</label><textarea name="content" rows="12" data-preview="content">'.esc($p['content']).'</textarea><small class="field-help">ใช้ย่อหน้าและหัวข้อเพื่อให้อ่านง่าย ใส่ลิงก์ภายในและลิงก์อ้างอิงภายนอกได้</small></div></div>'.
        '<div class="card mb editor-gallery-card"><div class="section-title" style="color:var(--accent2);">🖼️ แกลเลอรี่</div><div class="form-group"><label for="galleryUpload">อัปโหลดภาพหลายรูป หรือวาง URL หนึ่งรูปต่อบรรทัด</label><input id="galleryUpload" type="file" name="galleryUploads[]" accept="image/jpeg,image/png,image/gif,image/webp" multiple><small class="field-help">เลือกภาพได้หลายรูป · JPG, PNG, GIF, WebP ไม่เกิน 5 MB ต่อรูป · ภาพใหม่จะเพิ่มต่อจากภาพเดิม</small></div><div class="form-group"><textarea id="galleryUrls" name="gallery" rows="3" aria-label="URL ภาพแกลเลอรี่ หนึ่งรูปต่อบรรทัด" placeholder="https://example.com/image.jpg">'.esc(implode("\n",$gallery)).'</textarea></div><div id="editorGalleryPreview" class="editor-gallery-preview" aria-live="polite">'.$galleryPreviewHtml.'</div></div>'.
        '<div class="card mb editor-taxonomy-card editor-tags-card"><div class="section-title" style="color:var(--accent3);">🏷️ หมวดหมู่ / ค่าย / นักแสดง / แท็ก</div>'.
        '<div class="form-group"><label for="postCategoryInput">หมวดหมู่ <span class="usage-caption">ค้นหาหรือเพิ่มชื่อหมวดหมู่</span></label><div class="taxonomy-autocomplete"><input id="postCategoryInput" name="category" value="'.esc(isset($p['category'])?$p['category']:'').'" data-preview="category" autocomplete="off" aria-autocomplete="list" aria-controls="postCategorySuggestions" placeholder="พิมพ์ชื่อหมวดหมู่"><datalist id="postCategoryOptions">'.$categoryOptions.'</datalist><div id="postCategorySuggestions" class="taxonomy-suggestions" hidden></div></div><div class="taxonomy-recommendations" aria-label="หมวดหมู่ที่มีอยู่">'.$categoryRecommendations.'</div><small class="field-help"><span class="legend-recommended">เลือกชื่อที่แนะนำ หรือพิมพ์ชื่อใหม่แล้วกด Enter</span></small></div>'.
        '<div class="form-group"><label for="studioName">ค่าย <span class="usage-caption">พิมพ์ค้นหาหรือเลือก 10 ค่ายที่ใช้บ่อย</span></label><div class="taxonomy-autocomplete"><input id="studioName" name="studioName" value="'.esc(getStudio($p['studioId'])['name']).'" autocomplete="off" aria-autocomplete="list" aria-controls="studioSuggestions" placeholder="พิมพ์ค้นหาหรือเพิ่มชื่อค่าย"><datalist id="studioOptions">'.$studioOptions.'</datalist><div id="studioSuggestions" class="taxonomy-suggestions" hidden></div></div><div class="taxonomy-recommendations" aria-label="ค่ายที่ใช้บ่อยที่สุด">'.$popularStudioHtml.'</div><small class="field-help"><span class="legend-recommended">เลือกชื่อที่แนะนำ หรือพิมพ์ชื่อใหม่แล้วกด Enter</span></small></div>'.
        '<div class="form-group"><label>นักแสดง <span class="usage-caption">ค้นหา เลือกหลายคน หรือเพิ่มชื่อใหม่</span></label><div class="taxonomy-picker" data-taxonomy-picker="actors"><div class="taxonomy-selected" aria-live="polite"></div><input id="actorPickerInput" class="taxonomy-input" type="text" autocomplete="off" aria-autocomplete="list" aria-expanded="false" placeholder="พิมพ์ชื่อนักแสดงแล้วกด Enter"><div class="taxonomy-suggestions" hidden></div></div><input type="hidden" name="actorEN" value="'.esc(isset($p['actorEN'])?$p['actorEN']:'').'"><input type="hidden" name="newActorNames" class="taxonomy-custom-values" value="'.esc($newActorsJson).'"><select class="taxonomy-source" name="actorIds[]" multiple data-preview="actors">'.$actorOptions.'</select>'.
        '<div class="taxonomy-recommendations" aria-label="นักแสดงที่ใช้บ่อยที่สุด">'.$popularActorHtml.'</div><small class="field-help"><span class="legend-recommended">กรอบชมพู/ส้ม = ใช้บ่อย</span><span class="legend-selected">กรอบเขียวอมฟ้า = เลือกแล้วหรือเพิ่มเอง</span></small></div>'.
        '<div class="editor-tags-section"><div class="section-title" style="color:var(--accent3);">🏷️ แท็ก</div><div class="form-group"><label>แท็ก <span class="usage-caption">ค้นหา เลือกหลายแท็ก หรือพิมพ์เพิ่มเอง</span></label><div class="taxonomy-picker" data-taxonomy-picker="tags"><div class="taxonomy-selected" aria-live="polite"></div><input id="tagPickerInput" class="taxonomy-input" type="text" autocomplete="off" aria-autocomplete="list" aria-expanded="false" placeholder="พิมพ์แท็กแล้วกด Enter"><div class="taxonomy-suggestions" hidden></div></div><input type="hidden" name="newTagNames" class="taxonomy-custom-values" value="'.esc($newTagsJson).'"><select class="taxonomy-source" name="tagIds[]" multiple data-preview="tags">'.$tagOptions.'</select><div class="taxonomy-recommendations" aria-label="แท็กที่ใช้บ่อยที่สุด">'.$popularTagHtml.'</div><small class="field-help"><span class="legend-recommended">กรอบชมพู/ส้ม = ใช้บ่อย</span><span class="legend-selected">กรอบเขียวอมฟ้า = เลือกแล้วหรือเพิ่มเอง</span></small></div></div></div>'.
        '</div></div></div>'.
        '<div class="editor-actions"><button class="btn btn-primary" type="submit">💾 บันทึกโพสต์</button><button class="btn btn-primary" type="submit" name="post_intent" value="publish" style="background:var(--success);box-shadow:none;">🚀 เผยแพร่ทันที</button><button class="btn btn-ghost" type="submit" name="post_intent" value="draft">📝 บันทึกเป็นฉบับร่าง</button><a class="btn btn-ghost" href="index.php?page=posts">ยกเลิก</a></div></form></div>';
    }
    if (in_array($page,['studios','actors','tags'])) {
        $type = $page==='studios'?'studio':($page==='actors'?'actor':'tag');
        $table = $type==='studio'?'studios':($type==='actor'?'actors':'tags');
        $label = $type==='studio'?'ค่าย':($type==='actor'?'นักแสดง':'แท็ก');
        $items=[];$res=mysqli_query($conn,"SELECT * FROM $table");while($r=mysqli_fetch_assoc($res))$items[]=$r;
        $extra = $type==='studio'?'<div class="form-group"><label>โลโก้: ใส่ลิงก์หรือเลือกไฟล์</label><input name="logo" placeholder="https://..."><input type="file" name="logoUpload" accept="image/jpeg,image/png,image/gif,image/webp" style="margin-top:8px;"><small style="color:var(--muted);font-size:10px;">รูปไม่เกิน 5 MB</small></div><div class="form-group"><label>คำอธิบาย</label><textarea name="desc" rows="2"></textarea></div>':($type==='actor'?'<div class="form-group"><label>รูปประจำตัว: ใส่ลิงก์หรือเลือกไฟล์</label><input name="photo" placeholder="https://..."><input type="file" name="photoUpload" accept="image/jpeg,image/png,image/gif,image/webp" style="margin-top:8px;"><small style="color:var(--muted);font-size:10px;">รูปไม่เกิน 5 MB</small></div><div class="form-group"><label>ประวัติ/คำอธิบาย</label><textarea name="bio" rows="2"></textarea></div>':'');
        $list = implode('',array_map(function($it)use($type,$conn){
            $count=0;$all=publishedPosts();
            foreach($all as $p){if($type==='studio'&&$p['studioId']==$it['id'])$count++;elseif($type==='actor'&&in_array($it['id'],json_decode($p['actorIds'],true)?:[]))$count++;elseif($type==='tag'&&in_array($it['id'],json_decode($p['tagIds'],true)?:[]))$count++;}
            return '<tr><td style="font-weight:600;">'.esc($it['name']).'<div style="font-size:10px;color:var(--muted);font-weight:400;">/'.esc($it['slug']).'</div></td><td style="font-size:11px;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--muted);">'.esc($it['desc']??$it['bio']??'-').'</td><td>'.$count.'</td><td><form method="post" style="display:inline;"><input type="hidden" name="action" value="delete_tax"><input type="hidden" name="tax_type" value="'.$type.'"><input type="hidden" name="id" value="'.$it['id'].'"><button class="btn btn-danger btn-sm" onclick="return confirm(\'ลบรายการนี้?\')">ลบ</button></form></td></tr>';
        },$items));
        return '<h1 style="font-size:22px;font-weight:900;margin-bottom:6px;">🎬 จัดการ'.$label.'</h1><p style="color:var(--muted);font-size:12px;margin-bottom:16px;">แต่ละรายการมีหน้า Archive ของตัวเอง</p>'.
        '<div style="display:grid;grid-template-columns:1fr 1.6fr;gap:16px;"><div class="card"><h3 style="font-size:14px;font-weight:700;margin-bottom:12px;">เพิ่ม'.$label.'ใหม่</h3><form method="post" enctype="multipart/form-data"><input type="hidden" name="action" value="add_tax"><input type="hidden" name="tax_type" value="'.$type.'">'.
        '<div class="form-group"><label>ชื่อ *</label><input name="name" placeholder="ชื่อ'.$label.'"></div>'.$extra.
        '<button class="btn btn-primary" style="width:100%;" type="submit">+ เพิ่ม</button></form></div>'.
        '<div class="card"><div style="overflow-x:auto;"><table><thead><tr><th>ชื่อ/Slug</th><th>คำอธิบาย</th><th>ใช้ใน</th><th style="width:80px;">จัดการ</th></tr></thead><tbody>'.($list?:'<tr><td colspan="4" style="text-align:center;color:var(--muted);">ยังไม่มีข้อมูล</td></tr>').'</tbody></table></div></div></div>';
    }
    if ($page==='media') {
        $imgs=[];
        $uploadDirectory=__DIR__.'/../uploads';
        if (is_dir($uploadDirectory)) {
            foreach (glob($uploadDirectory.'/*.{jpg,jpeg,png,gif,webp}',GLOB_BRACE)?:[] as $file) {
                $basePath=str_replace('\\','/',dirname(dirname(isset($_SERVER['SCRIPT_NAME'])?$_SERVER['SCRIPT_NAME']:'/pinkcms-php/admin/index.php')));
                $imgs[]=['src'=>rtrim($basePath,'/').'/uploads/'.rawurlencode(basename($file)),'from'=>'คลังรูปภาพ','type'=>'อัปโหลด'];
            }
        }
        foreach($posts as $p){if($p['cover'])$imgs[]=['src'=>$p['cover'],'from'=>$p['title'],'type'=>'ปก'];foreach(json_decode($p['gallery'],true)?:[] as $g)$imgs[]=['src'=>$g,'from'=>$p['title'],'type'=>'แกลเลอรี่'];}
        $banners=[];$res=mysqli_query($conn,"SELECT * FROM banners");while($r=mysqli_fetch_assoc($res))$banners[]=$r;
        foreach($banners as $b)if($b['image'])$imgs[]=['src'=>$b['image'],'from'=>$b['title'],'type'=>'แบนเนอร์'];
        return '<h1 style="font-size:22px;font-weight:900;margin-bottom:6px;">🖼️ สื่อ/แกลเลอรี่ทั้งหมด</h1><p style="color:var(--muted);font-size:12px;margin-bottom:16px;">รวมรูปภาพทุกภาพที่ใช้ในระบบ ('.count($imgs).' รูป)</p><div class="card mb"><div class="section-title">เพิ่มรูปเข้า Media Library</div><form method="post" enctype="multipart/form-data"><input type="hidden" name="action" value="upload_media"><div class="form-row"><div class="form-group"><label>เลือกรูปภาพ (JPG, PNG, GIF, WebP ไม่เกิน 5 MB)</label><input type="file" name="mediaUpload" accept="image/jpeg,image/png,image/gif,image/webp" required></div><div class="form-group" style="display:flex;align-items:flex-end;"><button class="btn btn-primary" type="submit">⬆️ อัปโหลดรูป</button></div></div></form></div>'.
        (count($imgs)?'<div class="gallery-grid">'.implode('',array_map(function($im){return '<div class="gallery-item" onclick="openLightbox(\''.esc($im['src']).'\')" title="'.esc($im['from']).' ('.$im['type'].')"><img src="'.esc($im['src']).'" loading="lazy" onerror="this.parentElement.style.display=\'none\'"><span style="position:absolute;bottom:0;left:0;right:0;background:rgba(0,0,0,.7);font-size:9px;padding:2px 6px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">'.$im['type'].'</span></div>';},$imgs)).'</div>':'<div class="card" style="text-align:center;color:var(--muted);padding:40px;">ยังไม่มีรูปภาพ</div>');
    }
    if ($page==='pages') {
        $pages=[];$res=mysqli_query($conn,"SELECT * FROM pages");while($r=mysqli_fetch_assoc($res))$pages[]=$r;
        $editId=isset($_GET['edit'])?intval($_GET['edit']):0;
        $editPg=$editId?mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM pages WHERE id=$editId")):null;
        $h='<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:10px;"><h1 style="font-size:22px;font-weight:900;">📄 จัดการหน้าเว็บ</h1><a class="btn btn-primary" href="index.php?page=pages&edit=0">+ เพิ่มหน้าใหม่</a></div>';
        if ($editId!==0 || isset($_GET['edit'])) {
            $pg=$editPg?:['id'=>0,'title'=>'','slug'=>'','content'=>'<p>เนื้อหา</p>','status'=>'publish'];
            $h.='<div class="card mb"><h3 style="font-size:15px;font-weight:700;margin-bottom:14px;">'.($pg['id']?'แก้ไขหน้า: '.esc($pg['title']):'เพิ่มหน้าใหม่').'</h3><form method="post"><input type="hidden" name="action" value="save_page"><input type="hidden" name="id" value="'.$pg['id'].'">'.
            '<div class="form-row"><div class="form-group"><label>ชื่อหน้า</label><input name="title" value="'.esc($pg['title']).'"></div>'.
            '<div class="form-group"><label>Slug</label><input name="slug" value="'.esc($pg['slug']).'"></div></div>'.
            '<div class="form-group"><label>เนื้อหา (HTML)</label><textarea name="content" rows="6">'.esc($pg['content']).'</textarea></div>'.
            '<div class="form-group"><label>สถานะ</label><select name="status"><option value="publish"'.($pg['status']==='publish'?' selected':'').'>เผยแพร่</option><option value="draft"'.($pg['status']==='draft'?' selected':'').'>ซ่อน</option></select></div>'.
            '<button class="btn btn-primary btn-sm" type="submit">💾 บันทึก</button> <a class="btn btn-ghost btn-sm" href="index.php?page=pages">ยกเลิก</a></form></div>';
        }
        $h.='<div class="card"><div style="overflow-x:auto;"><table><thead><tr><th>ชื่อหน้า</th><th>Slug (URL)</th><th>สถานะ</th><th>วันที่</th><th style="width:180px;">จัดการ</th></tr></thead><tbody>'.
        implode('',array_map(function($pg){return '<tr><td style="font-weight:600;">'.esc($pg['title']).'</td><td style="font-size:11px;color:var(--muted);">index.php?page=page&slug='.esc($pg['slug']).'</td><td><span class="post-status '.($pg['status']==='publish'?'is-published':'is-draft').'">'.($pg['status']==='publish'?'เผยแพร่':'ซ่อน').'</span></td><td>'.fmtDate($pg['date']).'</td><td><a class="btn btn-ghost btn-sm" style="margin-right:4px;" href="index.php?page=pages&edit='.$pg['id'].'">แก้ไข</a><form method="post" style="display:inline;"><input type="hidden" name="action" value="delete_page"><input type="hidden" name="id" value="'.$pg['id'].'"><button class="btn btn-danger btn-sm" onclick="return confirm(\'ลบหน้านี้?\')">ลบ</button></form></td></tr>';},$pages)).
        '</tbody></table></div></div><p style="font-size:11px;color:var(--muted);margin-top:10px;">หน้าที่เผยแพร่จะแสดงลิงก์ใน Footer ของหน้าบ้านอัตโนมัติ</p>';
        return $h;
    }
    if ($page==='banners') {
        $banners=[];$res=mysqli_query($conn,"SELECT * FROM banners");while($r=mysqli_fetch_assoc($res))$banners[]=$r;
        $editId=isset($_GET['edit'])?intval($_GET['edit']):0;
        $editB=$editId?mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM banners WHERE id=$editId")):null;
        $h='<h1 style="font-size:22px;font-weight:900;margin-bottom:6px;">📢 แบนเนอร์ &amp; ประกาศ</h1><p style="color:var(--muted);font-size:12px;margin-bottom:16px;">แบนเนอร์แสดงเป็น Slideshow บนหน้าแรก ประกาศแสดงเป็นแถบเลื่อนบนสุด (ตั้งค่าใน ⚙️ ตั้งค่าระบบ)</p>';
        if ($editId!==0 || isset($_GET['edit'])) {
            $b=$editB?:['id'=>0,'title'=>'','link'=>'index.php','image'=>'','text'=>'','active'=>1];
            $h.='<div class="card mb"><h3 style="font-size:15px;font-weight:700;margin-bottom:14px;">'.($b['id']?'แก้ไขแบนเนอร์':'เพิ่มแบนเนอร์ใหม่').'</h3><form method="post" enctype="multipart/form-data"><input type="hidden" name="action" value="save_banner"><input type="hidden" name="id" value="'.$b['id'].'">'.
            '<div class="form-row"><div class="form-group"><label>ชื่อแบนเนอร์</label><input name="title" value="'.esc($b['title']).'"></div>'.
            '<div class="form-group"><label>ลิงก์ (คลิกแล้วไปไหน)</label><input name="link" value="'.esc($b['link']).'"></div></div>'.
            '<div class="form-row"><div class="form-group"><label>รูปภาพ: ใส่ลิงก์หรือเลือกไฟล์</label><input name="image" value="'.esc($b['image']).'" placeholder="https://..."><input type="file" name="bannerImageUpload" accept="image/jpeg,image/png,image/gif,image/webp" style="margin-top:8px;"><small style="color:var(--muted);font-size:10px;">JPG, PNG, GIF, WebP · ไม่เกิน 5 MB</small></div>'.
            '<div class="form-group"><label>ข้อความรอง</label><input name="text" value="'.esc($b['text']).'"></div></div>'.
            '<div class="toggle-row"><span>แสดงแบนเนอร์นี้</span><label class="switch"><input type="checkbox" name="active" '.($b['active']?'checked':'').'><span class="slider"></span></label></div>'.
            '<button class="btn btn-primary btn-sm" type="submit" style="margin-top:12px;">💾 บันทึก</button> <a class="btn btn-ghost btn-sm" href="index.php?page=banners">ยกเลิก</a></form></div>';
        }
        $h.='<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:8px;"><h2 style="font-size:16px;font-weight:700;">รายการแบนเนอร์</h2><a class="btn btn-primary btn-sm" href="index.php?page=banners&edit=0">+ เพิ่มแบนเนอร์</a></div>'.
        '<div class="grid" style="grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:10px;">'.
        implode('',array_map(function($b){return '<div class="card" style="padding:14px;"><div style="font-weight:700;font-size:13px;margin-bottom:4px;">'.esc($b['title']).'</div><div style="font-size:11px;color:var(--muted);margin-bottom:8px;">'.esc($b['text']).'</div><span class="post-status '.($b['active']?'is-published':'is-draft').'">'.($b['active']?'แสดง':'ซ่อน').'</span><div style="display:flex;gap:6px;margin-top:10px;"><a class="btn btn-ghost btn-sm" style="flex:1;" href="index.php?page=banners&edit='.$b['id'].'">แก้ไข</a><form method="post" style="display:inline;"><input type="hidden" name="action" value="delete_banner"><input type="hidden" name="id" value="'.$b['id'].'"><button class="btn btn-danger btn-sm" onclick="return confirm(\'ลบแบนเนอร์นี้?\')">ลบ</button></form></div></div>';},$banners)).'</div>';
        return $h;
    }
    if ($page==='comments') {
        $cmts=[];$res=mysqli_query($conn,"SELECT * FROM comments ORDER BY id DESC");while($r=mysqli_fetch_assoc($res))$cmts[]=$r;
        return '<h1 style="font-size:22px;font-weight:900;margin-bottom:6px;">💬 จัดการคอมเมนต์</h1><p style="color:var(--muted);font-size:12px;margin-bottom:16px;">คอมเมนต์ที่ผู้ใช้ส่งจะต้องอนุมัติก่อนแสดงบนหน้าบ้าน (รออนุมัติ '.$pendingCmts.' รายการ)</p>'.
        '<div class="card"><div style="overflow-x:auto;"><table><thead><tr><th>ผู้ส่ง</th><th>คอมเมนต์</th><th>โพสต์</th><th>วันที่</th><th>สถานะ</th><th style="width:160px;">จัดการ</th></tr></thead><tbody>'.
        implode('',array_map(function($c){$p=mysqli_fetch_assoc(mysqli_query($GLOBALS['conn'],"SELECT title FROM posts WHERE id=".$c['postId']));return '<tr><td style="font-weight:600;font-size:12px;">'.esc($c['name']).'</td><td style="max-width:200px;font-size:12px;">'.esc($c['text']).'</td><td style="font-size:11px;color:var(--muted);">'.esc($p?$p['title']:'-').'</td><td>'.fmtDate($c['date']).'</td><td><span class="post-status '.($c['approved']?'is-published':'is-pending').'">'.($c['approved']?'อนุมัติแล้ว':'รออนุมัติ').'</span></td><td><form method="post" style="display:inline;"><input type="hidden" name="action" value="toggle_cmt"><input type="hidden" name="id" value="'.$c['id'].'"><button class="btn '.($c['approved']?'btn-ghost':'btn-primary').' btn-sm" style="margin-right:4px;">'.($c['approved']?'ซ่อน':'อนุมัติ').'</button></form><form method="post" style="display:inline;"><input type="hidden" name="action" value="delete_cmt"><input type="hidden" name="id" value="'.$c['id'].'"><button class="btn btn-danger btn-sm" onclick="return confirm(\'ลบคอมเมนต์นี้?\')">ลบ</button></form></td></tr>';},$cmts)).
        '</tbody></table></div></div>';
    }
    if ($page==='users') {
        $u=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM users WHERE username='admin'"));
        return '<h1 style="font-size:22px;font-weight:900;margin-bottom:16px;">👥 ผู้ใช้งานระบบ</h1>'.
        '<div class="card"><div style="overflow-x:auto;"><table><thead><tr><th>ชื่อผู้ใช้</th><th>บทบาท</th><th>เข้าสู่ระบบล่าสุด</th></tr></thead><tbody>'.
        '<tr><td style="font-weight:600;">'.esc($u['username']).'</td><td><span class="badge badge-purple">'.esc($u['role']).'</span></td><td>'.fmtTime($u['lastLogin']).'</td></tr>'.
        '</tbody></table></div></div><p style="font-size:11px;color:var(--muted);margin-top:10px;">💡 เปลี่ยนรหัสผ่านได้ที่เมนู ⚙️ ตั้งค่าระบบ</p>';
    }
    if ($page==='reports') {
        $topPosts=array_slice(array_values(array_sort($posts,'views')),0,10);
        $studioViews=[];foreach($studios as $s)$studioViews[$s['id']]=['name'=>$s['name'],'views'=>0,'count'=>0];
        foreach(publishedPosts() as $p)if(isset($studioViews[$p['studioId']])){$studioViews[$p['studioId']]['views']+=$p['views'];$studioViews[$p['studioId']]['count']++;}
        $actorViews=[];foreach($actors as $a)$actorViews[$a['id']]=['name'=>$a['name'],'views'=>0];
        foreach(publishedPosts() as $p)foreach(json_decode($p['actorIds'],true)?:[] as $aid)if(isset($actorViews[$aid]))$actorViews[$aid]['views']+=$p['views'];
        uasort($actorViews,function($a,$b){return $b['views']-$a['views'];});
        return '<h1 style="font-size:22px;font-weight:900;margin-bottom:20px;">📈 รายงาน &amp; สถิติ</h1>'.
        '<div class="card mb"><div class="section-title">🏆 โพสต์ยอดนิยม 10 อันดับ</div><div style="overflow-x:auto;"><table><thead><tr><th>อันดับ</th><th>ชื่อโพสต์</th><th>ค่าย</th><th>ยอดวิว</th></tr></thead><tbody>'.
        implode('',array_map(function($p,$i){$rc=$i===0?'var(--gold)':($i===1?'var(--muted)':($i===2?'#cd7f32':'var(--muted)'));return '<tr><td><span style="font-weight:900;color:'.$rc.';font-size:16px;">'.($i+1).'</span></td><td style="font-weight:600;">'.esc($p['title']).'</td><td>'.esc(getStudio($p['studioId'])['name']).'</td><td style="color:var(--accent);font-weight:700;">'.number_format($p['views']).'</td></tr>';},$topPosts,array_keys($topPosts))).
        '</tbody></table></div></div>'.
        '<div class="grid reports-taxonomy-grid" style="grid-template-columns:1fr 1fr;gap:12px;">'.
        '<div class="card"><div class="section-title">🎬 ยอดวิวแยกตามค่าย</div><table><thead><tr><th>ค่าย</th><th>จำนวนโพสต์</th><th>ยอดวิว</th></tr></thead><tbody>'.
        implode('',array_map(function($s){return '<tr><td>'.esc($s['name']).'</td><td>'.$s['count'].'</td><td style="color:var(--accent);font-weight:700;">'.number_format($s['views']).'</td></tr>';},$studioViews)).'</tbody></table></div>'.
        '<div class="card"><div class="section-title">👤 ยอดวิวแยกตามนักแสดง</div><table><thead><tr><th>นักแสดง</th><th>ยอดวิวรวม</th></tr></thead><tbody>'.
        implode('',array_map(function($a){return '<tr><td>'.esc($a['name']).'</td><td style="color:var(--accent2);font-weight:700;">'.number_format($a['views']).'</td></tr>';},$actorViews)).'</tbody></table></div></div>';
    }
    if ($page==='site-content') {
        $categoryNames=[];
        foreach($posts as $post) {
            if($post['status']!=='publish')continue;
            $names=preg_split('/[,،;|，；\r\n]+/u',isset($post['category'])?$post['category']:'');
            foreach($names?:[] as $name)if(trim($name)!=='')$categoryNames[trim($name)]=true;
            foreach(json_decode(isset($post['tagIds'])?$post['tagIds']:'[]',true)?:[] as $tagId) {
                foreach($tags as $tag)if((int)$tag['id']===(int)$tagId)$categoryNames[$tag['name']]=true;
            }
        }
        uksort($categoryNames,'strnatcasecmp');
        $categoryOptions=[];$usedSlugs=[];
        foreach($categoryNames as $name=>$unused) {
            $base=slugify($name);$categorySlug=$base;$suffix=2;
            while(isset($usedSlugs[$categorySlug]))$categorySlug=$base.'-'.$suffix++;
            $usedSlugs[$categorySlug]=true;$categoryOptions[$categorySlug]=$name;
        }
        $menuTypes=[
            'home'=>'หน้าหลัก','all'=>'เรื่องทั้งหมด','categories'=>'หน้ารวมหมวดหมู่',
            'studio_list'=>'รายชื่อค่าย','actor_list'=>'รายชื่อนักแสดง','tag_list'=>'รายการแท็ก'
        ];
        $menuGroups=[
            'header'=>['label'=>'เมนูหลักบน Header','count'=>6,'defaults'=>[
                ['label'=>'นักแสดง','type'=>'actor_list','value'=>''],['label'=>'ค่าย','type'=>'studio_list','value'=>''],
                ['label'=>'รหัสเรื่อง','type'=>'all','value'=>''],['label'=>'บทความ','type'=>'categories','value'=>'']
            ]],
            'explore'=>['label'=>'แถบสำรวจเนื้อหา','count'=>6,'defaults'=>[
                ['label'=>'เรื่องทั้งหมด','type'=>'all','value'=>''],['label'=>'หมวดหมู่','type'=>'categories','value'=>'']
            ]],
            'bottom'=>['label'=>'เมนูล่าง','count'=>4,'defaults'=>array_map(function($index,$label){$types=['home','studio_list','actor_list','tag_list'];return ['label'=>$label,'type'=>$types[$index],'value'=>''];},[0,1,2,3],json_decode(setting('theme_navLabels','["หน้าหลัก","ค่าย","นักแสดง","แท็ก"]'),true)?:['หน้าหลัก','ค่าย','นักแสดง','แท็ก'])]
        ];
        $menuHtml='';
        foreach($menuGroups as $menuName=>$group) {
            $stored=json_decode(setting('site_menu_'.$menuName,''),true);
            if(!is_array($stored))$stored=$group['defaults'];
            $menuHtml.='<div class="card mb"><div class="section-title">'.esc($group['label']).'</div><p style="font-size:11px;color:var(--muted);margin-bottom:10px;">เว้นรายการว่างเพื่อไม่แสดงเมนู · เลือกหมวด/แท็กด้านล่างเพื่อเชื่อมไปหน้ารายการนั้น</p>';
            for($slot=0;$slot<$group['count'];$slot++) {
                $item=isset($stored[$slot])&&is_array($stored[$slot])?$stored[$slot]:[];
                $label=isset($item['label'])?$item['label']:'';
                $type=isset($item['type'])?$item['type']:'';
                $value=isset($item['value'])?$item['value']:'';
                $selection=$type;
                if($type==='category'||$type==='tag')$selection=$type.':'.$value;
                $destinationName='menu_'.$menuName.'_destination[]';
                $labelName='menu_'.$menuName.'_label[]';
                $customName='menu_'.$menuName.'_custom[]';
                $options='<option value="">— ไม่ใช้เมนูนี้ —</option>';
                foreach($menuTypes as $menuType=>$menuLabel)$options.='<option value="'.esc($menuType).'"'.($selection===$menuType?' selected':'').'>'.esc($menuLabel).'</option>';
                foreach($categoryOptions as $categorySlug=>$categoryName)$options.='<option value="category:'.esc($categorySlug).'"'.($selection==='category:'.$categorySlug?' selected':'').'>หมวดหมู่: '.esc($categoryName).'</option>';
                foreach($tags as $tag)$options.='<option value="tag:'.esc($tag['slug']).'"'.($selection==='tag:'.$tag['slug']?' selected':'').'>แท็ก: '.esc($tag['name']).'</option>';
                $options.='<option value="custom"'.($selection==='custom'?' selected':'').'>Custom URL</option>';
                $customUrl=$type==='custom'?$value:'';
                $menuHtml.='<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,190px),1fr));gap:8px;padding:10px 0;border-top:1px solid var(--border);align-items:end;"><div class="form-group" style="margin:0;"><label>ชื่อเมนู '.($slot+1).'</label><input name="'.esc($labelName).'" value="'.esc($label).'" maxlength="60" placeholder="เช่น หนังใหม่"></div><div class="form-group" style="margin:0;"><label>ปลายทาง</label><select name="'.esc($destinationName).'">'.$options.'</select></div><div class="form-group" style="margin:0;"><label>URL (ใช้เมื่อเลือก Custom)</label><input name="'.esc($customName).'" value="'.esc($customUrl).'" placeholder="https://example.com หรือ index.php?page=all"></div></div>';
            }
            $menuHtml.='</div>';
        }
        $contentFields=[
            ['homeFeaturedTitle','site_home_featured_title','หัวข้อการ์ดสไลด์แนะนำ','แนะนำ'],
            ['homeFeaturedAllLabel','site_home_featured_all_label','ข้อความปุ่มดูทั้งหมด','ดูทั้งหมด'],
            ['homeLatestTitle','site_home_latest_title','หัวข้อรายการล่าสุดหน้าแรก','โพสต์ล่าสุด'],
            ['bannerAccessibility','site_banner_accessibility','ชื่อส่วนสไลด์แบนเนอร์','บทความรหัสหนังใหม่'],
            ['siteAdLabel','site_ad_label','ป้ายกำกับโฆษณา','โฆษณา'],
            ['siteAdCloseLabel','site_ad_close_label','ข้อความปุ่มปิดโฆษณาในวิดีโอ','ปิดโฆษณา'],
            ['siteSearchPlaceholder','site_search_placeholder','ข้อความตัวอย่างช่องค้นหา','ค้นหา...'],
            ['siteSearchButton','site_search_button','ข้อความปุ่มค้นหา','ค้นหา']
        ];
        $contentInputs='';
        foreach($contentFields as $field)$contentInputs.='<div class="form-group"><label>'.esc($field[2]).'</label><input name="'.esc($field[0]).'" maxlength="120" value="'.esc(setting($field[1],$field[3])).'"></div>';
        $featuredLimit=(int)setting('site_home_featured_limit','12');
        if(!in_array($featuredLimit,[4,6,8,10,12,16,20,24],true))$featuredLimit=12;
        $bannerInterval=(int)setting('site_banner_interval_seconds','3');
        if($bannerInterval<3||$bannerInterval>30)$bannerInterval=3;
        $featuredInterval=(int)setting('site_home_featured_interval_seconds','6');
        if($featuredInterval<3||$featuredInterval>30)$featuredInterval=6;
        $selectField=function($name,$label,$value,$options) {
            $html='<div class="form-group"><label>'.esc($label).'</label><select name="'.esc($name).'">';
            foreach($options as $optionValue=>$optionLabel)$html.='<option value="'.esc((string)$optionValue).'"'.((string)$optionValue===(string)$value?' selected':'').'>'.esc($optionLabel).'</option>';
            return $html.'</select></div>';
        };
        $contentInputs.='<div class="form-row">'.$selectField('homeFeaturedLimit','จำนวนการ์ดในสไลด์แนะนำ',$featuredLimit,[4=>'4',6=>'6',8=>'8',10=>'10',12=>'12',16=>'16',20=>'20',24=>'24']).$selectField('bannerInterval','เวลาสลับแบนเนอร์ (วินาที)',$bannerInterval,array_combine(range(3,15),range(3,15))).'</div>';
        $contentInputs.='<div class="form-row">'.$selectField('featuredInterval','เวลาสไลด์การ์ดแนะนำ (วินาที)',$featuredInterval,array_combine(range(3,30),range(3,30))).'</div>';
        return '<h1 style="font-size:22px;font-weight:900;margin-bottom:8px;">🧩 เนื้อหาเว็บ &amp; เมนู</h1><p style="color:var(--muted);font-size:12px;margin-bottom:18px;">ปรับข้อความหน้าแรก เมนู และป้ายโฆษณาได้ที่นี่ แยกจากการตั้งค่า SEO หลักของเว็บไซต์ · ตั้งค่าลิงก์และภาพโฆษณาที่ <a href="index.php?page=settings#video-ad-settings" style="color:var(--accent);">ตั้งค่าระบบ → โฆษณารอบวิดีโอ</a></p>'.
        '<form method="post"><input type="hidden" name="action" value="save_site_content">'.
        '<div class="card mb"><div class="section-title">หัวข้อและป้ายบนหน้าเว็บ</div>'.$contentInputs.'</div>'.
        $menuHtml.
        '<button class="btn btn-primary" type="submit">💾 บันทึกเนื้อหาและเมนู</button> <a class="btn btn-ghost" href="../index.php" target="_blank" rel="noopener">👁 ดูหน้าบ้าน</a></form>';
    }
    if ($page==='theme') {
        $toggles=['showViews'=>'แสดงยอดวิว (Views)','showStudio'=>'แสดงชื่อค่ายบนการ์ด','showTags'=>'แสดงแท็กบนการ์ด','showActors'=>'แสดงนักแสดงบนการ์ด','showDuration'=>'แสดงความยาววิดีโอบนการ์ด','showBottomNav'=>'แสดงเมนูล่าง (Bottom Nav)','showSearch'=>'แสดงแถบค้นหาบน Header','showRelated'=>'แสดงโพสต์ที่เกี่ยวข้องใต้โพสต์','showBanners'=>'แสดงแบนเนอร์ Slideshow บนหน้าแรก','showAnnouncement'=>'แสดงแถบประกาศเลื่อนบนสุด'];
        return '<h1 style="font-size:22px;font-weight:900;margin-bottom:20px;">🎨 จัดการสไตล์ &amp; เมนูหน้าเว็บ</h1>'.
        '<form method="post"><input type="hidden" name="action" value="save_theme">'.
        '<div class="card mb"><div class="section-title">ข้อมูลแบรนด์</div>'.
        '<div class="form-row"><div class="form-group"><label>ชื่อโลโก้ (2 คำ คั่นด้วยช่องว่าง คำที่ 2 จะเป็นสี accent)</label><input name="logoText" value="'.esc(setting('logoText','PINK CMS')).'"></div>'.
        '<div class="form-group"><label>สี Accent</label><div style="display:flex;gap:8px;"><input type="color" name="accent" value="'.esc(setting('theme_accent','#ff2d78')).'" style="width:60px;padding:4px;height:42px;"><input name="accent_txt" value="'.esc(setting('theme_accent','#ff2d78')).'" style="flex:1;"></div></div></div></div>'.
        '<div class="card mb"><div class="section-title">เปิด / ปิด ไอเทมบนหน้าเว็บ</div>'.
        implode('',array_map(function($k,$label){$on=setting('theme_'.$k,'1')==='1';return '<div class="toggle-row"><span>'.$label.'</span><label class="switch"><input type="checkbox" name="'.$k.'" '.($on?'checked':'').'><span class="slider"></span></label></div>';},array_keys($toggles),$toggles)).'</div>'.
        '<div class="card mb"><div class="section-title">ตั้งค่าเมนู</div><p style="color:var(--muted);font-size:12px;">ตั้งชื่อ ปลายทาง หมวดหมู่ แท็ก และลิงก์ Custom ได้ที่ <a href="index.php?page=site-content" style="color:var(--accent);">เนื้อหาเว็บ &amp; เมนู</a></p></div>'.
        '<button class="btn btn-primary" type="submit">💾 บันทึกสไตล์</button> <a class="btn btn-ghost" href="../index.php" target="_blank">👁 ดูหน้าบ้าน</a></form>';
    }
    if ($page==='seo') {
        $host=isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'localhost';
        if (!preg_match('/^[a-z0-9.-]+(?::[0-9]{1,5})?$/i',$host)) $host='localhost';
        $scheme=(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off')?'https':'http';
        $basePath=str_replace('\\','/',dirname(dirname(isset($_SERVER['SCRIPT_NAME'])?$_SERVER['SCRIPT_NAME']:'/pinkcms-php/admin/index.php')));
        $siteBase=$scheme.'://'.$host.rtrim($basePath,'/');
        $allPages=[];$res=mysqli_query($conn,"SELECT * FROM pages ORDER BY title ASC");while($r=mysqli_fetch_assoc($res))$allPages[]=$r;
        $publishedPages=array_values(array_filter($allPages,function($pg){return $pg['status']==='publish';}));
        $publishedPosts=array_values(array_filter($posts,function($post){return $post['status']==='publish';}));
        $publishedCount=count($publishedPosts)+count($publishedPages);
        $publishedPostCount=count($publishedPosts);
        $metadataReady=count(array_filter($publishedPosts,function($post){return !empty($post['metaTitle']) && !empty($post['metaDesc']);}));
        $metadataCoverage=$publishedPostCount?round($metadataReady/$publishedPostCount*100):0;
        $videoPosts=array_values(array_filter($publishedPosts,function($post){return !empty($post['videoUrl']);}));
        $videoSchemaReady=count(array_filter($videoPosts,function($post){
            $source=parseVideo($post['videoUrl']);
            $thumbnail=!empty($post['cover'])?$post['cover']:$source['thumb'];
            return $source['error']==='' && ($source['embedUrl']!=='' || $source['contentUrl']!=='') && $thumbnail!=='' && validImageReference($thumbnail);
        }));
        $videoSchemaCoverage=count($videoPosts)?round($videoSchemaReady/count($videoPosts)*100):100;
        $siteTitle=trim(setting('siteName',''));
        $siteDescription=trim(setting('siteDesc',''));
        $primaryKeyword=trim(setting('seo_primary_keyword',''));
        $secondaryKeywords=trim(setting('seo_secondary_keywords',''));
        $seoScore=0;
        $seoScore+=($siteTitle!=='' && mb_strlen($siteTitle,'UTF-8')<=60)?15:0;
        $seoScore+=($siteDescription!=='' && mb_strlen($siteDescription,'UTF-8')>=50 && mb_strlen($siteDescription,'UTF-8')<=160)?15:0;
        $seoScore+=$primaryKeyword!==''?15:0;
        $seoScore+=$secondaryKeywords!==''?10:0;
        $seoScore+=$metadataCoverage>=80?15:($metadataCoverage>=50?10:($metadataCoverage>0?5:0));
        $seoScore+=$videoSchemaCoverage>=80?15:($videoSchemaCoverage>=50?10:($videoSchemaCoverage>0?5:0));
        $seoScore+=$publishedCount>0?15:0;
        $seoLabel=$seoScore>=80?'พร้อมดี':($seoScore>=50?'กำลังปรับปรุง':'ควรตั้งค่าเพิ่มเติม');
        $seoChecks=[
            [$siteTitle!=='' && mb_strlen($siteTitle,'UTF-8')<=60,'ไตเติลเว็บไซต์ (ไม่เกิน 60 ตัวอักษร)'],
            [$siteDescription!=='' && mb_strlen($siteDescription,'UTF-8')>=50 && mb_strlen($siteDescription,'UTF-8')<=160,'Meta Description (50–160 ตัวอักษร)'],
            [$primaryKeyword!=='','คีย์เวิร์ดหลัก'],
            [$secondaryKeywords!=='','คีย์เวิร์ดรอง'],
            [$metadataCoverage>=80,'Meta ครบ '.$metadataCoverage.'% ของโพสต์ที่เผยแพร่'],
            [$videoSchemaCoverage>=80,'VideoObject พร้อมรูปปก '.$videoSchemaReady.'/'.count($videoPosts).' โพสต์'],
            [$publishedCount>0,'มี '.$publishedCount.' หน้าที่เผยแพร่และอยู่ใน Sitemap']
        ];
        $sitemapUrls=[];
        $sitemapUrls[]=$siteBase.'/?page=all';
        $sitemapUrls[]=$siteBase.'/?page=categories';
        $tagNames=[];foreach($tags as $tag)$tagNames[(int)$tag['id']]=$tag['name'];
        $categoryNames=[];
        foreach($publishedPosts as $post) {
            $names=preg_split('/[,،;|，；\r\n]+/u',isset($post['category'])?$post['category']:'');
            foreach($names?:[] as $name)if(trim($name)!=='')$categoryNames['category:'.trim($name)]=true;
            foreach(json_decode(isset($post['tagIds'])?$post['tagIds']:'[]',true)?:[] as $tagId) {
                $tagId=(int)$tagId;if(isset($tagNames[$tagId]))$categoryNames['category:'.$tagNames[$tagId]]=true;
            }
        }
        uksort($categoryNames,function($a,$b){return strnatcasecmp(substr($a,9),substr($b,9));});
        $usedCategorySlugs=[];
        foreach($categoryNames as $categoryKey=>$unused) {
            $categoryName=substr($categoryKey,9);
            $baseSlug=slugify($categoryName);$categorySlug=$baseSlug;$suffix=2;
            while(isset($usedCategorySlugs[$categorySlug]))$categorySlug=$baseSlug.'-'.$suffix++;
            $usedCategorySlugs[$categorySlug]=true;
            $sitemapUrls[]=$siteBase.'/?page=category&slug='.rawurlencode($categorySlug);
        }
        foreach($publishedPosts as $post)$sitemapUrls[]=$siteBase.'/?page=post&slug='.rawurlencode($post['slug']);
        foreach($publishedPages as $pg)$sitemapUrls[]=$siteBase.'/?page=page&slug='.rawurlencode($pg['slug']);
        $statusRows='';
        foreach($posts as $post)$statusRows.='<tr><td>โพสต์</td><td>'.esc($post['title']).'</td><td><span class="post-status '.($post['status']==='publish'?'is-published':'is-draft').'">'.($post['status']==='publish'?'เผยแพร่':'ฉบับร่าง').'</span></td><td>'.(!empty($post['metaTitle']) && !empty($post['metaDesc'])?'ครบ':'ต้องตรวจ').'</td></tr>';
        foreach($allPages as $pg)$statusRows.='<tr><td>หน้า</td><td>'.esc($pg['title']).'</td><td><span class="post-status '.($pg['status']==='publish'?'is-published':'is-draft').'">'.($pg['status']==='publish'?'เผยแพร่':'ฉบับร่าง').'</span></td><td>'.($pg['status']==='publish'?'รวมในสถานะเผยแพร่':'ยังไม่เผยแพร่').'</td></tr>';
        return '<h1 style="font-size:22px;font-weight:900;margin-bottom:20px;">🔍 ตั้งค่า SEO ทั่วไป</h1>'.
        '<form method="post"><input type="hidden" name="action" value="save_seo">'.
        '<div class="seo-status"><div class="seo-status-head"><div><div style="font-size:16px;font-weight:800;">สถานะ SEO โดยรวม</div><div style="font-size:12px;color:var(--muted);">ประเมินจากข้อมูลตั้งค่าและหน้าที่เผยแพร่จริง</div></div><div class="seo-score">'.(int)$seoScore.'<span style="font-size:14px;color:var(--muted);"> / 100</span></div></div><div class="seo-meter"><span style="width:'.(int)$seoScore.'%;"></span></div><div style="margin-top:8px;font-size:13px;font-weight:700;color:'.($seoScore>=80?'var(--success)':($seoScore>=50?'var(--gold)':'var(--danger)')).';">'.esc($seoLabel).'</div><div class="seo-checks">'.implode('',array_map(function($check){return '<div class="seo-check" style="color:'.($check[0]?'var(--success)':'var(--muted)').';">'.($check[0]?'✓':'○').' '.esc($check[1]).'</div>';},$seoChecks)).'</div></div>'.
        '<div class="card mb"><div class="section-title">SEO เว็บไซต์</div>'.
        '<div class="form-row"><div class="form-group"><label>ไตเติลเว็บไซต์</label><input name="siteName" maxlength="200" value="'.esc($siteTitle).'" placeholder="ชื่อเว็บไซต์"></div>'.
        '<div class="form-group"><label>Meta Description เว็บไซต์</label><input name="siteDesc" maxlength="300" value="'.esc($siteDescription).'" placeholder="คำอธิบายเว็บไซต์สำหรับผลการค้นหา"></div></div>'.
        '<div class="form-row"><div class="form-group"><label>คีย์เวิร์ดหลัก</label><input name="primaryKeyword" value="'.esc($primaryKeyword).'" placeholder="คำค้นหลัก"></div>'.
        '<div class="form-group"><label>คีย์เวิร์ดรอง (คั่นด้วย ,)</label><input name="secondaryKeywords" value="'.esc($secondaryKeywords).'" placeholder="คำค้นรอง, คำค้นเพิ่มเติม"></div></div>'.
        '<div class="form-group"><label>Keywords เริ่มต้น (คั่นด้วย ,)</label><input name="keywords" value="'.esc(setting('seo_keywords','')).'"></div>'.
        '<div class="form-group"><label>Google Analytics / Tag ID (G-XXXXXXXXXX)</label><input name="gaId" value="'.esc(setting('seo_gaId','')).'" placeholder="G-XXXXXXXXXX"></div></div>'.
        '<div class="card mb"><div class="section-title">robots.txt (copy ไปวางที่ hosting)</div><textarea name="robots" rows="5" style="font-family:monospace;font-size:12px;">'.esc(setting('seo_robots','User-agent: *\nAllow: /')).'</textarea></div>'.
        '<button class="btn btn-primary" type="submit">💾 บันทึกตั้งค่า SEO</button></form>'.
        '<div class="card mt"><div class="section-title">สถานะหน้าเว็บทั้งหมด</div><div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(130px,1fr));margin-bottom:14px;"><div class="stat-card"><div class="stat-num">'.(count($posts)+count($allPages)).'</div><div class="stat-label">หน้าทั้งหมด</div></div><div class="stat-card"><div class="stat-num" style="color:var(--success);">'.$publishedCount.'</div><div class="stat-label">เผยแพร่</div></div><div class="stat-card"><div class="stat-num" style="color:var(--gold);">'.(count($posts)+count($allPages)-$publishedCount).'</div><div class="stat-label">ฉบับร่าง</div></div><div class="stat-card"><div class="stat-num" style="color:var(--accent2);">'.$metadataReady.'</div><div class="stat-label">Meta ครบ (โพสต์)</div></div></div><div style="overflow-x:auto;"><table><thead><tr><th>ชนิด</th><th>ชื่อ</th><th>สถานะ</th><th>SEO Meta</th></tr></thead><tbody>'.$statusRows.'</tbody></table></div></div>'.
        '<div class="card mt"><div class="section-title">Sitemap URL ('.count($sitemapUrls).' URL)</div><div style="background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:12px;font-family:monospace;font-size:11px;max-height:180px;overflow:auto;color:var(--muted);">'.implode('<br>',array_map('esc',$sitemapUrls)).'</div></div>';
    }
    if ($page==='settings') {
        $recentAct=[];$res=mysqli_query($conn,"SELECT * FROM activity ORDER BY time DESC LIMIT 50");while($r=mysqli_fetch_assoc($res))$recentAct[]=$r;
        $videoAdSlots=json_decode(setting('video_ad_slots','{}'),true);
        if(!is_array($videoAdSlots))$videoAdSlots=[];
        $videoAdsHtml='<div class="card mb" id="video-ad-settings"><div class="section-title">โฆษณารอบวิดีโอ</div><p style="font-size:12px;color:var(--muted);line-height:1.7;margin-bottom:14px;">ใส่เฉพาะ URL ปลายทางและ URL รูปภาพ ระบบจะสร้างป้ายโฆษณาให้อัตโนมัติ เว้นทั้งสองช่องว่างหากไม่ใช้ช่องนั้น</p>';
        foreach(['top'=>['label'=>'ด้านบนวิดีโอ','count'=>4],'inside'=>['label'=>'โฆษณาภายในเครื่องเล่น','count'=>1],'bottom'=>['label'=>'ด้านล่างวิดีโอ','count'=>3]] as $position=>$group) {
            $videoAdsHtml.='<h3 style="font-size:14px;font-weight:700;margin:14px 0 8px;">'.$group['label'].'</h3>';
            $entries=isset($videoAdSlots[$position])&&is_array($videoAdSlots[$position])?$videoAdSlots[$position]:[];
            for($slot=0;$slot<$group['count'];$slot++) {
                $entry=isset($entries[$slot])&&is_array($entries[$slot])?$entries[$slot]:[];
                $destination=isset($entry['destination'])?$entry['destination']:'';
                $image=isset($entry['image'])?$entry['image']:'';
                $destinationKey='videoAd'.ucfirst($position).'Destination';
                $imageKey='videoAd'.ucfirst($position).'Image';
                $videoAdsHtml.='<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,220px),1fr));gap:10px;padding:12px 0;border-top:1px solid var(--border);"><div class="form-group" style="margin:0;"><label>ป้าย '.($slot+1).' — URL ปลายทาง</label><input type="url" name="'.$destinationKey.'[]" value="'.esc($destination).'" placeholder="https://example.com/" inputmode="url"></div><div class="form-group" style="margin:0;"><label>URL รูปภาพ</label><input type="text" name="'.$imageKey.'[]" value="'.esc($image).'" placeholder="https://example.com/banner.gif" inputmode="url"></div></div>';
            }
        }
        $videoAdsHtml.='</div>';
        return '<h1 style="font-size:22px;font-weight:900;margin-bottom:20px;">⚙️ ตั้งค่าระบบ &amp; สำรองข้อมูล</h1>'.
        '<form method="post"><input type="hidden" name="action" value="save_system">'.
        '<div class="card mb"><div class="section-title">ข้อมูลเว็บไซต์หลัก</div>'.
        '<div class="form-row"><div class="form-group"><label>ชื่อเว็บไซต์</label><input name="siteName" value="'.esc(setting('siteName','PINKCMS')).'"></div>'.
        '<div class="form-group"><label>คำอธิบายเว็บ</label><input name="siteDesc" value="'.esc(setting('siteDesc','')).'"></div></div>'.
        '<div class="form-row"><div class="form-group"><label>ข้อความ Footer</label><input name="footerText" value="'.esc(setting('footerText','')).'"></div>'.
        '<div class="form-group"><label>รหัสผ่านหลังบ้านใหม่ (เว้นว่าง = ไม่เปลี่ยน)</label><input type="password" name="newPass" autocomplete="new-password"></div></div>'.
        '<div class="form-group"><label>ข้อความแถบประกาศเลื่อนบนสุด</label><input name="announcement" value="'.esc(setting('announcement','')).'"></div>'.
        '<button class="btn btn-primary btn-sm" type="submit">💾 บันทึก</button></div>'.
        $videoAdsHtml.'</form>'.
        '<div class="card mb"><div class="section-title">ข้อมูล &amp; สำรอง</div><p style="font-size:12px;color:var(--muted);margin-bottom:12px;">ข้อมูลเก็บใน MySQL — กดส่งออกเพื่อเก็บสำรอง</p>'.
        '<div style="display:flex;gap:10px;flex-wrap:wrap;"><form method="post"><input type="hidden" name="action" value="export"><button class="btn btn-ghost" type="submit">📥 ส่งออกข้อมูล (JSON)</button></form>'.
        '<form method="post" onsubmit="return confirm(\'รีเซ็ตข้อมูลทั้งหมด?\')"><input type="hidden" name="action" value="reset"><button class="btn btn-danger" type="submit">🔄 รีเซ็ตทั้งหมด</button></form></div></div>'.
        '<div class="card"><div class="section-title">🕐 บันทึกกิจกรรม (Activity Log) — ล่าสุด 50 รายการ</div><div style="font-size:12px;line-height:2;max-height:200px;overflow:auto;">'.
        implode('',array_map(function($a){return '<div style="border-bottom:1px solid var(--border);padding:4px 0;"><span style="color:var(--accent);">●</span> '.esc($a['action']).' <span style="color:var(--muted);font-size:10px;margin-left:8px;">'.fmtTime($a['time']).'</span></div>';},$recentAct)).'</div></div>';
    }
    return '<p>ไม่พบหน้านี้</p>';
}
function array_sort($arr,$key){usort($arr,function($a,$b)use($key){return $b[$key]-$a[$key];});return $arr;}
$content = adminContent($page);
ob_start();
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>หลังบ้าน - PINKCMS ULTIMATE</title>
<link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
:root{--bg:#0a0a0f;--card:#14141c;--card2:#1a1a24;--text:#fff;--muted:#8b8b9a;--accent:<?php echo $accent; ?>;--accent-soft:<?php echo $accent; ?>26;--accent2:#a855f7;--accent3:#22d3ee;--gold:#f5c542;--border:rgba(255,255,255,.08);--danger:#ef4444;--success:#22c55e}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Kanit',sans-serif;background:var(--bg);color:var(--text);line-height:1.5;font-size:14px}
a{color:inherit;text-decoration:none;cursor:pointer}
button{cursor:pointer;font-family:inherit}
input,textarea,select{font-family:inherit;font-size:13px;background:var(--bg);color:var(--text);border:1px solid #414858;border-radius:8px;padding:9px 12px;width:100%}
input:focus,textarea:focus,select:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 2px rgba(255,45,120,.2)}
.clearable-control{position:relative;display:flex;align-items:stretch;width:100%;min-width:0}
.clearable-control>input,.clearable-control>textarea{min-width:0;flex:1}
.clear-field-button{display:grid;width:34px;flex:0 0 34px;place-items:center;margin-left:-42px;align-self:center;padding:0;border:0;border-radius:7px;background:transparent;color:var(--muted);font-size:20px;line-height:1;cursor:pointer}
.clear-field-button:hover,.clear-field-button:focus-visible{background:rgba(255,45,120,.14);color:#ff8bb4;outline:0}
.clear-field-button[hidden]{display:none}
textarea{resize:vertical;min-height:80px}
.btn{padding:9px 18px;border-radius:8px;border:none;font-weight:600;font-size:13px;transition:all .2s;display:inline-block;text-align:center}
.btn-primary{background:var(--accent);color:#fff;box-shadow:0 0 12px rgba(255,45,120,.4)}
.btn-ghost{background:var(--card);color:var(--text);border:1px solid var(--border)}
.btn-danger{background:var(--danger);color:#fff}
.btn-sm{padding:5px 10px;font-size:11px}
.badge{display:inline-block;padding:2px 8px;border-radius:999px;font-size:11px;font-weight:600}
.badge-pink{background:var(--accent-soft);color:var(--accent);border:1px solid rgba(255,45,120,.3)}
.badge-purple{background:rgba(168,85,247,.15);color:#c084fc;border:1px solid rgba(168,85,247,.3)}
.badge-gold{background:rgba(245,197,66,.15);color:var(--gold);border:1px solid rgba(245,197,66,.3)}
.badge-gray{background:rgba(255,255,255,.06);color:var(--muted);border:1px solid var(--border)}
.badge-green{background:rgba(34,197,94,.15);color:var(--success);border:1px solid rgba(34,197,94,.3)}
.badge-red{background:rgba(239,68,68,.15);color:var(--danger);border:1px solid rgba(239,68,68,.3)}
.card{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:18px}
.grid{display:grid;gap:12px}
table{width:100%;border-collapse:collapse;font-size:13px}
th,td{padding:10px 8px;text-align:left;border-bottom:1px solid var(--border);vertical-align:top}
th{color:var(--muted);font-weight:600;font-size:12px;text-transform:uppercase;letter-spacing:.05em}
tr:hover{background:rgba(255,255,255,.02)}
.stat-card{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:18px;text-align:center}
.stat-num{font-size:28px;font-weight:900;color:var(--accent)}
.stat-label{font-size:11px;color:var(--muted);margin-top:4px}
.admin-layout{display:flex;min-height:100vh}
.admin-mobile-bar{display:none}
.admin-sidebar{width:240px;background:var(--card);border-right:1px solid var(--border);padding:16px 10px;flex-shrink:0;position:sticky;top:0;height:100vh;overflow-y:auto;scrollbar-width:none;-ms-overflow-style:none}
.admin-sidebar::-webkit-scrollbar,.editor-preview-panel::-webkit-scrollbar{width:0;height:0}
.sidebar-status{display:flex;align-items:center;gap:10px;padding:11px 12px;margin:0 2px 14px;border:1px solid rgba(34,197,94,.2);border-radius:12px;background:linear-gradient(120deg,rgba(34,197,94,.12),rgba(34,211,238,.05))}
.sidebar-status-dot{width:9px;height:9px;flex:0 0 9px;border-radius:50%;background:var(--success);box-shadow:0 0 12px rgba(34,197,94,.8)}
.sidebar-status strong,.sidebar-status small{display:block}
.sidebar-status strong{font-size:11px;color:#d8ffe5}
.sidebar-status small{font-size:10px;color:var(--muted);margin-top:2px}
.admin-main{flex:1;padding:24px;min-width:0;max-width:100%}
.admin-nav-item{display:block;padding:9px 12px;border-radius:8px;color:var(--muted);font-size:12.5px;font-weight:500;margin-bottom:2px;transition:all .2s}
.admin-nav-item:hover{background:var(--accent-soft);color:var(--accent)}
.admin-nav-item.active{background:var(--accent);color:#fff;box-shadow:0 0 12px rgba(255,45,120,.4)}
.section-title{font-size:15px;font-weight:700;margin-bottom:14px;padding-bottom:8px;border-bottom:1px solid var(--border)}
.form-group{margin-bottom:14px}
.form-group label{display:block;font-size:12px;font-weight:600;color:var(--muted);margin-bottom:6px}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.form-row-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px}
.video-input-help{display:block;color:var(--muted);font-size:11px;line-height:1.65;margin-top:7px}
.remote-api-status{display:flex;align-items:flex-start;gap:10px;padding:11px 13px;border-radius:10px;margin:10px 0 14px}
.remote-api-status.is-ready{border:1px solid rgba(34,197,94,.24);background:rgba(34,197,94,.07)}
.remote-api-status.is-off{border:1px solid rgba(245,197,66,.22);background:rgba(245,197,66,.06)}
.remote-api-dot{width:8px;height:8px;border-radius:50%;margin-top:5px;flex:0 0 8px;background:var(--gold)}
.is-ready .remote-api-dot{background:var(--success);box-shadow:0 0 10px rgba(34,197,94,.6)}
.remote-api-status strong,.remote-api-status small{display:block}
.remote-api-status strong{font-size:11px}
.remote-api-status small{font-size:10px;color:var(--muted);margin-top:2px;line-height:1.5}
.video-input-preview{min-height:42px;color:var(--muted);font-size:11px}
.video-input-preview .video-wrap{max-width:480px}
.video-preview-frame,.video-preview-media{display:block;width:min(100%,480px);aspect-ratio:16/9;border:1px solid var(--border);border-radius:12px;background:#000}
.video-preview-media{height:auto}
input[type=file]{padding:7px 9px;background:linear-gradient(145deg,rgba(255,45,120,.08),rgba(168,85,247,.07));border:1px dashed rgba(255,255,255,.2);border-radius:10px;cursor:pointer;color:var(--muted)}
input[type=file]::file-selector-button{margin-right:10px;padding:7px 12px;border:0;border-radius:7px;background:linear-gradient(100deg,var(--accent),var(--accent2));color:#fff;font:600 11px Kanit,sans-serif;cursor:pointer}
input[type=file]::-webkit-file-upload-button{margin-right:10px;padding:7px 12px;border:0;border-radius:7px;background:linear-gradient(100deg,var(--accent),var(--accent2));color:#fff;font:600 11px Kanit,sans-serif;cursor:pointer}
.editor-preview-layout{display:block}
.editor-form{display:flex;flex-direction:column;gap:16px}
.editor-form>.card,.editor-form>.editor-columns{min-width:0;margin:0}
.editor-basic-card{width:100%}
.editor-columns{display:grid;grid-template-columns:minmax(0,1.15fr) minmax(320px,.85fr);gap:16px;align-items:start}
.editor-primary-column,.editor-side-column{display:flex;min-width:0;flex-direction:column;gap:16px}
.editor-primary-column{grid-column:1;grid-row:1}
.editor-side-column{grid-column:2;grid-row:1}
.editor-primary-column>.card,.editor-side-column>.card{min-width:0;margin:0}
.editor-preview-panel{position:static}
.seo-audit-sticky{position:sticky;top:16px;z-index:5}
.editor-cover-preview{position:relative;overflow:hidden;min-height:110px;border:1px solid var(--border);border-radius:14px;background:var(--card)}
.editor-cover-preview[hidden]{display:none}
.editor-cover-preview img{display:block;width:100%;max-height:320px;object-fit:contain;background:#080810}
.editor-cover-preview span{display:block;padding:7px 12px;color:var(--muted);font-size:10px}
.editor-tags-section{margin-top:18px;padding-top:18px;border-top:1px solid var(--border)}
.editor-actions{display:flex;gap:10px;flex-wrap:wrap;padding:2px 0 10px}
.code-keyphrase-row{grid-template-columns:minmax(150px,.55fr) minmax(0,1.45fr)}
.code-input{max-width:230px}
.seo-audit{margin-bottom:14px;padding:0;border:1px solid var(--border);border-radius:12px;background:linear-gradient(145deg,rgba(255,255,255,.035),rgba(255,255,255,.01))}
.seo-audit-summary{display:flex;align-items:center;gap:9px;padding:11px 13px;cursor:pointer;list-style:none}
.seo-audit-summary::-webkit-details-marker{display:none}
.seo-audit-summary::after{content:"⌄";margin-left:2px;color:var(--muted);transition:transform .2s}
.seo-audit[open]>.seo-audit-summary::after{transform:rotate(180deg)}
.seo-audit-summary strong{flex:1;font-size:13px}
.seo-audit-list{padding:0 13px}
.seo-audit-note{padding:0 13px 12px}
.seo-audit-light{width:10px;height:10px;flex:0 0 10px;border-radius:50%;background:var(--muted)}
.seo-audit-score{padding:3px 8px;border:1px solid var(--border);border-radius:999px;color:var(--muted);font-size:10px;font-weight:700}
.seo-audit[data-state="good"]{border-color:rgba(34,197,94,.35);background:rgba(34,197,94,.055)}
.seo-audit[data-state="good"] .seo-audit-light{background:var(--success);box-shadow:0 0 10px rgba(34,197,94,.5);animation:seoPulse 3.2s ease-in-out infinite}
.seo-audit[data-state="warning"]{border-color:rgba(245,158,11,.35);background:rgba(245,158,11,.055)}
.seo-audit[data-state="warning"] .seo-audit-light{background:#f59e0b}
.seo-audit[data-state="error"]{border-color:rgba(239,68,68,.35);background:rgba(239,68,68,.055)}
.seo-audit[data-state="error"] .seo-audit-light{background:var(--danger)}
.seo-audit[data-state="good"] .seo-audit-score{color:#86efac;border-color:rgba(34,197,94,.3)}
.seo-audit[data-state="warning"] .seo-audit-score{color:#fcd34d;border-color:rgba(245,158,11,.3)}
.seo-audit[data-state="error"] .seo-audit-score{color:#fca5a5;border-color:rgba(239,68,68,.3)}
.seo-audit-list{display:grid;gap:7px;margin:0;padding-bottom:11px;list-style:none}
.seo-audit-check{display:flex;align-items:flex-start;gap:7px;color:var(--muted);font-size:11px;line-height:1.5}
.seo-audit-check::before{content:"•";font-size:16px;font-weight:900;line-height:1}
.seo-audit-check.is-good::before{content:"✓";color:var(--success)}
.seo-audit-check.is-warning::before{content:"!";color:#f59e0b}
.seo-audit-check.is-error::before{content:"×";color:var(--danger)}
.seo-audit-note{display:block;margin-top:10px;color:var(--muted);font-size:10px;line-height:1.5}
@keyframes seoPulse{0%,100%{opacity:.65;box-shadow:0 0 4px rgba(34,197,94,.35)}50%{opacity:1;box-shadow:0 0 12px rgba(34,197,94,.8)}}
@media(prefers-reduced-motion:reduce){.seo-audit[data-state="good"] .seo-audit-light{animation:none}}
.post-preview-content{max-height:260px;overflow:auto;white-space:pre-wrap;overflow-wrap:anywhere;color:var(--muted);line-height:1.8}
.mb{margin-bottom:16px}.mt{margin-top:16px}
.toggle-row{display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid var(--border);font-size:13px}
.toggle-row:last-child{border-bottom:none}
.switch{position:relative;width:44px;height:24px;flex-shrink:0}
.switch input{opacity:0;width:0;height:0}
.slider{position:absolute;cursor:pointer;inset:0;background:#333;border-radius:24px;transition:.3s}
.slider:before{content:"";position:absolute;height:18px;width:18px;left:3px;bottom:3px;background:#fff;border-radius:50%;transition:.3s}
.switch input:checked + .slider{background:var(--accent)}
.switch input:checked + .slider:before{transform:translateX(20px)}
.auto-hint{font-size:11px;color:var(--accent3);background:rgba(34,211,238,.08);border:1px solid rgba(34,211,238,.25);border-radius:8px;padding:8px 10px;margin-top:6px;line-height:1.5}
.duration-input{display:flex;align-items:center;gap:6px;max-width:260px}
.duration-input input{width:62px;text-align:center;border-color:#596273}
.duration-separator{font-weight:800;color:var(--muted)}
.field-help{display:flex;gap:10px;flex-wrap:wrap;margin-top:6px;color:var(--muted);font-size:10px;line-height:1.5}
.usage-caption{color:var(--muted);font-size:10px;font-weight:400}
.taxonomy-source{position:absolute!important;width:1px!important;height:1px!important;padding:0!important;margin:-1px!important;overflow:hidden!important;clip:rect(0,0,0,0)!important;white-space:nowrap!important;border:0!important}
.taxonomy-autocomplete{position:relative}
.taxonomy-picker{position:relative;display:flex;min-height:44px;align-items:center;gap:6px;flex-wrap:wrap;padding:6px 8px;border:1px solid #32998e;border-radius:9px;background:#0c1518;transition:border-color .2s,box-shadow .2s}
.taxonomy-picker:focus-within{border-color:#54dbc1;box-shadow:0 0 0 2px rgba(52,211,183,.15)}
.taxonomy-selected{display:contents}
.taxonomy-chip{display:inline-flex;max-width:100%;align-items:center;gap:7px;padding:5px 8px;border:1px solid #318f83;border-radius:999px;background:rgba(20,184,166,.16);color:#a7f3e5;font-size:11px;line-height:1.2}
.taxonomy-chip span:first-child{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.taxonomy-chip small{color:#72d8cb;font-size:9px;white-space:nowrap}
.taxonomy-chip button{display:grid;width:18px;height:18px;flex:0 0 18px;place-items:center;padding:0;border:0;border-radius:50%;background:rgba(45,212,191,.2);color:#bffcf0;font-size:13px;cursor:pointer}
.taxonomy-chip.is-custom{border-color:#398e8c;background:rgba(34,211,238,.12);color:#a5f3fc}
.taxonomy-input{width:auto;min-width:150px;max-width:100%;flex:1;border:0!important;outline:0!important;box-shadow:none!important;background:transparent!important;padding:5px 4px!important;color:var(--text)}
.taxonomy-suggestions{position:absolute;z-index:30;top:calc(100% + 4px);left:0;right:0;max-height:220px;overflow:auto;padding:5px;border:1px solid #414858;border-radius:10px;background:#11151d;box-shadow:0 12px 28px rgba(0,0,0,.45)}
.taxonomy-suggestions:not([hidden]){display:grid;gap:4px}
.taxonomy-suggestion{display:flex;align-items:center;justify-content:space-between;gap:8px;padding:8px 10px;border:0;border-radius:7px;background:transparent;color:var(--text);text-align:left;cursor:pointer}
.taxonomy-suggestion:hover,.taxonomy-suggestion:focus{outline:0;background:rgba(45,212,191,.13)}
.taxonomy-suggestion small{color:#80d7cb;font-size:10px;white-space:nowrap}
.taxonomy-suggestion.is-create{justify-content:flex-start;border-top:1px solid var(--border);border-radius:0 0 7px 7px;color:#a5f3fc}
.taxonomy-suggestion.is-active{outline:0;background:rgba(45,212,191,.2)}
.taxonomy-recommendations{display:flex;gap:6px;flex-wrap:wrap;margin-top:8px}
.taxonomy-recommendation{display:inline-flex;align-items:center;gap:7px;padding:5px 9px;border:1px solid #d97706;border-radius:999px;background:rgba(245,158,11,.12);color:#fed7aa;font:600 11px Kanit,sans-serif;cursor:pointer;transition:transform .16s,background .16s}
.taxonomy-recommendation:hover{transform:translateY(-1px);background:rgba(245,158,11,.22)}
.taxonomy-recommendation span{padding:1px 6px;border-radius:999px;background:#f59e0b;color:#271306;font-size:9px;font-weight:800;white-space:nowrap}
.legend-recommended{color:#fdba74}
.legend-selected{color:#7ee7d6}
.editor-gallery-preview{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px}
.editor-gallery-item{aspect-ratio:1;overflow:hidden;border:1px solid var(--border);border-radius:9px;background:#080810}
.editor-gallery-item img{display:block;width:100%;height:100%;object-fit:cover}
.editor-gallery-empty{grid-column:1/-1;padding:14px;border:1px dashed var(--border);border-radius:9px;color:var(--muted);font-size:11px;text-align:center}
.post-status{display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:700;white-space:nowrap}
.post-status:before{content:"";width:8px;height:8px;border-radius:50%;background:currentColor;box-shadow:0 0 10px currentColor}
.post-status.is-published{color:var(--success)}
.post-status.is-draft{color:var(--muted)}
.post-status.is-pending{color:var(--gold)}
.seo-status{border:1px solid var(--border);border-radius:14px;padding:18px;background:linear-gradient(135deg,rgba(255,45,120,.14),rgba(168,85,247,.12));margin-bottom:16px}
.seo-status-head{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
.seo-score{font-size:28px;font-weight:900;color:var(--accent)}
.seo-meter{height:8px;background:var(--bg);border-radius:10px;overflow:hidden;margin-top:12px}
.seo-meter span{display:block;height:100%;background:linear-gradient(90deg,var(--accent),var(--accent2));border-radius:inherit}
.seo-checks{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:8px;margin-top:14px}
.seo-check{padding:9px 11px;border-radius:9px;background:rgba(0,0,0,.16);font-size:12px}
html,body{scrollbar-width:none;-ms-overflow-style:none}
*::-webkit-scrollbar{width:0;height:0}
.video-wrap{position:relative;width:100%;aspect-ratio:16/9;border-radius:14px;overflow:hidden;background:#000}
.video-wrap iframe,.video-wrap video{width:100%;height:100%;border:none}
.bar-chart{display:flex;align-items:flex-end;gap:8px;height:120px;padding:10px 0}
.bar-chart .bar{flex:1;background:linear-gradient(to top,var(--accent),var(--accent2));border-radius:6px 6px 0 0;position:relative;min-height:4px}
.bar-chart .bar span{position:absolute;top:-18px;left:50%;transform:translateX(-50%);font-size:10px;color:var(--muted)}
.gallery-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(110px,1fr));gap:8px}
.gallery-item{aspect-ratio:1;border-radius:8px;overflow:hidden;cursor:pointer;position:relative}
.gallery-item img{width:100%;height:100%;object-fit:cover}
.lightbox{position:fixed;inset:0;background:rgba(0,0,0,.92);z-index:1000;display:none;align-items:center;justify-content:center;padding:20px}
.lightbox.active{display:flex}
.lightbox img{max-width:90%;max-height:85vh;border-radius:12px}
.lightbox-close{position:absolute;top:20px;right:24px;font-size:32px;color:#fff;cursor:pointer;background:none;border:none}
.toast{position:fixed;bottom:24px;left:50%;transform:translateX(-50%);background:var(--accent);color:#fff;padding:10px 22px;border-radius:999px;font-size:13px;font-weight:600;box-shadow:0 0 20px rgba(255,45,120,.5);z-index:999;animation:toastIn .3s ease}
@keyframes toastIn{from{opacity:0;transform:translate(-50%,20px)}to{opacity:1;transform:translate(-50%,0)}}
@media(max-width:768px){.form-row,.form-row-3{grid-template-columns:1fr}.admin-mobile-bar{position:sticky;top:0;z-index:120;display:flex;align-items:center;justify-content:space-between;gap:12px;min-height:56px;padding:8px 14px;border-bottom:1px solid var(--border);background:rgba(10,10,15,.96);backdrop-filter:blur(12px)}.admin-mobile-brand{font-size:14px;font-weight:900}.admin-menu-toggle{display:inline-flex;align-items:center;gap:8px;min-height:38px;padding:7px 12px;border:1px solid var(--border);border-radius:9px;background:var(--card);color:var(--text);font-family:inherit;font-size:13px;font-weight:600}.admin-layout{display:block;min-height:calc(100vh - 56px)}.admin-sidebar{display:none;position:fixed;top:56px;bottom:0;left:0;z-index:110;width:min(300px,86vw);height:auto;padding:16px 12px;overflow-y:auto;border-right:1px solid var(--border);box-shadow:14px 0 36px rgba(0,0,0,.4)}.admin-layout.menu-open .admin-sidebar{display:block}.admin-menu-backdrop{display:none;position:fixed;inset:56px 0 0;z-index:105;width:100%;height:calc(100dvh - 56px);border:0;background:rgba(0,0,0,.62)}.admin-layout.menu-open .admin-menu-backdrop{display:block}.admin-main{padding:14px;min-width:0;width:100%;overflow-x:clip}.admin-main>*{min-width:0;max-width:100%}.admin-main .card{min-width:0}.dashboard-chart-grid,.reports-taxonomy-grid{grid-template-columns:minmax(0,1fr)!important}.admin-menu-toggle:focus-visible,.admin-nav-item:focus-visible{outline:2px solid var(--accent);outline-offset:2px}}
@media(max-width:1000px){.editor-columns{display:grid;grid-template-columns:minmax(0,1fr)}.editor-primary-column,.editor-side-column{display:contents}.editor-basic-card{grid-column:1;grid-row:1}.seo-audit-sticky{grid-column:1;grid-row:2;position:static}.editor-preview-panel{grid-column:1;grid-row:3;position:static}.editor-cover-preview{grid-column:1;grid-row:4}.editor-image-card{grid-column:1;grid-row:5}.editor-video-card{grid-column:1;grid-row:6}.editor-seo-card{grid-column:1;grid-row:7}.editor-content-card{grid-column:1;grid-row:8}.editor-gallery-card{grid-column:1;grid-row:9}.editor-taxonomy-card{grid-column:1;grid-row:10}}
.posts-page{max-width:1500px;margin:0 auto}
.posts-page-heading{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-bottom:18px}
.posts-page-heading h1{font-size:22px;font-weight:900}
.posts-page-heading p{margin-top:3px;color:var(--muted);font-size:12px}
.posts-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:10px;margin-bottom:14px}
.posts-stat-card{display:flex;min-height:82px;flex-direction:column;justify-content:center;align-items:center;gap:3px;padding:12px;border:1px solid rgba(255,45,120,.17);border-radius:13px;background:linear-gradient(145deg,#17131e,#11121b);text-align:center}
.posts-stat-card strong{color:#ff6c9c;font-size:25px;font-weight:900;line-height:1.1}
.posts-stat-card strong.is-published{color:var(--success)}
.posts-stat-card strong.is-draft{color:var(--gold)}
.posts-stat-card strong.is-pending{color:var(--accent2)}
.posts-stat-card span{color:#b8b4c8;font-size:11px}
.post-keyword-notes{margin-bottom:16px;overflow:hidden;border:1px solid rgba(168,85,247,.25);border-radius:13px;background:linear-gradient(145deg,rgba(168,85,247,.07),rgba(20,20,28,.96))}
.post-keyword-notes>summary{display:flex;min-height:50px;align-items:center;gap:12px;padding:11px 15px;cursor:pointer;list-style:none}
.post-keyword-notes>summary::-webkit-details-marker{display:none}
.post-keyword-notes>summary:after{content:"⌄";margin-left:auto;color:#c4a4ff;font-size:19px;transition:transform .2s}
.post-keyword-notes[open]>summary:after{transform:rotate(180deg)}
.post-keyword-notes>summary span{font-size:13px;font-weight:700}
.post-keyword-notes>summary small{color:var(--muted);font-size:10px}
.keyword-notes-form{padding:0 15px 14px;border-top:1px solid var(--border)}
.keyword-notes-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;padding-top:14px}
.keyword-notes-grid section{min-width:0}
.keyword-notes-grid label{display:block;margin-bottom:7px;color:#ddd;font-size:12px;font-weight:700}
.keyword-notes-grid label span{display:block;color:var(--muted);font-size:10px;font-weight:400}
.keyword-note-preview{display:flex;max-height:174px;gap:6px;flex-wrap:wrap;overflow:auto;margin-bottom:8px;padding:9px;border:1px solid rgba(255,255,255,.07);border-radius:10px;background:rgba(5,7,12,.48)}
.keyword-note-chip{display:inline-flex;max-width:100%;align-items:center;gap:6px;padding:4px 7px;border:1px solid rgba(245,158,11,.35);border-radius:999px;background:rgba(245,158,11,.09);color:#ffe0a3;font-size:10px}
.keyword-note-chip>span:nth-child(2){overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.keyword-note-chip.is-secondary{border-color:rgba(168,85,247,.35);background:rgba(168,85,247,.09);color:#e0ccff}
.keyword-note-index{color:#9ca3af;font-size:9px}
.keyword-note-rank{padding:1px 5px;border-radius:999px;background:#fb923c;color:#261405;font-size:8px;font-weight:800;white-space:nowrap}
.keyword-note-language{font-size:8px;font-weight:800}
.keyword-note-language.is-en{color:#67e8f9}
.keyword-note-language.is-th{color:#f9a8d4}
.keyword-notes-grid textarea{min-height:126px;font-size:11px;line-height:1.65}
.keyword-notes-footer{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:10px}
.post-keyword-notes[open]{position:fixed;inset:0;z-index:1000;width:100%;height:100vh;height:100dvh;max-height:100vh;max-height:100dvh;overflow-y:auto;padding:clamp(12px,2.4vw,30px);border:0;border-radius:0;background:radial-gradient(ellipse at 10% 0%,rgba(168,85,247,.14),transparent 38%),#090912;box-shadow:none}
.post-keyword-notes[open]>summary{position:sticky;top:0;z-index:3;max-width:1440px;min-height:58px;margin:0 auto;padding:12px 16px;border:1px solid rgba(168,85,247,.27);border-radius:12px;background:rgba(18,16,28,.96);backdrop-filter:blur(12px)}
.post-keyword-notes[open]>.keyword-notes-content{width:min(100%,1440px);min-height:calc(100dvh - 100px);margin:12px auto 0;padding:clamp(13px,2vw,24px);border:1px solid rgba(168,85,247,.2);border-radius:15px;background:linear-gradient(145deg,rgba(20,20,30,.96),rgba(12,13,21,.98))}
.keyword-notes-toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid var(--border)}
.keyword-notes-toolbar strong,.keyword-notes-toolbar small{display:block}
.keyword-notes-toolbar strong{font-size:16px}
.keyword-notes-toolbar small{margin-top:2px;color:var(--muted);font-size:10px}
.keyword-notes-read{display:grid;gap:16px}
.keyword-notes-read section{min-width:0}
.keyword-notes-read h3{display:flex;align-items:center;gap:8px;margin-bottom:9px;color:#eee;font-size:13px}
.keyword-notes-read h3 span{color:var(--muted);font-size:10px;font-weight:400}
.keyword-notes-read .keyword-note-preview{max-height:none;overflow:visible;gap:8px;padding:12px}
.keyword-note-chip{appearance:none;-webkit-appearance:none;cursor:pointer;font:inherit;transition:transform .14s,border-color .14s,background .14s}
.keyword-note-chip:hover{transform:translateY(-1px);border-color:#f59e0b;background:rgba(245,158,11,.2)}
.keyword-note-chip.is-secondary:hover{border-color:#c084fc;background:rgba(168,85,247,.2)}
.keyword-note-chip:focus-visible{outline:2px solid #fff;outline-offset:2px}
.keyword-notes-form.is-editing .keyword-notes-read{display:none}
.keyword-notes-form:not(.is-editing) .keyword-notes-edit{display:none!important}
.keyword-notes-edit[hidden]{display:none!important}
.keyword-notes-edit .keyword-notes-grid{padding-top:0}
.keyword-notes-edit textarea{min-height:300px}
.keyword-notes-footer>div{display:flex;gap:7px}
.keyword-notes-footer small{color:var(--muted);font-size:10px}
.posts-toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;margin:18px 0 10px}
.posts-toolbar>strong{font-size:14px}
.posts-toolbar>strong span{margin-left:5px;color:var(--muted);font-size:11px;font-weight:500}
.posts-view-switch{display:flex;gap:5px;padding:4px;border:1px solid var(--border);border-radius:10px;background:#101018}
.posts-view-switch button{padding:6px 10px;border:0;border-radius:7px;background:transparent;color:var(--muted);font-size:11px;white-space:nowrap}
.posts-view-switch button[aria-pressed="true"]{background:var(--accent);color:#fff;box-shadow:0 3px 12px rgba(255,45,120,.2)}
.posts-view{display:none}
.posts-view.is-active{display:block}
.posts-table-card{padding:0;overflow:hidden}
.posts-table-scroll{overflow:auto}
.posts-table{min-width:830px}
.posts-table th,.posts-table td{padding:10px 9px}
.post-table-title{display:flex;min-width:250px;align-items:center;gap:10px}
.post-table-title img,.post-table-placeholder{width:46px;height:58px;flex:0 0 46px;border-radius:7px;object-fit:cover;background:#22212c}
.post-table-placeholder{display:grid;place-items:center;padding:4px;color:var(--muted);font-size:8px;text-align:center}
.post-table-title>span:last-child{min-width:0}
.post-table-title strong,.post-table-title small{display:block;max-width:320px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.post-table-title strong{font-weight:600}
.post-table-title small{margin-top:3px;color:var(--muted);font-size:10px}
.post-table-actions,.post-summary-actions{display:flex;align-items:center;gap:5px}
.post-delete-form{display:inline-flex}
.posts-empty{text-align:center;color:var(--muted);padding:32px!important}
.posts-view-rows,.posts-view-columns{gap:10px}
.posts-view-rows.is-active{display:grid}
.post-summary-card{display:grid;grid-template-columns:88px minmax(0,1fr);overflow:hidden;border:1px solid var(--border);border-radius:13px;background:linear-gradient(120deg,#15151f,#11121a);transition:border-color .18s,transform .18s}
.post-summary-card:hover{border-color:rgba(255,45,120,.4);transform:translateY(-1px)}
.post-summary-cover{position:relative;min-height:126px;background:#20202b}
.post-summary-cover img{display:block;width:100%;height:100%;position:absolute;inset:0;object-fit:cover}
.post-summary-cover>span:not(.post-status){display:grid;position:absolute;inset:0;place-items:center;padding:6px;color:var(--muted);font-size:9px;text-align:center}
.post-summary-cover .post-status{position:absolute;left:5px;bottom:5px;padding:3px 6px;border-radius:999px;background:rgba(0,0,0,.78);font-size:9px}
.post-summary-cover .post-status:before{width:6px;height:6px}
.post-summary-body{min-width:0;padding:11px 13px}
.post-summary-heading{display:flex;justify-content:space-between;gap:10px;align-items:flex-start}
.post-summary-heading h2{display:-webkit-box;overflow:hidden;margin:2px 0 0;font-size:14px;font-weight:700;line-height:1.45;-webkit-box-orient:vertical;-webkit-line-clamp:2}
.post-summary-code{color:var(--accent);font-size:10px;font-weight:800;letter-spacing:.04em}
.post-summary-views{flex:0 0 auto;color:var(--muted);font-size:10px;white-space:nowrap}
.post-summary-byline{display:flex;gap:5px 12px;flex-wrap:wrap;margin-top:7px;color:#b8b8c4;font-size:10px}
.post-summary-meta{display:flex;gap:5px 12px;flex-wrap:wrap;margin-top:5px;color:#b8b8c4;font-size:10px}
.post-summary-taxonomy{display:flex;gap:5px;flex-wrap:wrap;margin-top:8px}
.post-summary-pill{display:inline-flex;align-items:center;max-width:100%;padding:2px 8px;border:1px solid rgba(255,255,255,.1);border-radius:999px;background:rgba(255,255,255,.045);color:#d5d1df;font-size:9px;line-height:1.45;overflow-wrap:anywhere}
.post-summary-pill.is-category{border-color:rgba(255,45,120,.28);background:var(--accent-soft);color:#ff8ab1}
.post-summary-pill.is-studio{border-color:rgba(245,197,66,.25);background:rgba(245,197,66,.08);color:#f4d77e}
.post-summary-pill.is-tag{color:#c6bfd6}
.post-summary-pill.is-empty{color:var(--muted)}
.post-summary-actions{justify-content:flex-end;margin-top:8px}
.posts-view-columns.is-active{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;align-items:stretch}
.posts-view-columns .post-summary-card{display:flex;min-width:0;flex-direction:column}
.posts-view-columns .post-summary-cover{min-height:0;aspect-ratio:16/9}
.posts-view-columns .post-summary-cover .post-status{left:auto;right:7px}
.posts-view-columns .post-summary-body{display:flex;flex:1;flex-direction:column}
.posts-view-columns .post-summary-heading h2{-webkit-line-clamp:3}
.posts-view-columns .post-summary-actions{margin-top:auto;padding-top:10px}
@media(max-width:1100px){.posts-view-columns.is-active{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:700px){.posts-page-heading{align-items:flex-start}.posts-page-heading h1{font-size:19px}.posts-page-heading p{max-width:250px}.posts-stats{grid-template-columns:repeat(2,minmax(0,1fr))}.posts-toolbar{align-items:flex-start;flex-direction:column}.posts-view-switch{width:100%}.posts-view-switch button{flex:1;padding:8px 5px}.keyword-notes-grid{grid-template-columns:1fr}.post-summary-card{grid-template-columns:72px minmax(0,1fr)}.post-summary-cover{min-height:112px}.posts-view-columns.is-active{grid-template-columns:1fr}.posts-view-columns .post-summary-card{display:grid;grid-template-columns:78px minmax(0,1fr);flex-direction:initial}.posts-view-columns .post-summary-cover{min-height:120px;aspect-ratio:auto}.posts-view-columns .post-summary-cover .post-status{left:4px;right:auto}}
@media(max-width:420px){.posts-page-heading{align-items:stretch;flex-direction:column}.posts-page-heading>.btn{align-self:flex-start}.posts-stat-card{min-height:72px}.posts-stat-card strong{font-size:22px}.post-summary-body{padding:9px}.post-summary-heading h2{font-size:12px}.post-summary-meta{gap:4px 8px;font-size:9px}}
@media(max-width:700px){.post-keyword-notes[open]{padding:8px}.post-keyword-notes[open]>summary{min-height:54px;padding:9px 10px}.post-keyword-notes>summary small{max-width:48%;font-size:9px;line-height:1.35}.post-keyword-notes[open]>.keyword-notes-content{min-height:calc(100dvh - 78px);margin-top:8px;padding:12px}.keyword-notes-toolbar strong{font-size:13px}.keyword-notes-read .keyword-note-preview{gap:6px;padding:8px}.keyword-note-chip{gap:4px;padding:4px 6px;font-size:9px}.keyword-note-index,.keyword-note-rank,.keyword-note-language{font-size:8px}.keyword-notes-edit textarea{min-height:220px}.keyword-notes-footer{align-items:flex-start;flex-direction:column}.keyword-notes-footer>div{align-self:flex-end}}
@media(prefers-reduced-motion:reduce){.keyword-note-chip{transition:none}}
</style>
</head>
<body>
<?php if ($msg) echo '<script>window.addEventListener("load",function(){var t=document.createElement("div");t.className="toast";t.textContent="'.esc($msg).'";document.body.appendChild(t);setTimeout(function(){t.remove();},2500);});</script>'; ?>
<div class="admin-mobile-bar"><div class="admin-mobile-brand">PINK<span style="color:var(--accent);">CMS</span> <span style="color:var(--gold);font-size:10px;">ADMIN</span></div><button class="admin-menu-toggle" type="button" aria-expanded="false" aria-controls="adminSidebar"><span aria-hidden="true">☰</span><span>เมนู</span></button></div>
<div class="admin-layout">
<aside class="admin-sidebar" id="adminSidebar" aria-label="เมนูจัดการระบบ">
<div style="font-size:17px;font-weight:900;margin-bottom:16px;padding:0 12px;line-height:1.2;">PINK<span style="color:var(--accent);">CMS</span> <span style="font-size:10px;color:var(--gold);font-weight:600;">ULTIMATE</span><div style="font-size:10px;color:var(--muted);font-weight:400;margin-top:2px;">Admin Panel (PHP+MySQL)</div></div>
<div class="sidebar-status"><span class="sidebar-status-dot"></span><span><strong>ระบบพร้อมใช้งาน</strong><small>พื้นที่จัดการสำหรับผู้ดูแล</small></span></div>
<?php foreach ($menus as $key=>$label): $active = ($page===$key || ($page==='post-edit' && $key==='posts')) ? 'active':''; ?>
<a class="admin-nav-item <?php echo $active; ?>" href="index.php?page=<?php echo $key; ?>"><?php echo $label; ?></a>
<?php endforeach; ?>
<div style="height:1px;background:var(--border);margin:12px 8px;"></div>
<a class="admin-nav-item" href="../index.php" target="_blank">← กลับหน้าบ้าน</a>
<a class="admin-nav-item" href="index.php?logout=1" style="color:var(--danger);">🚪 ออกจากระบบ</a>
</aside>
<button class="admin-menu-backdrop" type="button" aria-label="ปิดเมนู"></button>
<main class="admin-main"><?php echo $content; ?></main>
</div>
<div id="lightbox" class="lightbox" onclick="this.classList.remove('active')"><button class="lightbox-close">×</button><img id="lightboxImg" src="" alt=""></div>
<script>
function openLightbox(src){document.getElementById('lightboxImg').src=src;document.getElementById('lightbox').classList.add('active');}
(function(){
  var layout=document.querySelector('.admin-layout');
  var toggle=document.querySelector('.admin-menu-toggle');
  var backdrop=document.querySelector('.admin-menu-backdrop');
  if(!layout||!toggle||!backdrop)return;
  function setMenuOpen(open){
    layout.classList.toggle('menu-open',open);
    toggle.setAttribute('aria-expanded',open?'true':'false');
    toggle.querySelector('[aria-hidden="true"]').textContent=open?'×':'☰';
    toggle.querySelectorAll('span')[1].textContent=open?'ปิดเมนู':'เมนู';
  }
  toggle.addEventListener('click',function(){setMenuOpen(!layout.classList.contains('menu-open'));});
  backdrop.addEventListener('click',function(){setMenuOpen(false);});
  layout.querySelectorAll('.admin-nav-item').forEach(function(link){link.addEventListener('click',function(){setMenuOpen(false);});});
  document.addEventListener('keydown',function(event){if(event.key==='Escape')setMenuOpen(false);});
})();
(function(){
  var details=document.getElementById('post-keyword-notes');
  if(!details)return;
  var form=details.querySelector('[data-keyword-form]');
  var read=details.querySelector('[data-keyword-read]');
  var editor=details.querySelector('[data-keyword-editor]');
  var editButton=details.querySelector('[data-keyword-edit]');
  var cancelButton=details.querySelector('[data-keyword-cancel]');
  var notify=function(message,isError){
    var old=document.querySelector('.toast');
    if(old)old.remove();
    var toast=document.createElement('div');
    toast.className='toast';
    toast.textContent=message;
    if(isError)toast.style.background='#b4233a';
    document.body.appendChild(toast);
    window.setTimeout(function(){toast.remove();},2600);
  };
  var setEditing=function(enabled){
    form.classList.toggle('is-editing',enabled);
    editor.hidden=!enabled;
    editButton.hidden=enabled;
    read.hidden=enabled;
    if(enabled)details.querySelector('#primaryKeywordNotes').focus();
  };
  details.addEventListener('toggle',function(){document.body.style.overflow=details.open?'hidden':'';});
  editButton.addEventListener('click',function(){setEditing(true);});
  cancelButton.addEventListener('click',function(){form.reset();setEditing(false);});
  details.addEventListener('click',function(event){
    var chip=event.target.closest('[data-copy-keyword]');
    if(!chip)return;
    var keyword=chip.getAttribute('data-copy-keyword')||'';
    var fallbackCopy=function(){
      var helper=document.createElement('textarea');
      helper.value=keyword;
      helper.setAttribute('readonly','');
      helper.style.position='fixed';
      helper.style.opacity='0';
      document.body.appendChild(helper);
      helper.select();
      var copied=false;
      try{copied=document.execCommand('copy');}catch(error){copied=false;}
      helper.remove();
      if(copied)notify('คัดลอก “'+keyword+'” แล้ว');
      else notify('คัดลอกไม่สำเร็จ กรุณาเลือกคีย์แล้วคัดลอกด้วยตนเอง',true);
    };
    if(navigator.clipboard&&navigator.clipboard.writeText){
      navigator.clipboard.writeText(keyword).then(function(){notify('คัดลอก “'+keyword+'” แล้ว');}).catch(fallbackCopy);
    } else fallbackCopy();
  });
  document.addEventListener('keydown',function(event){
    if(event.key==='Escape'&&details.open){
      if(form.classList.contains('is-editing')){form.reset();setEditing(false);}
      else details.open=false;
      details.querySelector('summary').focus();
    }
  });
})();
(function(){
  var page=document.querySelector('[data-posts-page]');
  if(!page)return;
  var buttons=page.querySelectorAll('[data-post-view]');
  var panels=page.querySelectorAll('[data-post-view-panel]');
  var setView=function(view){
    if(!Array.from(page.querySelectorAll('[data-post-view-panel]')).some(function(panel){return panel.getAttribute('data-post-view-panel')===view;}))view='normal';
    buttons.forEach(function(button){button.setAttribute('aria-pressed',button.getAttribute('data-post-view')===view?'true':'false');});
    panels.forEach(function(panel){panel.classList.toggle('is-active',panel.getAttribute('data-post-view-panel')===view);});
    try{localStorage.setItem('pinkcms-posts-view',view);}catch(error){}
  };
  buttons.forEach(function(button){button.addEventListener('click',function(){setView(button.getAttribute('data-post-view'));});});
  var savedView='normal';
  try{savedView=localStorage.getItem('pinkcms-posts-view')||'normal';}catch(error){}
  setView(savedView);
})();
(function(){
  var form=document.querySelector('.editor-form');if(!form)return;
  form.querySelectorAll('.form-group input:not([type]),.form-group input[type="text"],.form-group input[type="search"],.form-group input[type="url"],.form-group input[type="email"],.form-group input[type="tel"],.form-group input[type="number"],.form-group input[type="file"],.form-group textarea').forEach(function(field){
    if(field.classList.contains('taxonomy-input')||field.closest('.clearable-control'))return;
    var wrapper=document.createElement('div');wrapper.className='clearable-control';
    field.parentNode.insertBefore(wrapper,field);wrapper.appendChild(field);
    var clear=document.createElement('button');clear.type='button';clear.className='clear-field-button';clear.textContent='×';
    var group=field.closest('.form-group'),label=group?group.querySelector('label'):null;
    clear.setAttribute('aria-label','ล้าง'+(label?label.textContent.trim():'ข้อมูลในช่องนี้'));clear.title='ล้างข้อมูลในช่องนี้';
    field.style.paddingRight='42px';
    var updateClearState=function(){clear.hidden=field.type==='file'?!field.files||field.files.length===0:field.value==='';};
    field.addEventListener('input',updateClearState);field.addEventListener('change',updateClearState);
    clear.addEventListener('click',function(){
      if(field.type==='file')field.value='';
      else field.value='';
      field.dispatchEvent(new Event('input',{bubbles:true}));field.dispatchEvent(new Event('change',{bubbles:true}));
      updateClearState();field.focus();
    });
    wrapper.appendChild(clear);updateClearState();
  });
  var galleryUrls=form.querySelector('#galleryUrls'),galleryUpload=form.querySelector('#galleryUpload'),galleryPreview=form.querySelector('#editorGalleryPreview'),galleryObjectUrls=[];
  var updateGalleryPreview=function(){
    galleryObjectUrls.forEach(function(url){URL.revokeObjectURL(url);});
    galleryObjectUrls=[];
    galleryPreview.replaceChildren();
    var sources=galleryUrls.value.split(/\r\n|\r|\n/).map(function(url){return url.trim();}).filter(Boolean);
    Array.prototype.forEach.call(galleryUpload.files||[],function(file){
      var objectUrl=URL.createObjectURL(file);galleryObjectUrls.push(objectUrl);sources.push(objectUrl);
    });
    if(!sources.length){
      var empty=document.createElement('div');empty.className='editor-gallery-empty';empty.textContent='ภาพแกลเลอรี่จะแสดงตัวอย่างที่นี่';galleryPreview.appendChild(empty);return;
    }
    sources.forEach(function(source){
      var item=document.createElement('div');item.className='editor-gallery-item';
      var image=document.createElement('img');image.src=source;image.alt='';image.loading='lazy';
      image.addEventListener('error',function(){item.remove();});
      item.appendChild(image);galleryPreview.appendChild(item);
    });
  };
  if(galleryUrls&&galleryUpload&&galleryPreview){
    galleryUrls.addEventListener('input',updateGalleryPreview);
    galleryUpload.addEventListener('change',updateGalleryPreview);
    updateGalleryPreview();
  }
  var videoField=form.querySelector('#videoUrlInput'),videoPreview=document.getElementById('videoInputPreview');
  if(videoField&&videoPreview){
    var updateVideoPreview=function(){
      var source=videoField.value.trim(),frameSource=source,match;
      if(source.indexOf('<iframe')!==-1){
        match=source.match(/^\s*<iframe\b[^>]*\bsrc\s*=\s*(["'])(.*?)\1[^>]*>\s*(?:<\/iframe\s*>)?\s*$/i);
        frameSource=match?match[2]:'';
      } else if(/^[A-Za-z0-9_-]{5,120}$/.test(source)){frameSource='https://javsteemplayer.fembed.co/embed/'+source+'/';}
      var target;
      try{target=new URL(frameSource.indexOf('//')===0?'https:'+frameSource:frameSource,location.href);}catch(e){target=null;}
      videoPreview.replaceChildren();
      if(!source){videoPreview.textContent='ตัวอย่างเครื่องเล่นจะแสดงที่นี่';return;}
      if(!target||(target.protocol!=='http:'&&target.protocol!=='https:')){videoPreview.textContent='ลิงก์หรือ iframe ยังไม่ถูกต้อง';return;}
      var youtube=target.href.match(/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([A-Za-z0-9_-]{11})/i);
      if(youtube)target=new URL('https://www.youtube.com/embed/'+youtube[1]);
      else if(target.hostname.toLowerCase()==='drive.google.com')target=new URL(target.href.replace(/\/view(?:\?.*)?$/,'/preview').replace(/([?&])usp=[^&]+/,''));
      else if(/(^|\.)fembed\.(co|com|net)$/i.test(target.hostname)){
        var fembed=target.pathname.match(/\/(?:(?:embed|v|f)\/)?([A-Za-z0-9_-]{5,160})\/?$/i);
        if(fembed)target=new URL('https://javsteemplayer.fembed.co/embed/'+fembed[1]+'/');
      } else if(target.hostname.toLowerCase()==='pronxxnx.ggcdn.xyz'){
        var pronxxnx=target.pathname.match(/\/(?:embed\/)?([A-Za-z0-9_-]{5,160})(?:\/|$)/i);
        if(pronxxnx)target=new URL('https://pronxxnx.ggcdn.xyz/embed/'+pronxxnx[1]);
      }
      var media=document.createElement(/\.(mp4|webm|ogg)(?:$|\?)/i.test(target.href)?'video':'iframe');
      media.className=media.tagName==='VIDEO'?'video-preview-media':'video-preview-frame';
      if(media.tagName==='VIDEO'){media.controls=true;media.playsInline=true;media.preload='metadata';media.src=target.href;}
      else{media.src=target.href;media.title='ตัวอย่างเครื่องเล่นวิดีโอ';media.loading='lazy';media.referrerPolicy='strict-origin-when-cross-origin';media.allow='autoplay; fullscreen; encrypted-media; picture-in-picture';}
      videoPreview.appendChild(media);
    };
    videoField.addEventListener('input',updateVideoPreview);
    updateVideoPreview();
  }
  var coverPreview=document.getElementById('editorCoverPreview'),coverPreviewImage=document.getElementById('editorCoverPreviewImage');
  var coverField=form.querySelector('[name="cover"]'),posterField=form.querySelector('[name="poster"]');
  var coverUpload=form.querySelector('[name="coverUpload"]'),posterUpload=form.querySelector('[name="posterUpload"]');
  var coverObjectUrl='';
  if(coverPreview&&coverPreviewImage){
    var updateCoverPreview=function(){
      if(coverObjectUrl){URL.revokeObjectURL(coverObjectUrl);coverObjectUrl='';}
      var selectedFile=(coverUpload&&coverUpload.files&&coverUpload.files[0])||(posterUpload&&posterUpload.files&&posterUpload.files[0]);
      var source='';
      if(selectedFile){coverObjectUrl=URL.createObjectURL(selectedFile);source=coverObjectUrl;}
      else source=(coverField&&coverField.value.trim())||(posterField&&posterField.value.trim())||'';
      if(!source){coverPreview.hidden=true;coverPreviewImage.removeAttribute('src');return;}
      if(!selectedFile){
        try{
          var imageUrl=new URL(source,location.href);
          if(imageUrl.protocol!=='http:'&&imageUrl.protocol!=='https:'){coverPreview.hidden=true;return;}
          source=imageUrl.href;
        }catch(error){coverPreview.hidden=true;return;}
      }
      coverPreviewImage.onload=function(){coverPreview.hidden=false;};
      coverPreviewImage.onerror=function(){coverPreview.hidden=true;};
      coverPreviewImage.src=source;
    };
    [coverField,posterField,coverUpload,posterUpload].forEach(function(input){
      if(input){input.addEventListener(input.type==='file'?'change':'input',updateCoverPreview);}
    });
    updateCoverPreview();
  }
  var hours=document.getElementById('videoHours'),minutes=document.getElementById('videoMinutes'),seconds=document.getElementById('videoSeconds'),duration=document.getElementById('videoDuration');
  var readableDuration=document.getElementById('durationReadable');
  var durationInputs=[hours,minutes,seconds];
  var syncDuration=function(){
    hours.value=hours.value.replace(/\D/g,'').slice(0,5);
    minutes.value=minutes.value.replace(/\D/g,'').slice(0,2);
    seconds.value=seconds.value.replace(/\D/g,'').slice(0,2);
    var hasDuration=durationInputs.some(function(input){return input.value!=='';});
    duration.value=hasDuration?String(parseInt(hours.value||'0',10)).padStart(2,'0')+':'+String(parseInt(minutes.value||'0',10)).padStart(2,'0')+':'+String(parseInt(seconds.value||'0',10)).padStart(2,'0'):'';
    hours.setCustomValidity(hours.value.length>5?'ชั่วโมงมีค่าเกินกำหนด':'');
    minutes.setCustomValidity(minutes.value!==''&&parseInt(minutes.value,10)>59?'นาทีต้องอยู่ระหว่าง 00 ถึง 59':'');
    seconds.setCustomValidity(seconds.value!==''&&parseInt(seconds.value,10)>59?'วินาทีต้องอยู่ระหว่าง 00 ถึง 59':'');
    readableDuration.textContent=hasDuration?'ระยะเวลา '+parseInt(hours.value||'0',10)+' ชั่วโมง '+parseInt(minutes.value||'0',10)+' นาที '+parseInt(seconds.value||'0',10)+' วินาที':'ระบุชั่วโมง นาที และวินาทีได้ เช่น 01:22:30';
  };
  var advanceOnColon=function(input,next,partCount){
    input.addEventListener('input',function(){
      if(input.value.indexOf(':')!==-1){
        var parts=input.value.split(':',partCount);
        input.value=(parts[0]||'').replace(/\D/g,'').slice(0,input===hours?5:2);
        if(parts[1]!==undefined)next.value=parts[1].replace(/\D/g,'').slice(0,2);
        if(input===hours&&parts[2]!==undefined)seconds.value=parts[2].replace(/\D/g,'').slice(0,2);
        next.focus();
      } else if(input!==hours&&input.value.length>=2&&next){next.focus();}
      syncDuration();
    });
  };
  advanceOnColon(hours,minutes,3);
  advanceOnColon(minutes,seconds,2);
  seconds.addEventListener('input',syncDuration);
  [minutes,seconds].forEach(function(input,index){
    input.addEventListener('keydown',function(event){
      if(event.key==='Backspace'&&input.selectionStart===0&&input.selectionEnd===0)durationInputs[index].focus();
    });
  });
  form.addEventListener('submit',function(event){
    syncDuration();
    var invalid=durationInputs.find(function(input){return !input.checkValidity();});
    if(invalid){event.preventDefault();invalid.reportValidity();}
  });
  syncDuration();
  form.querySelectorAll('[data-taxonomy-picker]').forEach(function(picker){
    var type=picker.getAttribute('data-taxonomy-picker');
    var source=form.querySelector('[data-preview="'+type+'"]');
    var selected=picker.querySelector('.taxonomy-selected');
    var input=picker.querySelector('.taxonomy-input');
    var suggestions=picker.querySelector('.taxonomy-suggestions');
    var customField=form.querySelector('[name="new'+(type==='actors'?'Actor':'Tag')+'Names"]');
    var customNames=[];
    var pickerInitialized=false;
    try{customNames=JSON.parse(customField.value||'[]');}catch(error){customNames=[];}
    if(!Array.isArray(customNames))customNames=[];
    var normalize=function(text){return text.trim().toLocaleLowerCase();};
    var syncPicker=function(){
      customField.value=JSON.stringify(customNames);
      selected.replaceChildren();
      Array.prototype.forEach.call(source.options,function(option){
        if(!option.selected)return;
        var chip=document.createElement('span');chip.className='taxonomy-chip';
        var name=document.createElement('span');name.textContent=option.textContent;
        var count=document.createElement('small');count.textContent=(option.getAttribute('data-count')||'0')+' เรื่อง';
        var remove=document.createElement('button');remove.type='button';remove.setAttribute('aria-label','นำ '+option.textContent+' ออก');remove.textContent='×';
        remove.addEventListener('click',function(){option.selected=false;syncPicker();form.dispatchEvent(new Event('change',{bubbles:true}));});
        chip.appendChild(name);chip.appendChild(count);chip.appendChild(remove);selected.appendChild(chip);
      });
      customNames.forEach(function(term,index){
        var chip=document.createElement('span');chip.className='taxonomy-chip is-custom';
        var name=document.createElement('span');name.textContent=term;
        var label=document.createElement('small');label.textContent='เพิ่มเอง';
        var remove=document.createElement('button');remove.type='button';remove.setAttribute('aria-label','นำ '+term+' ออก');remove.textContent='×';
        remove.addEventListener('click',function(){customNames.splice(index,1);syncPicker();form.dispatchEvent(new Event('change',{bubbles:true}));});
        chip.appendChild(name);chip.appendChild(label);chip.appendChild(remove);selected.appendChild(chip);
      });
      if(type==='actors'){
        var actorNameField=form.querySelector('[name="actorEN"]');
        var actorNames=Array.prototype.filter.call(source.options,function(option){return option.selected;}).map(function(option){return option.textContent;}).concat(customNames);
        if(actorNameField){
          if(actorNames.length)actorNameField.value=actorNames.join(', ');
          else if(pickerInitialized)actorNameField.value='';
        }
      }
      pickerInitialized=true;
    };
    var hideSuggestions=function(){if(suggestions)suggestions.hidden=true;input.setAttribute('aria-expanded','false');};
    var addTerm=function(term){
      term=term.trim();if(!term)return;
      var match=null;
      Array.prototype.some.call(source.options,function(option){if(normalize(option.textContent)===normalize(term)){match=option;return true;}return false;});
      if(match)match.selected=true;
      else if(!customNames.some(function(name){return normalize(name)===normalize(term);}))customNames.push(term);
      input.value='';hideSuggestions();syncPicker();form.dispatchEvent(new Event('change',{bubbles:true}));
    };
    var showSuggestions=function(){
      if(!suggestions)return;
      var query=normalize(input.value);
      suggestions.replaceChildren();
      var matches=Array.prototype.filter.call(source.options,function(option){return !option.selected&&(!query||normalize(option.textContent).indexOf(query)!==-1);});
      matches.sort(function(a,b){return parseInt(b.getAttribute('data-count')||'0',10)-parseInt(a.getAttribute('data-count')||'0',10)||a.textContent.localeCompare(b.textContent);});
      matches.slice(0,12).forEach(function(option){
        var button=document.createElement('button');button.type='button';button.className='taxonomy-suggestion';
        var name=document.createElement('span');name.textContent=option.textContent;
        var count=document.createElement('small');count.textContent=(option.getAttribute('data-count')||'0')+' เรื่อง';
        button.appendChild(name);button.appendChild(count);
        button.addEventListener('click',function(){addTerm(option.textContent);});
        suggestions.appendChild(button);
      });
      var exact=Array.prototype.some.call(source.options,function(option){return normalize(option.textContent)===query;});
      if(query&&!exact){
        var create=document.createElement('button');create.type='button';create.className='taxonomy-suggestion is-create';
        create.textContent='เพิ่มชื่อใหม่: '+input.value.trim()+' (กด Enter)';
        create.addEventListener('mousedown',function(event){event.preventDefault();});
        create.addEventListener('click',function(){addTerm(input.value);});
        suggestions.appendChild(create);
      }
      suggestions.hidden=suggestions.childElementCount===0;
      input.setAttribute('aria-expanded',suggestions.hidden?'false':'true');
    };
    input.addEventListener('input',showSuggestions);
    input.addEventListener('focus',showSuggestions);
    input.addEventListener('keydown',function(event){
      if(event.key==='Enter'){event.preventDefault();addTerm(input.value);}
      else if(event.key==='Escape')hideSuggestions();
    });
    input.addEventListener('blur',function(){window.setTimeout(hideSuggestions,120);});
    picker.parentElement.querySelectorAll('.taxonomy-recommendation[data-term-id]').forEach(function(button){
      button.addEventListener('click',function(){addTerm(button.getAttribute('data-term-name')||'');});
    });
    syncPicker();
    source.addEventListener('change',syncPicker);
  });
  var setupTaxonomyAutocomplete=function(inputId,optionsId,suggestionsId){
    var input=form.querySelector('#'+inputId),source=form.querySelector('#'+optionsId),suggestions=form.querySelector('#'+suggestionsId);
    if(!input||!source||!suggestions)return;
    var activeIndex=-1;
    var normalize=function(text){return text.trim().toLocaleLowerCase();};
    var close=function(){suggestions.hidden=true;input.setAttribute('aria-expanded','false');activeIndex=-1;};
    var choose=function(value){
      input.value=value;
      close();
      input.dispatchEvent(new Event('input',{bubbles:true}));
    };
    var render=function(){
      var query=normalize(input.value);
      var options=Array.prototype.filter.call(source.options,function(option){
        return !query||normalize(option.value).indexOf(query)!==-1;
      });
      options.sort(function(a,b){
        return parseInt(b.getAttribute('data-count')||'0',10)-parseInt(a.getAttribute('data-count')||'0',10)||a.value.localeCompare(b.value);
      });
      suggestions.replaceChildren();
      options.slice(0,12).forEach(function(option){
        var button=document.createElement('button');button.type='button';button.className='taxonomy-suggestion';
        var name=document.createElement('span');name.textContent=option.value;
        var count=document.createElement('small');count.textContent=(option.getAttribute('data-count')||'0')+' เรื่อง';
        button.appendChild(name);button.appendChild(count);
        button.addEventListener('mousedown',function(event){event.preventDefault();});
        button.addEventListener('click',function(){choose(option.value);});
        suggestions.appendChild(button);
      });
      var exact=Array.prototype.some.call(source.options,function(option){return normalize(option.value)===query;});
      if(query&&!exact){
        var create=document.createElement('button');create.type='button';create.className='taxonomy-suggestion is-create';
        create.textContent='เพิ่มชื่อใหม่: '+input.value.trim()+' (กด Enter)';
        create.addEventListener('mousedown',function(event){event.preventDefault();});
        create.addEventListener('click',function(){choose(input.value.trim());});
        suggestions.appendChild(create);
      }
      suggestions.hidden=suggestions.childElementCount===0;
      input.setAttribute('aria-expanded',suggestions.hidden?'false':'true');
      activeIndex=-1;
    };
    var activate=function(index){
      var options=suggestions.querySelectorAll('.taxonomy-suggestion:not(.is-create)');
      if(!options.length)return;
      activeIndex=(index+options.length)%options.length;
      Array.prototype.forEach.call(options,function(option,itemIndex){
        option.classList.toggle('is-active',itemIndex===activeIndex);
      });
    };
    input.addEventListener('input',render);
    input.addEventListener('focus',render);
    input.addEventListener('keydown',function(event){
      if(event.key==='ArrowDown'&&!suggestions.hidden){event.preventDefault();activate(activeIndex+1);}
      else if(event.key==='ArrowUp'&&!suggestions.hidden){event.preventDefault();activate(activeIndex<0?0:activeIndex-1);}
      else if(event.key==='Enter'){
        event.preventDefault();
        var options=suggestions.querySelectorAll('.taxonomy-suggestion:not(.is-create)');
        var query=normalize(input.value);
        var exact=Array.prototype.find.call(source.options,function(option){return normalize(option.value)===query;});
        if(activeIndex>=0&&options[activeIndex])choose(options[activeIndex].querySelector('span').textContent);
        else if(exact)choose(exact.value);
        else if(query){input.value=input.value.trim();close();input.dispatchEvent(new Event('input',{bubbles:true}));}
      }else if(event.key==='Escape')close();
    });
    input.addEventListener('blur',function(){window.setTimeout(close,120);});
  };
  setupTaxonomyAutocomplete('postCategoryInput','postCategoryOptions','postCategorySuggestions');
  setupTaxonomyAutocomplete('studioName','studioOptions','studioSuggestions');
  var studioInput=form.querySelector('#studioName');
  if(studioInput)form.querySelectorAll('.taxonomy-recommendation[data-studio-name]').forEach(function(button){
    button.addEventListener('click',function(){studioInput.value=button.getAttribute('data-studio-name')||'';studioInput.dispatchEvent(new Event('input',{bubbles:true}));});
  });
  var categoryInput=form.querySelector('#postCategoryInput');
  if(categoryInput)form.querySelectorAll('.taxonomy-recommendation[data-category-name]').forEach(function(button){
    button.addEventListener('click',function(){categoryInput.value=button.getAttribute('data-category-name')||'';categoryInput.dispatchEvent(new Event('input',{bubbles:true}));});
  });
  var value=function(key){var el=form.querySelector('[data-preview="'+key+'"]');return el?el.value.trim():'';};
  var audit=function(){
    var panel=form.querySelector('#seoAudit'),list=panel.querySelector('[data-seo-checks]'),overall=panel.querySelector('[data-seo-overall]'),scoreEl=panel.querySelector('[data-seo-score]');
    var title=value('title'),rawMetaTitle=value('metaTitle')||title,siteName=panel.getAttribute('data-site-name')||'',metaTitle=rawMetaTitle+(siteName?' | '+siteName:''),metaDesc=value('metaDesc');
    var contentHtml=value('content'),contentNode=document.createElement('div');
    contentNode.innerHTML=contentHtml;
    var contentText=(contentNode.textContent||'').replace(/\s+/g,' ').trim();
    var effectiveDesc=metaDesc||Array.from(contentText).slice(0,140).join('');
    var keyphrase=value('focusKeyphrase'),slug=value('slug');
    var contentWords=0;
    if(contentText){
      if(typeof Intl.Segmenter==='function')contentWords=Array.from(new Intl.Segmenter('th',{granularity:'word'}).segment(contentText)).filter(function(part){return part.isWordLike;}).length;
      else contentWords=contentText.split(/\s+/).length;
    }
    var paragraphText=Array.from(contentNode.querySelectorAll('p,h2,h3,h4,div')).map(function(node){return (node.textContent||'').trim();});
    var longestParagraph=paragraphText.reduce(function(longest,text){return Math.max(longest,Array.from(text).length);},0);
    var missingAlt=Array.from(contentNode.querySelectorAll('img')).filter(function(image){return !image.getAttribute('alt')||!image.getAttribute('alt').trim();}).length;
    var internalLinks=0,externalLinks=0;
    Array.from(contentNode.querySelectorAll('a[href]')).forEach(function(link){
      var href=link.getAttribute('href')||'';
      if(!href||/^(mailto:|tel:|#|javascript:)/i.test(href))return;
      try{
        var url=new URL(href,location.href);
        if(url.protocol!=='http:'&&url.protocol!=='https:')return;
        if(url.hostname===location.hostname)internalLinks++;else externalLinks++;
      }catch(error){}
    });
    var checks=[
      {state:!title?'error':Array.from(title).length>70?'error':Array.from(title).length<15?'warning':'good',text:!title?'ใส่ชื่อเรื่อง':Array.from(title).length>70?'ชื่อเรื่องยาวเกิน 70 ตัวอักษร ('+Array.from(title).length+')':Array.from(title).length<15?'ชื่อเรื่องสั้น ('+Array.from(title).length+' ตัวอักษร)':'ชื่อเรื่องเหมาะสม ('+Array.from(title).length+'/70 ตัวอักษร)'},
      {state:!rawMetaTitle?'error':Array.from(metaTitle).length>70?'error':'good',text:!rawMetaTitle?'ใส่ Meta Title':Array.from(metaTitle).length>70?'Meta Title รวมชื่อเว็บไซต์แล้วเกิน 70 ตัวอักษร ('+Array.from(metaTitle).length+')':'Meta Title รวมชื่อเว็บไซต์แล้ว '+Array.from(metaTitle).length+'/70 ตัวอักษร'},
      {state:!effectiveDesc?'error':Array.from(effectiveDesc).length>140?'error':Array.from(effectiveDesc).length<70?'warning':'good',text:!effectiveDesc?'ใส่ Meta Description':Array.from(effectiveDesc).length>140?'Meta Description เกิน 140 ตัวอักษร':(metaDesc?'Meta Description':'คำอธิบายอัตโนมัติ')+' '+Array.from(effectiveDesc).length+'/140 ตัวอักษร'+(Array.from(effectiveDesc).length<70?' (ควรเพิ่มรายละเอียด)':'')},
      {state:!keyphrase?'error':(title.toLocaleLowerCase().indexOf(keyphrase.toLocaleLowerCase())===-1&&contentText.toLocaleLowerCase().indexOf(keyphrase.toLocaleLowerCase())===-1)?'warning':'good',text:!keyphrase?'ระบุ Focus Keyphrase':(title.toLocaleLowerCase().indexOf(keyphrase.toLocaleLowerCase())===-1&&contentText.toLocaleLowerCase().indexOf(keyphrase.toLocaleLowerCase())===-1)?'ไม่พบ Focus Keyphrase ในชื่อเรื่องหรือเนื้อหา':'พบ Focus Keyphrase ในชื่อเรื่องหรือเนื้อหา'},
      {state:!slug?'error':'good',text:!slug?'ระบุ Slug สำหรับ URL':'มี Slug สำหรับ URL'},
      {state:!contentText?'error':contentWords<100?'warning':(paragraphText.length===0&&contentText.length>350)||longestParagraph>600?'warning':'good',text:!contentText?'เพิ่มเนื้อหาโพสต์':contentWords<100?'เนื้อหาสั้น ('+contentWords+' คำ แนะนำเพิ่มเนื้อหา)':'เนื้อหา '+contentWords+' คำ · '+paragraphText.length+' ย่อหน้า'+(((paragraphText.length===0&&contentText.length>350)||longestParagraph>600)?' · แบ่งย่อหน้าให้สั้นลง':'')},
      {state:missingAlt?'error':'good',text:missingAlt?'รูปในเนื้อหา '+missingAlt+' รูปไม่มี alt':'รูปในเนื้อหามี alt ครบ (ปก/โปสเตอร์ใช้ชื่อเรื่องเป็น alt)'},
      {state:internalLinks?'good':'warning',text:internalLinks?'มีลิงก์ภายในในเนื้อหา '+internalLinks+' ลิงก์':'ยังไม่มีลิงก์ภายในในเนื้อหา (ไม่รวมค่าย/นักแสดง/หมวดหมู่ที่ระบบเชื่อมให้อัตโนมัติ)'},
      {state:externalLinks?'good':'warning',text:externalLinks?'มีลิงก์ภายนอก '+externalLinks+' ลิงก์':'ยังไม่มีลิงก์ภายนอก (ใส่เมื่อมีแหล่งอ้างอิงที่น่าเชื่อถือ)'}
    ];
    list.replaceChildren();
    var penalty=0,hasError=false,hasWarning=false;
    checks.forEach(function(check){
      var item=document.createElement('li');
      item.className='seo-audit-check is-'+check.state;
      item.textContent=check.text;
      list.appendChild(item);
      if(check.state==='error'){penalty+=15;hasError=true;}
      else if(check.state==='warning'){penalty+=6;hasWarning=true;}
    });
    var state=hasError?'error':hasWarning?'warning':'good';
    panel.setAttribute('data-state',state);
    overall.textContent=state==='error'?'ควรแก้รายการสำคัญ':state==='warning'?'มีข้อแนะนำให้ปรับ':'ข้อมูลพื้นฐานพร้อม';
    scoreEl.textContent=Math.max(0,100-penalty)+'/100';
  };
  var update=function(){
    document.getElementById('postPreviewTitle').textContent=value('title')||'ชื่อเรื่องจะแสดงที่นี่';
    var status=value('status'),statusEl=document.getElementById('postPreviewStatus');
    statusEl.textContent=status==='publish'?'เผยแพร่':'ฉบับร่าง';statusEl.className='post-status '+(status==='publish'?'is-published':'is-draft');
    var studio=form.querySelector('#studioName'),studioText=studio?studio.value.trim():'';
    var actorPicker=form.querySelector('[data-taxonomy-picker="actors"]');
    var actors=actorPicker?Array.from(actorPicker.querySelectorAll('.taxonomy-chip span:first-child')).map(function(name){return name.textContent;}):[];
    var tags=Array.from(form.querySelector('[data-preview="tags"]').selectedOptions).map(function(option){return '#'+option.textContent;});
    document.getElementById('postPreviewMeta').textContent=[value('code'),studioText,actors.join(', '),value('category'),tags.join(' ')].filter(Boolean).join(' · ');
    var content=value('content').replace(/<[^>]*>/g,' ').replace(/\s+/g,' ').trim();
    document.getElementById('postPreviewContent').textContent=content||'เนื้อหาตัวอย่างจะแสดงที่นี่';
    document.getElementById('postPreviewSEO').textContent='Focus Keyphrase: '+(value('focusKeyphrase')||'-')+' | Meta Description: '+(value('metaDesc')||'ระบบจะสร้างจากเนื้อหาอัตโนมัติ');
    audit();
  };
  form.addEventListener('input',update);form.addEventListener('change',update);
  update();
})();
</script>
</body>
</html>
<?php
$pageOutput = ob_get_clean();
$csrfField = '<input type="hidden" name="csrf_token" value="'.esc($_SESSION['csrf_token']).'">';
$pageOutput = preg_replace_callback('/<form\b[^>]*method=["\']post["\'][^>]*>/i', function($match) use ($csrfField) {
    return $match[0].$csrfField;
}, $pageOutput);
echo $pageOutput;
?>
