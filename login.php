<?php

class Ncr_Login_Captcha extends Ncr_No_Captcha_Recaptcha {

	public static function initialize() {

		// initialize if login is activated
		if ( self::$captcha_login == 'yes' ) {

			if( self::$captcha_login_after_n_failures > 0 ) {
				add_filter( 'authenticate', array( __CLASS__, 'log_errors' ), 100 );
			}

			if( self::$login_failures >= self::$captcha_login_after_n_failures ) {

				// adds the captcha to the login form
				add_action( 'login_form', array( __CLASS__, 'display_captcha' ) );

				// authenticate the captcha answer
				add_action( 'wp_authenticate_user', array( __CLASS__, 'validate_captcha' ), 10, 2 );

			}
		}
	}

	public static function log_errors( $user ){
		if ( is_wp_error( $user ) ) {
			// log the error in the database
			self::$plugin_options['login_failures'] = self::$login_failures + 1;
		} else {
			self::$plugin_options['login_failures'] = 0;
		}
		update_option( 'ncr_options', self::$plugin_options );

		return $user;
	}

	/**
	 * Verify the captcha answer
	 *
	 * @param $user string login username
	 * @param $password string login password
	 *
	 * @return WP_Error|WP_user
	 */
	public static function validate_captcha( $user, $password ) {

		if ( ! isset( $_POST['g-recaptcha-response'] ) || ! self::captcha_verification() ) {
			return new WP_Error( 'empty_captcha', self::$error_message );
		}

		return $user;
	}
}
