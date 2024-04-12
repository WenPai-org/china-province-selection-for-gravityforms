<?php

defined( 'ABSPATH' ) || die();

GFForms::include_payment_addon_framework();

/**
 * Gravity Forms Gravity Forms PayPal Checkout Add-On.
 *
 * @since     1.0
 * @package   GravityForms
 * @author    2
 * @copyright Copyright (c) 2019, 2
 */
class WP_POST_FIELD extends GFAddOn {

	/**
	 * Contains an instance of this class, if available.
	 *
	 * @since  1.0
	 * @var    WP_POST_FIELD $_instance If available, contains an instance of this class
	 */
	private static $_instance = null;

	/**
	 * Defines the plugin slug.
	 *
	 * @since  1.0
	 * @var    string $_slug The slug used for this plugin.
	 */
	protected $_slug = 'gravityformswpfield';


	public function init() {
		parent::init();
		add_action( 'gform_enqueue_scripts', array( $this, 'enqueue_front_end_scripts' ), 10, 2 );
		add_action( 'gform_pre_validation', array( $this, 'pre_render' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'custom_gf_placeholder_enqueue_script' ) );
	}

	public function custom_gf_placeholder_enqueue_script() {
		// 只在 Gravity Forms 编辑页面加载脚本
		$screen = get_current_screen();
		if ( $screen->id == 'toplevel_page_gf_edit_forms' ) {
			wp_enqueue_script( 'custom-wp-post-form-edit-script', plugins_url( '/includes/js/wpp_form_edit.js', __FILE__ ), array( 'jquery' ), time() . '' );
		}
	}

	/**
	 * If necessary configure the select placeholder and shuffle the fields before the form is displayed.
	 *
	 * @param array $form The form currently being processed for display.
	 *
	 * @return array
	 */
	public function pre_render( $form ) {
		$wppost_fields = GFAPI::get_fields_by_type( $form, array(
			'wppfield_phone',
			'wppfield_idcard',
		) );
		if ( empty( $wppost_fields ) ) {
			return $form;
		}

		foreach ( $wppost_fields as $wppost_field ) {
			$value = rgpost( 'input_' . $wppost_field->id );
			if ( $wppost_field->type == 'wppfield_phone' ) {
				if ( $value && ! preg_match( '/^1[3456789]\d{9}$/', $value ) ) {
					$wppost_field->failed_validation  = true;
					$wppost_field->validation_message = '请输入有效的中国手机号码';
				}
			}

			if ( $wppost_field->type == 'wppfield_idcard' ) {
				if ( ! $value ) {
					continue;
				}
				$len         = strlen( $value );
				$is_error_fn = function ( &$wppost_field ) {
					if ( $wppost_field->failed_validation ) {
						return;
					}
					$wppost_field->failed_validation  = true;
					$wppost_field->validation_message = '请输入有效的身份证号码';
				};

				if ( $len != 15 && $len != 18 ) {
					$is_error_fn( $wppost_field );
				}

				// 根据长度选择正则表达式
				if ( $len == 18 ) {
					$regex = "/^[1-9]\d{5}(19|20)\d{2}(0[1-9]|1[0-2])(0[1-9]|[12]\d|3[01])\d{3}[\dXx]$/";
				} else {
					$regex = "/^[1-9]\d{5}\d{2}(0[1-9]|1[0-2])(0[1-9]|[12]\d|3[01])\d{3}$/";

					// 正则表达式校验基本格式
					if ( ! preg_match( $regex, $value ) ) {
						$is_error_fn( $wppost_field );
					}

					// 提取出生年月日
					if ( $len == 18 ) {
						$year  = (int) ( substr( $value, 6, 4 ) );
						$month = (int) ( substr( $value, 10, 2 ) );
						$day   = (int) ( substr( $value, 12, 2 ) );
					} else {
						$year  = (int) ( '19' . substr( $value, 6, 2 ) );
						$month = (int) ( substr( $value, 8, 2 ) );
						$day   = (int) ( substr( $value, 10, 2 ) );
					}

					// 校验日期
					if ( ! checkdate( $month, $day, $year ) ) {
						$is_error_fn( $wppost_field );
					}

					// 仅对18位身份证进行校验码校验
					if ( $len == 18 ) {
						$factor = [ 7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2 ];
						$sum    = 0;
						for ( $i = 0; $i < 17; $i ++ ) {
							$sum += substr( $value, $i, 1 ) * $factor[ $i ];
						}
						$mod       = $sum % 11;
						$checkCode = [ '1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2' ];
						$checkNum  = $checkCode[ $mod ];
						if ( strtoupper( substr( $value, 17, 1 ) ) != $checkNum ) {
							$is_error_fn( $wppost_field );
						}
					}
				}
			}

		}

		return $form;
	}

