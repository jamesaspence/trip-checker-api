<?php
/**
 * Created by PhpStorm.
 * User: jamesspence
 * Date: 2019-01-07
 * Time: 19:38
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function templateItems()
    {
        return $this->hasMany(TemplateItem::class);
    }
}