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
	if( function_exists('wpcf7_add_shortcode') ) {
		wpcf7_add_shortcode( array( 'mailpoetsignup', 'mailpoetsignup*' ),
			'wpcf7_mailpoetsignup_shortcode_handler', true );
	}
}

function wpcf7_mailpoetsignup_shortcode_handler( $tag ) {
	$tag = new WPCF7_Shortcode( $tag );

	if ( empty( $tag->name ) ) {
		return '';
	}

	$validation_error = wpcf7_get_validation_error( $tag->name );

	$class = wpcf7_form_controls_class( $tag->type );

	if ( $validation_error ) {
		$class .= ' wpcf7-not-valid';
	}

	$atts = array();

	$atts['class'] = $tag->get_class_option( $class );
	$atts['id'] = $tag->get_option( 'id', 'id', true );

	// get checkbox value
	// first get all of the lists
	$lists = wpcf7_mailpoetsignup_get_lists();
	if ( ! empty ( $lists ) ) {
		$checkbox_values = array();
		foreach ( $lists as $key => $l ) {
			// check if that list was added to the form
			if ( $tag->has_option( 'mailpoet_list_' . $l['list_id'] ) ) {
				// add the list id into the array of checkbox values
				$checkbox_values[] = $l['list_id'];
			}
		}
	}

	// we still want a value for the checkbox so *some* data gets posted
	if ( ! empty ( $checkbox_values ) ) {
		// now implode them all into a comma separated string
		$atts['value'] = implode( $checkbox_values, "," );
	} else {
		// set a 0 so we know to add the user to Mailpoet but not to any specific list
		$atts['value'] = "0";
	}


	if ( $tag->is_required() ) {
		$atts['aria-required'] = 'true';
	}

	// set default checked state
	$atts['checked'] = "";
	if ( $tag->has_option( 'default:on' ) ) {
		$atts['checked'] = 'checked';
	}

	$value = (string) reset( $tag->values );

	if ( '' !== $tag->content ) {
		$value = $tag->content;
	}

	if ( wpcf7_is_posted() && isset( $_POST[$tag->name] ) ) {
		$value = stripslashes_deep( $_POST[$tag->name] );
	}

	$atts['name'] = $tag->name;
	$id = $atts['id'] = $atts['name'];

	$atts = wpcf7_format_atts( $atts );

	// get the content from the tag to make the checkbox label
	$label = __( 'Sign up for the newsletter', 'mpcf7' );
	$values = $tag->values;
	if( isset( $values ) && !empty ($values) ){
		$label = esc_textarea( $values[0] );
	}

	$html = sprintf(
		'<span class="wpcf7-form-control-wrap %1$s"><input type="checkbox" %2$s />&nbsp;</span><label for="%3$s">%4$s</label>&nbsp;%5$s',
		$tag->name, $atts, $id, $value, $validation_error );

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
	// if ( ! function_exists( 'wpcf7_add_tag_generator' ) ) {
	// 	return;
	// }

	// wpcf7_add_tag_generator( 'mailpoetsignup', __( 'Mailpoet Signup', 'mpcf7' ),
	// 	'wpcf7-tg-pane-mailpoetsignup', 'wpcf7_tg_pane_mailpoetsignup' );

	$tag_generator = WPCF7_TagGenerator::get_instance();
	$tag_generator->add( 'mailpoetsignup', __( 'Mailpoet Signup', 'mpcf7' ),
		'wpcf7_tg_pane_mailpoetsignup' );
}


function wpcf7_tg_pane_mailpoetsignup( $contact_form, $args = '' ) {

	$args = wp_parse_args( $args, array() );
	$type = 'mailpoetsignup';

	$description = __( "Mailpoet Signup Form.", 'mpcf7' );

	$desc_link = ''; //wpcf7_link( __( 'http://contactform7.com/text-fields/', 'contact-form-7' ), __( 'Text Fields', 'contact-form-7' ) );


	?>

<div class="control-box">
<fieldset>
<legend><?php echo esc_html( $description ); ?></legend>

<table class="form-table">
<tbody>
	<tr>
	<th scope="row"><?php echo esc_html( __( 'Field type', 'mpcf7' ) ); ?></th>
	<td>
		<fieldset>
		<legend class="screen-reader-text"><?php echo esc_html( __( 'Field type', 'contact-form-7' ) ); ?></legend>
		<label><input type="checkbox" name="required" /> <?php echo esc_html( __( 'Required field', 'contact-form-7' ) ); ?></label>
		</fieldset>
	</td>
	</tr>

	<tr>
	<th scope="row"><?php echo esc_html( __( 'MailPoet Lists', 'contact-form-7' ) ); ?></th>
	<td>					
		<?php
		// print mailpoet lists
		echo wpcf7_mailpoetsignup_get_list_inputs();
		?>
	</td>
	</tr>

	<tr>
	<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-default:on' ); ?>"><?php echo esc_html( __( 'Checked by Default', 'mpcf7' ) ); ?></label></th>
	<td><input type="checkbox" name="default:on" class="option" />&nbsp;<?php echo esc_html( __( "Make this checkbox checked by default?", 'contact-form-7' ) ); ?></td>
	</tr>

	<tr>
	<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-values' ); ?>"><?php echo esc_html( __( 'Checkbox Label', 'mpcf7' ) ); ?></label></th>
	<td><input type="text" name="values" class="oneline" id="<?php echo esc_attr( $args['content'] . '-values' ); ?>" /><br />
	</tr>

	<tr>
	<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-name' ); ?>"><?php echo esc_html( __( 'Name', 'contact-form-7' ) ); ?></label></th>
	<td><input type="text" name="name" class="tg-name oneline" id="<?php echo esc_attr( $args['content'] . '-name' ); ?>" /></td>
	</tr>

	<tr>
	<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-id' ); ?>"><?php echo esc_html( __( 'Id attribute', 'contact-form-7' ) ); ?></label></th>
	<td><input type="text" name="id" class="idvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-id' ); ?>" /></td>
	</tr>

	<tr>
	<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-class' ); ?>"><?php echo esc_html( __( 'Class attribute', 'contact-form-7' ) ); ?></label></th>
	<td><input type="text" name="class" class="classvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-class' ); ?>" /></td>
	</tr>

</tbody>
</table>
</fieldset>
</div>

<div class="insert-box">
	<input type="text" name="<?php echo $type; ?>" class="tag code" readonly="readonly" onfocus="this.select()" />

	<div class="submitbox">
	<input type="button" class="button button-primary insert-tag" value="<?php echo esc_attr( __( 'Insert Tag', 'contact-form-7' ) ); ?>" />
	</div>

	<br class="clear" />

	<p class="description mail-tag"><label for="<?php echo esc_attr( $args['content'] . '-mailtag' ); ?>"><?php echo sprintf( esc_html( __( "To use the value input through this field in a mail field, you need to insert the corresponding mail-tag (%s) into the field on the Mail tab.", 'contact-form-7' ) ), '<strong><span class="mail-tag"></span></strong>' ); ?><input type="text" class="mail-tag code hidden" readonly="readonly" id="<?php echo esc_attr( $args['content'] . '-mailtag' ); ?>" /></label></p>
</div>

	<?php
}

