<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Car;
use Tests\TestCase;

class CarControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_list_all_cars()
    {
        Car::factory()->count(3)->create();

        $response = $this->getJson('/api/cars');

        $response->assertStatus(200)
                 ->assertJsonCount(3);
    }

    public function test_it_can_filter_cars_by_category()
    {
        Car::factory()->create(['category' => 'supercar']);
        Car::factory()->create(['category' => 'oldtimer']);

        $response = $this->getJson('/api/cars?category=supercar');

        $response->assertStatus(200)
                 ->assertJsonCount(1)
                 ->assertJsonPath('0.category', 'supercar');
    }

    public function test_it_can_create_a_car()
    {
        $admin = \App\Models\User::factory()->create([
            'is_admin' => true,
        ]);

        $data = [
            'name' => 'F40',
            'manufacturer' => 'Ferrari',
            'year' => 1987,
            'category' => 'supercar',
            'price' => 1500000,
            'horsepower' => 478,
            'engine' => 'V8 Twin-Turbo',
            'top_speed' => 324,
            'acceleration' => 4.1,
            'description' => 'A legend.',
            'is_featured' => true,
        ];

        $response = $this->actingAs($admin)->postJson('/api/cars', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment(['name' => 'F40']);

        $this->assertDatabaseHas('cars', ['name' => 'F40']);
    }

    public function test_it_fails_validation_when_creating_a_car()
    {
        $admin = \App\Models\User::factory()->create([
            'is_admin' => true,
        ]);

        $response = $this->actingAs($admin)->postJson('/api/cars', []);

        $response->assertStatus(422)
                 ->assertJsonStructure(['errors' => ['name', 'manufacturer', 'category']]);
    }

    public function test_it_can_show_a_specific_car()
    {
        $car = Car::factory()->create(['name' => 'Test Car']);

        $response = $this->getJson("/api/cars/{$car->id}");

        $response->assertStatus(200)
                 ->assertJsonPath('name', 'Test Car');
    }

    public function test_it_can_update_a_car()
    {   
        $admin = \App\Models\User::factory()->create([
            'is_admin' => true,
        ]);

        $car = Car::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($admin)->putJson("/api/cars/{$car->id}", [
            'name' => 'New Name'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('cars', ['id' => $car->id, 'name' => 'New Name']);
    }

    public function test_it_can_delete_a_car()
    {   
        $admin = \App\Models\User::factory()->create([
            'is_admin' => true,
        ]);

        $car = Car::factory()->create();

        $response = $this->actingAs($admin)->deleteJson("/api/cars/{$car->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('cars', ['id' => $car->id]);
    }
}
