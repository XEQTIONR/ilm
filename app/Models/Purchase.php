<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['user_id', 'course_format_id', 'stripe_account_id', 'stripe_checkout_session_id', 'stripe_payment_intent_id', 'price', 'currency', 'status'])]
class Purchase extends Model
{
    //


    protected function price(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100,
        );
    }
}
