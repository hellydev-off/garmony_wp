<?php
use Timber\Timber;

$context = Timber::context();
$context['post'] = Timber::get_post();

// Отзывы именно об этом враче (только опубликованные — отправленные с сайта уходят на модерацию).
$context['doctor_reviews'] = Timber::get_posts( [
	'post_type'      => 'review',
	'posts_per_page' => -1,
	'orderby'        => 'date',
	'order'          => 'DESC',
	'meta_query'     => [
		[ 'key' => 'doctor', 'value' => get_the_ID() ],
	],
] );

$ratings = array_map( fn( $r ) => (float) $r->meta( 'rating' ), iterator_to_array( $context['doctor_reviews'] ) );
$context['doctor_avg_rating'] = $ratings ? round( array_sum( $ratings ) / count( $ratings ), 1 ) : null;

Timber::render( 'pages/single-doctor.twig', $context );
