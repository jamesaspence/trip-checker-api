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

        $responseLink = $data['template'];

        $this->assertEquals($template->id, $responseLink['id']);
    }

    public function testEditTemplateNotFound()
    {
        $this->stub();
    }

    public function testEditTemplateNotOwnedByUser()
    {
        $this->stub();
    }

    public function testEditTemplateSuccess()
    {
        $this->stub();
    }

    public function testDeleteTemplateNotFound()
    {
        $this->stub();
    }

    public function testDeleteTemplateNotOwnedByUser()
    {
        $this->stub();
    }

    public function testDeleteTemplateSuccess()
    {
        $this->stub();
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