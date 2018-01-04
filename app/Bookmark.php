<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bookmark extends Model
{
    protected $fillable = [
        'name', 'url', 'type_id'
    ];

    public function isFolder(): bool
    {
        return $this->type_id == BookmarkType::FOLDER;
    }
}
