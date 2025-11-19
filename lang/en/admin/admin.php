<?php
return [
    'admin' => 'Admin',

    'fields' => [
        'label' => [
            'avatar' => 'Avatar',
            'name' => 'Name',
            'email' => 'Email',
            'password' => 'Password',
            'role' => 'Role',
        ],
        'placeholder' => [
            'name' => 'Enter name',
            'email' => 'Enter email',
            'password' => 'Enter password',
        ],
        'helper' => [
            'email' => 'The email address is only used to log in to the system, it does not need to be a real email.',
            'password' => 'The password must be at least 8 characters long.',
            'role' => 'Admin: has full control of the system. - Human Resource Manager: manages the partner rental system. - Design Manager: manages the design upload system. - Rental Manager: manages the equipment rental system.',
        ]
    ],
];
