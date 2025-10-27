<?php
return [
    'singular' => 'Rent Product',
    'plural' => 'Rent Products',

    'fields' => [
        'name' => 'Name',
        'price' => 'Price',
        'slug' => 'Slug',
        'category' => 'Category',
        'tags' => 'Tags',
        'description' => 'Description',
        'image' => 'Image',
        'deleted_at' => 'Deleted At',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
    ],

    'placeholders' => [
        'name' => 'Enter rent product name',
        'tags' => 'Enter tag name to search and press enter to add',
        'slug' => 'The slug will be automatically generated from the rent product name',
        'description' => 'Enter description for the rent product',
        'price' => 'Enter rental price for the rent product (optional)',
    ],
];
