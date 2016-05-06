<?php namespace Lease317\APNS;

use Illuminate\Database\Eloquent\Model as Eloquent;

class PushNotification extends Eloquent
{
    public $fillable = [
        'user_id',
        'device_token',
        'text',
        'custom_properties',
    ];
}