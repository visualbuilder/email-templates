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
	public function definition()
	{
		return [
		        'key'        => Str::random(20),
		        'language'   => config('email-templates.default-locale'),
                'view'       => 'emails.generic_email',
                'cc'         => null,
                'bcc'         => null,
		        'send_to'   => 'user',
		        'from'       => $this->faker->email,
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
