<?php

if ( ! class_exists( 'GFForms' ) ) {
	die();
}

/**
 * The PayPal field is a payment methods field used specifically by the PayPal Checkout Add-On.
 *
 * @since 1.0
 *
 * Class GF_Field_WP_POST
 */
class GF_Field_WP_POST_PHONE extends GF_Field {

	/**
	 * Field type.
	 *
	 * @since 1.0
	 *
	 * @var string
	 */
	public $type = 'wppfield_phone';

	/**
	 * Get field button title.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	public function get_form_editor_field_title() {
		return '中国手机号码';
	}

	/**
	 * Get this field's icon.
	 *
	 * @since 1.4
	 *
	 * @return string
	 */
	public function get_form_editor_field_icon() {
		return 'dashicons-yes';
	}

	/**
	 * Get form editor button.
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	public function get_form_editor_button() {
		return array(
			'group' => 'standard_fields',
			'text'  => $this->get_form_editor_field_title(),
		);
	}

	/**
	 * Get field settings in the form editor.
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	public function get_form_editor_field_settings() {
		return array(
			'label_setting',
			'rules_setting',
			'placeholder_setting',
		);
	}


	/**
	 * Get field input.
	 *
	 * @since 1.0
	 *
	 * @param array      $form  The Form Object currently being processed.
	 * @param array      $value The field value. From default/dynamic population, $_POST, or a resumed incomplete submission.
	 * @param null|array $entry Null or the Entry Object currently being edited.
	 *
	 * @return string
	 */
	public function get_field_input( $form, $value = '', $entry = null ) {
		if (is_array($value)) $value = rgpost('input_' . $this->id);
		$is_form_editor  = $this->is_form_editor();
		$placeholder_attribute  = $this->get_field_placeholder_attribute();
		if($is_form_editor) {
			return "<input class='large' type='text' {$placeholder_attribute} disabled>";
		}
		return '';
	}

	/**
	 * Returns the field markup; including field label, description, validation, and the form editor admin buttons.
	 *
	 * The {FIELD} placeholder will be replaced in GFFormDisplay::get_field_content with the markup returned by GF_Field::get_field_input().
	 *
	 * @since 1.0
	 *
	 * @param string|array $value                The field value. From default/dynamic population, $_POST, or a resumed incomplete submission.
	 * @param bool         $force_frontend_label Should the frontend label be displayed in the admin even if an admin label is configured.
	 * @param array        $form                 The Form Object currently being processed.
	 *
	 * @return string
	 */
	public function get_field_content( $value, $force_frontend_label, $form ) {

		// Get the default HTML markup.
		$form_id = (int) rgar( $form, 'id' );

		$field_label = $this->get_field_label( $force_frontend_label, $value );

		$validation_message_id = 'validation_message_' . $form_id . '_' . $this->id;
		$validation_message    = ( $this->failed_validation && ! empty( $this->validation_message ) ) ? sprintf( "<div id='%s' class='gfield_description validation_message gfield_validation_message' aria-live='polite'>%s</div>", $validation_message_id, $this->validation_message ) : '';

		$is_form_editor  = $this->is_form_editor();
		$is_entry_detail = $this->is_entry_detail();
		$is_admin        = $is_form_editor || $is_entry_detail;

		$required_div = $is_admin || $this->isRequired ? sprintf( "<span class='gfield_required'>%s</span>", $this->isRequired ? '*' : '' ) : '';

		$admin_buttons = $this->get_admin_buttons();

		$for_attribute = empty( $target_input_id ) ? '' : "for='{$target_input_id}'";

		$legend_wrapper       = '';
		$legend_wrapper_close = '';

		if ( method_exists( 'GF_Field', 'get_field_label_tag' ) ) {
			$label_tag = parent::get_field_label_tag( $form );
			if ( $is_form_editor && 'legend' === $label_tag ) {
				$legend_wrapper       = '<label class="gfield_label gform-field-label">';
				$legend_wrapper_close = '</label>';
			}
		} else {
			$label_tag = 'label';
		}

		$input_content = '';

		$invalid_attribute      = $this->failed_validation ? 'aria-invalid="true"' : 'aria-invalid="false"';
		$required_attribute     = $this->isRequired ? 'aria-required="true"' : '';

		if (!$is_form_editor) {
			$placeholder_attribute  = $this->get_field_placeholder_attribute();
			$input_content = "<div class='ginput_container ginput_container_text' id='cascader_wrap_input_{$form_id}'><input class='large' type='text' name='input_{$this->id}' id='input_{$this->id}' value='{$value}' {$required_attribute} {$placeholder_attribute} {$invalid_attribute}/></div>";
		}

		$field_content = sprintf( "%s<$label_tag class='%s' $for_attribute >$legend_wrapper%s%s$legend_wrapper_close</$label_tag>{FIELD}%s%s", $admin_buttons, esc_attr( $this->get_field_label_class() ), esc_html( $field_label ), $required_div, $input_content, $validation_message );
		
		return $field_content;
	}

	/**
	 * Overwrite the parent method to avoid the field upgrade from the credit card field class.
	 *
	 * @since 1.0
	 */
	public function post_convert_field() {
		GF_Field::post_convert_field();
	}
}

GF_Fields::register( new GF_Field_WP_POST_PHONE() );
