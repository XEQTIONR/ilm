<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Fillable;


#[Fillable(['title', 'description', 'difficulty', 'category', 'length_type', 'age_groups', 'image', 'video', 'total_lessons', 'status', 'owned_by_id', 'meta'])]
class Course extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'age_groups' => 'array',
        ];
    }

    public function ownedBy(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
