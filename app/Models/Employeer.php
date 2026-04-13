<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employeer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'cpf',
        'adress',
        'position',
        'hired_at'
    ];

    protected function casts(): array
    {
        return [
            'hired_at'=> 'hired_at',
        ];
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workLog(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WorksLog::class);
    }

    public function todayLog(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(WorksLog::class)->whereDate('work_date', today());
    }


}
