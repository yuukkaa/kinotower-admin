<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Country;
use App\Models\Film;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CountryCrudTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::factory()->create();
    }

    public function test_guest_cannot_access_countries_index(): void
    {
        $response = $this->get(route('admin.countries.index'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_countries_list(): void
    {
        $country = Country::factory()->create(['name' => 'Россия']);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.countries.index'));

        $response->assertOk();
        $response->assertSee('Список стран');
        $response->assertSee($country->name);
    }

    public function test_admin_can_create_country(): void
    {
        $createPage = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.countries.create'));

        $createPage->assertOk();
        $createPage->assertSee('Новая страна');

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.countries.store'), [
                'name' => 'Франция',
            ]);

        $response->assertRedirect(route('admin.countries.index'));
        $response->assertSessionHas('status');
        $this->assertDatabaseHas('countries', ['name' => 'Франция']);
    }

    public function test_country_creation_validates_name(): void
    {
        Country::factory()->create(['name' => 'Россия']);

        $emptyResponse = $this->actingAs($this->admin, 'admin')
            ->from(route('admin.countries.create'))
            ->post(route('admin.countries.store'), ['name' => '']);

        $emptyResponse->assertRedirect(route('admin.countries.create'));
        $emptyResponse->assertSessionHasErrors('name');

        $duplicateResponse = $this->actingAs($this->admin, 'admin')
            ->from(route('admin.countries.create'))
            ->post(route('admin.countries.store'), ['name' => 'Россия']);

        $duplicateResponse->assertRedirect(route('admin.countries.create'));
        $duplicateResponse->assertSessionHasErrors('name');
    }

    public function test_admin_can_edit_and_update_country(): void
    {
        $country = Country::factory()->create(['name' => 'США']);

        $editPage = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.countries.edit', $country));

        $editPage->assertOk();
        $editPage->assertSee('Изменение страны');
        $editPage->assertSee('США');

        $response = $this->actingAs($this->admin, 'admin')
            ->put(route('admin.countries.update', $country), [
                'name' => 'Соединённые Штаты',
            ]);

        $response->assertRedirect(route('admin.countries.index'));
        $response->assertSessionHas('status');
        $this->assertDatabaseHas('countries', [
            'id' => $country->id,
            'name' => 'Соединённые Штаты',
        ]);
    }

    public function test_admin_can_delete_unused_country(): void
    {
        $country = Country::factory()->create(['name' => 'Япония']);

        $response = $this->actingAs($this->admin, 'admin')
            ->delete(route('admin.countries.destroy', $country));

        $response->assertRedirect(route('admin.countries.index'));
        $response->assertSessionHas('status');
        $this->assertDatabaseMissing('countries', ['id' => $country->id]);
    }

    public function test_admin_cannot_delete_country_with_films(): void
    {
        $country = Country::factory()->create(['name' => 'Италия']);

        Film::create([
            'name' => 'Тестовый фильм',
            'country_id' => $country->id,
            'duration' => 120,
            'year_of_issue' => 2020,
            'age' => 16,
            'link_video' => 'https://example.com/video.mp4',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->delete(route('admin.countries.destroy', $country));

        $response->assertRedirect(route('admin.countries.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('countries', ['id' => $country->id]);
    }

    public function test_country_show_route_is_excluded(): void
    {
        $country = Country::factory()->create();

        $response = $this->actingAs($this->admin, 'admin')
            ->get("/admin/countries/{$country->id}");

        $response->assertStatus(405);
    }
}
