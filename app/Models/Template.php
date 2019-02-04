<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(TemplateItem::class);
    }
}