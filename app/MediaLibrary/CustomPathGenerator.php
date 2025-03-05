<?php

namespace App\MediaLibrary;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class CustomPathGenerator implements PathGenerator
{
    /**
     * Tentukan path untuk file media.
     */
    public function getPath(Media $media): string
    {
        // Simpan file di /storage/{collection}/{id}/
        return $media->collection_name . '/' . $media->id . '/';
    }

    /**
     * Tentukan path untuk file konversi (misal: thumbnail).
     */
    public function getPathForConversions(Media $media): string
    {
        // Simpan konversi di /storage/{collection}/{id}/conversions/
        return $media->collection_name . '/' . $media->id . '/conversions/';
    }

    /**
     * Tentukan path untuk file responsive images (opsional).
     */
    public function getPathForResponsiveImages(Media $media): string
    {
        // Simpan responsive images di /storage/{collection}/{id}/responsive/
        return $media->collection_name . '/' . $media->id . '/responsive/';
    }
}
