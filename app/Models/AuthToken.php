<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property User user
 * @property string token
 */
class AuthToken extends Model
{
    use SoftDeletes;

    public $timestamps = true;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}