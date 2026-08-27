<?php
use Timber\Timber;

$context = Timber::context();
$post    = Timber::get_post();
$context['post'] = $post;

/**
 * Часть страниц теперь тянет данные из CPT doctor/review вместо хардкода в twig.
 */
switch ( $post->post_name ) {

	case 'about':
		$context['featured_doctors'] = Timber::get_posts( [
			'post_type'      => 'doctor',
			'posts_per_page' => -1,
			'meta_query'     => [
				'relation' => 'AND',
				[ 'key' => 'is_featured', 'value' => '1' ],
				// Отдельный флаг «Скрыть из общих списков» — например, для врачей,
				// которых показывают только на своей отдельной странице (см. «Ведение
				// беременности» ниже), не завязано на заполненность поля «Цитата».
				[
					'relation' => 'OR',
					[ 'key' => 'hide_general', 'compare' => 'NOT EXISTS' ],
					[ 'key' => 'hide_general', 'value' => '1', 'compare' => '!=' ],
				],
			],
		] );
		break;

	case 'doctors':
		$context['doctors'] = Timber::get_posts( [
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

		// Только по врачам из фильтруемой сетки (без «Ключевых специалистов» наверху) —
		// иначе в списке появится специальность, для которой в сетке нет ни одной карточки.
		$tags = [];
		foreach ( $context['doctors'] as $doctor ) {
			if ( $doctor->meta( 'is_featured' ) ) {
				continue;
			}
			$tags[] = harmony_doctor_specialty_tag( (string) $doctor->meta( 'specialization' ) );
		}
		$tags = array_unique( $tags );
		sort( $tags, SORT_STRING | SORT_FLAG_CASE );
		$context['specialty_tags'] = $tags;
		break;

	case 'gynecology':
		$context['gyno_doctors'] = Timber::get_posts( [
			'post_type'      => 'doctor',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'meta_query'     => [
				'relation' => 'AND',
				[ 'key' => 'is_gynecologist', 'value' => '1' ],
				[
					'relation' => 'OR',
					[ 'key' => 'hide_general', 'compare' => 'NOT EXISTS' ],
					[ 'key' => 'hide_general', 'value' => '1', 'compare' => '!=' ],
				],
			],
		] );
		break;

	case 'vedenie-beremennosti':
		// Врачи этого блока определяются наличием заполненной цитаты (поле quote),
		// а не отдельным флагом — так же, как этот список был curated вручную в исходной вёрстке.
		$context['vb_doctors'] = Timber::get_posts( [
			'post_type'      => 'doctor',
			'posts_per_page' => -1,
			'meta_query'     => [
				[ 'key' => 'quote', 'value' => '', 'compare' => '!=' ],
			],
		] );
		break;

	case 'reviews':
		$context['reviews'] = Timber::get_posts( [
			'post_type'      => 'review',
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
		] );
		break;

	case 'news':
		$context['posts'] = Timber::get_posts( [
			'post_type'      => 'post',
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
		] );
		break;

	case 'encyclopedia':
		$context['articles'] = Timber::get_posts( [
			'post_type'      => 'enc_article',
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'ASC',
		] );
		break;
}

/**
 * Слаг WP-страницы соответствует имени файла в views/pages/*.twig
 * (about, doctors, encyclopedia, gynecology, catalog, news, reviews, vedenie-beremennosti).
 * Если для слага нет отдельного шаблона — рендерим заголовок + контент из редактора.
 */
$template = "pages/{$post->post_name}.twig";

if ( ! file_exists( get_stylesheet_directory() . '/views/' . $template ) ) {
	$template = 'pages/page-generic.twig';
}

Timber::render( $template, $context );
