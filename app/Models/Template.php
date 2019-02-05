<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer user_id
 * @property string name
 * @property Collection items
 * @property integer id
 */
class Template extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(TemplateItem::class)->orderBy('order');
    }
}