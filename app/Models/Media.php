<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasFactory;

    public const TYPE_IMAGE = 'image';
    public const TYPE_VIDEO = 'video';
    public const TYPE_DOCUMENT = 'document';

    protected $table = 'media';

    protected $fillable = [
        'post_id',
        'file_path',
        'disk',
        'media_type',
        'drive_link',
        'sort_order',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function url(): string
    {
        if ($this->drive_link && ! $this->file_path) {
            return $this->directDriveUrl();
        }

        return Storage::disk($this->disk)->url($this->file_path);
    }

    public function isVideo(): bool
    {
        return $this->media_type === self::TYPE_VIDEO;
    }

    /**
     * Convert a Google Drive share link into a direct preview/embed URL.
     */
    public function directDriveUrl(): string
    {
        if (preg_match('/\/d\/([a-zA-Z0-9_-]+)/', $this->drive_link, $m)) {
            return "https://drive.google.com/uc?export=view&id={$m[1]}";
        }

        if (preg_match('/[?&]id=([a-zA-Z0-9_-]+)/', $this->drive_link, $m)) {
            return "https://drive.google.com/uc?export=view&id={$m[1]}";
        }

        return $this->drive_link;
    }

    protected static function booted(): void
    {
        static::deleting(function (Media $media) {
            if ($media->file_path) {
                Storage::disk($media->disk)->delete($media->file_path);
            }
        });
    }
}
