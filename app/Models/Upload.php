<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Casts\Attribute;

#[Fillable(['path', 'expires_at'])]
#[Appends(['url'])]
class Upload extends Model
{
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    protected function url(): Attribute
    {
        return Attribute::make(
            get: fn ($value, array $attr) =>  
            isset($attr['path']) ? 
            Storage::url($attr['path']) : null
        );
    }
}
