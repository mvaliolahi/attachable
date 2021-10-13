<?php

namespace Mvaliolahi\Attachable\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait Attachable
{
    public static function bootAttachable()
    {
        static::saving(function ($model) {

            collect($model->attachable)->each(function ($field) use ($model) {
                if (request($field) instanceof UploadedFile) {
                    self::deletePreviousFile($model, $field);
                    $model->$field = self::upload($model, request($field));
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
    private static function upload($model, UploadedFile $uploadedFile)
    {
        $basePath = $model->upload_path ?? 'public';
        $directoryName = strtolower(Str::plural(class_basename($model), 2));

        if ($model->user_directory ?? false) {
            $directoryName = $directoryName . '/' . sha1(auth()->id());
        }

        return Storage::put("{$basePath}/{$directoryName}", $uploadedFile, 'public');
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
}
