<?php

namespace Visualbuilder\EmailTemplates\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Visualbuilder\EmailTemplates\Models\EmailTemplate;

class EmailTemplateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EmailTemplate::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition() {
        return [
            'key'        => Str::random(20),
            'language'   => config('filament-email-templates.default_locale'),
            'view'       => config('filament-email-templates.default_view'),
            'cc'         => null,
            'bcc'        => null,
            'send_to'    => 'user',
            'from'       => ['name'=>$this->faker->email,'email'=>$this->faker->name],
            'name'       => $this->faker->name,
            'preheader'  => $this->faker->sentence,
            'subject'    => $this->faker->sentence,
            'title'      => $this->faker->sentence,
            'content'    => $this->faker->text,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ];
    }
}
