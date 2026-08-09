<?php
/**
 * Harmony theme bootstrap (Timber/Twig).
 */

require_once __DIR__ . '/vendor/autoload.php';

use Timber\Timber;

if ( ! class_exists( 'Timber\Timber' ) ) {
	add_action( 'admin_notices', function () {
		echo '<div class="notice notice-error"><p>Timber не найден. Выполните <code>composer install</code> в wp-content/themes/harmony.</p></div>';
	} );
	return;
}

Timber::init();
Timber::$dirname = [ 'views' ];

require_once __DIR__ . '/inc/post-types.php';
require_once __DIR__ . '/inc/acf-fields.php';
require_once __DIR__ . '/inc/ajax.php';

/**
 * Приводит свободный текст поля «Специализация» врача к одной укрупнённой категории
 * для фильтра на странице «Врачи» (specialization — текст вида «Гинеколог, врач
 * высшей категории», без единого формата, так что для фильтра нужна нормализация).
 * Порядок ключевых слов важен: например, у главного врача в специализации встречаются
 * и «главный врач», и «гинеколог» — для фильтра важнее медицинский профиль.
 */
function harmony_doctor_specialty_tag( string $specialization ): string {
	$s = mb_strtolower( $specialization );

	$map = [
		'гинеколог'       => 'Гинекология',
		'акушер'          => 'Гинекология',
		'педиатр'         => 'Педиатрия',
		'лор'             => 'Оториноларингология (ЛОР)',
		'гастроэнтеролог' => 'Гастроэнтерология',
		'пульмонолог'     => 'Пульмонология',
		'нутрициолог'     => 'Нутрициология',
		'терапевт'        => 'Терапия',
		'узи'             => 'УЗИ-диагностика',
		'директор'        => 'Администрация',
		'главный врач'    => 'Администрация',
	];

	foreach ( $map as $needle => $tag ) {
		if ( mb_strpos( $s, $needle ) !== false ) {
			return $tag;
		}
	}

	return 'Другое';
}

/**
 * Регистрация меню и сайдбаров.
 */
add_action( 'after_setup_theme', function () {
	register_nav_menus( [
		'primary' => 'Главное меню (шапка)',
		'footer'  => 'Меню в подвале',
	] );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', [ 'search-form', 'gallery', 'caption' ] );
} );

/**
 * Подключение стилей и скриптов темы.
 * Исходники собраны gulp-ом в dist/ и перенесены в assets/ 1:1
 * (относительные url() в main.css рассчитаны на такую же структуру папок).
 */
add_action( 'wp_enqueue_scripts', function () {
	// Версия по времени изменения файла — чтобы браузер не отдавал закэшированный
	// старый CSS/JS после каждой правки (fallback на Version темы, если файла нет).
	$theme_version = wp_get_theme()->get( 'Version' );
	$asset_version = function ( $relative_path ) use ( $theme_version ) {
		$path = get_stylesheet_directory() . '/' . $relative_path;
		return file_exists( $path ) ? filemtime( $path ) : $theme_version;
	};

	wp_enqueue_style( 'harmony-swiper', get_theme_file_uri( 'assets/css/swiper-bundle.min.css' ), [], $asset_version( 'assets/css/swiper-bundle.min.css' ) );
	wp_enqueue_style( 'harmony-main', get_theme_file_uri( 'assets/css/main.css' ), [ 'harmony-swiper' ], $asset_version( 'assets/css/main.css' ) );

	// jQuery + Bootstrap 3 JS + Swiper + кастомный JS, собранные в один bundle.js gulp-ом.
	wp_enqueue_script( 'harmony-bundle', get_theme_file_uri( 'assets/js/bundle.js' ), [], $asset_version( 'assets/js/bundle.js' ), true );

	wp_localize_script( 'harmony-bundle', 'harmonyAjax', [
		'url'   => admin_url( 'admin-ajax.php' ),
		'nonce' => wp_create_nonce( 'harmony_forms' ),
	] );
} );

/**
 * Базовый Timber-контекст: меню, ссылки на тему/сайт, год для копирайта в футере.
 */
add_filter( 'timber/context', function ( $context ) {
	$context['primary_menu'] = Timber::get_menu( 'primary' );
	$context['footer_menu']  = Timber::get_menu( 'footer' );
	$context['theme_uri']    = get_stylesheet_directory_uri();
	$context['site_url']     = home_url();
	$context['siteName']     = get_bloginfo( 'name' );
	$context['year']         = date( 'Y' );

	// Дефолтные отзывы для partials/reviews-slider.twig, когда конкретные не переданы явно.
	$context['latest_reviews'] = Timber::get_posts( [
		'post_type'      => 'review',
		'posts_per_page' => 3,
		'orderby'        => 'date',
		'order'          => 'DESC',
	] );

	// Последние новости для слайдера на главной (partials/news.twig).
	$context['latest_news'] = Timber::get_posts( [
		'post_type'      => 'post',
		'posts_per_page' => 3,
		'orderby'        => 'date',
		'order'          => 'DESC',
	] );

	// Полный список врачей — для выбора в модалке заявки (partials/appointment-modal.twig).
	$context['all_doctors'] = Timber::get_posts( [
		'post_type'      => 'doctor',
		'posts_per_page' => -1,
		'orderby'        => 'title',
		'order'          => 'ASC',
	] );

	return $context;
} );
