-- ============================================================
-- CYBER DASHBOARD PROFILE — FULL DATABASE MIGRATION v2
-- Database: profile_db
-- ============================================================

CREATE DATABASE IF NOT EXISTS `profile_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `profile_db`;

-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: profile_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `achievements`
--

DROP TABLE IF EXISTS `achievements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `achievements` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `icon_class` varchar(200) NOT NULL,
  `title` varchar(300) NOT NULL,
  `content` varchar(500) DEFAULT '',
  `sort_order` int(11) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `achievements`
--

LOCK TABLES `achievements` WRITE;
/*!40000 ALTER TABLE `achievements` DISABLE KEYS */;
INSERT INTO `achievements` VALUES (1,'fas fa-globe','500+ Website Hoàn Thành','Từ landing page đến hệ thống enterprise',1,1,'2026-06-24 04:57:29'),(2,'fas fa-shopping-cart','10.000+ Đơn Hàng Xử Lý','Tỉ lệ thành công 99.8%',2,1,'2026-06-24 04:57:29'),(3,'fas fa-calendar-check','5+ Năm Kinh Nghiệm','Làm việc với khách hàng toàn cầu',3,1,'2026-06-24 04:57:29'),(4,'fas fa-users','1.000+ Khách Hàng','Đối tác tin cậy lâu dài',4,1,'2026-06-24 04:57:29'),(5,'fas fa-star','99% Đánh Giá Tích Cực','Cam kết chất lượng dịch vụ',5,1,'2026-06-24 04:57:29'),(6,'fas fa-headset','Hỗ Trợ 24/7','Luôn sẵn sàng hỗ trợ kịp thời',6,1,'2026-06-24 04:57:29');
/*!40000 ALTER TABLE `achievements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `banks`
--

DROP TABLE IF EXISTS `banks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `banks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `logo` varchar(500) DEFAULT '',
  `bank_name` varchar(200) NOT NULL,
  `bank_short` varchar(50) DEFAULT '',
  `account_holder` varchar(200) NOT NULL,
  `account_number` varchar(100) NOT NULL,
  `branch` varchar(300) DEFAULT '',
  `qr_code` varchar(500) DEFAULT '',
  `description` text DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `banks`
--

LOCK TABLES `banks` WRITE;
/*!40000 ALTER TABLE `banks` DISABLE KEYS */;
INSERT INTO `banks` VALUES (7,'','Ngân hàng MBB Quân đội','MBBANK','LE VU PHONG','23001999999999','TPHCM','','',1,'2026-06-24 05:12:06');
/*!40000 ALTER TABLE `banks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contacts`
--

DROP TABLE IF EXISTS `contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contacts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT '',
  `message` text NOT NULL,
  `ip_address` varchar(45) DEFAULT '',
  `user_agent` varchar(500) DEFAULT '',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contacts`
--

LOCK TABLES `contacts` WRITE;
/*!40000 ALTER TABLE `contacts` DISABLE KEYS */;
INSERT INTO `contacts` VALUES (1,'levuphong01','levuphongg09@gmail.com','0394756564','tyutyutyityityityirtyi','::1','',0,'2026-06-24 05:22:34');
/*!40000 ALTER TABLE `contacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login_attempts`
--

DROP TABLE IF EXISTS `login_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `login_attempts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `attempts` int(11) DEFAULT 1,
  `last_attempt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ip_address` (`ip_address`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_attempts`
--

LOCK TABLES `login_attempts` WRITE;
/*!40000 ALTER TABLE `login_attempts` DISABLE KEYS */;
INSERT INTO `login_attempts` VALUES (1,'::1',1,'2026-06-24 05:11:13'),(2,'::1',1,'2026-06-24 05:11:52'),(3,'::1',1,'2026-06-24 05:12:10'),(4,'::1',1,'2026-06-24 05:17:00');
/*!40000 ALTER TABLE `login_attempts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `media`
--

DROP TABLE IF EXISTS `media`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `media` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `filename` varchar(500) NOT NULL,
  `original_name` varchar(500) NOT NULL,
  `path` varchar(500) NOT NULL,
  `url` varchar(500) NOT NULL,
  `type` varchar(100) DEFAULT 'image',
  `size` int(11) DEFAULT 0,
  `folder` varchar(100) DEFAULT 'general',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `media`
--

LOCK TABLES `media` WRITE;
/*!40000 ALTER TABLE `media` DISABLE KEYS */;
INSERT INTO `media` VALUES (5,'ad7cc35ad37f591db9a2195645b6860e.jpg','photo_2025-10-24_23-57-56.jpg','C:\\xampp\\htdocs\\profile/assets/uploads/avatars/ad7cc35ad37f591db9a2195645b6860e.jpg','http://localhost/profile/assets/uploads/avatars/ad7cc35ad37f591db9a2195645b6860e.jpg','image',35099,'avatars','2026-06-24 06:23:19'),(6,'1c368b6da6689229c4c8b4ddd810c833.png','dvgr.png','C:\\xampp\\htdocs\\profile/assets/uploads/website_logos/1c368b6da6689229c4c8b4ddd810c833.png','http://localhost/profile/assets/uploads/website_logos/1c368b6da6689229c4c8b4ddd810c833.png','image',219552,'website_logos','2026-06-24 06:44:25');
/*!40000 ALTER TABLE `media` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `online_sessions`
--

DROP TABLE IF EXISTS `online_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `online_sessions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `session_id` varchar(128) NOT NULL,
  `ip_address` varchar(45) DEFAULT '',
  `last_seen` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_id` (`session_id`)
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `online_sessions`
--

LOCK TABLES `online_sessions` WRITE;
/*!40000 ALTER TABLE `online_sessions` DISABLE KEYS */;
INSERT INTO `online_sessions` VALUES (99,'gtil1mqfg3ad7cu7027e05gt67','::1','2026-06-24 06:52:43');
/*!40000 ALTER TABLE `online_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `page_sections`
--

DROP TABLE IF EXISTS `page_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `page_sections` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `section_key` varchar(100) NOT NULL,
  `section_name` varchar(200) NOT NULL,
  `icon_class` varchar(100) DEFAULT 'fas fa-layer-group',
  `sort_order` int(11) DEFAULT 0,
  `visible` tinyint(1) DEFAULT 1,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `section_key` (`section_key`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `page_sections`
--

LOCK TABLES `page_sections` WRITE;
/*!40000 ALTER TABLE `page_sections` DISABLE KEYS */;
INSERT INTO `page_sections` VALUES (1,'websites','Website Ecosystem','fas fa-globe',1,1,'Hệ Sinh Thái <span class=\"grad\">Sản Phẩm</span>','Các nền tảng và sản phẩm công nghệ đang vận hành trong hệ sinh thái'),(2,'about','About Me','fas fa-user',2,1,'Tôi Là <span class=\"grad\">Ai?</span>','Giới thiệu ngắn gọn về bản thân và định hướng'),(3,'social','Social Network','fas fa-share-alt',3,1,'Kết Nối <span class=\"grad\">Với Tôi</span>','Theo dõi tôi trên các nền tảng để cập nhật dự án mới nhất'),(4,'skills','Tech Stack','fas fa-code',4,1,'Tech <span class=\"grad\">Stack</span>','Công nghệ và công cụ tôi sử dụng hàng ngày để xây dựng sản phẩm'),(5,'payment','Payment Methods','fas fa-credit-card',5,1,'Thông Tin <span class=\"grad\">Ngân Hàng</span>','Phương thức thanh toán và giao dịch an toàn'),(6,'achievements','Achievements','fas fa-trophy',6,1,'Những Gì <span class=\"grad\">Đã Đạt Được</span>','Giải thưởng, chứng chỉ và các cột mốc quan trọng trong sự nghiệp'),(7,'services','My Services','fas fa-briefcase',7,1,'Tôi Có Thể <span class=\"grad\">Giúp Gì?</span>','Các dịch vụ chuyên nghiệp tôi cung cấp để giải quyết vấn đề của bạn'),(8,'statistics','Live Statistics','fas fa-chart-bar',8,1,'Live <span class=\"grad\">Statistics</span>','Số liệu thống kê trực tiếp từ hệ thống'),(9,'reviews','Customer Reviews','fas fa-star',9,1,'Khách Hàng <span class=\"grad\">Nói Gì?</span>','Đánh giá chân thực từ đối tác và khách hàng đã làm việc cùng');
/*!40000 ALTER TABLE `page_sections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `services` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `icon_class` varchar(200) NOT NULL,
  `name` varchar(300) NOT NULL,
  `description` text DEFAULT NULL,
  `price` varchar(100) DEFAULT '',
  `link` varchar(500) DEFAULT '',
  `sort_order` int(11) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `services`
--

LOCK TABLES `services` WRITE;
/*!40000 ALTER TABLE `services` DISABLE KEYS */;
INSERT INTO `services` VALUES (1,'fas fa-code','Tạo Website','Website chuẩn SEO, responsive, tốc độ cao, giao diện đẹp.','Liên hệ','https://dichvugiare.net/website',1,1,'2026-06-24 04:57:29'),(2,'fas fa-server','Hosting','Máy chủ tốc độ cao, uptime 99.9%, băng thông không giới hạn.','Liên hệ','https://dichvugiare.net/hosting',2,1,'2026-06-24 04:57:29'),(3,'fas fa-hdd','VPS','Máy chủ ảo riêng tư, hiệu năng mạnh mẽ, chống DDoS.','Liên hệ','https://dichvugiare.net/cloudvps',3,1,'2026-06-24 04:57:29'),(4,'fas fa-network-wired','Proxy','Hệ thống proxy private tốc độ cao, ổn định, ẩn danh an toàn.','Liên hệ','https://dichvugiare.net/proxy',4,1,'2026-06-24 04:57:29'),(5,'fas fa-laptop-code','Mã Nguồn','Cung cấp script, theme, plugin chất lượng cao giá tốt.','Liên hệ','https://dichvugiare.net/services',5,1,'2026-06-24 04:57:29'),(6,'fas fa-palette','Thiết Kế Logo','Nhận diện thương hiệu chuyên nghiệp ấn tượng.','Liên hệ','https://dichvugiare.net/logo',6,1,'2026-06-24 04:57:29'),(7,'fas fa-globe','Tên Miền','Đăng ký domain .vn, .com, .net giá rẻ nhất thị trường.','Liên hệ','https://dichvugiare.net/domain',7,1,'2026-06-24 04:57:29'),(8,'fas fa-star','Test Service 123','Desc 123','','',0,1,'2026-06-24 06:34:09');
/*!40000 ALTER TABLE `services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` longtext DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=127 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'name','Le Vu Phong','2026-06-24 06:37:56'),(2,'title','DICHVUGIARE.NET','2026-06-24 06:37:56'),(3,'tagline','Chuyên cung cấp giải pháp công nghệ, thiết kế website, hosting, VPS, proxy, tên miền, mã nguồn và nhận diện thương hiệu.','2026-06-24 06:37:56'),(4,'bio','Chuyên cung cấp giải pháp công nghệ, thiết kế website, hosting, VPS, proxy, tên miền, mã nguồn và nhận diện thương hiệu.','2026-06-24 06:37:56'),(5,'about_me','<p>Tôi là một <strong>Fullstack Developer</strong> đam mê công nghệ và luôn theo đuổi những giải pháp tối ưu nhất cho từng dự án.</p><p>Với kinh nghiệm làm việc tại nhiều công ty công nghệ hàng đầu, tôi đã xây dựng và quản lý hàng trăm hệ thống web từ quy mô nhỏ đến enterprise.</p><p>Tôi tin rằng code tốt không chỉ là code chạy được — mà phải <em>đẹp, maintainable và scalable</em>.</p>','2026-06-24 06:37:56'),(6,'experience','5+ Năm Kinh Nghiệm','2026-06-24 06:37:56'),(7,'goals','Phát triển các sản phẩm SaaS có tác động lớn đến cộng đồng developer Việt Nam và quốc tế.','2026-06-24 06:37:56'),(8,'email','levuphongcskh@gmail.com','2026-06-24 06:37:56'),(9,'phone','0855550612','2026-06-24 06:37:56'),(10,'address','TP. Hồ Chí Minh, Việt Nam','2026-06-24 06:37:56'),(11,'avatar','http://localhost/profile/assets/uploads/avatars/ad7cc35ad37f591db9a2195645b6860e.jpg','2026-06-24 06:23:19'),(12,'cover','','2026-06-24 04:57:29'),(13,'badge_1','Fullstack Developer','2026-06-24 06:37:56'),(14,'badge_2','UI/UX Designer','2026-06-24 06:37:56'),(15,'badge_3','System Architect','2026-06-24 06:37:56'),(16,'badge_4','DevOps Engineer','2026-06-24 06:37:56'),(17,'badge_5','Founder','2026-06-24 06:37:56'),(18,'accent_color','#6366F1','2026-06-24 05:07:49'),(19,'accent_secondary','#8B5CF6','2026-06-24 05:07:49'),(20,'bg_color','#050505','2026-06-24 05:07:49'),(21,'card_color','#0D0D0D','2026-06-24 05:07:49'),(22,'border_color','#1A1A1A','2026-06-24 05:07:49'),(23,'text_color','#FFFFFF','2026-06-24 05:07:49'),(24,'text_secondary','#B8B8B8','2026-06-24 05:07:49'),(25,'site_name','Cyber Dashboard Profile','2026-06-24 04:57:29'),(26,'meta_title','DICHVUGIARE.NET - Global Technology Ecosystem','2026-06-24 06:39:46'),(27,'meta_description','Cyber Profile and Digital Platform by DICHVUGIARE.NET','2026-06-24 06:39:46'),(28,'meta_keywords','le vu phong, developer, fullstack, php, laravel, nodejs, vps, hosting, web design, vietnam, dichvugiare, dichvugiarenet, dichvugiare, dịch vụ giá rẻ','2026-06-24 06:39:46'),(29,'og_image','http://localhost/profile/assets/uploads/avatars/ad7cc35ad37f591db9a2195645b6860e.jpg','2026-06-24 06:39:46'),(30,'favicon','assets/img/favicon.ico','2026-06-24 06:39:46'),(31,'canonical_url','https://dichvugiare.net','2026-06-24 06:39:46'),(32,'twitter_card','summary_large_image','2026-06-24 06:39:46'),(33,'twitter_site','@adminprofile','2026-06-24 06:39:46'),(34,'timezone','Asia/Ho_Chi_Minh','2026-06-24 04:57:29'),(35,'language','vi','2026-06-24 04:57:29'),(36,'site_logo','','2026-06-24 04:57:29'),(37,'system_email','admin@profile.local','2026-06-24 04:57:29'),(38,'header_script','','2026-06-24 04:57:29'),(39,'footer_script','','2026-06-24 04:57:29'),(40,'ga_code','','2026-06-24 04:57:29'),(41,'fb_pixel','','2026-06-24 04:57:29'),(42,'gtm_code','','2026-06-24 04:57:29'),(43,'announcement_enabled','0','2026-06-24 04:57:29'),(44,'announcement_text','🎉 Chào mừng bạn đến với trang profile của tôi!','2026-06-24 04:57:29'),(45,'announcement_color','#6366F1','2026-06-24 04:57:29'),(46,'announcement_expiry','','2026-06-24 04:57:29'),(47,'pwa_enabled','1','2026-06-24 04:57:29'),(48,'pwa_name','Cyber Dashboard Profile','2026-06-24 04:57:29'),(49,'pwa_short_name','Profile','2026-06-24 04:57:29'),(50,'pwa_theme_color','#6366F1','2026-06-24 04:57:29'),(51,'sitemap_enabled','1','2026-06-24 06:39:46'),(52,'robots_txt','User-agent: *\nAllow: /\nDisallow: /admin/\nDisallow: /config/\nDisallow: /includes/\nSitemap: http://localhost/profile/sitemap.xml','2026-06-24 06:55:03');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `skills`
--

DROP TABLE IF EXISTS `skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `skills` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `logo` varchar(500) DEFAULT '',
  `name` varchar(100) NOT NULL,
  `level` int(11) DEFAULT 80,
  `sort_order` int(11) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `skills`
--

LOCK TABLES `skills` WRITE;
/*!40000 ALTER TABLE `skills` DISABLE KEYS */;
INSERT INTO `skills` VALUES (1,'','HTML5',98,1,1,'2026-06-24 04:57:29'),(2,'','CSS3',95,2,1,'2026-06-24 04:57:29'),(3,'','JavaScript',90,3,1,'2026-06-24 04:57:29'),(4,'','PHP',95,4,1,'2026-06-24 04:57:29'),(5,'','Laravel',88,5,1,'2026-06-24 04:57:29'),(6,'','NodeJS',82,6,1,'2026-06-24 04:57:29'),(7,'','MySQL',90,7,1,'2026-06-24 04:57:29'),(8,'','PostgreSQL',75,8,1,'2026-06-24 04:57:29'),(9,'','Docker',80,9,1,'2026-06-24 04:57:29'),(10,'','Redis',78,10,1,'2026-06-24 04:57:29'),(11,'','Git',95,11,1,'2026-06-24 04:57:29'),(12,'','Linux',85,12,1,'2026-06-24 04:57:29'),(13,'','Bootstrap',92,13,1,'2026-06-24 04:57:29'),(14,'','Tailwind',88,14,1,'2026-06-24 04:57:29'),(15,'','TypeScript',80,15,1,'2026-06-24 04:57:29');
/*!40000 ALTER TABLE `skills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `socials`
--

DROP TABLE IF EXISTS `socials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `socials` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `platform` varchar(100) NOT NULL,
  `icon_class` varchar(200) NOT NULL,
  `color` varchar(20) DEFAULT '#6366F1',
  `username` varchar(200) DEFAULT '',
  `link` varchar(500) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `socials`
--

LOCK TABLES `socials` WRITE;
/*!40000 ALTER TABLE `socials` DISABLE KEYS */;
INSERT INTO `socials` VALUES (1,'Facebook','fab fa-facebook-f','#1877F2','@adminprofile','https://facebook.com',1,1,'2026-06-24 04:57:29'),(2,'Telegram','fab fa-telegram-plane','#0088CC','@adminprofile','https://t.me',2,1,'2026-06-24 04:57:29'),(3,'TikTok','fab fa-tiktok','#FF0050','@adminprofile','https://tiktok.com',3,1,'2026-06-24 04:57:29'),(4,'Discord','fab fa-discord','#5865F2','adminprofile#0001','https://discord.gg',4,1,'2026-06-24 04:57:29'),(5,'YouTube','fab fa-youtube','#FF0000','Admin Profile','https://youtube.com',5,1,'2026-06-24 04:57:29'),(6,'GitHub','fab fa-github','#FFFFFF','@adminprofile','https://github.com',6,1,'2026-06-24 04:57:29');
/*!40000 ALTER TABLE `socials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `statistics`
--

DROP TABLE IF EXISTS `statistics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `statistics` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(200) NOT NULL,
  `value` varchar(100) NOT NULL,
  `suffix` varchar(50) DEFAULT '',
  `icon_class` varchar(200) DEFAULT 'fas fa-chart-bar',
  `sort_order` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `statistics`
--

LOCK TABLES `statistics` WRITE;
/*!40000 ALTER TABLE `statistics` DISABLE KEYS */;
INSERT INTO `statistics` VALUES (1,'Khách Hàng','10532','+','fas fa-users',1,'2026-06-24 04:57:29'),(2,'Đơn Hàng','25831','+','fas fa-shopping-cart',2,'2026-06-24 04:57:29'),(3,'Website','1250','+','fas fa-globe',3,'2026-06-24 04:57:29'),(4,'Doanh Thu','2.5B','+','fas fa-dollar-sign',4,'2026-06-24 04:57:29');
/*!40000 ALTER TABLE `statistics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `testimonials`
--

DROP TABLE IF EXISTS `testimonials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testimonials` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `avatar` varchar(500) DEFAULT '',
  `name` varchar(200) NOT NULL,
  `position` varchar(200) DEFAULT '',
  `review` text NOT NULL,
  `rating` tinyint(4) DEFAULT 5,
  `approved` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `testimonials`
--

LOCK TABLES `testimonials` WRITE;
/*!40000 ALTER TABLE `testimonials` DISABLE KEYS */;
INSERT INTO `testimonials` VALUES (1,'','Trần Minh Khoa','CEO tại TechCorp','Dịch vụ cực kỳ chuyên nghiệp! Website load nhanh hơn 3x sau khi chuyển sang VPS. Hỗ trợ 24/7 rất tận tâm.',5,1,'2026-06-24 04:57:29'),(2,'','Lê Thị Hoa','Marketing Manager','Thiết kế website rất đẹp và chuẩn SEO. Sau 2 tháng đã lên top Google. Rất hài lòng!',5,1,'2026-06-24 04:57:29'),(3,'','Phạm Văn Đức','Freelancer Developer','Mã nguồn chất lượng cao, tài liệu rõ ràng. Đã mua nhiều script và đều rất ưng ý.',5,1,'2026-06-24 04:57:29'),(4,'','Nguyễn Thu Trang','Shop Owner','Logo AI tạo ra rất đẹp và chuyên nghiệp. Giá rất hợp lý, tiết kiệm chi phí so với thuê designer.',5,1,'2026-06-24 04:57:29'),(5,'','Hoàng Văn Nam','Startup Founder','Team rất chuyên nghiệp và nhiệt tình. Dự án hoàn thành đúng deadline, quality vượt mong đợi. 5 sao!',5,1,'2026-06-24 04:57:29'),(6,'','Đỗ Thị Mai','E-commerce Owner','Hosting ổn định, không có downtime trong 1 năm sử dụng. Tốc độ website cải thiện rõ rệt.',5,1,'2026-06-24 04:57:29');
/*!40000 ALTER TABLE `testimonials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `avatar` varchar(500) DEFAULT '',
  `remember_token` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','$2y$10$I8eDEKKqHTl/XeTDOVakKO/I.C0wuHT2oqeetW4zaK50fFgW83Pta','admin@profile.local','',NULL,'2026-06-24 04:57:29');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `visits`
--

DROP TABLE IF EXISTS `visits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `visits` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `visit_date` date NOT NULL,
  `visit_count` int(11) DEFAULT 0,
  `unique_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `visit_date` (`visit_date`)
) ENGINE=InnoDB AUTO_INCREMENT=133 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `visits`
--

LOCK TABLES `visits` WRITE;
/*!40000 ALTER TABLE `visits` DISABLE KEYS */;
INSERT INTO `visits` VALUES (1,'2026-05-26',45,32,'2026-06-24 04:57:29','2026-06-24 04:57:29'),(2,'2026-05-27',62,48,'2026-06-24 04:57:29','2026-06-24 04:57:29'),(3,'2026-05-28',38,27,'2026-06-24 04:57:29','2026-06-24 04:57:29'),(4,'2026-05-29',74,55,'2026-06-24 04:57:29','2026-06-24 04:57:29'),(5,'2026-05-30',91,68,'2026-06-24 04:57:29','2026-06-24 04:57:29'),(6,'2026-05-31',55,41,'2026-06-24 04:57:29','2026-06-24 04:57:29'),(7,'2026-06-01',103,79,'2026-06-24 04:57:29','2026-06-24 04:57:29'),(8,'2026-06-02',88,64,'2026-06-24 04:57:29','2026-06-24 04:57:29'),(9,'2026-06-03',120,94,'2026-06-24 04:57:29','2026-06-24 04:57:29'),(10,'2026-06-04',97,73,'2026-06-24 04:57:29','2026-06-24 04:57:29'),(11,'2026-06-05',134,102,'2026-06-24 04:57:29','2026-06-24 04:57:29'),(12,'2026-06-06',156,118,'2026-06-24 04:57:29','2026-06-24 04:57:29'),(13,'2026-06-07',112,85,'2026-06-24 04:57:29','2026-06-24 04:57:29'),(14,'2026-06-08',89,67,'2026-06-24 04:57:29','2026-06-24 04:57:29'),(15,'2026-06-09',143,110,'2026-06-24 04:57:29','2026-06-24 04:57:29'),(16,'2026-06-10',167,128,'2026-06-24 04:57:29','2026-06-24 04:57:29'),(17,'2026-06-11',198,152,'2026-06-24 04:57:29','2026-06-24 04:57:29'),(18,'2026-06-12',176,135,'2026-06-24 04:57:29','2026-06-24 04:57:29'),(19,'2026-06-13',145,112,'2026-06-24 04:57:29','2026-06-24 04:57:29'),(20,'2026-06-14',189,145,'2026-06-24 04:57:29','2026-06-24 04:57:29'),(21,'2026-06-15',212,163,'2026-06-24 04:57:29','2026-06-24 04:57:29'),(22,'2026-06-16',234,180,'2026-06-24 04:57:29','2026-06-24 04:57:29'),(23,'2026-06-17',201,155,'2026-06-24 04:57:29','2026-06-24 04:57:29'),(24,'2026-06-18',178,137,'2026-06-24 04:57:29','2026-06-24 04:57:29'),(25,'2026-06-19',256,197,'2026-06-24 04:57:29','2026-06-24 04:57:29'),(26,'2026-06-20',243,188,'2026-06-24 04:57:29','2026-06-24 04:57:29'),(27,'2026-06-21',289,222,'2026-06-24 04:57:29','2026-06-24 04:57:29'),(28,'2026-06-22',267,205,'2026-06-24 04:57:29','2026-06-24 04:57:29'),(29,'2026-06-23',312,240,'2026-06-24 04:57:29','2026-06-24 04:57:29'),(30,'2026-06-24',189,65,'2026-06-24 04:57:29','2026-06-24 06:52:43');
/*!40000 ALTER TABLE `visits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `websites`
--

DROP TABLE IF EXISTS `websites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `websites` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `logo` varchar(500) DEFAULT '',
  `name` varchar(200) NOT NULL,
  `domain` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `link` varchar(500) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `websites`
--

LOCK TABLES `websites` WRITE;
/*!40000 ALTER TABLE `websites` DISABLE KEYS */;
INSERT INTO `websites` VALUES (1,'http://localhost/profile/assets/uploads/website_logos/1c368b6da6689229c4c8b4ddd810c833.png','DỊCH VỤ GIÁ RẺ','dichvugiare.net','Hệ sinh thái dịch vụ công nghệ toàn diện: Thiết kế Website, Hosting, VPS...','https://dichvugiare.net',1,1,'2026-06-24 04:57:29'),(2,'','HOSTING & VPS','dichvugiare.net','Máy chủ tốc độ cao, hiệu năng mạnh mẽ, uptime 99.9%','https://dichvugiare.net',2,1,'2026-06-24 04:57:29'),(3,'','PROXY SERVER','dichvugiare.net','Hệ thống proxy private tốc độ cao, ổn định, ẩn danh an toàn','https://dichvugiare.net',3,1,'2026-06-24 04:57:29'),(4,'','KHO MÃ NGUỒN','dichvugiare.net','Cung cấp script, theme, plugin chất lượng cao giá tốt nhất','https://dichvugiare.net',4,1,'2026-06-24 04:57:29'),(5,'','THIẾT KẾ LOGO','dichvugiare.net','Thiết kế nhận diện thương hiệu, logo chuyên nghiệp ấn tượng','https://dichvugiare.net',5,1,'2026-06-24 04:57:29'),(6,'','TÊN MIỀN','dichvugiare.net','Đăng ký domain .vn, .com, .net giá rẻ, hỗ trợ kỹ thuật 24/7','https://dichvugiare.net',6,1,'2026-06-24 04:57:29');
/*!40000 ALTER TABLE `websites` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-24 14:12:30
