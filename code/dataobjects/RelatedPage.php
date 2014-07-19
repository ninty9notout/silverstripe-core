<?php
class RelatedPage extends DataObject {
	static $extensions = array(
		'RelatedLink'
	);

	public function getCMSFields() {
		$fields = parent::getCMSFields();

		$fields->addFieldToTab('Root.Main', new DropdownField('LinkToID', 'Page', DataObject::get('InformationPage')->map('ID', 'Title')));

		return $fields;
	}
}