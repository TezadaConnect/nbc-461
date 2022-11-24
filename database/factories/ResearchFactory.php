<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Research;
use App\Models\Researcher;
use App\Models\User;

class ResearchFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Research::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {   
        for ($i = 0; $i < 4; $i++){
            Research::factory()->make([
                'classification' => rand(1, 3),
                'category' => rand(4, 5),
                'agenda' => rand(6, 10),
                'title' => $this->faker->sentence(),
                'researchers' => $this->faker->name.', '.$this->faker->name.', '.$this->faker->name,
                'keywords' => $this->faker->word().', '.$this->faker->word().', '.$this->faker->word().', '.
                        $this->faker->word().', '.$this->faker->word(),
                'discipline' => 304, // Computer and Information Sciences
                'nature_of_involvement' => rand(11, 12, 13, 224), // All options in involvement
                'research_type' => rand(14, 22),
                'funding_type' => rand(23, 25),
                'funding_amount' => $this->faker->randomFloat(2),
                'funding_agency' => $this->faker->text(),
                'status' => 26, // New Commitment
                'start_date' => date("Y-m-d"),
                'target_date' => date("Y-m-d", strtotime("+1 day")),
                'college_id' => 88,
                'department_id' => 296,
                'description' => $this->faker->word(),
                'user_id' => 1,
                'report_quarter' => 4,
                'report_year' => 2022,
            ]);
        }
    }
}
