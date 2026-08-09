<?php
/**
 * AJAX-приём формы заявки («Записаться на приём» / «Вызвать врача»)
 * и формы отзыва о враче.
 */

add_action( 'wp_ajax_harmony_submit_appointment', 'harmony_submit_appointment' );
add_action( 'wp_ajax_nopriv_harmony_submit_appointment', 'harmony_submit_appointment' );

function harmony_submit_appointment() {
	check_ajax_referer( 'harmony_forms', 'nonce' );

	$name  = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$phone = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
	$type  = isset( $_POST['request_type'] ) ? sanitize_text_field( wp_unslash( $_POST['request_type'] ) ) : 'Заявка с сайта';
	$doctor_id = isset( $_POST['doctor_id'] ) ? absint( $_POST['doctor_id'] ) : 0;

	if ( '' === $name || '' === $phone ) {
		wp_send_json_error( [ 'message' => 'Заполните имя и телефон.' ] );
	}

	$doctor_title = $doctor_id && get_post_type( $doctor_id ) === 'doctor' ? get_the_title( $doctor_id ) : 'любой врач';

	$post_id = wp_insert_post( [
		'post_type'   => 'appointment_request',
		'post_title'  => sprintf( '%s — %s', $name, date_i18n( 'd.m.Y H:i' ) ),
		'post_status' => 'publish',
	], true );

	if ( is_wp_error( $post_id ) ) {
		wp_send_json_error( [ 'message' => 'Не удалось сохранить заявку, попробуйте ещё раз.' ] );
	}

	update_field( 'name', $name, $post_id );
	update_field( 'phone', $phone, $post_id );
	update_field( 'request_type', $type, $post_id );
	update_field( 'is_processed', false, $post_id );
	if ( $doctor_id && get_post_type( $doctor_id ) === 'doctor' ) {
		update_field( 'doctor', $doctor_id, $post_id );
	}

	$admin_email = get_option( 'admin_email' );
	$subject     = sprintf( '[%s] Новая заявка: %s', get_bloginfo( 'name' ), $type );
	$message     = "Тип: {$type}\nИмя: {$name}\nТелефон: {$phone}\nВрач: {$doctor_title}\n\nАдминка: " . admin_url( 'edit.php?post_type=appointment_request' );
	wp_mail( $admin_email, $subject, $message );

	wp_send_json_success( [ 'message' => 'Спасибо! Мы свяжемся с вами в ближайшее время.' ] );
}

add_action( 'wp_ajax_harmony_submit_review', 'harmony_submit_review' );
add_action( 'wp_ajax_nopriv_harmony_submit_review', 'harmony_submit_review' );

function harmony_submit_review() {
	check_ajax_referer( 'harmony_forms', 'nonce' );

	$author = isset( $_POST['author_name'] ) ? sanitize_text_field( wp_unslash( $_POST['author_name'] ) ) : '';
	$text   = isset( $_POST['review_text'] ) ? sanitize_textarea_field( wp_unslash( $_POST['review_text'] ) ) : '';
	$rating = isset( $_POST['rating'] ) ? (float) $_POST['rating'] : 0;
	$doctor_id = isset( $_POST['doctor_id'] ) ? absint( $_POST['doctor_id'] ) : 0;

	if ( '' === $author || '' === $text || $rating < 1 || $rating > 5 || ! $doctor_id || get_post_type( $doctor_id ) !== 'doctor' ) {
		wp_send_json_error( [ 'message' => 'Заполните имя, оценку и текст отзыва.' ] );
	}

	$post_id = wp_insert_post( [
		'post_type'   => 'review',
		'post_title'  => sprintf( '%s — %s', $author, date_i18n( 'd.m.Y' ) ),
		'post_status' => 'pending', // Модерация: появится на сайте только после публикации в админке.
	], true );

	if ( is_wp_error( $post_id ) ) {
		wp_send_json_error( [ 'message' => 'Не удалось сохранить отзыв, попробуйте ещё раз.' ] );
	}

	update_field( 'author_name', $author, $post_id );
	update_field( 'rating', $rating, $post_id );
	update_field( 'review_date', date_i18n( 'Ymd' ), $post_id );
	update_field( 'review_text', $text, $post_id );
	update_field( 'doctor', $doctor_id, $post_id );

	wp_send_json_success( [ 'message' => 'Спасибо! Ваш отзыв отправлен на модерацию.' ] );
}
