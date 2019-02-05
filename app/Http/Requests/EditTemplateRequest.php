<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rules\Unique;

/**
 * @property string template
 */
class EditTemplateRequest extends CreateTemplateRequest
{
    protected function generateUniqueNameRule(): Unique
    {
        return parent::generateUniqueNameRule()
            ->ignore($this->template);
    }
}
