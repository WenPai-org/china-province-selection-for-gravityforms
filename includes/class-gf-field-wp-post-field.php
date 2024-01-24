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
class GF_Field_WP_POST_CASCADER extends GF_Field {

	/**
	 * Field type.
	 *
	 * @since 1.0
	 *
	 * @var string
	 */
	public $type = 'wppfield_cascader';

	/**
	 * Get field button title.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	public function get_form_editor_field_title() {
		return '地址选择';
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
		$is_entry_detail = $this->is_entry_detail();
		$form_id         = absint( $form['id'] );
		$id          = (int) $this->id;
		$field_id    = $is_entry_detail || $is_form_editor || $form_id == 0 ? "input_$id" : 'input_' . $form_id . "_$id";
		if($is_form_editor) {
			return '<input class="large" type="text" placeholder="展示格式：xx省 / xx市 / xx区 / xx街道" disabled>';
		}
		// return $this->echo_div_and_class() . "<div class='ginput_container ginput_container_{$this->type}' id='cascader_wrap_input_{$form_id}'><input name='input_{$this->id}' id='input_{$this->id}' type='hidden' value='{$value}'/></div>";
		return '';
	}

	public function echo_div_and_class()
	{
		return '<div id="areaField" class="van-field" role="button">
		<input type="text" class="" readonly placeholder="请选择地区" id="selectedArea">
	</div>
	<div class="van-overlay" role="button" tabindex="0"></div>
	<div id="areaPopup" class="van-popup">
		<div>
			<header>请选择地区</header>
			<div class="van-tabs__nav">
				<div id="tab-province" class="van-tab van-tab--active">省份</div>
				<div id="tab-city" class="van-tab">城市</div>
				<div id="tab-district" class="van-tab">区县</div>
				<div id="tab-town" class="van-tab">镇</div>
			</div>
			<div id="province-container" class="area-list"></div>
			<div id="city-container" class="area-list"></div>
			<div id="district-container" class="area-list"></div>
			<div id="town-container" class="area-list"></div>
		</div>
	</div>';
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

		// var_dump('?');die;
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


		$field_content = sprintf( "%s<$label_tag class='%s' $for_attribute >$legend_wrapper%s%s$legend_wrapper_close</$label_tag>{FIELD}%s", $admin_buttons, esc_attr( $this->get_field_label_class() ), esc_html( $field_label ), $required_div, $validation_message );

		if (!$is_admin) {
			$field_content .= $this->echo_div_and_class();
		}
		$field_content .= "<div class='ginput_container ginput_container_{$this->type}' id='cascader_wrap_input_{$form_id}'><input name='input_{$this->id}' id='input_{$this->id}' type='hidden' value='{$value}'/></div>";
		
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

GF_Fields::register( new GF_Field_WP_POST_CASCADER() );