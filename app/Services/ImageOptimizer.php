<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\ImageManager;

class ImageOptimizer
{
    private const int MAX_DIMENSION = 1920;

    private const int QUALITY = 85;

    private ImageManager $manager;

    public function __construct()
    {
        $driver = extension_loaded('imagick') ? new ImagickDriver : new GdDriver;
        $this->manager = new ImageManager($driver);
    }

    public function store(UploadedFile $file, string $directory, string $disk = 'public'): string
    {
        $filename = $directory.'/'.Str::uuid().'.webp';

        $image = $this->manager->read($file->getRealPath())
            ->scaleDown(self::MAX_DIMENSION, self::MAX_DIMENSION);

        Storage::disk($disk)->put($filename, $image->toWebp(self::QUALITY));

        return $filename;
    }
}
