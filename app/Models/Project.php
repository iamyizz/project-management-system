<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'end_user_id',
        'pic_id',
        'project_name',
        'project_value',
        'po_number',
        'quotation_number',
        'internal_cost',
        'status',
        'deadline_date',
        'submission_token',
    ];

    protected $casts = [
        'deadline_date' => 'date',
        'project_value' => 'decimal:2',
        'internal_cost' => 'decimal:2',
        'margin' => 'decimal:2',
        'percentage' => 'decimal:2',
    ];

    public function endUser()
    {
        return $this->belongsTo(
            EndUser::class
        );
    }

    public function pic()
    {
        return $this->belongsTo(
            User::class,
            'pic_id'
        );
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(ProjectStatusHistory::class)->latest();
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ProjectActivity::class)->latest();
    }

}
