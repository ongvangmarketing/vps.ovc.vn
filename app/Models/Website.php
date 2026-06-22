<?php

namespace App\Models;

use App\Enums\WebsiteStatus;
use App\Enums\WebsiteType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Website extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'type' => WebsiteType::class,
        'status' => WebsiteStatus::class,
        'auto_deploy' => 'boolean',
        'ssl_enabled' => 'boolean',
        'last_deployed_at' => 'datetime',
    ];

    public function vpsFolder(): BelongsTo
    {
        return $this->belongsTo(VpsFolder::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(DeploymentLog::class)->orderBy('created_at', 'desc');
    }

    public function databases(): HasMany
    {
        return $this->hasMany(Database::class);
    }
}
