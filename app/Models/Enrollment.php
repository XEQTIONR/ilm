<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

#[Fillable(['user_id', 'course_format_id', 'length', 'start_at', 'end_at', 'status', 'schedule', 'timezone'])]
class Enrollment extends Model
{
    //

    protected function casts(): array
    {
        return [
            'length' => 'integer',
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'schedule' => 'array',
            'timezone' => 'string',
        ];
    }

    public function format(): BelongsTo
    {
        return $this->belongsTo(CourseFormat::class, 'course_format_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('start_at', '<=', now())
            ->where(function ($query) {
                $query->where('end_at', '>=', now())
                    ->orWhereNull('end_at');
            });
    }
}
