<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WorksLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'employeer_id',
        'work_date',
        'clock_in',
        'clock_out',
        'lunch_in',
        'lunch_out',
        'minutes_worked',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'work_date' => 'date',
            'clock_in' => 'datetime',
            'clock_out' => 'datetime',
            'lunch_in' => 'datetime',
            'lunch_out' => 'datetime',
        ];
    }

    public function employee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Employeer::class);
    }

    public function adjustments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ClockAdjustment::class);
    }

    public function getFormarttedHoursAtribute(): string
    {
        if (is_null($this->minutes_worked)) {
            return '--:--';
        }

        $hours   = intdiv($this->minutes_worked, 60);
        $minutes = $this->minutes_worked % 60;
        
        return sprintf('%02d:%02d', $hours, $minutes);
    }

    public function getNextActionAttribute(): ?string
    {
        return match ($this->status) {
            'in_progress' => 'lunch_out',
            'on_launch' => 'lunch_in',
            'back_from_launch' => 'clock_out',
            default => null,
        };
    }

    public function isComplete(): bool
    {
        return $this->status === 'complete';
    }

}
