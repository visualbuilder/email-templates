<?php

namespace Visualbuilder\EmailTemplates\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Visualbuilder\EmailTemplates\Models\EmailTemplateTheme;

class EmailTemplateThemeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EmailTemplateTheme::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'colours' => '{}',
            'is_default' => 0,
        ];
    }
}
