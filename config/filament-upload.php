<?php

return [

    /**
     * Upload configuration.
     *
     * All files are uploaded by default using a chunked upload process. This
     * helps with better handling very large files and resuming the upload
     * after a network interruption. You can manage any settings below.
     */
    'upload' => [
        /**
         * The middleware that the endpoint receiving the chunks should use.
         */
        'middleware' => [
            'web',
            'throttle:300,1',
        ],

        /**
         * The larger the chunk size, the less network requests are made.
         *
         * Laravel Vapor: due to AWS Lambda limitations, file uploads
         * made directly to your application backend can only be up
         * to roughly 4.5MB in size.
         * Source: https://docs.vapor.build/resources/storage#file-uploads
         *
         * S3-temporary uploads storage: in case of directly uploading
         * multipart to S3, the chunk size should be minimum 5MB.
         */
        'chunk_size' => 1024 * 1024 * 20, // 20 MB

        /**
         * The rules applied to the chunk uploads.
         *
         * There are on purpose not any rules about the file type
         * and/or file size. This is because each of the chunks
         * is a stream of bytes and therefore no file tupe can
         * be validated. The file size is also not validated
         * here, because it can be validated on the upload
         * component in your respective Filament forms.
         */
        'rules' => [
            'file',
        ],

        /**
         * If you are using S3 Multipart uploads, then a multipart
         * upload could stay pending indefinitely if it is never
         * completed, for example, if a user closes the browser
         * tab. However, you are still billed for the storage
         * taken up by these pending uploads. Therefore, the
         * package will automatically register a scheduled
         * command to run daily that will remove pending
         * multipart uploads that are older than 7 days.
         * If you are not using S3, you could either
         * keep the command active or disable it.
         */
        's3_cleanup_multipart_command' => [
            /**
             * Disable the automatic scheduler registration.
             */
            'enabled' => env('FILAMENT_UPLOAD_S3_CLEANUP_MULTIPART_COMMAND_ENABLED', true),

            'delete_after_days' => 7,
        ],

        /**
         * The number of days to keep old multipart uploads before cleaning them up.
         * The cleanup command will remove uploads older than this many days.
         */
        'cleanup_days' => 7,
    ],
];
