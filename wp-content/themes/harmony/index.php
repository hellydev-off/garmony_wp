<?php
use Timber\Timber;

$context = Timber::context();
$context['posts'] = Timber::get_posts();

if ( is_singular() ) {
	$context['post'] = Timber::get_post();
}

Timber::render( 'pages/page-generic.twig', $context );
