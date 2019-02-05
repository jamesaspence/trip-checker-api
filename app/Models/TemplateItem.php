<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * @property string item
 * @property integer order
 */
class TemplateItem extends Model
{
    public function template()
    {
        return $this->belongsTo(Template::class);
    }
}