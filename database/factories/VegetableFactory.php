<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Vegetable;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vegetable>
 */
class VegetableFactory extends Factory
{
    // ✅ Tell Laravel which model this factory is for
    protected $model = Vegetable::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::all()->random()->id,
            'name' => $this->faker->unique()->sentence(),
            'description' => $this->faker->text(),
        ];
    }
}
