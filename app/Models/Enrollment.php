<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

#[Fillable(['user_id', 'course_format_id', 'length', 'start_at', 'end_at', 'price', 'currency', 'status', 'schedule'])]
class Enrollment extends Model
{
    //

    protected function casts(): array
    {
        return [
            'length' => 'integer',
            'start_at' => 'timestamp',
            'end_at' => 'timestamp',
            'schedule' => 'array',
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

    protected function price(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100,
        );
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
