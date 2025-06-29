<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Category;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subcategory>
 */
class SubCategoryFactory extends Factory
{
    public function definition(): array
    {
        $subCategoryNames = [
            'Tax' => ['Income Tax', 'Property Tax', 'Sales Tax', 'Business Tax'],
            'Utilities' => ['Electricity', 'Water', 'Gas', 'Sewer'],
            'Supplies' => ['Office Supplies', 'Cleaning Supplies', 'Medical Supplies', 'IT Supplies'],
            'Salaries' => ['Staff Salaries', 'Overtime Pay', 'Bonuses', 'Contractor Payments'],
            'Maintenance' => ['Building Maintenance', 'Vehicle Maintenance', 'Equipment Maintenance', 'Groundskeeping'],
            'Insurance' => ['Health Insurance', 'Property Insurance', 'Vehicle Insurance', 'Liability Insurance'],
            'Travel' => ['Local Travel', 'International Travel', 'Accommodation', 'Transportation'],
            'Training' => ['Workshops', 'Seminars', 'Online Courses', 'Certifications'],
            'Equipment' => ['Computers', 'Furniture', 'Machinery', 'Tools'],
            'Miscellaneous' => ['Petty Cash', 'Unexpected Expenses', 'Other'],
            'Office Expenses' => ['Stationery', 'Coffee Supplies', 'Breakroom Supplies'],
            'Legal Fees' => ['Consultation Fees', 'Court Fees', 'Document Preparation'],
            'Consulting' => ['IT Consulting', 'Business Consulting', 'HR Consulting'],
            'Advertising' => ['Online Ads', 'Print Ads', 'Event Sponsorship'],
            'Repairs' => ['Building Repairs', 'Vehicle Repairs', 'Equipment Repairs'],
            'Rent' => ['Office Rent', 'Warehouse Rent', 'Equipment Rent'],
            'Telephone' => ['Landline', 'Mobile', 'Internet Calls'],
            'Internet' => ['Broadband', 'Fiber', 'Mobile Data'],
            'Postage' => ['Mailing', 'Courier', 'Shipping'],
            'Printing' => ['Brochures', 'Business Cards', 'Reports'],
            'Subscriptions' => ['Software Subscriptions', 'Magazine Subscriptions', 'Online Services'],
        ];

        $category = Category::inRandomOrder()->first() ?? Category::factory()->create();
        $categoryName = $category->name;
        $possibleSubs = $subCategoryNames[$categoryName] ?? ['General'];

        return [
            'category_id' => $category->id,
            'name' => $this->faker->randomElement($possibleSubs),
            'description' => $this->faker->sentence(),
            'status' => $this->faker->randomElement(['active', 'inactive']),
        ];
    }
}   