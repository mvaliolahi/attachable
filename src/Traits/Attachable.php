<?php

namespace Mvaliolahi\Attachable\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

trait Attachable
{
    public static function bootAttachable()
    {
        static::saving(function ($model) {

            collect($model->attachable)->each(function ($field) use ($model) {
                if (request($field) instanceof UploadedFile) {
                    self::deletePreviousFile($model, $field);
                    $model->$field = self::upload($model, $field,  request($field));
                } else {
                    // use previous value to prevent delete field value in update scenario.
                    if (is_null($model->fresh())) {
                        return;
                    }

                    $model->$field =  $model->fresh()->getRawOriginal($field) ?? null;
                }
            });
        });

        static::deleting(function ($model) {
            collect($model->attachable)->each(function ($field) use ($model) {

                if (isset($model->$field)) {
                    self::deletePreviousFile($model, $field);
                }
            });
        });
    }

    /**
     * @param object|mixed $model
     * @param UploadedFile $uploadedFile
     * @return string
     */
    private static function upload($model, $field, UploadedFile $uploadedFile)
    {
        $basePath = $model->upload_path ?? 'public';
        $directoryName = strtolower(Str::plural(class_basename($model), 2));

        if ($model->user_directory ?? false) {
            $directoryName = $directoryName . '/' . sha1(auth()->id());
        }

        if (($model->resize_image ?? false) && ($model->resize_image[$field] ?? false)) {
            $hashName = $uploadedFile->hashName();
            $uploadedFile = self::compressImage($uploadedFile, $model->resize_image[$field]);

            Storage::disk($basePath)->put($filename = $directoryName . '/' . $hashName, $uploadedFile);

            return $filename;
        }

        return Storage::disk($basePath)->put($uploadedFile->hashName($directoryName), $uploadedFile);
    }

    /**
     * @param string $filePath
     * @return void
     */
    private static function deletePreviousFile($model, $field)
    {
        // When model not exists yet.
        if (is_null($model->fresh())) {
            return;
        }

        if ($filePath = $model->fresh()->$field) {
            Storage::delete($filePath);
        }
    }

    private static function compressImage($uploadedFile, $config)
    {
        $image = Image::make($uploadedFile->getRealPath());
        $fit = $config['fit'] ?? true;

        if ($fit == true) {
            $image->fit($config['width'] ?? null, $config['height'] ?? null);
        } else {
            if ($image->getWidth() > $config['resize_if_width'] ?? null) {
                $image->resize($config['width'], $config['height'] ?? null, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }
        }

        return (string) $image->encode(null, $config['quality'] ?? 100);
    }
}
