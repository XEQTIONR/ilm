<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['reading', 'writing', 'speaking', 'languages', 'is_muslim', 'titles', 'course_categories'])]
class Instructor extends Model
{

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
