<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'course_format_id', 'length', 'start_date', 'end_date', 'price', 'currency', 'status', 'schedule'])]
class Enrollment extends Model
{
    //

    protected function casts(): array
    {
        return [
            'length' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
            'schedule' => 'array',
        ];
    }

    public function format(): BelongsTo
    {
        return $this->belongsTo(CourseFormat::class, 'course_format_id');
    }

    protected function price(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100,
        );
    }
}
