<?php
/**
 * 301-редиректы со старого сайта (garmonia-clinic.ru) на новую структуру адресов.
 * Нужны для старых ссылок/закладок/позиций в поиске, когда домен garmonia-clinic.ru
 * переключат на этот хостинг — старая структура (/services/..., отдельные
 * лендинги) на новом сайте не существует или живёт по другому адресу.
 *
 * Карта вида: 'старый путь' => 'новый путь'. Пути без ведущего/конечного слэша,
 * сравнение регистронезависимое.
 */

function harmony_redirect_map() {
	return [
		// ── Общие страницы ──────────────────────────────────────────────────────
		// 'dms' не редиректим: /dms/ — настоящая страница нового сайта, редирект
		// на саму себя зацикливался (ERR_TOO_MANY_REDIRECTS).
		'prices'                                       => '/services/',
		'displasiya-sheyki-matki'                      => '/encyclopedia/displaziya-sheyki-matki/',
		'trepanobiopsiya'                              => '/trepanobiopsiya-novost/',
		'astma-shkola'                                 => '/pulmonologiya/',
		'konsultaciya'                                 => '/contacts/',
		'lechenie-hrapa'                                => '/otorinolaringologiya/',
		'patronazh-novorozhdennogo-rebenka-na-domu'    => '/pediatriya/',
		'prenetix'                                     => '/vedenie-beremennosti/',
		'proverka-zrenija-besplatno'                   => '/obshchaya-praktika/',
		'rannee-starenie'                              => '/',
		'video-blog'                                   => '/',
		'voprosi'                                      => '/',

		// ── Услуги: /services/... → плоские адреса нового сайта ────────────────
		// 'services' не редиректим: /services/ — настоящая страница нового сайта,
		// редирект на саму себя зацикливался (ERR_TOO_MANY_REDIRECTS).
		'services/uzi'                                 => '/uzi/',
		'services/uzi/uzi-malogo-taza'                 => '/uzi-malogo-taza/',
		'services/uzi/uzi-brjushnoj-polosti'           => '/uzi-brjushnoj-polosti/',
		'services/uzi/uzi-dlja-beremennyh'             => '/uzi-dlja-beremennyh/',
		'services/uzi/uzi-mjagkih-tkanej'              => '/uzi-mjagkih-tkanej/',
		'services/uzi/uzi-molochnyh-zhelez'            => '/uzi-molochnyh-zhelez/',
		'services/uzi/uzi-pochek'                      => '/uzi-pochek/',
		'services/uzi/uzi-shhitovidki'                 => '/uzi-shhitovidki/',
		'services/uzi/uzi-sosudov'                     => '/uzi-sosudov/',
		'services/uzi/uzi-v-urologii'                  => '/uzi-v-urologii/',
		'services/uzi/uzi-serdca'                      => '/uzi/',
		'services/uzi/uzi-sustavov'                    => '/uzi/',
		'services/uzi/prenatalnyj-skrining'            => '/vedenie-beremennosti/#screening-details',
		'services/diagnostica'                         => '/funkcionalnaya-diagnostika/',
		'services/diagnostica/ekg'                     => '/ekg/',
		'services/diagnostica/holter_monitorirovanie'  => '/holterovskoe-monitorirovanie/',
		'services/diagnostica/spirometria'             => '/spirometriya/',
		'services/diagnostica/urofloumetria'           => '/urofluometriya/',
		'services/dnevnoy-stacionar'                   => '/dnevnoy-stacionar/',
		'services/onkoskrining'                        => '/onkoskrining/',
		'services/endoskopiya'                         => '/endoskopiya/',
		'services/ginekolog'                           => '/gynecology/',
		'services/urolog'                              => '/urologiya/',
		'services/individualnoe-vedenie-beremennosti'  => '/vedenie-beremennosti/',
		'services/detskoe-otdelenie'                   => '/pediatriya/',
		'services/massage'                             => '/massazh/',
		'services/phizioterapia'                       => '/fizioterapiya/',
		'services/phizioterapia/ozon'                  => '/fizioterapiya/',
		'services/issledovania'                        => '/laboratoria/',
		'services/practica'                            => '/obshchaya-praktika/',
		'services/practica/pediatr'                    => '/pediatr/',
		'services/practica/allergolog-immunolog'       => '/allergologiya/',
		'services/practica/angionevrolog'              => '/nevrologiya/',
		'services/practica/endokrinolog'               => '/hirurgiya-endokrinologiya/',
		'services/practica/flebolog'                   => '/flebologiya/',
		'services/practica/gastroenterolog'            => '/gastroenterologiya/',
		'services/practica/gematolog'                  => '/gematologiya/',
		'services/practica/hirurg'                     => '/hirurgiya-endokrinologiya/',
		'services/practica/jendoskopist'                => '/endoskopiya/',
		'services/practica/kardiolog'                  => '/kardiologiya/',
		'services/practica/mammolog'                   => '/onkologiya-mammologiya/',
		'services/practica/nevrolog'                   => '/nevrologiya/',
		'services/practica/oftalmolog'                 => '/obshchaya-praktika/',
		'services/practica/onkolog'                    => '/onkologiya-mammologiya/',
		'services/practica/ortoped'                    => '/travmatologiya-ortopediya/',
		'services/practica/otorinolaringolog'          => '/otorinolaringologiya/',
		'services/practica/pulmonolog'                 => '/pulmonologiya/',
		'services/practica/serdechno-sosudistyj-hirurg' => '/hirurgiya-endokrinologiya/',
		'services/practica/terapevt'                   => '/terapiya/',
		'services/practica/venerolog'                  => '/urologiya/',
	];
}

add_action( 'template_redirect', function () {
	$path = trim( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );
	$map  = harmony_redirect_map();

	foreach ( $map as $old => $new ) {
		if ( strcasecmp( $path, $old ) === 0 ) {
			wp_redirect( home_url( $new ), 301 );
			exit;
		}
	}
}, 1 );
