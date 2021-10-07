<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bookmark extends Model
{
    protected $fillable = [
        'name', 'url', 'type_id'
    ];

    public function isBookmark(): bool
    {
        return $this->type_id == BookmarkType::BOOKMARK;
    }

    public function isFolder(): bool
    {
        return $this->type_id == BookmarkType::FOLDER;
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Bookmark::class, 'parent_id');
    }
}
