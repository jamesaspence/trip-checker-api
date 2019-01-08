<?php
/**
 * Created by PhpStorm.
 * User: jamesspence
 * Date: 2019-01-07
 * Time: 19:38
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class TemplateItem extends Model
{
    public function template()
    {
        return $this->belongsTo(Template::class);
    }
}