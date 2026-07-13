<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'title', 
    'description', 
    'unit', 
    'lesson_length', 
    'lessons_per_week', 
    'total_lessons', 
    'price', 
    'currency', 
    'billing_cycles', 
    'default'
])]
class CourseFormat extends Model
{

    protected function casts(): array {
        return [
            'default' => 'boolean',
        ];
    }
    
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    protected function price(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100,
        );
    }
}