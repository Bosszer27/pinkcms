USE `pinkcms_demo`;

INSERT INTO `settings` (`skey`, `svalue`) VALUES
('siteName', 'PinkCMS Demo'),
('siteDesc', 'ตัวอย่างเว็บไซต์ PinkCMS สำหรับทดลองระบบจัดการเนื้อหา'),
('site_home_featured_title', 'ตัวอย่างเรื่องแนะนำ'),
('site_home_featured_all_label', 'ดูตัวอย่างทั้งหมด'),
('site_home_featured_limit', '6'),
('site_home_featured_interval_seconds', '6'),
('site_banner_interval_seconds', '5'),
('site_ad_label', 'ตัวอย่างพื้นที่โฆษณา'),
('seo_keywords', 'PinkCMS, demo, sample'),
('theme_accent', '#ff2d78'),
('theme_showViews', '1'),
('theme_showStudio', '1'),
('theme_showActors', '1'),
('theme_showTags', '1'),
('theme_showDuration', '1'),
('theme_showAnnouncement', '1'),
('announcement', 'เว็บไซต์ตัวอย่าง: ข้อมูลทั้งหมดเป็นข้อมูลสมมติ'),
('site_menu_header', '[{"label":"นักแสดงตัวอย่าง","type":"actor_list","value":""},{"label":"ค่ายตัวอย่าง","type":"studio_list","value":""},{"label":"เรื่องทั้งหมด","type":"all","value":""},{"label":"หมวดหมู่","type":"categories","value":""}]'),
('site_menu_explore', '[{"label":"เรื่องทั้งหมด","type":"all","value":""},{"label":"หมวดหมู่","type":"categories","value":""}]'),
('site_menu_bottom', '[{"label":"หน้าหลัก","type":"home","value":""},{"label":"ค่าย","type":"studio_list","value":""},{"label":"นักแสดง","type":"actor_list","value":""},{"label":"แท็ก","type":"tag_list","value":""}]');

INSERT INTO `studios` (`id`, `name`, `slug`, `logo`, `desc`) VALUES
(1, 'Demo Studio One', 'demo-studio-one', '', 'ค่ายสมมติสำหรับข้อมูลสาธิต'),
(2, 'Demo Studio Two', 'demo-studio-two', '', 'ค่ายสมมติสำหรับข้อมูลสาธิต');

INSERT INTO `actors` (`id`, `name`, `slug`, `photo`, `bio`) VALUES
(1, 'Demo Actor A', 'demo-actor-a', '', 'นักแสดงสมมติสำหรับทดสอบระบบ'),
(2, 'Demo Actor B', 'demo-actor-b', '', 'นักแสดงสมมติสำหรับทดสอบระบบ'),
(3, 'Demo Actor C', 'demo-actor-c', '', 'นักแสดงสมมติสำหรับทดสอบระบบ');

INSERT INTO `tags` (`id`, `name`, `slug`) VALUES
(1, 'ตัวอย่าง', 'sample'),
(2, 'แนะนำ', 'featured'),
(3, 'ทดสอบระบบ', 'demo-test');

INSERT INTO `posts`
(`id`, `code`, `title`, `slug`, `content`, `cover`, `posterUrl`, `videoUrl`,
 `embedUrl`, `videoDuration`, `gallery`, `studioId`, `actorIds`, `actorEN`,
 `tagIds`, `category`, `focusKeyphrase`, `metaTitle`, `metaDesc`, `keywords`,
 `status`, `date`, `author`, `createdAt`, `views`) VALUES
(1, 'DEMO-001', 'ตัวอย่างโพสต์: แนะนำ PinkCMS', 'demo-001-pinkcms-intro',
 'นี่คือเนื้อหาสมมติสำหรับแสดงตัวอย่างการ์ดโพสต์และหน้ารายละเอียดของ PinkCMS\n\nคุณสามารถแก้ไขหรือลบข้อมูลนี้ได้จากฐานข้อมูลสาธิต',
 'assets/demo-cover-one.svg', 'assets/demo-cover-one.svg', '', '', '00:12:18',
 '[]', 1, '[1]', 'Demo Actor A', '[1,2]', 'ตัวอย่าง',
 'ตัวอย่างเว็บไซต์ PinkCMS', 'ตัวอย่างโพสต์: แนะนำ PinkCMS',
 'ข้อมูลสมมติสำหรับทดลองการแสดงผลของเว็บไซต์ PinkCMS',
 'demo, sample, PinkCMS', 'publish', '2026-10-24', 'Demo', '2026-10-24 12:00:00', 128),
(2, 'DEMO-002', 'ตัวอย่างโพสต์: จัดการหมวดหมู่', 'demo-002-categories',
 'โพสต์ตัวอย่างนี้ใช้สาธิตการเชื่อมโยงหมวดหมู่ ค่าย และนักแสดง\n\nข้อมูลทุกส่วนเป็นข้อมูลสมมติ ไม่ได้อ้างอิงบุคคลหรือผลงานจริง',
 'assets/demo-cover-two.svg', 'assets/demo-cover-two.svg', '', '', '00:08:45',
 '[]', 2, '[2]', 'Demo Actor B', '[1,3]', 'เบื้องหลัง',
 'ตัวอย่างการจัดหมวดหมู่', 'ตัวอย่างโพสต์: จัดการหมวดหมู่',
 'ข้อมูลสาธิตการแสดงหมวดหมู่และแท็กของ PinkCMS',
 'demo, categories, sample', 'publish', '2026-10-23', 'Demo', '2026-10-23 12:00:00', 76),
(3, 'DEMO-003', 'ตัวอย่างโพสต์: ตั้งค่าเว็บไซต์', 'demo-003-site-settings',
 'ตัวอย่างเนื้อหาสำหรับทดลองช่องค้นหา การนำทาง และการตั้งค่าเว็บไซต์\n\nเปลี่ยนข้อความได้จากแผงควบคุมหลังบ้าน',
 'assets/demo-cover-three.svg', 'assets/demo-cover-three.svg', '', '', '00:15:30',
 '[]', 1, '[1,3]', 'Demo Actor A, Demo Actor C', '[2,3]', 'คู่มือ',
 'การตั้งค่า PinkCMS', 'ตัวอย่างโพสต์: ตั้งค่าเว็บไซต์',
 'ตัวอย่างข้อมูลสำหรับตรวจสอบหน้าเว็บไซต์และเครื่องมือจัดการเนื้อหา',
 'demo, settings, PinkCMS', 'publish', '2026-10-22', 'Demo', '2026-10-22 12:00:00', 42);

INSERT INTO `banners` (`id`, `title`, `link`, `image`, `text`, `active`) VALUES
(1, 'ตัวอย่างโพสต์: แนะนำ PinkCMS', 'index.php?page=post&slug=demo-001-pinkcms-intro', '', '', 1),
(2, 'ตัวอย่างโพสต์: จัดการหมวดหมู่', 'index.php?page=post&slug=demo-002-categories', '', '', 1),
(3, 'ตัวอย่างโพสต์: ตั้งค่าเว็บไซต์', 'index.php?page=post&slug=demo-003-site-settings', '', '', 1);
