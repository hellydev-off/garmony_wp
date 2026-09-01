<?php
use Timber\Timber;

$context = Timber::context();

$context['new_doctors'] = Timber::get_posts( [
	'post_type'      => 'doctor',
	'posts_per_page' => -1,
	'meta_query'     => [
		'relation' => 'AND',
		[ 'key' => 'is_new', 'value' => '1' ],
		// Отдельный флаг «Скрыть из общих списков» — не завязано на заполненность
		// поля «Цитата», так что врач может иметь цитату (для «Ведение беременности»)
		// и всё равно оставаться в общих подборках.
		[
					'relation' => 'OR',
					[ 'key' => 'hide_general', 'compare' => 'NOT EXISTS' ],
					[ 'key' => 'hide_general', 'value' => '1', 'compare' => '!=' ],
				],
	],
] );

// Слайдер "Наши специалисты" на главной — показывает всех врачей клиники
// (кроме тех, кто явно скрыт флагом hide_general), а не фиксированную подборку из 4.
$context['specialist_doctors'] = Timber::get_posts( [
	'post_type'      => 'doctor',
	'posts_per_page' => -1,
	'orderby'        => 'title',
	'order'          => 'ASC',
	'meta_query'     => [
		[
					'relation' => 'OR',
					[ 'key' => 'hide_general', 'compare' => 'NOT EXISTS' ],
					[ 'key' => 'hide_general', 'value' => '1', 'compare' => '!=' ],
				],
	],
] );

Timber::render( 'pages/index.twig', $context );
