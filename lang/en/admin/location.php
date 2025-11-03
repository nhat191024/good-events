<?php
return [
    'singular' => 'City',

    'wards' => 'Wards',

    'manage_wards' => 'Manage Wards: :name',

    'cannot_hidden_has_wards' => 'Cannot hide location with wards.',
    'cannot_delete_has_wards' => 'Cannot delete location with wards.',

    'fields' => [
        'id' => 'ID',
        'name' => 'Name',
        'code' => 'Code',
        'codename' => 'Codename',
        'short_codename' => 'Short Codename',
        'type' => 'Type',
        'phone_code' => 'Phone Code',
        'parent_id' => 'Belongs to Province/City',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
        'deleted_at' => 'Deleted At',
    ],

    'placeholders' => [
        'type' => 'Select location type',
    ],

    'filters' => [
        'province' => 'Province',
        'city' => 'City',
        'special_zone' => 'Special Zone',
        'ward' => 'Ward',
        'commune' => 'Commune',
    ],

    'actions' => [
        'manage_wards' => 'Manage Wards',
    ],
];
