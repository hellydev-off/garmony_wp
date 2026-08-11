<?php
use Timber\Timber;

$context = Timber::context();

$context['new_doctors'] = Timber::get_posts( [
	'post_type'      => 'doctor',
	'posts_per_page' => -1,
	'meta_query'     => [
		'relation' => 'AND',
		[ 'key' => 'is_new', 'value' => '1' ],
		// Врачи с заполненной цитатой (quote) — это фотосессия для страницы «Ведение
		// беременности», её не показываем в общих подборках врачей на сайте.
		[ 'key' => 'quote', 'value' => '', 'compare' => '=' ],
	],
] );

// TODO: в исходной вёрстке этот блок показывал произвольную ручную подборку из 4 врачей,
// не связанную ни с одним из согласованных флагов (is_featured/is_new/is_gynecologist).
// Здесь — первые 4 обычных врача (без этих флагов), это best-effort, а не точное 1:1.
$context['specialist_doctors'] = Timber::get_posts( [
	'post_type'      => 'doctor',
	'posts_per_page' => 4,
	'orderby'        => 'title',
	'order'          => 'ASC',
	'meta_query'     => [
		'relation' => 'AND',
		[ 'key' => 'is_featured', 'value' => '1', 'compare' => '!=' ],
		[ 'key' => 'is_new', 'value' => '1', 'compare' => '!=' ],
		[ 'key' => 'photo', 'compare' => 'EXISTS' ],
		[ 'key' => 'quote', 'value' => '', 'compare' => '=' ],
	],
] );

Timber::render( 'pages/index.twig', $context );
