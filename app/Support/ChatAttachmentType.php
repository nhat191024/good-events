<?php

namespace App\Support;

use Closure;
use Illuminate\Http\UploadedFile;
use Symfony\Component\Mime\MimeTypes;

class ChatAttachmentType
{
    private const array FILE_EXTENSIONS = [
        'zip', 'rar', '7z', 'pdf', 'txt', 'csv', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
        'psd', 'psb', 'ai', 'eps', 'indd', 'idml', 'aep', 'prproj', 'xd',
    ];

    private const array VIDEO_EXTENSIONS = ['mp4', 'mov', 'm4v', 'webm'];

    /** @var array<string, list<string>> */
    private const array ADOBE_MIME_TYPES = [
        'psd' => ['image/vnd.adobe.photoshop', 'image/x-photoshop', 'application/photoshop', 'application/x-photoshop', 'image/photoshop', 'image/psd', 'image/x-psd'],
        'psb' => ['image/vnd.adobe.photoshop', 'image/x-photoshop', 'application/photoshop', 'application/x-photoshop'],
        'ai' => ['application/pdf', 'application/postscript', 'application/illustrator', 'application/vnd.adobe.illustrator'],
        'eps' => ['application/postscript', 'image/x-eps'],
        'indd' => ['application/x-indesign', 'application/vnd.adobe.indesign'],
        'idml' => ['application/zip', 'application/vnd.adobe.indesign-idml-package'],
        'aep' => ['application/vnd.adobe.aftereffects.project', 'application/x-aftereffects'],
        'prproj' => ['application/gzip', 'application/x-gzip', 'application/vnd.adobe.premiere'],
        'xd' => ['application/zip', 'application/vnd.adobe.xd'],
    ];

    /** @return Closure(string, mixed, Closure): void */
    public static function rule(bool $video = false): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($video): void {
            if (! $value instanceof UploadedFile || ! $value->isValid()) {
                return;
            }

            $extension = strtolower($value->getClientOriginalExtension());
            $extensions = $video ? self::VIDEO_EXTENSIONS : self::FILE_EXTENSIONS;

            if (! in_array($extension, $extensions, true)) {
                $fail($video ? 'Định dạng video không được hỗ trợ.' : 'Định dạng file không được hỗ trợ.');

                return;
            }

            $mimeTypes = self::ADOBE_MIME_TYPES[$extension] ?? MimeTypes::getDefault()->getMimeTypes($extension);

            if (! in_array($value->getMimeType(), $mimeTypes, true)) {
                $fail('Không nhận diện được nội dung file hoặc MIME không khớp với định dạng file.');
            }
        };
    }
}
