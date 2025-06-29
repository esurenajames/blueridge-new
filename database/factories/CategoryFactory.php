<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        static $groupPositions = [
            'Beginning Cash Balance' => 1,
            'Receipts' => 1,
            'Expenditures' => 1,
        ];

        $categoryNames = [
            'Tax',
            'Utilities',
            'Supplies',
            'Salaries',
            'Maintenance',
            'Insurance',
            'Travel',
            'Training',
            'Equipment',
            'Miscellaneous',
            'Office Expenses',
            'Legal Fees',
            'Consulting',
            'Advertising',
            'Repairs',
            'Rent',
            'Telephone',
            'Internet',
            'Postage',
            'Printing',
            'Subscriptions'
        ];

        $groupName = $this->faker->randomElement([
            'Beginning Cash Balance',
            'Receipts',
            'Expenditures'
        ]);

        $position = $groupPositions[$groupName];
        $groupPositions[$groupName]++;

        return [
            'name' => $this->faker->unique()->randomElement($categoryNames),
            'description' => $this->faker->sentence(),
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'position' => $position, 
            'group_name' => $groupName,
        ];
    }
}