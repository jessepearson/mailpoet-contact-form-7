<?php
/*
 * MailPoet - Contact Form 7 Integration
 *
 * Add the mailpoet cf7 shortcode
 *
 * @author Patrick Rauland
 * @since 1.0.0
 */


add_action( 'init', 'wpcf7_add_shortcode_mailpoetsignup', 5 );

function wpcf7_add_shortcode_mailpoetsignup() {
	wpcf7_add_shortcode( array( 'mailpoetsignup', 'mailpoetsignup*' ),
		'wpcf7_mailpoetsignup_shortcode_handler', true );
}

function wpcf7_mailpoetsignup_shortcode_handler( $tag ) {
	$tag = new WPCF7_Shortcode( $tag );

	if ( empty( $tag->name ) )
		return '';

	$validation_error = wpcf7_get_validation_error( $tag->name );

	$class = wpcf7_form_controls_class( $tag->type );

	if ( $validation_error )
		$class .= ' wpcf7-not-valid';

	$atts = array();

	$atts['class'] = $tag->get_class_option( $class );
	$atts['id'] = $tag->get_option( 'id', 'id', true );
	$atts['value'] = $tag->get_option( 'mailpoet_list', 'int', true );

	if ( $tag->has_option( 'readonly' ) )
		$atts['readonly'] = 'readonly';

	if ( $tag->is_required() )
		$atts['aria-required'] = 'true';

	$value = (string) reset( $tag->values );

	if ( '' !== $tag->content )
		$value = $tag->content;

	if ( wpcf7_is_posted() && isset( $_POST[$tag->name] ) )
		$value = stripslashes_deep( $_POST[$tag->name] );

	$atts['name'] = $tag->name;
	$atts['id'] = $atts['name'];

	$atts = wpcf7_format_atts( $atts );

	// get the content from the tag to make the checkbox label
	$label = __( 'Sign up for the newsletter', 'mpcf7' );
	$values = $tag->values;
	if( isset( $values ) && !empty ($values) ){
		$label = $values[0];
	}

	$html = sprintf(
		'<span class="wpcf7-form-control-wrap %1$s"><label for="%2$s">%3$s</label><input type="checkbox" %4$s />%5$s</span>',
		$tag->name, $tag->name, esc_textarea( $value ), $atts, $validation_error );

	return $html;
}


/* Validation filter */

add_filter( 'wpcf7_validate_mailpoetsignup', 'wpcf7_mailpoetsignup_validation_filter', 10, 2 );
add_filter( 'wpcf7_validate_mailpoetsignup*', 'wpcf7_mailpoetsignup_validation_filter', 10, 2 );

function wpcf7_mailpoetsignup_validation_filter( $result, $tag ) {
	$tag = new WPCF7_Shortcode( $tag );

	$type = $tag->type;
	$name = $tag->name;

	$value = isset( $_POST[$name] ) ? (string) $_POST[$name] : '';

	if ( 'mailpoetsignup*' == $type ) {
		if ( '' == $value ) {
			$result['valid'] = false;
			$result['reason'][$name] = wpcf7_get_message( 'invalid_required' );
		}
	}

	return $result;
}


/* Tag generator */

add_action( 'admin_init', 'wpcf7_add_tag_generator_mailpoetsignup', 20 );

function wpcf7_add_tag_generator_mailpoetsignup() {
	if ( ! function_exists( 'wpcf7_add_tag_generator' ) )
		return;

	wpcf7_add_tag_generator( 'mailpoetsignup', __( 'Mailpoet Signup', 'mpcf7' ),
		'wpcf7-tg-pane-mailpoetsignup', 'wpcf7_tg_pane_mailpoetsignup' );
}

function wpcf7_tg_pane_mailpoetsignup( &$contact_form ) {
	?>
	<div id="wpcf7-tg-pane-mailpoetsignup" class="hidden">
	<form action="">
		<table>
			<tr>
				<td>
					<input type="checkbox" name="required" />&nbsp;<?php echo esc_html( __( 'Required field?', 'wpcf7' ) ); ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo esc_html( __( 'Name', 'wpcf7' ) ); ?><br /><input type="text" name="name" class="tg-name oneline" />
				</td>
				<td></td>
			</tr>
		</table>

		<table>
			<tr>
				<td>
					<code>mailpoet list</code> (<?php echo esc_html( __( 'required', 'wpcf7' ) ); ?>)<br />
					<input type="text" name="mailpoet_list" class="mailpostlistvalue oneline option" />
				</td>
				<td>
					<code>checkbox label</code> <br />
					<textarea name="values"></textarea>
				</td>
			</tr>
			<tr>
				<td><code>id</code> (<?php echo esc_html( __( 'optional', 'wpcf7' ) ); ?>)<br />
					<input type="text" name="id" class="idvalue oneline option" />
				</td>
				<td><code>class</code> (<?php echo esc_html( __( 'optional', 'wpcf7' ) ); ?>)<br />
					<input type="text" name="class" class="classvalue oneline option" />
				</td>
			</tr>
			
		</table>

		<div class="tg-tag"><?php echo esc_html( __( "Copy this code and paste it into the form left.", 'wpcf7' ) ); ?><br /><input type="text" name="mailpoetsignup" class="tag" readonly="readonly" onfocus="this.select()" /></div>

		<div class="tg-mail-tag"><?php echo esc_html( __( "And, put this code into the Mail fields below.", 'wpcf7' ) ); ?><br /><span class="arrow">&#11015;</span>&nbsp;<input type="text" class="mail-tag" readonly="readonly" onfocus="this.select()" /></div>
	</form>
	</div>
	<?php
}

/* Process the form field */

add_action( 'wpcf7_before_send_mail', 'wpcf7_mailpoet_before_send_mail' );

function wpcf7_mailpoet_before_send_mail( $contactform ) {
	// make sure the user has Mailpoet (Wysija) installed & active
	if ( ! ( class_exists( 'WYSIJA' ) ) )
		return;

	// and make sure they have something in their contact form
	if ( empty( $contactform->posted_data ) || ! empty( $contactform->skip_mail ) )
		return;

	// set defaults for mailpoet user data
	$user_data = array(
		'email' => "",
		'firstname' => "",
		'lastname' => ""
	);

	// get form data
	$posted_data = $contactform->posted_data;
	$user_data['email'] = isset( $posted_data['your-email'] ) ? trim( $posted_data['your-email'] ) : '';
	$user_data['firstname'] = isset( $posted_data['your-name'] ) ? trim( $posted_data['your-name'] ) : '';
	if( isset( $posted_data['your-first-name'] ) && !empty( $posted_data['your-first-name'] ) ){
		$user_data['firstname'] = trim( $posted_data['your-first-name'] );
	}
	if( isset( $posted_data['your-last-name'] ) && !empty( $posted_data['your-last-name'] ) ){
		$user_data['lastname'] = trim( $posted_data['your-last-name'] );
	}
	if( isset( $posted_data['mailpoet-list'] ) ){
		$mailpoet_list = trim( $posted_data['mailpoet-list'] );
	}
	
	 // make sure we have a legitimate list
	if( ( ! isset($mailpoet_list) ) || ( ! is_numeric( $mailpoet_list ) ) )
		return;

	// configure the list
	$data = array(
		'user' => $user_data,
		'user_list' => array( 'list_ids' => array( $mailpoet_list ) )
	);

	// if akismet is set make sure it's valid
	$akismet = isset( $contactform->akismet ) ? (array) $contactform->akismet : null;

	// add the subscriber to the Wysija list
	$userHelper=&WYSIJA::get('user','helper');
	$userHelper->addSubscriber($data);

}

// that's all folks!