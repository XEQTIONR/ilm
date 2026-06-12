<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['instructor_id', 'start_date', 'end_date', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun', 'exceptions', 'meta'])]
class Availability extends Model
{
    use SoftDeletes;
    
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'mon' => 'array',
            'tue' => 'array',
            'wed' => 'array',
            'thu' => 'array',
            'fri' => 'array',
            'sat' => 'array',
            'sun' => 'array',
            'exceptions' => 'array',
            'meta' => 'array',
        ];
    }
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }
}