/* Process the form field */

add_action( 'wpcf7_before_send_mail', 'wpcf7_mailpoet_before_send_mail' );

function wpcf7_mailpoet_before_send_mail( $contactform ) {
	// make sure the user has Mailpoet (Wysija) installed & active
	if ( ! ( class_exists( 'WYSIJA' ) ) ) {
		return;
	}

	if (! empty( $contactform->skip_mail )) {
		return;
	}

	$posted_data = null;
	if ( class_exists( 'WPCF7_Submission' ) ) {// for Contact-Form-7 3.9 and above, http://contactform7.com/2014/07/02/contact-form-7-39-beta/
		$submission = WPCF7_Submission::get_instance();
		if ( $submission ) {
			$posted_data = $submission->get_posted_data();
		}
	} elseif ( ! empty( $contactform->posted_data ) ) {// for Contact-Form-7 older than 3.9
		$posted_data = $contactform->posted_data;
	}

	// and make sure they have something in their contact form
	if ( empty($posted_data)) {
		return;
	}

	wpcf7_mailpoet_subscribe_to_lists( $posted_data );
}

function wpcf7_mailpoet_subscribe_to_lists($posted_data) {
	// set defaults for mailpoet user data
	$user_data = array(
		'email' => "",
		'firstname' => "",
		'lastname' => ""
	);

	// get form data
	$user_data['email'] = isset( $posted_data['your-email'] ) ? trim( $posted_data['your-email'] ) : '';
	$user_data['firstname'] = isset( $posted_data['your-name'] ) ? trim( $posted_data['your-name'] ) : '';
	if ( isset( $posted_data['your-first-name'] ) && !empty( $posted_data['your-first-name'] ) ) {
		$user_data['firstname'] = trim( $posted_data['your-first-name'] );
	}
	if ( isset( $posted_data['your-last-name'] ) && !empty( $posted_data['your-last-name'] ) ) {
		$user_data['lastname'] = trim( $posted_data['your-last-name'] );
	}

        // find all of the keys in $posted_data that belong to mailpoet-cf7's plugin
        $keys = array_keys($posted_data);
        $mailpoet_signups = preg_grep("/^mailpoetsignup.*/", $keys);
        $mailpoet_lists = array();
	if ( ! empty( $mailpoet_signups ) ) {
                foreach($mailpoet_signups as $mailpoet_signup_field){
                    $mailpoet_lists = array_unique( array_merge( $mailpoet_lists, explode( ",", $posted_data[$mailpoet_signup_field] ) ) );
                }

	} else {
		// FYI an empty array is there just to add the user to MailPoet but not to any specific list
		$mailpoet_lists = array();
	}

	// configure the list
	$data = array(
		'user' => $user_data,
		'user_list' => array( 'list_ids' => $mailpoet_lists )
	);

	// if akismet is set make sure it's valid
	$akismet = isset( $contactform->akismet ) ? (array) $contactform->akismet : null;
	$akismet = $akismet;// temporarily, not in use!

	// add the subscriber to the Wysija list
	$user_helper = WYSIJA::get('user','helper');
	$user_helper->addSubscriber( $data );
}


/**
 * Create a formatted list of input's for the CF7 tag generator
 *
 * @return string
 */
function wpcf7_mailpoetsignup_get_list_inputs ( ) {

	$html = '';

	// get lists
	$lists = wpcf7_mailpoetsignup_get_lists();

	if ( is_array( $lists ) && ! empty( $lists ) ) {
		foreach ( $lists as $key => $l ) {
			// add input to returned html
			$input_name = wpcf7_mailpoetsignup_get_list_input_field_name();
			$input = "<input type='checkbox' name='" . $input_name . "' class='option' />%s<br />";
			$html .= sprintf( $input, $l['list_id'], $l['name'] );
		}
	}

	return $html;
}


/**
 * Create input name for both the form processing & tag generation
 *
 * @return string
 */
function wpcf7_mailpoetsignup_get_list_input_field_name ( ) {
	return 'mailpoet_list_%d';
}


// that's all folks!