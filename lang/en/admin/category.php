<?php
return [
    'plural' => 'Categories',
    'singular' => 'Category',

    'singulars' => [
        'blog' => 'Blog Category',
        'design' => 'Design Category',
        'rental' => 'Rental Category',
    ],

    'cannot_hidden_category_has_children' => 'Cannot hide category because it has child categories.',
    'manage_children_categories' => 'Manage child categories of :name',
    'children_category' => 'Child Category',

    'fields' => [
        'name' => 'Category Name',
        'slug' => 'Slug',
        'order' => 'Order',
        'image' => 'Image',
        'parents' => 'Parent Categories',
        'children' => 'Child Categories',
        'type' => 'Category Type',
        'description' => 'Description',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
        'deleted_at' => 'Deleted At',
    ],

    'category_options' => [
        'good_location' => 'Good Location',
        'vocational_knowledge' => 'Vocational Knowledge',
        'event_organization_guide' => 'Event Organization Guide',
    ],

    'placeholders' => [
        'name' => 'Enter category name',
        'slug' => 'Slug is automatically generated from category name',
        'description' => 'Enter category description',
    ],

    'actions' => [
        'create_child_category' => 'Create Child Category',
        'manage_children_categories' => 'Manage Child Categories',
    ]
];
