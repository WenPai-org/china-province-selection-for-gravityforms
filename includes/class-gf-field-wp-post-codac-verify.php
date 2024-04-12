<?php

if ( ! class_exists( 'GFForms' ) ) {
	die();
}

/**
 * 人体器官捐赠平台验证码
 *
 * @since 1.0
 *
 * Class GF_Field_WP_POST_Codac_Verify
 */
class GF_Field_WP_POST_Codac_Verify extends GF_Field {

	/**
	 * Field type.
	 *
	 * @since 1.0
	 *
	 * @var string
	 */
	public $type = 'wppfield_codac_verify';

	/**
	 * Get field button title.
	 *
	 * @return string
	 * @since 1.0
	 *
	 */
	public function get_form_editor_field_title() {
		return '人体器官捐赠平台验证码';
	}

	/**
	 * Get this field's icon.
	 *
	 * @return string
	 * @since 1.4
	 *
	 */
	public function get_form_editor_field_icon() {
		return 'dashicons-yes';
	}

	/**
	 * Get form editor button.
	 *
	 * @return array
	 * @since 1.0
	 *
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
	 * @return array
	 * @since 1.0
	 *
	 */
	public function get_form_editor_field_settings() {
		return array(
			'label_setting',
			'rules_setting',
			'placeholder_setting',
			'conditional_logic_field_setting',
		);
	}

	/**
	 * Get field input.
	 *
	 * @param array $form The Form Object currently being processed.
	 * @param array $value The field value. From default/dynamic population, $_POST, or a resumed incomplete submission.
	 * @param null|array $entry Null or the Entry Object currently being edited.
	 *
	 * @return string
	 * @since 1.0
	 *
	 */
	public function get_field_input( $form, $value = '', $entry = null ) {
		if ( is_array( $value ) ) {
			$value = rgpost( 'input_' . $this->id );
		}
		$is_form_editor  = $this->is_form_editor();
		$is_entry_detail = $this->is_entry_detail();

		$form_id               = absint( $form['id'] );
		$tabindex              = $this->get_tabindex();
		$id                    = (int) $this->id;
		$placeholder_attribute = $this->get_field_placeholder_attribute();
		$field_id              = $is_entry_detail || $is_form_editor || $form_id == 0 ? "input_$id" : 'input_' . $form_id . "_$id";
		$required_attribute    = $this->isRequired ? 'aria-required="true"' : '';
		$invalid_attribute     = $this->failed_validation ? 'aria-invalid="true"' : 'aria-invalid="false"';
		$disabled_text         = $is_form_editor ? 'disabled="disabled"' : '';

        $input = '<div class="ginput_container ginput_container_sms_verification">';
        $input .= '<div class="input-group">';
        $input .= "<input name='input_{$id}' id='{$field_id}' type='text' value='{$value}' class='form-control input_yzm' required {$tabindex} {$placeholder_attribute} {$required_attribute} {$invalid_attribute} {$disabled_text} />";
        $input .= '<a class="btn" id="send_sms_verification">发送验证码</a>';
        $input .= '<div id="validationServer03Feedback" class="invalid-feedback" >请输入正确的验证码</div>';
        $input .= '</div>';
        $input .= '</div>';

		return $input;
	}

	/**
	 * Returns the field markup; including field label, description, validation, and the form editor admin buttons.
	 *
	 * The {FIELD} placeholder will be replaced in GFFormDisplay::get_field_content with the markup returned by GF_Field::get_field_input().
	 *
	 * @param string|array $value The field value. From default/dynamic population, $_POST, or a resumed incomplete submission.
	 * @param bool $force_frontend_label Should the frontend label be displayed in the admin even if an admin label is configured.
	 * @param array $form The Form Object currently being processed.
	 *
	 * @return string
	 * @since 1.0
	 *
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

		// $invalid_attribute      = $this->failed_validation ? 'aria-invalid="true"' : 'aria-invalid="false"';
		// $required_attribute     = $this->isRequired ? 'aria-required="true"' : '';

		// if (!$is_form_editor) {
		// 	$placeholder_attribute  = $this->get_field_placeholder_attribute();
		// 	// $input_content = "<div class='ginput_container ginput_container_text'><input class='large' type='text' name='input_{$this->id}' id='input_{$this->id}' value='{$value}' {$required_attribute} {$placeholder_attribute} {$invalid_attribute}/></div>";
		// }

		$field_content = sprintf( "%s<$label_tag class='%s' $for_attribute >$legend_wrapper%s%s$legend_wrapper_close</$label_tag>{FIELD}%s", $admin_buttons, esc_attr( $this->get_field_label_class() ), esc_html( $field_label ), $required_div, $validation_message );

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

GF_Fields::register( new GF_Field_WP_POST_Codac_Verify() );
