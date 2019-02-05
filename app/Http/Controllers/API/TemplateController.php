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

        $templates = Template::with('items')
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
            'items.*.item' => 'required|string|distinct',
            'items.*.order' => 'required|integer|distinct'
        ]);

        $template = new Template();
        $template->name = $request->name;
        $template->user()->associate($request->user());
        $template->save();

        $templateItems = new Collection();
        foreach ($request->get('items', []) as $item) {
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

    public function editTemplate(Template $template, Request $request)
    {
        $this->validate($request, [
            'name' => [
                'required',
                'string',
                Rule::unique('templates')->ignore($template->id)->where(function ($query) use ($request) {
                    return $query->join('templates', 'template_items.template_id', '=', 'templates.id')
                        ->where('templates.user_id', '=', $request->user()->id);
                })
            ],
            'items' => 'required|array',
            'items.*.item' => 'required|string|distinct',
            'items.*.order' => 'required|integer|distinct'
        ]);

        $template->load('items');
        $template->name = $request->name;
        $template->save();

        /** @var Collection $existingItems */
        $existingItems = $template->items;
        $modifiedItems = new Collection();
        foreach ($request->get('items', []) as $requestItem) {
            $itemName = $requestItem['item'];
            $itemOrder = $requestItem['order'];

            $item = $existingItems->first(function ($indvItem) use ($itemName) {
                return $indvItem->item === $itemName;
            });

            if (is_null($item)) {
                $item = new TemplateItem();
                $item->item = $itemName;
                $item->template()->associate($template);
            }

            $item->order = $itemOrder;
            $item->save();
            $modifiedItems->push($item);
        }

        $modifiedItemIds = $modifiedItems->pluck('id');
        $itemsToDelete = $existingItems->whereNotIn('id', $modifiedItemIds);

        $itemsToDelete->each(function ($indvItem) {
            /** @var TemplateItem $indvItem */
            $indvItem->delete();
        });

        $template->load('items');

        return response()
            ->json([
                'status' => 'success',
                'message' => 'Successfully edited template',
                'data' => [
                    'template' => $template
                ]
            ]);
    }

    public function deleteTemplate(Template $template)
    {
        $template->delete();

        return response(null, 204);
    }

}