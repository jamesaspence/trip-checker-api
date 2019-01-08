<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class TemplateItem extends Model
{
    public function template()
    {
        return $this->belongsTo(Template::class);
    }
}