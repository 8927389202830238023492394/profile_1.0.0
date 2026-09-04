<?php
namespace DichVuGiaReVendor;

class LeVuPhongLicenseManager {
    public static function getLicense() {
        return defined('LEVUPHONG_LICENSE') ? LEVUPHONG_LICENSE : 'MIT License';
    }
}
