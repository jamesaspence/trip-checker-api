<?php


namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;
use App\Models\Template;
use App\Models\TemplateItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TemplateController extends Controller
{

    public function getTemplates(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $templates = Template::with('templateItems')
            ->where('user_id', '=', $user->id)
            ->get();

        return response()
            ->json([
                'status' => 'success',
                'message' => 'Successfully retrieved templates',
                'data' => [
                    'templates' => $templates
                ]
            ]);
    }

    public function getTemplate(Template $template)
    {
        $template->load('items');

        return response()
            ->json([
                'status' => 'success',
                'message' => 'Successfully retrieved template',
                'data' => [
                    'template' => $template
                ]
            ]);
    }

    public function createTemplate(Request $request)
    {
        $this->validate($request, [
            'name' => [
                'required',
                'string',
                Rule::unique('templates')->where(function ($query) use ($request) {
                    return $query->join('templates', 'template_items.template_id', '=', 'templates.id')
                        ->where('templates.user_id', '=', $request->user()->id);
                })
            ],
            'items' => 'required|array',
            'items.item' => 'required|string',
            'items.order' => 'required|integer'
        ]);

        $template = new Template();
        $template->name = $request->name;
        $template->user()->associate($request->user());
        $template->save();

        $templateItems = new Collection();
        foreach ($request->items as $item) {
            $templateItem = new TemplateItem();
            $templateItem->item = $item['item'];
            $templateItem->order = $item['order'];
            $templateItem->template()->associate($template);
            $templateItem->save();

            $templateItems->push($templateItem);
        }

        $template->load('items');

        return response()
            ->json([
                'status' => 'success',
                'message' => 'Successfully created template',
                'data' => [
                    'template' => $template
                ]
            ], 201);
    }

}