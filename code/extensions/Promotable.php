<?php
class Promotable extends DataExtension {
	static $db = array(
		'Promotion' => 'Enum(array("Demote", "Neutral", "Promote"))'
	);

	static $indexes = array(
		'idx_promotion' => '(Promotion)'
	);

	static $defaults = array(
		'Promotion' => 'Neutral'
	);

	public function updateCMSFields(FieldList $fields) {
		// Fetch values for the promotion db field
		$options = $this->owner->dbObject('Promotion')->enumValues();
		
		// Add a radio group field to select the promotion for this page
		$fields->insertAfter(new OptionsetField('Promotion', 'Promotion', $options), 'MenuTitle');
	}
}