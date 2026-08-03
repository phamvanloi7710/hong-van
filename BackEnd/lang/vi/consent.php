<?php

return [
    'banner' => [
        'title' => 'Quyền riêng tư của bạn',
        'message' => 'Chúng tôi chỉ dùng cookie phân tích khi bạn đồng ý. Cookie cần thiết luôn hoạt động để website vận hành an toàn.',
        'accept_all' => 'Đồng ý tất cả',
        'necessary_only' => 'Chỉ cookie cần thiết',
        'preferences' => 'Tùy chọn cookie',
        'save' => 'Lưu lựa chọn',
        'revoke' => 'Thu hồi đồng ý',
        'policy_link' => 'Xem chính sách cookie',
    ],
    'categories' => [
        'necessary' => ['label' => 'Cần thiết', 'description' => 'Bắt buộc cho bảo mật, phiên làm việc và chức năng cốt lõi của website.'],
        'analytics' => ['label' => 'Phân tích', 'description' => 'Giúp đo lường lượt xem và hiệu quả nội dung mà không gửi dữ liệu nhận diện cá nhân.'],
        'marketing' => ['label' => 'Tiếp thị', 'description' => 'Chỉ dùng cho nhà cung cấp tiếp thị đã được phê duyệt khi tính năng này được bật.'],
    ],
    'messages' => ['saved' => 'Đã lưu lựa chọn cookie.', 'revoked' => 'Đã thu hồi lựa chọn cookie.'],
    'validation' => [
        'provider' => 'Nhà cung cấp analytics chưa được phê duyệt.',
        'identifier_required' => 'Cần nhập mã theo dõi khi bật analytics hoặc đổi nhà cung cấp.',
        'identifier_invalid' => 'Mã theo dõi không đúng định dạng của nhà cung cấp đã chọn.',
    ],
];
