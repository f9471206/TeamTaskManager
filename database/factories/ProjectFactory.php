<?php

namespace Database\Factories;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->optional()->sentence(8),
            'status' => $this->faker->randomElement([
                ProjectStatus::ACTIVE,
                ProjectStatus::ARCHIVED,
            ]),
            'created_by' => User::factory(),
            'due_date' => $this->faker->optional()->date(),
        ];
    }
}
