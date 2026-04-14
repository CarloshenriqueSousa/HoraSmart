<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClockAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_log_id',
        'requested_by',
        'reviewed_by',
        'field',
        'requested_time',
        'reason',
        'status',
        'reviewer_comment',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'requested_time' => 'datetime',
            'reviewed_at'    => 'datetime',
        ];
    }

    public function workLog(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(WorkLog::class);
    }

    public function requester(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}