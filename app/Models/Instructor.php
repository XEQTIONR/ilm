<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Instructor extends Model
{
    protected $fillable = [
        'reading',
        'writing',
        'speaking',
        'languages',
        'is_muslim',
        'titles',
        'course_categories',
    ];

    protected function casts(): array
    {
        return [
            'languages' => 'array',
            'titles' => 'array',
            'course_categories' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
