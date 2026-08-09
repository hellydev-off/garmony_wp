<?php
/**
 * ACF field groups, registered in PHP (не через UI) — чтобы поля версионировались в git.
 */

add_action( 'acf/init', function () {

	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group( [
		'key'    => 'group_doctor',
		'title'  => 'Врач',
		'fields' => [
			[
				'key'   => 'field_doctor_photo',
				'label' => 'Фото',
				'name'  => 'photo',
				'type'  => 'image',
				'return_format' => 'array',
				'preview_size'  => 'medium',
			],
			[
				'key'   => 'field_doctor_specialization',
				'label' => 'Специализация',
				'name'  => 'specialization',
				'type'  => 'text',
				'instructions' => 'Например: «Гинеколог, врач высшей категории» или «Исполнительный директор».',
			],
			[
				'key'   => 'field_doctor_experience',
				'label' => 'Стаж',
				'name'  => 'experience',
				'type'  => 'text',
				'instructions' => 'Например: «7 лет». Необязательно.',
			],
			[
				'key'   => 'field_doctor_price_from',
				'label' => 'Стоимость приёма (от)',
				'name'  => 'price_from',
				'type'  => 'text',
				'instructions' => 'Например: «от 1900 ₽». Необязательно.',
			],
			[
				'key'   => 'field_doctor_quote',
				'label' => 'Цитата',
				'name'  => 'quote',
				'type'  => 'textarea',
				'rows'  => 2,
				'instructions' => 'Короткая цитата врача (используется в блоке «Ведение беременности»). Необязательно.',
			],
			[
				'key'   => 'field_doctor_diplomas',
				'label' => 'Дипломы и сертификаты',
				'name'  => 'diplomas',
				'type'  => 'repeater',
				'layout' => 'table',
				'button_label' => 'Добавить диплом',
				'sub_fields' => [
					[
						'key'   => 'field_doctor_diploma_year',
						'label' => 'Год',
						'name'  => 'year',
						'type'  => 'text',
						'wrapper' => [ 'width' => '20' ],
					],
					[
						'key'   => 'field_doctor_diploma_text',
						'label' => 'Описание',
						'name'  => 'text',
						'type'  => 'text',
						'wrapper' => [ 'width' => '80' ],
					],
				],
			],
			[
				'key'   => 'field_doctor_accreditations',
				'label' => 'Аккредитации',
				'name'  => 'accreditations',
				'type'  => 'repeater',
				'layout' => 'table',
				'button_label' => 'Добавить аккредитацию',
				'sub_fields' => [
					[
						'key'   => 'field_doctor_accred_text',
						'label' => 'Текст',
						'name'  => 'text',
						'type'  => 'text',
						'wrapper' => [ 'width' => '70' ],
					],
					[
						'key'   => 'field_doctor_accred_link',
						'label' => 'Ссылка',
						'name'  => 'link',
						'type'  => 'url',
						'wrapper' => [ 'width' => '30' ],
					],
				],
			],
			[
				'key'   => 'field_doctor_is_featured',
				'label' => 'Ключевой специалист',
				'name'  => 'is_featured',
				'type'  => 'true_false',
				'ui'    => 1,
				'instructions' => 'Показывать в блоке «Ключевые специалисты клиники» на странице «Врачи» и на главной.',
			],
			[
				'key'   => 'field_doctor_is_new',
				'label' => 'Новый врач',
				'name'  => 'is_new',
				'type'  => 'true_false',
				'ui'    => 1,
				'instructions' => 'Показывать в блоке «Наши новые врачи» на главной.',
			],
			[
				'key'   => 'field_doctor_is_gynecologist',
				'label' => 'Гинеколог (показывать на странице «Гинекология»)',
				'name'  => 'is_gynecologist',
				'type'  => 'true_false',
				'ui'    => 1,
				'instructions' => 'Показывать в списке врачей на странице «Гинекология».',
			],
		],
		'location' => [
			[
				[
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'doctor',
				],
			],
		],
	] );

	acf_add_local_field_group( [
		'key'    => 'group_review',
		'title'  => 'Отзыв',
		'fields' => [
			[
				'key'   => 'field_review_author_name',
				'label' => 'Имя автора',
				'name'  => 'author_name',
				'type'  => 'text',
				'required' => 1,
			],
			[
				'key'   => 'field_review_rating',
				'label' => 'Рейтинг',
				'name'  => 'rating',
				'type'  => 'number',
				'min'   => 0,
				'max'   => 5,
				'step'  => 0.5,
				'required' => 1,
			],
			[
				'key'   => 'field_review_date',
				'label' => 'Дата',
				'name'  => 'review_date',
				'type'  => 'date_picker',
				'display_format' => 'd.m.Y',
				'return_format'  => 'd.m.Y',
			],
			[
				'key'   => 'field_review_doctor',
				'label' => 'Врач',
				'name'  => 'doctor',
				'type'  => 'post_object',
				'post_type' => [ 'doctor' ],
				'return_format' => 'object',
				'instructions'  => 'Необязательно — если отзыв не привязан к конкретному врачу.',
			],
			[
				'key'   => 'field_review_text',
				'label' => 'Текст отзыва',
				'name'  => 'review_text',
				'type'  => 'textarea',
				'rows'  => 4,
				'required' => 1,
			],
		],
		'location' => [
			[
				[
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'review',
				],
			],
		],
	] );

	acf_add_local_field_group( [
		'key'    => 'group_appointment_request',
		'title'  => 'Заявка',
		'fields' => [
			[
				'key'   => 'field_appointment_name',
				'label' => 'Имя',
				'name'  => 'name',
				'type'  => 'text',
			],
			[
				'key'   => 'field_appointment_phone',
				'label' => 'Телефон',
				'name'  => 'phone',
				'type'  => 'text',
			],
			[
				'key'   => 'field_appointment_type',
				'label' => 'Тип заявки',
				'name'  => 'request_type',
				'type'  => 'text',
				'instructions' => 'Например: «Запись на приём» или «Вызов врача» — какая кнопка была нажата.',
			],
			[
				'key'   => 'field_appointment_doctor',
				'label' => 'Врач',
				'name'  => 'doctor',
				'type'  => 'post_object',
				'post_type' => [ 'doctor' ],
				'return_format' => 'object',
				'instructions'  => 'Пусто — если посетитель выбрал «Любой врач».',
			],
			[
				'key'   => 'field_appointment_is_processed',
				'label' => 'Обработано',
				'name'  => 'is_processed',
				'type'  => 'true_false',
				'ui'    => 1,
			],
		],
		'location' => [
			[
				[
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'appointment_request',
				],
			],
		],
	] );

} );
