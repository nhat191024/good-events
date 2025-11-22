<?php
return [
    'singulars' => [
        'good_location' => 'Địa điểm tốt',
        'vocational_knowledge' => 'Kiến thức nghề',
        'event_organization_guide' => 'Hướng dẫn tổ chức sự kiện',
    ],

    'plurals' => [
        'good_location' => 'Bài viết địa điểm tốt',
        'vocational_knowledge' => 'Bài viết kiến thức nghề',
        'event_organization_guide' => 'Bài viết hướng dẫn tổ chức sự kiện',
    ],

    'fields' => [
        'id' => 'ID',
        'thumbnail' => 'Hình đại diện',
        'title' => 'Tiêu đề',
        'max_people' => 'Số người tối đa',
        'video_url' => 'Link video',
        'slug' => 'Đường dẫn tĩnh',
        'tags' => 'Thẻ',
        'city' => 'Thành phố',
        'ward' => 'Phường/Xã',
        'content' => 'Nội dung',
        'category_id' => 'Danh mục',
        'author_id' => 'Tác giả',
        'address' => 'Địa chỉ',
        'latitude' => 'Vĩ độ',
        'longitude' => 'Kinh độ',
        'search' => 'Tìm kiếm',
        'map' => 'Bản đồ',
        'created_at' => 'Ngày tạo',
        'updated_at' => 'Ngày cập nhật',
        'deleted_at' => 'Ngày xóa',
    ],

    'placeholders' => [
        'title' => 'Nhập tiêu đề bài viết',
        'max_people' => 'Nhập số người tối đa',
        'video_url' => 'Nhập URL video',
        'slug' => 'Đường dẫn tĩnh sẽ được tự động tạo',
        'tags' => 'Nhập tên thẻ để tìm kiếm và nhấn enter để thêm',
        'city' => 'Chọn thành phố',
        'ward' => 'Chọn phường/xã',
        'content' => 'Nhập nội dung bài viết ở đây...',
        'address' => 'Nhập địa chỉ cụ thể của địa điểm',
        'latitude' => 'Có thể tự động điền nhờ map',
        'longitude' => 'Có thể tự động điền nhờ map',
        'search' => 'Chỉ nhập tên đường, thành phố'
    ],

    'helpers' => [
        'latitude' => 'Không tự nhập trừ khi bạn biết mình đang làm gì.',
        'longitude' => 'Không tự nhập trừ khi bạn biết mình đang làm gì.',
        'search' => 'Sử dụng nút tìm kiếm để tự động điền tọa độ dựa trên địa chỉ. (lưu ý: kết quả có thể không chính xác 100%) - Tọa độ được điền sẽ giúp hiển thị vị trí trên bản đồ ở giao diện khách',
    ],
];
