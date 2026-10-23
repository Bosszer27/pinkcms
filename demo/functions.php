<?php
// =============================================
// PINKCMS - functions.php (ฟังก์ชันร่วม)
// =============================================
function slugify($s) {
    $s = strtolower(trim($s));
    $s = preg_replace('/[^a-z0-9ก-๙\s-]/u', '', $s);
    $s = preg_replace('/\s+/', '-', $s);
    $s = preg_replace('/-+/', '-', $s);
    return $s ? $s : 'post-'.time();
}
function stripHtml($s) {
    $s = strip_tags($s);
    $s = preg_replace('/\s+/', ' ', $s);
    return trim($s);
}
function fmtDate($d) {
    if (!$d) return '';
    $ts = strtotime($d);
    return date('d M Y', $ts); // 20 Oct 2026
}
function fmtTime($t) {
    return date('d M H:i', strtotime($t));
}
function fmtThaiDate($value) {
    if (!$value) return '';
    try {
        $date = new DateTimeImmutable($value, new DateTimeZone('Asia/Bangkok'));
    } catch (Exception $error) {
        return '';
    }
    $months = ['ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
    return $date->format('j').' '.$months[(int)$date->format('n')-1].' '.((int)$date->format('Y')+543);
}
function fmtThaiDateTime($value) {
    if (!$value) return '';
    try {
        $date = new DateTimeImmutable($value, new DateTimeZone('Asia/Bangkok'));
    } catch (Exception $error) {
        return '';
    }
    return fmtThaiDate($value).' เวลา '.$date->format('H:i').' น.';
}
function esc($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function storeImageUpload($field) {
    if (!isset($_FILES[$field]) || !is_array($_FILES[$field])) return ['path'=>'','error'=>''];
    $file = $_FILES[$field];
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) return ['path'=>'','error'=>''];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $message = $file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE
            ? 'รูปมีขนาดเกินกำหนด'
            : 'อัปโหลดรูปไม่สำเร็จ กรุณาลองใหม่';
        return ['path'=>'','error'=>$message];
    }
    if (!isset($file['tmp_name'],$file['size']) || !is_uploaded_file($file['tmp_name'])) return ['path'=>'','error'=>'ไฟล์อัปโหลดไม่ถูกต้อง'];
    if ((int)$file['size'] < 1 || (int)$file['size'] > 5 * 1024 * 1024) return ['path'=>'','error'=>'รองรับรูปภาพขนาดไม่เกิน 5 MB'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    if (!$finfo) return ['path'=>'','error'=>'ตรวจสอบชนิดรูปภาพไม่ได้'];
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    $extensions = ['image/jpeg'=>'jpg','image/png'=>'png','image/gif'=>'gif','image/webp'=>'webp'];
    $image = @getimagesize($file['tmp_name']);
    if (!isset($extensions[$mime]) || !$image || !isset($image['mime']) || $image['mime'] !== $mime) {
        return ['path'=>'','error'=>'รองรับเฉพาะรูป JPG, PNG, GIF และ WebP'];
    }
    $directory = __DIR__.DIRECTORY_SEPARATOR.'uploads';
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        return ['path'=>'','error'=>'สร้างโฟลเดอร์เก็บรูปไม่ได้ กรุณาตรวจสอบสิทธิ์เขียนไฟล์ของเซิร์ฟเวอร์'];
    }
    try {
        $filename = bin2hex(random_bytes(16)).'.'.$extensions[$mime];
    } catch (Exception $e) {
        error_log('PinkCMS could not create a secure upload filename: '.$e->getMessage());
        return ['path'=>'','error'=>'เตรียมชื่อไฟล์อัปโหลดไม่สำเร็จ'];
    }
    if (!move_uploaded_file($file['tmp_name'], $directory.DIRECTORY_SEPARATOR.$filename)) {
        return ['path'=>'','error'=>'บันทึกรูปลงเซิร์ฟเวอร์ไม่ได้ กรุณาตรวจสอบสิทธิ์โฟลเดอร์ uploads'];
    }
    $basePath = str_replace('\\','/',dirname(dirname(isset($_SERVER['SCRIPT_NAME'])?$_SERVER['SCRIPT_NAME']:'/pinkcms-php/admin/index.php')));
    return ['path'=>rtrim($basePath,'/').'/uploads/'.$filename,'error'=>''];
}
function storeMultipleImageUploads($field) {
    if (!isset($_FILES[$field]) || !is_array($_FILES[$field]) || !isset($_FILES[$field]['name']) || !is_array($_FILES[$field]['name'])) {
        return ['paths'=>[],'error'=>''];
    }
    $files=$_FILES[$field];$paths=[];
    foreach($files['name'] as $index=>$name) {
        if (!isset($files['error'][$index]) || $files['error'][$index]===UPLOAD_ERR_NO_FILE) continue;
        $single=[];
        foreach(['name','type','tmp_name','error','size'] as $key) $single[$key]=isset($files[$key][$index])?$files[$key][$index]:null;
        $original=isset($_FILES[$field])?$_FILES[$field]:null;
        $_FILES[$field]=$single;
        $upload=storeImageUpload($field);
        if ($original!==null) $_FILES[$field]=$original;
        if ($upload['error']!=='') return ['paths'=>$paths,'error'=>$upload['error']];
        if ($upload['path']!=='') $paths[]=$upload['path'];
    }
    return ['paths'=>$paths,'error'=>''];
}
function validImageReference($value) {
    $value = trim($value);
    if ($value === '') return true;
    if (preg_match('/[\x00-\x20<>"]/',$value)) return false;
    if (preg_match('/^https?:\/\/[^\s]+$/i',$value)) return true;
    return isset($value[0]) && $value[0] === '/' && substr($value,0,2) !== '//';
}
function validAdDestinationUrl($value) {
    $value = trim((string)$value);
    if ($value === '' || preg_match('/[\x00-\x20<>"]/',$value)) return false;
    $parts = parse_url($value);
    return is_array($parts) && isset($parts['scheme'],$parts['host']) &&
        in_array(strtolower($parts['scheme']),['http','https'],true) &&
        !isset($parts['user']) && !isset($parts['pass']);
}
function getStudio($id) {
    global $conn;
    $id = intval($id);
    $r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM studios WHERE id=$id"));
    return $r ? $r : ['id'=>0,'name'=>'ไม่ระบุ','slug'=>'','desc'=>''];
}
function parseTaxonomyIds($value) {
    $ids = is_array($value) ? $value : json_decode((string)$value, true);
    if (!is_array($ids)) $ids = preg_split('/[,،;|，；\r\n]+/u', (string)$value) ?: [];
    return array_values(array_unique(array_filter(array_map('intval', $ids), function($id) {
        return $id > 0;
    })));
}
function getActor($id) {
    global $conn;
    $id = intval($id);
    $r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM actors WHERE id=$id"));
    return $r ? $r : ['id'=>0,'name'=>'ไม่ระบุ','slug'=>'','bio'=>''];
}
function getTag($id) {
    global $conn;
    $id = intval($id);
    $r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tags WHERE id=$id"));
    return $r ? $r : ['id'=>0,'name'=>'ไม่ระบุ','slug'=>''];
}
function publishedPosts() {
    global $conn;
    $res = mysqli_query($conn, "SELECT * FROM posts WHERE status='publish' ORDER BY date DESC");
    $out = [];
    while ($r = mysqli_fetch_assoc($res)) $out[] = $r;
    return $out;
}
function normalizeVideoInput($input) {
    $input = trim((string)$input);
    if ($input === '') return ['url'=>'','error'=>''];
    if (stripos($input, '<iframe') !== false) {
        if (!preg_match('/^\s*<iframe\b[^>]*\bsrc\s*=\s*(["\'])(.*?)\1[^>]*>\s*(?:<\/iframe\s*>)?\s*$/is', $input, $match)) {
            return ['url'=>'','error'=>'วาง iframe เต็มรูปแบบที่มี src URL เดียวเท่านั้น'];
        }
        $input = trim(html_entity_decode($match[2], ENT_QUOTES|ENT_HTML5, 'UTF-8'));
    }
    if (preg_match('/^[A-Za-z0-9_-]{5,120}$/', $input)) {
        $input = 'https://javsteemplayer.fembed.co/embed/'.$input.'/';
    }
    if (substr($input,0,2)==='//') $input='https:'.$input;
    if (preg_match('/[\x00-\x20<>"]/',$input)) return ['url'=>'','error'=>'URL วิดีโอมีรูปแบบไม่ถูกต้อง'];
    $parts = parse_url($input);
    if (!$parts || empty($parts['scheme']) || !in_array(strtolower($parts['scheme']),['http','https'],true) || empty($parts['host']) || isset($parts['user']) || isset($parts['pass'])) {
        return ['url'=>'','error'=>'ใช้ลิงก์วิดีโอ http(s) หรือ iframe ที่มีลิงก์ http(s) เท่านั้น'];
    }
    return ['url'=>$input,'error'=>''];
}
function videoHost($url) {
    $host = strtolower((string)parse_url($url, PHP_URL_HOST));
    return preg_replace('/^www\./','',$host);
}
function isKnownVideoPlayer($url) {
    $host = videoHost($url);
    $known = ['javfunxxx.javzero.xyz','movie8k.ggcdn.xyz','sexjapanhd.ggcdn.xyz','avmoviexxx.ggcdn.xyz','pronkub.ggcdn.xyz','pronxxnx.ggcdn.xyz'];
    if (in_array($host,$known,true)) return true;
    return preg_match('/(^|\.)fembed\.(co|com|net)$/',$host)===1;
}
function videoApiRemote($source) {
    $token = getenv('PINKCMS_VDOHIDE_TOKEN');
    if ($token===false || trim($token)==='') return ['url'=>'','error'=>''];
    $sourceInfo = normalizeVideoInput($source);
    if ($sourceInfo['error']!=='' || $sourceInfo['url']==='') return ['url'=>'','error'=>$sourceInfo['error']];
    if (isKnownVideoPlayer($sourceInfo['url'])) return ['url'=>$sourceInfo['url'],'error'=>''];
    if (!function_exists('curl_init')) return ['url'=>'','error'=>'เซิร์ฟเวอร์ยังไม่ได้เปิด PHP cURL สำหรับ Remote API'];
    $payload = json_encode(['source'=>$sourceInfo['url'],'token'=>trim($token)]);
    if ($payload===false) return ['url'=>'','error'=>'เตรียมข้อมูลสำหรับ Remote API ไม่สำเร็จ'];
    $curl = curl_init('https://vdohide.com/api/remote');
    if ($curl===false) return ['url'=>'','error'=>'เริ่มการเชื่อมต่อ Remote API ไม่สำเร็จ'];
    $optionsSet=curl_setopt_array($curl,[
        CURLOPT_POST=>true,
        CURLOPT_POSTFIELDS=>$payload,
        CURLOPT_HTTPHEADER=>['Content-Type: application/json','Accept: application/json'],
        CURLOPT_RETURNTRANSFER=>true,
        CURLOPT_CONNECTTIMEOUT=>5,
        CURLOPT_TIMEOUT=>15,
        CURLOPT_FOLLOWLOCATION=>false,
        CURLOPT_SSL_VERIFYPEER=>true,
        CURLOPT_SSL_VERIFYHOST=>2
    ]);
    if (!$optionsSet) {
        curl_close($curl);
        return ['url'=>'','error'=>'ตั้งค่าการเชื่อมต่อ Remote API ไม่สำเร็จ'];
    }
    $body = curl_exec($curl);
    $status = (int)curl_getinfo($curl,CURLINFO_HTTP_CODE);
    $error = curl_error($curl);
    curl_close($curl);
    if ($body===false || $status<200 || $status>=300) {
        error_log('PinkCMS remote video API failed; HTTP '.$status.($error!==''?' '.$error:''));
        return ['url'=>'','error'=>'Remote API ไม่ตอบกลับสำเร็จ (HTTP '.$status.') บันทึกลิงก์ต้นฉบับไว้แล้ว'];
    }
    $data = json_decode($body,true);
    $slug = is_array($data) && isset($data['slug'])?(string)$data['slug']:'';
    if (!preg_match('/^[A-Za-z0-9_-]{3,160}$/',$slug)) {
        error_log('PinkCMS remote video API returned an invalid response');
        return ['url'=>'','error'=>'Remote API ไม่ส่ง player ID ที่ถูกต้อง บันทึกลิงก์ต้นฉบับไว้แล้ว'];
    }
    return ['url'=>'https://javsteemplayer.fembed.co/embed/'.$slug.'/','error'=>''];
}
function videoEmbedUrl($url) {
    $parsed = parseVideo($url);
    return isset($parsed['embedUrl'])?$parsed['embedUrl']:'';
}
function parseVideo($input) {
    $normalized = normalizeVideoInput($input);
    if ($normalized['error']!=='' || $normalized['url']==='') return ['embed'=>'','thumb'=>'','embedUrl'=>'','contentUrl'=>'','error'=>$normalized['error']];
    $url = $normalized['url'];
    $host = videoHost($url);
    if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([A-Za-z0-9_-]{11})/i',$url,$match)) {
        $id = $match[1];
        $embedUrl='https://www.youtube.com/embed/'.$id;
        return ['embed'=>'<iframe src="'.esc($embedUrl).'" title="Video player" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>','thumb'=>'https://img.youtube.com/vi/'.$id.'/hqdefault.jpg','embedUrl'=>$embedUrl,'contentUrl'=>'','error'=>''];
    }
    if ($host==='drive.google.com') {
        $driveUrl=preg_replace('/\/view(?:\?.*)?$/','/preview',$url);
        $driveUrl=preg_replace('/([?&])usp=[^&]+/','',$driveUrl);
        return ['embed'=>'<iframe src="'.esc($driveUrl).'" title="Video player" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allow="autoplay; fullscreen"></iframe>','thumb'=>'','embedUrl'=>$driveUrl,'contentUrl'=>'','error'=>''];
    }
    if (preg_match('/(^|\.)fembed\.(co|com|net)$/',$host) && preg_match('~/((?:embed|v|f)/)?([A-Za-z0-9_-]{5,160})/?(?:\?.*)?$~i',parse_url($url,PHP_URL_PATH),$match)) {
        $embedUrl='https://javsteemplayer.fembed.co/embed/'.$match[2].'/';
        return ['embed'=>'<iframe src="'.esc($embedUrl).'" title="Video player" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allow="autoplay; fullscreen; encrypted-media; picture-in-picture"></iframe>','thumb'=>'','embedUrl'=>$embedUrl,'contentUrl'=>'','error'=>''];
    }
    if ($host==='pronxxnx.ggcdn.xyz' && preg_match('~/(?:embed/)?([A-Za-z0-9_-]{5,160})(?:/|$)~',parse_url($url,PHP_URL_PATH),$match)) {
        $embedUrl='https://pronxxnx.ggcdn.xyz/embed/'.$match[1];
        return ['embed'=>'<iframe src="'.esc($embedUrl).'" title="Video player" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allow="autoplay; fullscreen; encrypted-media; picture-in-picture"></iframe>','thumb'=>'','embedUrl'=>$embedUrl,'contentUrl'=>'','error'=>''];
    }
    if (preg_match('/\.(mp4|webm|ogg)(?:$|\?)/i',$url)) {
        return ['embed'=>'<video src="'.esc($url).'" controls playsinline preload="metadata"></video>','thumb'=>'','embedUrl'=>'','contentUrl'=>$url,'error'=>''];
    }
    $embedUrl=$url;
    return ['embed'=>'<iframe src="'.esc($embedUrl).'" title="Video player" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allow="autoplay; fullscreen; encrypted-media; picture-in-picture"></iframe>','thumb'=>'','embedUrl'=>$embedUrl,'contentUrl'=>'','error'=>''];
}
function isoVideoDuration($duration) {
    $parts=videoDurationParts($duration);
    if ($parts===null) return '';
    list($hours,$minutes,$seconds)=$parts;
    return 'PT'.($hours?$hours.'H':'').($minutes?$minutes.'M':'').($seconds?$seconds.'S':'0S');
}
function videoDurationParts($duration) {
    $duration=trim((string)$duration);
    if ($duration==='') return [0,0,0];
    if (preg_match('/^(\d{1,5}):([0-5]\d):([0-5]\d)$/',$duration,$match)) {
        return [(int)$match[1],(int)$match[2],(int)$match[3]];
    }
    if (preg_match('/^\d{1,7}$/',$duration)) {
        $total=(int)$duration;
        return [(int)floor($total/3600),(int)floor(($total%3600)/60),$total%60];
    }
    if (preg_match('/^(\d{1,7}):([0-5]\d)$/',$duration,$match)) {
        $first=(int)$match[1];
        if ($first>999) {
            $total=$first;
            return [(int)floor($total/3600),(int)floor(($total%3600)/60),$total%60];
        }
        return [(int)floor($first/60),$first%60,(int)$match[2]];
    }
    return null;
}
function formatVideoDuration($duration) {
    $parts=videoDurationParts($duration);
    if ($parts===null || trim((string)$duration)==='') return '';
    list($hours,$minutes,$seconds)=$parts;
    return sprintf('%02d:%02d:%02d',$hours,$minutes,$seconds);
}
function autoMeta($p) {
    $mt = !empty($p['metaTitle']) ? $p['metaTitle'] : $p['title'];
    $md = !empty($p['metaDesc']) ? $p['metaDesc'] : mb_substr(stripHtml($p['content']), 0, 140, 'UTF-8');
    return ['title'=>$mt, 'desc'=>$md];
}
function clockIcon() {
    return '<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>';
}
function themeOn($key) { return setting('theme_'.$key, '1') === '1'; }
function toast($msg) { echo '<script>window.addEventListener("load",function(){var t=document.createElement("div");t.className="toast";t.textContent="'.esc($msg).'";document.body.appendChild(t);setTimeout(function(){t.remove();},2500);});</script>'; }
