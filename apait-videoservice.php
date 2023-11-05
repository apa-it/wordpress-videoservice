<?php
/**
 * Plugin Name: APA-IT VideoService
 * Plugin URI: https://github.com/apa-it/wordpress-videoservice
 * Description: Einfaches Plugin für das APA IT VideoService
 * Version: 1.0.0
 * Requires at least: 4.4
 * Requires PHP: 7.4
 * Author: APA-IT
 * Author URI: https://www.apa-it.at
 * License: GPL v2 or later
 */

if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

/**
 * Wird augerufen, wenn in einem Block [uvp...] verwendet wird.
 * Folgende Parameter sind aktuell moeglich:
 *   guid: die eindeutige ID des Videos oder Livestreams. Zu finden entweder per Notification oder Portal.
 *   secure (optional, default: false): Wenn dieser Parameter auf true gesetzt wird, wird ein Parameter mit einem Secure Token
 *                                      dem Aufruf hinzugefuegt. Dazu werden die IP Adresse des Aufrufers, und die Guid genommen,
 *                                      und mit dem Ablaufdatum (aktuelle Zeit plus ein Tag) und eine eindeutige ID genommen, und
 *                                      mit dem per Setting eingetragenen Secure Token als JWT erstellt.
 *   width: Die Breite, die das Video auf der Oberfläche haben soll, default ist 640
 *   height: Die Höhe, die das Video auf der Oberfläche haben soll, default ist 380
 */
function vs_uvp_call( $atts ): string {
	extract( shortcode_atts( array(
		"height" => '380',
		"width"  => '640',
		"guid"   => '',
		"secure" => false,
	), $atts ) );

	$uvpdomain = esc_attr( get_option( 'vs_uvp_domain' ) );

	$retval = "Error";

	if ( $guid != '' ) {
		$htmliframe = 'https://' . $uvpdomain . '/embed/' . $guid;

		if ( $secure ) {
			$token = esc_attr( get_option( 'vs_uvp_token' ) );
			$date  = date_create();
			date_add( $date, date_interval_create_from_date_string( '1 day' ) );

			$header             = json_encode( [ 'typ' => 'JWT', 'alg' => 'HS256' ] );
			$payload            = json_encode( [
				'ip'   => $_SERVER['REMOTE_ADDR'],
				'guid' => $guid,
				'jti'  => vs_guid(),
				'exp'  => date_timestamp_get( $date )
			] );
			$base64UrlHeader    = str_replace( [ '+', '/', '=' ], [ '-', '_', '' ], base64_encode( $header ) );
			$base64UrlPayload   = str_replace( [ '+', '/', '=' ], [ '-', '_', '' ], base64_encode( $payload ) );
			$signature          = hash_hmac( 'sha256', $base64UrlHeader . "." . $base64UrlPayload, $token, true );
			$base64UrlSignature = str_replace( [ '+', '/', '=' ], [ '-', '_', '' ], base64_encode( $signature ) );
			$jwt                = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

			$htmliframe .= '?SecureToken=' . $jwt;
		}

		$retval = '<iframe width="' . $width . '" height="' . $height . '" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" allowfullscreen src="' . $htmliframe . '"></iframe>';

	}

	return $retval;
}

/**
 * Wird verwendet, um im Portal einen Hinweis anzuzeigen,
 * wenn in den Einstellungen die Kundendomain noch nicht erfasst wurde.
 *
 * @return void
 */
function vs_admin_notice() {
	if ( esc_attr( get_option( 'vs_uvp_domain' ) ) == "" ) {
		echo "<div class=\"updated notice notice-success\"><p>Um den APA-IT VideoService uvp verwenden zu k&ouml;nnen, m&uuml;ssen Sie in den Einstellungen die Ihre Domain erfassen.</p></div>";
	}
}

/**
 * Erstellt den Menüeintrag für die Einstellungsseite
 *
 * @return void
 */
function vs_create_menu() {
	add_options_page( 'APA-IT VideoService Einstellungen', 'APA-IT VideoService', 'manage_options', __FILE__, 'vs_settings_page' );
}

/**
 * Registriert die Einstellungen im Wordpress System
 *
 * @return void
 */
function vs_register_settings() {
	register_setting( 'vs-settings-group', 'vs_uvp_domain' );
	register_setting( 'vs-settings-group', 'vs_uvp_token' );
}

/**
 * Die eigentliche Settingsseite
 *
 * @return void
 */
function vs_settings_page() {
	?>
    <div class="wrap">
        <h1>APA-IT VideoService</h1>

        <form method="post" action="options.php">
            <?php settings_fields( 'vs-settings-group' ); ?>
            <?php do_settings_sections( 'vs-settings-group' ); ?>
            <h2>uvp Einstellungen</h2>
            <p class="Description">Der uvp (universal video player) ist der Videoplayer des APA-IT VideoService.</p>

            <table class="form-table" style="width:700px !important">
                <tr>
                    <th scope="row">uvp Domain</th>
                    <td>
                        <input class="regular-text code" type="text" name="vs_uvp_domain"
                               value="<?php echo esc_attr( get_option( 'vs_uvp_domain' ) ); ?>"/>
                        <p class="description">
                            Um Videos per [uvp... Tag anzeigen zu k&ouml;nnenm m&uuml;ssen Sie als
                            Kunde des APA-IT VideoService die Ihnen bekannt gegebene Domain eingeben.
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">uvp Secret Token</th>
                    <td>
                        <input class="regular-text code" type="password" name="vs_uvp_token"
                               value="<?php echo esc_attr( get_option( 'vs_uvp_token' ) ); ?>"/>
                        <p class="description">Um gesch&uuml;tzte Streams verwenden zu k&ouml;nnen, m&uuml;ssen Sie hier
                            den Ihnen bekannt gegebenen Secure Token eingeben.</p>
                    </td>
                </tr>

            </table>

            <?php submit_button(); ?>

        </form>
    </div>
	<?php
}


/**
 * Liefert eine Guid, die fuer den aktuellen Request als Unique Identifier verwendet wird.
 * Damit kann man sicherstellen, dass ein Request nur einmal gueltig ist.
 *
 * @return string
 */
function vs_guid(): string {
	if ( function_exists( 'com_create_guid' ) === true ) {
		return trim( com_create_guid(), '{}' );
	}

	$data    = openssl_random_pseudo_bytes( 16 );
	$data[6] = chr( ord( $data[6] ) & 0x0f | 0x40 );
	$data[8] = chr( ord( $data[8] ) & 0x3f | 0x80 );

	return vsprintf( '%s%s-%s-%s-%s-%s%s%s', str_split( bin2hex( $data ), 4 ) );
}


// Registrierung der eigentlichen Callbacks im Wordpress

add_action( 'wp_enqueue_scripts', 'vs_init' );
add_action( 'admin_notices', 'vs_admin_notice' );
add_shortcode( 'uvp', 'vs_uvp_call' );
add_action( 'admin_menu', 'vs_create_menu' );
add_action( 'admin_init', 'vs_register_settings' );
