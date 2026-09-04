<?php
namespace DichVuGiaReVendor;

class LeVuPhongSignature {
    public static function getSignature() {
        return defined('LEVUPHONG_SIGNATURE') ? LEVUPHONG_SIGNATURE : 'Le Vu Phong Ecosystem';
    }
}
