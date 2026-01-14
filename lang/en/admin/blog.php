<?php
return [
    'singulars' => [
        'good_location' => 'Good Location',
        'vocational_knowledge' => 'Vocational Knowledge',
        'event_organization_guide' => 'Event Organization Guide',
    ],

    'plurals' => [
        'good_location' => 'Good Location Posts',
        'vocational_knowledge' => 'Vocational Knowledge Posts',
        'event_organization_guide' => 'Event Organization Guide Posts',
    ],

    'sections' => [
        'basic_info' => 'Basic Information',
        'basic_info_description' => 'Enter general information about the blog post',
        'content' => 'Content',
        'content_description' => 'Detailed description of the blog post',
        'media' => 'Media',
        'media_description' => 'Upload a featured image for the blog post',
        'location' => 'Location',
        'location_description' => 'Select city, ward, and specific address',
        'map' => 'Map & Coordinates',
        'map_description' => 'Pinpoint the exact location on the map',
    ],

    'fields' => [
        'id' => 'ID',
        'thumbnail' => 'Thumbnail',
        'title' => 'Title',
        'max_people' => 'Max People',
        'video_url' => 'Video URL',
        'slug' => 'Slug',
        'tags' => 'Tags',
        'city' => 'City',
        'ward' => 'Ward',
        'content' => 'Content',
        'category_id' => 'Category',
        'author_id' => 'Author',
        'address' => 'Address',
        'latitude' => 'Latitude',
        'longitude' => 'Longitude',
        'search' => 'Search',
        'map' => 'Map',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
        'deleted_at' => 'Deleted At',
    ],

    'placeholders' => [
        'title' => 'Enter the blog title',
        'max_people' => 'Enter the maximum number of people',
        'video_url' => 'Enter the video URL',
        'slug' => 'The slug will be generated automatically',
        'tags' => 'Type tag names to search and press enter to add',
        'city' => 'Select city',
        'ward' => 'Select ward',
        'content' => 'Enter the blog content here...',
        'address' => 'Enter the specific address of the location',
        'latitude' => 'Can be auto-filled via map',
        'longitude' => 'Can be auto-filled via map',
        'search' => 'Enter street name, city'
    ],

    'helpers' => [
        'video_url' => 'Website will automatically convert standard video URLs from platforms like YouTube and Vimeo into embedded videos.',
        'latitude' => 'Do not enter manually unless you know what you are doing.',
        'longitude' => 'Do not enter manually unless you know what you are doing.',
        'search' => 'Use the search button to auto-fill coordinates based on the address. (note: results may not be 100% accurate) - The filled coordinates will help display the location on the map in the client interface',
        'thumbnail' => 'Supported formats: jpg, png, webp. Maximum size: 10MB.',
    ],
];
