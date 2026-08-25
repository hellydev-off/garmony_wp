<?php
use Timber\Timber;

$context = Timber::context();
$context['post'] = Timber::get_post();

$terms = get_the_terms( get_the_ID(), 'enc_category' );
$context['category'] = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : null;

Timber::render( 'pages/single-encyclopedia.twig', $context );
