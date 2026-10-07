<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{

    protected $fillable = [

        'end_user_id',
        'pic_id',
        'project_name',
        'account',
        'po_number',
        'quotation_number',
        'quotation_distribusi',
        'margin',
        'percentage',
        'status',
        'deadline_date',
    ];

    protected $casts = [
        'deadline_date' => 'date',
        'quotation_distribusi' => 'decimal:2',
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
}
