<?php
return [
    'singular' => 'Rent Product',
    'plural' => 'Rent Products',

    'fields' => [
        'id' => 'ID',
        'name' => 'Name',
        'slug' => 'Slug',
        'description' => 'Description',
        'price_per_day' => 'Price Per Day',
        'available_quantity' => 'Available Quantity',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
        'deleted_at' => 'Deleted At',
    ],

    'placeholders' => [
        'name' => 'Enter the name of the rent product',
        'slug' => 'The slug will be generated automatically',
        'description' => 'Enter a detailed description of the rent product',
        'price_per_day' => 'Enter the rental price per day',
        'available_quantity' => 'Enter the available quantity for rent',
    ],
];
