<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EndUser extends Model
{
    protected $fillable = [
        'nama',
        'industri',
        'contact',
        'telepon',
        'email',
        'kota',
        'npwp',
    ];


    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}
