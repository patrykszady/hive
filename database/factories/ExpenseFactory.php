<?php

namespace Database\Factories;

use App\Models\Vendor;
use App\Models\Expense;
use App\Models\Project;

use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Expense::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'amount' => $this->faker->randomFloat($nbMaxDecimals = 2, $min = -100, $max = 999),
            'date' => $this->faker->dateTimeBetween('-3 years', 'now'),
            'project_id' => function(){
                return Project::inRandomOrder()->first()->id;
            },
            'vendor_id' => function(){
                return Vendor::withoutGlobalScopes()->whereBetween('id',[2,49])->inRandomOrder()->first()->id;
            },
            'belongs_to_vendor_id' => 1,
            'created_by_user_id' => 1,
        ];
    }
}
