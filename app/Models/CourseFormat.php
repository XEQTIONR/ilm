<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

#[Fillable(['title', 'description', 'unit', 'lesson_length', 'lessons_per_week', 'price', 'billing_cycles'])]
class CourseFormat extends Model
{
    
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    protected function price(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100,
        );
    }
}