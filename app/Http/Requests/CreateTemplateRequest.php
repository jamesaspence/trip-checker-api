<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

/**
 * @property string name
 */
class CreateTemplateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                $this->generateUniqueNameRule()
            ],
            'items' => 'required|array',
            'items.*.item' => 'required|string|distinct',
            'items.*.order' => 'required|integer|distinct'
        ];
    }

    protected function generateUniqueNameRule(): Unique
    {
        return Rule::unique('templates')->where(function ($query) {
            return $query->join('templates', 'template_items.template_id', '=', 'templates.id')
                ->where('templates.user_id', '=', $this->user()->id);
        });
    }
}
