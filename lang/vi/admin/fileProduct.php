<?php
return [
    'singular' => 'Thiết kế',

    'fields' => [
        'name' => 'Tên',
        'category_id' => 'Danh mục',
        'tags' => 'Thẻ',
        'slug' => 'Đường dẫn tĩnh',
        'description' => 'Mô tả',
        'price' => 'Giá',
        'thumbnail' => 'Hình xem trước',
        'created_at' => 'Tạo lúc',
        'updated_at' => 'Cập nhật lúc',
        'deleted_at' => 'Xóa lúc',
    ],

    'placeholders' => [
        'name' => 'Nhập tên thiết kế',
        'tags' => 'Nhập tên thẻ để tìm kiếm và nhấn enter để thêm',
        'slug' => 'Đường dẫn tĩnh sẽ được tạo tự động',
        'description' => 'Nhập mô tả thiết kế',
        'price' => 'Nhập giá thiết kế',
    ],

    'helpers' => [
        'thumbnail' => 'Tải lên hình ảnh xem trước cho thiết kế. Kích thước tối đa 3MB, định dạng: jpg, png, jpeg, webp.',
    ],
];
