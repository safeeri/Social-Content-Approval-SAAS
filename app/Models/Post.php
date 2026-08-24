<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_INTERNAL_REVIEW = 'internal_review';
    public const STATUS_PENDING_APPROVAL = 'pending_approval';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_INTERNAL_REVIEW,
        self::STATUS_PENDING_APPROVAL,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
    ];

    public const STATUS_LABELS = [
        self::STATUS_DRAFT => 'Draft',
        self::STATUS_INTERNAL_REVIEW => 'Internal Review',
        self::STATUS_PENDING_APPROVAL => 'Pending Approval',
        self::STATUS_APPROVED => 'Approved',
        self::STATUS_REJECTED => 'Rejected',
    ];

    public const TYPE_FEED = 'feed';
    public const TYPE_REEL = 'reel';
    public const TYPE_SHORT = 'short';
    public const TYPE_LONG_VIDEO = 'long_video';

    public const TYPES = [
        self::TYPE_FEED => 'Feed Post',
        self::TYPE_REEL => 'Reel',
        self::TYPE_SHORT => 'Short',
        self::TYPE_LONG_VIDEO => 'Long Video',
    ];

    protected $fillable = [
        'client_id',
        'platform_id',
        'content',
        'status',
        'post_type',
        'publish_date',
    ];

    protected function casts(): array
    {
        return [
            'publish_date' => 'datetime',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }

    public function media()
    {
        return $this->hasMany(Media::class)->orderBy('sort_order');
    }

    public function feedback()
    {
        return $this->hasMany(PostFeedback::class)->latest();
    }

    /**
     * Tenant isolation: scope posts to whatever the given user may see.
     */
    public function scopeVisibleTo($query, User $user)
    {
        if ($user->isSaasAdmin()) {
            return $query;
        }

        return $query->whereHas('client', function ($q) use ($user) {
            $q->where('company_id', $user->company_id);

            if ($user->isClient()) {
                $q->where('clients.id', $user->client_id);
            }
        });
    }
}
