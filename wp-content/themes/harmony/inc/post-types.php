<?php
/**
 * Custom Post Types: doctor, review.
 * Новости используют встроенный post — отдельный CPT для них не заводим.
 */

add_action( 'init', function () {

	register_post_type( 'doctor', [
		'labels' => [
			'name'                  => 'Врачи',
			'singular_name'         => 'Врач',
			'add_new'               => 'Добавить врача',
			'add_new_item'          => 'Добавить врача',
			'edit_item'             => 'Редактировать врача',
			'new_item'              => 'Новый врач',
			'view_item'             => 'Смотреть врача',
			'search_items'          => 'Искать врачей',
			'not_found'             => 'Врачи не найдены',
			'not_found_in_trash'    => 'В корзине врачей не найдено',
			'all_items'             => 'Все врачи',
			'menu_name'             => 'Врачи',
		],
		'public'       => true,
		'show_in_menu' => true,
		'show_in_rest' => true,
		'menu_icon'    => 'dashicons-groups',
		'supports'     => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
		'has_archive'  => false,
		'rewrite'      => [ 'slug' => 'doctor' ],
	] );

	register_post_type( 'review', [
		'labels' => [
			'name'                  => 'Отзывы',
			'singular_name'         => 'Отзыв',
			'add_new'               => 'Добавить отзыв',
			'add_new_item'          => 'Добавить отзыв',
			'edit_item'             => 'Редактировать отзыв',
			'new_item'              => 'Новый отзыв',
			'view_item'             => 'Смотреть отзыв',
			'search_items'          => 'Искать отзывы',
			'not_found'             => 'Отзывы не найдены',
			'not_found_in_trash'    => 'В корзине отзывов не найдено',
			'all_items'             => 'Все отзывы',
			'menu_name'             => 'Отзывы',
		],
		'public'       => true,
		'show_in_menu' => true,
		'show_in_rest' => true,
		'menu_icon'    => 'dashicons-testimonial',
		'supports'     => [ 'title' ],
		'has_archive'  => false,
		'rewrite'      => [ 'slug' => 'review' ],
	] );

	register_post_type( 'appointment_request', [
		'labels' => [
			'name'               => 'Заявки',
			'singular_name'      => 'Заявка',
			'add_new'            => 'Добавить заявку',
			'add_new_item'       => 'Добавить заявку',
			'edit_item'          => 'Редактировать заявку',
			'new_item'           => 'Новая заявка',
			'view_item'          => 'Смотреть заявку',
			'search_items'       => 'Искать заявки',
			'not_found'          => 'Заявки не найдены',
			'not_found_in_trash' => 'В корзине заявок не найдено',
			'all_items'          => 'Все заявки',
			'menu_name'          => 'Заявки',
		],
		'public'       => false,
		'show_ui'      => true,
		'show_in_menu' => true,
		'show_in_rest' => false,
		'menu_icon'    => 'dashicons-phone',
		'supports'     => [ 'title' ],
		'has_archive'  => false,
		'capabilities' => [
			'create_posts' => 'do_not_allow', // Заявки создаются только через форму на сайте, не вручную в админке.
		],
		'map_meta_cap' => true,
	] );

} );
