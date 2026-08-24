<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    public const ROLE_SAAS_ADMIN = 'saas_admin';
    public const ROLE_COMPANY_ADMIN = 'company_admin';
    public const ROLE_COMPANY_MANAGER = 'company_manager';
    public const ROLE_COMPANY_APPROVER = 'company_approver';
    public const ROLE_CLIENT = 'client';

    public const ROLES = [
        self::ROLE_SAAS_ADMIN,
        self::ROLE_COMPANY_ADMIN,
        self::ROLE_COMPANY_MANAGER,
        self::ROLE_COMPANY_APPROVER,
        self::ROLE_CLIENT,
    ];

    /** @var array<int, string> */
    protected $fillable = [
        'company_id',
        'client_id',
        'role',
        'name',
        'email',
        'password',
        'timezone',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function isSaasAdmin(): bool
    {
        return $this->role === self::ROLE_SAAS_ADMIN;
    }

    public function isCompanyAdmin(): bool
    {
        return $this->role === self::ROLE_COMPANY_ADMIN;
    }

    public function isManager(): bool
    {
        return $this->role === self::ROLE_COMPANY_MANAGER;
    }

    public function isApprover(): bool
    {
        return $this->role === self::ROLE_COMPANY_APPROVER;
    }

    public function isClient(): bool
    {
        return $this->role === self::ROLE_CLIENT;
    }

    public function isInternal(): bool
    {
        return in_array($this->role, [self::ROLE_COMPANY_ADMIN, self::ROLE_COMPANY_MANAGER, self::ROLE_COMPANY_APPROVER]);
    }

    /**
     * Users who should receive approval workflow notifications for a company.
     *
     * @return \Illuminate\Support\Collection<int, self>
     */
    public static function managersForCompany(?int $companyId)
    {
        $managers = self::where('company_id', $companyId)
            ->whereIn('role', [self::ROLE_COMPANY_MANAGER])
            ->get();

        if ($managers->isNotEmpty()) {
            return $managers;
        }

        return self::where('company_id', $companyId)
            ->where('role', self::ROLE_COMPANY_ADMIN)
            ->get();
    }
}
