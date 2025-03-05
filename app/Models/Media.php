<?php

namespace App\Models;

use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

class Media extends BaseMedia
{
    public function getPathAttribute()
    {
        $collection = $this->collection_name;
        return "storage/{$collection}/{$this->id}/{$this->file_name}";
    }
}