	public function enqueue_front_end_scripts( $form ) {
		$wppost_fields = GFAPI::get_fields_by_type( $form, array( 'wppfield_cascader' ) );
		if ( empty ( $wppost_fields ) ) {
			return;
		}
		wp_enqueue_script( 'custom-wp-post-gf-script', plugins_url( '/includes/js/wppostgffield.js', __FILE__ ), array( 'jquery' ), time() . '' );
		wp_enqueue_style( 'wpblog_post_gf_field_css', plugins_url( '/includes/css/wppostgffield.css', __FILE__ ), array(), time() . '' );
		wp_enqueue_script( 'gf_field_codac_verify_js', plugins_url( '/includes/js/codac-verify.js', __FILE__ ), array(), time() . '' );
		wp_enqueue_style( 'gf_field_codac_verify_css', plugins_url( '/includes/css/codac-verify.css', __FILE__ ), array(), time() . '' );

	}

	/**
	 * Configure the survey results page.
	 *
	 * @return array
	 */
	public function get_results_page_config() {
		return array(
			'title'        => 'WPPOST自定义字段',
			'capabilities' => array( 'gravityforms_wppost_gf_results' ),
			'callbacks'    => array(
				'fields'  => array( $this, 'results_fields' ),
				'filters' => array( $this, 'results_filters' )
			)
		);
	}

	/**
	 * Update the results page filters depending on how the grading for this form has been configured.
	 *
	 * @param array $filters The current filters.
	 * @param array $form The current form.
	 *
	 * @return array
	 */
	public function results_filters( $filters, $form ) {
		$unwanted_filters = array( 'wppfield_cascader' );
		if ( empty( $unwanted_filters ) ) {
			return $filters;
		}

		foreach ( $filters as $key => $filter ) {
			if ( in_array( $filter['key'], $unwanted_filters ) ) {
				unset( $filters[ $key ] );
			}
		}

		return $filters;
	}

	/**
	 * Get all the quiz fields for the current form.
	 *
	 * @param array $form The current form object.
	 *
	 * @return GF_Field[]
	 */
	public function results_fields( $form ) {
		return GFAPI::get_fields_by_type( $form, array( 'gravityformswpfield' ) );
	}

	/**
	 * Register AJAX callbacks.
	 *
	 * @since  1.0
	 */
	public function init_ajax() {
		parent::init_ajax();
		add_action( 'wp_ajax_nopriv_send_sms_verification', array( $this, 'handle_send_sms_verification' ) );
		add_action( 'wp_ajax_send_sms_verification', array( $this, 'handle_send_sms_verification' ) );
	}

	public function handle_send_sms_verification() {
		$phone_number = $_POST['phone_number'];
		// 消毒
		$phone_number = preg_replace( '/[^0-9]/', '', $phone_number );
		if ( ! preg_match( '/^1[3456789]\d{9}$/', $phone_number ) ) {
			wp_send_json_error( [ 'message' => '请输入有效的手机号码' ] );
		}

		$resp = wp_remote_post( 'http://10.190.237.52:3001/codacOrgCn/sendRegisterVerify', array(
			'body'    => array(
				'phone' => $phone_number,
			),
			"timeout" => 60,
		) );

		$body      = wp_remote_retrieve_body( $resp );
		$body_json = json_decode( $body, true );
		// 判断 JSON 是否解析成功
		if ( json_last_error() != JSON_ERROR_NONE ) {
			error_log( 'JSON 解析错误：' . $body );
			wp_send_json_error( [ 'message' => '服务器响应异常' ] );

			return;
		}

		if ( ! isset( $body_json['code'] ) || ! isset( $body_json['message'] ) ) {
			wp_send_json_error( [ 'message' => '服务器响应异常' ] );

			return;
		}

		if ( $body_json['code'] != 0 ) {
			wp_send_json_error( [ 'message' => $body_json['message'] ] );

			return;
		}

		wp_send_json_success( [ 'message' => $body_json['message'] ] );
	}

	/**
	 * Load the PayPal field.
	 *
	 * @since 1.0
	 */
	public function pre_init() {
		parent::pre_init();
		require_once 'includes/class-gf-field-wp-post-field.php';
		require_once 'includes/class-gf-field-wp-post-phone.php';
		require_once 'includes/class-gf-field-wp-post-idcard.php';
		require_once 'includes/class-gf-field-wp-post-nation.php';
		require_once 'includes/class-gf-field-wp-post-codac-verify.php';
	}

	/**
	 * Returns an instance of this class, and stores it in the $_instance property.
	 *
	 * @return WP_POST_FIELD $_instance An instance of the WP_POST_FIELD class
	 * @since  1.0
	 *
	 */
	public static function get_instance() {

		if ( self::$_instance == null ) {
			self::$_instance = new self();
		}

		return self::$_instance;

	}

}
