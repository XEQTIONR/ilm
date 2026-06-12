<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['reading', 'writing', 'speaking', 'languages', 'is_muslim', 'titles', 'course_categories'])]
class Instructor extends Model
{

    protected function casts(): array
    {
        return [
            'languages' => 'array',
            'titles' => 'array',
            'course_categories' => 'array',
            'availability' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function availability(): HasMany
    {
        return $this->hasMany(Availability::class);
    }
}
