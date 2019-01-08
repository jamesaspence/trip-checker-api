<?php
/**
 * Created by PhpStorm.
 * User: jamesspence
 * Date: 2019-01-07
 * Time: 19:25
 */

namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;
use App\Models\Template;
use App\Models\User;
use Illuminate\Http\Request;

class TemplateController extends Controller
{

    public function getTemplates(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        return Template::with('templateItems')
            ->where('user_id', '=', $user->id)
            ->get();
    }

    public function getTemplate(Template $template)
    {
        $template->load('templateItems');

        return $template;
    }

    public function createTemplate(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'items' => 'required|array',
            'items.item' => 'required|string',
            'items.order' => 'required|integer'
        ]);
    }

}