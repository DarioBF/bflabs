<?php

/*
Plugin Name: BF reCAPTCHA Register Form
Plugin URI: https://www.dariobf.com
Description: Añade un reCaptcha de Google al formulario de registro
Version: 1.0.0
Author: DarioBF
Author URI: https://www.dariobf.com
Text Domain: bf-recaptcha
License: GPL2
*/

/* Creating the options page */
// create custom plugin settings menu
add_action('admin_menu', 'bf_recaptcha_create_menu');

function bf_recaptcha_create_menu() {

	add_options_page( "BF reCaptcha", 'BF reCaptcha', 'administrator', 'bf-recaptcha-settings', 'bf_recaptcha_settings_page');

	//call register settings function
	add_action( 'admin_init', 'bf_recaptcha_register_settings' );
}


function bf_recaptcha_register_settings() {
	//register our settings
	register_setting( 'bfr-settings-group', 'public_key' );
	register_setting( 'bfr-settings-group', 'private_key' );
}

function bf_recaptcha_settings_page() {
?>
<div class="wrap">
<h1>BF reCaptcha settings</h1>
<p><?php _e( 'Puedes conseguir tus claves API en <a href="https://www.google.com/recaptcha">Google reCaptcha</a>', 'bf_recaptcha' ); ?></p>

<form method="post" action="options.php">
    <?php settings_fields( 'bfr-settings-group' ); ?>
    <?php do_settings_sections( 'bfr-settings-group' ); ?>
    <table class="form-table">
        <tr valign="top">
        <th scope="row"><?php _e( 'Clave del sitio', 'bf_recaptcha' ); ?></th>
        <td><input type="text" name="public_key" value="<?php echo esc_attr( get_option('public_key') ); ?>" /></td>
        </tr>
         
        <tr valign="top">
        <th scope="row"><?php _e( 'Clave secreta', 'bf_recaptcha' ); ?></th>
        <td><input type="text" name="private_key" value="<?php echo esc_attr( get_option('private_key') ); ?>" /></td>
        </tr>
    </table>
    
    <?php submit_button(); ?>

</form>
</div>
<?php }

/**
 * Adds first and last name to the registration field
 */
function bf_recaptcha_register_fields () {
	$public_key = get_option('public_key');
    ?>
    <div class="g-recaptcha" data-sitekey="<?php print $public_key; ?>"></div>
    <script src='https://www.google.com/recaptcha/api.js'></script>

<?php
}
if( !empty(get_option( 'public_key' )) && !empty(get_option( 'private_key' )) ) 
	add_action( 'register_form', 'bf_recaptcha_register_fields', 20 );

/**
 * Require first and last name
 * @param $errors
 * @return mixed
 */
function bf_recaptcha_register_fields_validation ( $errors ) {
	$private_key = get_option('private_key');

    // Recaptcha check
    if ( isset( $_POST['wp-submit'] ) && empty( $_POST['g-recaptcha-response'] ) ) {
        $errors->add( 'recpatcha_error', '<strong>ERROR</strong>: Rellena el captcha, por favor' );
    } else if ( isset( $_POST['wp-submit'] ) ) {

        $response = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', array(
            'body' => array(
                'secret'   => $private_key,
                'response' => $_POST['g-recaptcha-response'],
                'remoteip' => $_SERVER['REMOTE_ADDR']
            )
        ) );

        $response_body = json_decode( $response['body'] );

        if ( empty( $response_body->success ) || ! $response_body->success ) {
            $errors->add( 'recpatcha_error', '<strong>ERROR</strong>: Has fallado el captcha, ¡repítelo!' );
        }
    }
    return $errors;
}

add_filter( 'registration_errors', 'bf_recaptcha_register_fields_validation', 20 );

/* Some custom styles for login page */
function bf_captcha_styles() { ?>
    <style type="text/css">
        body #login {
			width:350px;
        }
    </style>
<?php }
add_action( 'login_enqueue_scripts', 'bf_captcha_styles', 9999 );

?>