<?php
require 'includes/functions.php';

$defaults = [
    'about' => ['title' => 'Tôi Là <span class="grad">Ai?</span>', 'desc' => 'Giới thiệu ngắn gọn về bản thân và định hướng'],
    'social' => ['title' => 'Kết Nối <span class="grad">Với Tôi</span>', 'desc' => 'Theo dõi tôi trên các nền tảng để cập nhật dự án mới nhất'],
    'skills' => ['title' => 'Tech <span class="grad">Stack</span>', 'desc' => 'Công nghệ và công cụ tôi sử dụng hàng ngày để xây dựng sản phẩm'],
    'payment' => ['title' => 'Thông Tin <span class="grad">Ngân Hàng</span>', 'desc' => 'Phương thức thanh toán và giao dịch an toàn'],
    'achievements' => ['title' => 'Những Gì <span class="grad">Đã Đạt Được</span>', 'desc' => 'Giải thưởng, chứng chỉ và các cột mốc quan trọng trong sự nghiệp'],
    'services' => ['title' => 'Tôi Có Thể <span class="grad">Giúp Gì?</span>', 'desc' => 'Các dịch vụ chuyên nghiệp tôi cung cấp để giải quyết vấn đề của bạn'],
    'statistics' => ['title' => 'Live <span class="grad">Statistics</span>', 'desc' => 'Số liệu thống kê trực tiếp từ hệ thống'],
    'reviews' => ['title' => 'Khách Hàng <span class="grad">Nói Gì?</span>', 'desc' => 'Đánh giá chân thực từ đối tác và khách hàng đã làm việc cùng'],
    'global_map' => ['title' => 'Global <span class="grad">Presence</span>', 'desc' => 'Hệ thống kết nối và vận hành trên toàn cầu — dữ liệu thực tế từ các node mạng'],
    'contact' => ['title' => 'Hãy <span class="grad">Liên Hệ Tôi</span>', 'desc' => 'Sẵn sàng thảo luận về ý tưởng và dự án tiếp theo của bạn']
];

foreach ($defaults as $key => $data) {
    db()->prepare("UPDATE page_sections SET title=?, description=? WHERE section_key=?")
        ->execute([$data['title'], $data['desc'], $key]);
}
echo "OK";
