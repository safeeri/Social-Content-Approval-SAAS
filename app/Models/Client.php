<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'phone',
        'address',
        'website',
        'website_start_date',
        'platform_bottom_content',
    ];

    protected function casts(): array
    {
        return [
            'website_start_date' => 'date',
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function platforms()
    {
        return $this->belongsToMany(Platform::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function scopeOwnedBy($query, User $user)
    {
        if ($user->role === 'client') {
            return $query->where('clients.id', $user->client_id);
        }

        return $query->where('clients.company_id', $user->company_id);
    }
}
