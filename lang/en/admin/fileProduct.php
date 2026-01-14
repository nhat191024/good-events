<?php
return [
    'singular' => 'File Product',

    'fields' => [
        'name' => 'Name',
        'category_id' => 'Category',
        'tags' => 'Tags',
        'slug' => 'Slug',
        'description' => 'Description',
        'price' => 'Price',
        'thumbnail' => 'Thumbnail',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
        'deleted_at' => 'Deleted At',
    ],

    'placeholders' => [
        'name' => 'Enter file product name',
        'tags' => 'Enter tag name to search and enter to add',
        'slug' => 'The slug will be generated automatically',
        'description' => 'Enter file product description',
        'price' => 'Enter file product price',
    ],

    'helpers' => [
        'thumbnail' => 'Upload a thumbnail image for the file product. Maximum size 3MB, formats: jpg, png, jpeg, webp.',
    ],
];
