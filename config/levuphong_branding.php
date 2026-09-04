<?php
namespace DichVuGiaReVendor;

// ============================================================
// BRANDING CONFIGURATION
// ============================================================
// Core Namespace Constants for Branding
define('LEVUPHONG_AUTHOR', 'Le Vu Phong');
define('DICHVUGIARE_VENDOR', 'DichVuGiaRe');
define('LEVUPHONG_SIGNATURE', 'Le Vu Phong Ecosystem');

// General Project Branding Constants
define('PROJECT_AUTHOR', LEVUPHONG_AUTHOR);
define('PROJECT_WEBSITE', 'https://dichvugiare.net');
define('PROJECT_CONTACT', '0855550612');
define('PROJECT_COPYRIGHT', 'Copyright © ' . date('Y') . ' by Le Vu Phong. All rights reserved.');
define('PROJECT_GENERATOR', 'Le Vu Phong Framework V4');

// HTML Source Comment Signature
define('HTML_BRANDING_COMMENT', "
<!--
=========================================================
Project: Le Vu Phong Ecosystem
Website: https://dichvugiare.net
Contact: Zalo 0855550612
=========================================================
-->
");

define('LEVUPHONG_LICENSE', 'MIT License');

class LeVuPhongBrandingManager {
    public static function getAuthor() {
        return defined('LEVUPHONG_AUTHOR') ? LEVUPHONG_AUTHOR : 'Le Vu Phong';
    }
}

