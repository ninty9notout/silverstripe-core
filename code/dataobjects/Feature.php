<?php
class Feature extends DataObject {
	static $db = array(
		'Title' => 'Varchar(255)',
		'Prefix' => 'Varchar(255)',
		'Suffix' => 'Varchar(255)',
		'Description' => 'Text',
		'Display' => 'Boolean'
	);

	static $has_one = array(
		'Page' => 'Page',
		'Image' => 'Image'
	);

	static $extensions = array(
		'PageLink'
	);

	static $summary_fields = array(
		'Image.StripThumbnail' => 'Image',
		'Title' => 'Title',
		'IsDisplayed' => 'Display'
	);

	static $casting = array(
		'Description' => 'HTMLText'
	);

	public function getCMSFields() {
		$fields = parent::getCMSFields();

		$imageField = $fields->dataFieldByName('Image');
		$imageField->setAllowedExtensions(array('jpg', 'jpeg', 'png', 'gif'));
		$imageField->getValidator()->setAllowedMaxFileSize(10 * 1024 * 1024); // 10MB

		$fields->removeByName('PageID');

		return $fields;
	}

	/**
	 * Check if this feature is set to display
	 *
	 * @return string A HTML entity tick if feature is set to display
	 */
	public function IsDisplayed() {
		return $this->Display ? '✔' : '✘';
	}

	/**
	 * Check if this feature has a prefix
	 *
	 * @return boolean True if feature has a prefix
	 */
	public function HasPrefix() {
		return !empty($this->Prefix);
	}

	/**
	 * Check if this feature has a suffix
	 *
	 * @return boolean True if feature has a suffix
	 */
	public function HasSuffix() {
		return !empty($this->Suffix);
	}

	/**
	 * Check if this feature has a description
	 *
	 * @return boolean True if feature has a description
	 */
	public function HasDescription() {
		return !empty($this->Description);
	}
}

class FeatureBanner extends Feature { }