<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryCrudTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::factory()->create();
    }

    public function test_guest_cannot_access_categories_index(): void
    {
        $response = $this->get(route('admin.categories.index'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_categories_list(): void
    {
        $parent = Category::factory()->create(['name' => 'Фантастика']);
        $child = Category::factory()->create([
            'name' => 'Киберпанк',
            'parent_id' => $parent->id,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.categories.index'));

        $response->assertOk();
        $response->assertSee('Список жанров');
        $response->assertSee($parent->name);
        $response->assertSee($child->name);
    }

    public function test_admin_can_create_category_with_and_without_parent(): void
    {
        $parent = Category::factory()->create(['name' => 'Драма']);

        $createPage = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.categories.create'));

        $createPage->assertOk();
        $createPage->assertSee('Новый жанр');
        $createPage->assertSee('Драма');

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.categories.store'), [
                'name' => 'Мелодрама',
                'parent_id' => $parent->id,
            ]);

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('status');
        $this->assertDatabaseHas('categories', [
            'name' => 'Мелодрама',
            'parent_id' => $parent->id,
        ]);
    }

    public function test_category_creation_validates_fields(): void
    {
        Category::factory()->create(['name' => 'Боевик']);

        $emptyResponse = $this->actingAs($this->admin, 'admin')
            ->from(route('admin.categories.create'))
            ->post(route('admin.categories.store'), ['name' => '']);

        $emptyResponse->assertRedirect(route('admin.categories.create'));
        $emptyResponse->assertSessionHasErrors('name');

        $duplicateResponse = $this->actingAs($this->admin, 'admin')
            ->from(route('admin.categories.create'))
            ->post(route('admin.categories.store'), ['name' => 'Боевик']);

        $duplicateResponse->assertRedirect(route('admin.categories.create'));
        $duplicateResponse->assertSessionHasErrors('name');
    }

    public function test_admin_can_edit_and_update_category(): void
    {
        $parent = Category::factory()->create(['name' => 'Комедия']);
        $category = Category::factory()->create(['name' => 'Ситком']);

        $editPage = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.categories.edit', $category));

        $editPage->assertOk();
        $editPage->assertSee('Изменение жанра');
        $editPage->assertSee('Ситком');

        $response = $this->actingAs($this->admin, 'admin')
            ->put(route('admin.categories.update', $category), [
                'name' => 'Романтическая комедия',
                'parent_id' => $parent->id,
            ]);

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('status');
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Романтическая комедия',
            'parent_id' => $parent->id,
        ]);
    }

    public function test_category_cannot_be_its_own_parent(): void
    {
        $category = Category::factory()->create(['name' => 'Триллер']);

        $response = $this->actingAs($this->admin, 'admin')
            ->from(route('admin.categories.edit', $category))
            ->put(route('admin.categories.update', $category), [
                'name' => 'Триллер',
                'parent_id' => $category->id,
            ]);

        $response->assertRedirect(route('admin.categories.edit', $category));
        $response->assertSessionHasErrors('parent_id');
    }

    public function test_admin_can_delete_category(): void
    {
        $category = Category::factory()->create(['name' => 'Ужасы']);

        $response = $this->actingAs($this->admin, 'admin')
            ->delete(route('admin.categories.destroy', $category));

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('status');
        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }

    public function test_category_show_route_is_excluded(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->admin, 'admin')
            ->get("/admin/categories/{$category->id}");

        $response->assertStatus(405);
    }
}
