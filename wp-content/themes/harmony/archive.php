<?php
use Timber\Timber;

/**
 * TODO: pages/news.twig сейчас перебирает статический массив `newsItems`,
 * заданный внутри самого twig, а не $context['posts']. Если новости станут
 * WP-постами/CPT, нужно будет прокинуть Timber::get_posts() в цикл шаблона.
 */
$context = Timber::context();
$context['posts'] = Timber::get_posts();

Timber::render( 'pages/news.twig', $context );
