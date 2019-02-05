<?php


namespace Tests\Feature\API;


use App\Models\Template;
use App\Models\TemplateItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TemplateCrudTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;
    use MakesAuthenticatedRequest;

    /**
     * @var User
     */
    private $user;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->user = $this->createUser();
    }

    public function testGetTemplates()
    {
        $templates = $this->createTemplates($this->user);
        $templateIds = $templates->pluck('id');

        $otherUser = $this->createUser();
        $this->createTemplates($otherUser);

        $response = $this->makeRequest(route('api.templates.getTemplates'), 'GET', [], $this->user);

        $response->assertSuccessful();
        $data = $response->json('data');
        $this->assertNotNull($data);

        $this->assertArrayHasKey('templates', $data);

        $responseTemplates = $data['templates'];
        $this->assertCount($templates->count(), $responseTemplates);
        foreach ($responseTemplates as $responseTemplate) {
            $this->assertEquals($this->user->id, $responseTemplate['user_id']);
            $this->assertContains($responseTemplate['id'], $templateIds);
        }
    }

    public function testGetTemplateNotFound()
    {
        $template = $this->createTemplate($this->user);
        $id = $template->id + 1;

        $response = $this->makeRequest(
            $this->generateRouteForTemplate($id, 'getTemplate'),
            'GET',
            [],
            $this->user
        );

        $response->assertNotFound();
    }

    public function testGetTemplateNotOwnedByUser()
    {
        $otherUser = $this->createUser();

        $template = $this->createTemplate($otherUser);

        $url = $this->generateRouteForTemplate($template->id, 'getTemplate');

        $response = $this->makeRequest($url, 'GET', [], $this->user);

        $response->assertForbidden();
    }

    public function testGetTemplateSuccess()
    {
        $template = $this->createTemplate($this->user);

        $url = $this->generateRouteForTemplate($template->id, 'getTemplate');

        $response = $this->makeRequest($url, 'GET', [], $this->user);

        $response->assertSuccessful();

        $data = $response->json('data');

        $this->assertNotNull($data);

        $this->assertArrayHasKey('template', $data);

        $responseTemplate = $data['template'];

        $this->assertEquals($template->id, $responseTemplate['id']);
    }

    public function testCreateTemplateValidation()
    {
        $url = route('api.templates.create');

        $response = $this->makeRequest($url, 'POST', [], $this->user);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'name',
            'items'
        ]);
    }

    public function testCreateTemplateNameInUse()
    {
        $this->createTemplate($this->user, [
            'name' => 'test name'
        ]);

        $url = route('api.templates.create');

        $response = $this->makeRequest(
            $url,
            'POST',
            [
                'name' => 'test name',
                'items' => [
                    [
                        'item' => 'first',
                        'order' => 1
                    ],
                    [
                        'item' => 'second',
                        'order' => 2
                    ]
                ]
            ],
            $this->user
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'name'
        ]);
    }

    public function testCreateTemplateSuccess()
    {
        $url = route('api.templates.create');

        $response = $this->makeRequest(
            $url,
            'POST',
            [
                'name' => 'test name',
                'items' => [
                    [
                        'item' => 'first',
                        'order' => 1
                    ],
                    [
                        'item' => 'second',
                        'order' => 2
                    ]
                ]
            ],
            $this->user
        );

        $response->assertStatus(201);

        $data = $response->json('data');

        $this->assertNotNull($data);

        $this->assertArrayHasKey('template', $data);

        $responseTemplate = $data['template'];

        $this->assertEquals('test name', $responseTemplate['name']);
        $this->assertCount(2, $responseTemplate['items']);
    }

    public function testEditTemplateValidation()
    {
        $template = $this->createTemplate($this->user);

        $response = $this->makeRequest(
            $this->generateRouteForTemplate($template->id, 'update'),
            'PUT',
            [],
            $this->user
        );

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'name',
            'items'
        ]);
    }

    public function testEditTemplateNotFound()
    {
        $response = $this->makeRequest(
            $this->generateRouteForTemplate(1, 'update'),
            'PUT',
            [
                'name' => 'Test name',
                'items' => [
                    [
                        'item' => 'first',
                        'order' => 1
                    ],
                    [
                        'item' => 'second',
                        'order' => 2
                    ]
                ]
            ],
            $this->user
        );

        $response->assertNotFound();
    }

    public function testEditTemplateNameAlreadyInUse()
    {
        $this->createTemplate($this->user, [
            'name' => 'Test name'
        ]);
        $template = $this->createTemplate($this->user, [
            'name' => 'Second name'
        ]);

        $response = $this->makeRequest(
            $this->generateRouteForTemplate($template->id, 'update'),
            'PUT',
            [
                'name' => 'Test name',
                'items' => [
                    [
                        'item' => 'first',
                        'order' => 1
                    ],
                    [
                        'item' => 'second',
                        'order' => 2
                    ]
                ]
            ],
            $this->user
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'name'
        ]);
    }

    public function testEditTemplateNotOwnedByUser()
    {
        $otherUser = $this->createUser();
        $otherTemplate = $this->createTemplate($otherUser);

        $response = $this->makeRequest(
            $this->generateRouteForTemplate($otherTemplate->id, 'update'),
            'PUT',
            [
                'name' => 'Test name',
                'items' => [
                    [
                        'item' => 'first',
                        'order' => 1
                    ],
                    [
                        'item' => 'second',
                        'order' => 2
                    ]
                ]
            ],
            $this->user
        );

        $response->assertForbidden();
    }

    public function testEditTemplateSuccess()
    {
        $template = $this->createTemplate($this->user, [
            'name' => 'Original name'
        ]);

        $response = $this->makeRequest(
            $this->generateRouteForTemplate($template->id, 'update'),
            'PUT',
            [
                'name' => 'Test name',
                'items' => [
                    [
                        'item' => 'first',
                        'order' => 1
                    ],
                    [
                        'item' => 'second',
                        'order' => 2
                    ]
                ]
            ],
            $this->user
        );

        $response->assertSuccessful();
        $data = $this->assertAndRetrieveJsonData($response);

        $this->assertNotNull($data['template']);
        $this->assertEquals('Test name', $data['template']['name']);
        $this->assertCount(2, $data['template']['items']);
    }

    public function testEditTemplateUpdatesItems()
    {
        $template = $this->createTemplate($this->user, [
            'name' => 'Test name'
        ], 5);

        $template->loadMissing('items');
        $items = $template->items;

        $i = 0;
        $newItems = $items->shuffle()
            ->map(function (TemplateItem $item) use (&$i) {
                $i++;
                return [
                    'item' => $item->item . ' #' . $i,
                    'order' => $i
                ];
            });

        $response = $this->makeRequest(
            $this->generateRouteForTemplate($template->id, 'update'),
            'PUT',
            [
                'name' => 'Test name',
                'items' => $newItems
            ],
            $this->user
        );

        $response->assertSuccessful();
        $data = $this->assertAndRetrieveJsonData($response);

        $this->assertArrayHasKey('template', $data);

        $responseTemplate = $data['template'];
        $responseItems = $responseTemplate['items'];

        foreach ($responseItems as $j => $responseItem) {
            $newItem = $newItems->get($j);
            $this->assertNotNull($newItem);
            $this->assertEquals($newItem['item'], $responseItem['item']);
            $this->assertEquals($newItem['order'], $responseItem['order']);
        }
    }

    public function testDeleteTemplateNotFound()
    {
        $response = $this->makeRequest(
            $this->generateRouteForTemplate(1, 'delete'),
            'DELETE',
            [],
            $this->user
        );

        $response->assertNotFound();
    }

    public function testDeleteTemplateNotOwnedByUser()
    {
        $otherUser = $this->createUser();
        $otherTemplate = $this->createTemplate($otherUser);

        $response = $this->makeRequest(
            $this->generateRouteForTemplate($otherTemplate->id, 'delete'),
            'DELETE',
            [],
            $this->user
        );

        $response->assertForbidden();
    }

    public function testDeleteTemplateSuccess()
    {
        $template = $this->createTemplate($this->user);

        $response = $this->makeRequest(
            $this->generateRouteForTemplate($template->id, 'delete'),
            'DELETE',
            [],
            $this->user
        );

        $response->assertStatus(204);
    }

    private function createTemplate(User $user, array $attributes = [], $numberOfItems = null): Template
    {
        return $this->createTemplates($user, 1, $attributes, $numberOfItems)
            ->first();
    }

    private function createTemplates(User $user, $number = 3, array $attributes = [], int $numberOfItems = null): Collection
    {
        $numberOfItems = is_null($numberOfItems) ? rand(1, 5) : $numberOfItems;

        return factory(Template::class, $number)
            ->make($attributes)
            ->each(function (Template $template) use ($user, $numberOfItems) {
                $template->user()->associate($user);
                $template->save();
                $templateItems = new Collection();
                for ($i = 1; $i <= $numberOfItems; $i++) {
                    $templateItems->push($this->createTemplateItem($template, [
                        'order' => $i
                    ]));
                }
                $template->setRelation('items', $templateItems);
            });
    }

    private function createTemplateItem(Template $template, array $attributes = []): TemplateItem
    {
        $templateItem = factory(TemplateItem::class)
            ->make($attributes);

        $templateItem->template()->associate($template);
        $templateItem->save();

        return $templateItem;
    }

    private function generateRouteForTemplate(int $id, string $routeNameSuffix): string
    {
        return route('api.templates.' . $routeNameSuffix, [
            'template' => $id
        ]);
    }
}