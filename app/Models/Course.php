<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

#[Fillable([
    'title', 
    'description', 
    'difficulty', 
    'category', 
    'length_type', 
    'age_groups', 
    'image', 
    'is_published', 
    'video', 
    'status', 
    'meta'
])]
class Course extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'age_groups' => 'array',
            'is_published' => 'boolean',
        ];
    }

    public function ownedBy(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function formats(): HasMany
    {
        return $this->hasMany(CourseFormat::class);
    }

    public function enrollments(): HasManyThrough
    {
        return $this->hasManyThrough(Enrollment::class, CourseFormat::class);
    }
}
