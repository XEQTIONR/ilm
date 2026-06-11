<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['start_date', 'end_date', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday', 'exceptions', 'meta'])]
class Availibility extends Model
{
    use SoftDeletes;
    
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'monday' => 'array',
            'tuesday' => 'array',
            'wednesday' => 'array',
            'thursday' => 'array',
            'friday' => 'array',
            'saturday' => 'array',
            'sunday' => 'array',
            'exceptions' => 'array',
            'meta' => 'array',
        ];
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
