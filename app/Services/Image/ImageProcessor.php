<?php

namespace App\Services\Image;

use getID3;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ImageProcessor
{
    public function __construct()
    {
        //
    }

    /**
     * ファイルの情報を取得する
     * @access public
     * @param UploadedFile $file
     * @return array
     */
    public function process(UploadedFile $file): array
    {
        $img_path = $file->getPathname();
        $original_name = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $size = $file->getSize();
        $hash = hash_file('sha256', $img_path);

        $width = null;
        $height = null;
        $mimeType = $file->getMimeType();
        if (Str::startsWith($mimeType, 'image/')) {
            [$width, $height] = getimagesize($img_path);
        } elseif (Str::startsWith($mimeType, 'video/')) {
            $getID3 = new getID3();
            $fileInfo = $getID3->analyze($img_path);
            $width = $fileInfo['video']['resolution_x'];
            $height = $fileInfo['video']['resolution_y'];
        }
        return [
            'temp_path'     => $img_path,
            'original_name' => $original_name,
            'extension'     => $extension,
            'dimensions'    => compact('width', 'height'),
            'size'          => $size,
            'hash'          => $hash,
        ];
    }

    // /**
    //  * サムネイルを生成する
    //  * @access public
    //  */
    // public function createThumbnail(string $temp_path, string $thumbnail_path, int $width, int $height) : void
    // {
    //     $img = \Image::make($temp_path);
    //     $img->resize($width, $height, function ($constraint) {
    //         $constraint->aspectRatio();
    //         $constraint->upsize();
    //     });
    //     $img->save($thumbnail_path);
    // }
}
