<?php
/**
 * Text input field with validation for URLs that
 * are accepted by the Video class.
 * 
 * @package forms
 * @subpackage fields-formattedinput
 */
class VideoURLField extends TextField {
	public function Type() {
		return 'text';
	}

	/**
	 * Validates entered URL against {@link Video::$allowed_sources}
	 * 
	 * @param Validator $validator
	 * @return String
	 */
	public function validate($validator) {
		$this->value = trim($this->value);

		if(!Video::fetch_video_details($this->value)) {
			$validator->validationError(
				$this->name,
				_t('VideoURLField.VALIDATION', 'Please enter a valid ' . implode(' or ', implode(' or ', array_keys(Video::$allowed_sources))) . ' URL'),
				'validation'
			);
			return false;
		}

		return true;
	}
}