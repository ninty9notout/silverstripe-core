<?php
class Address extends DataObject {
	static $db = array(
		'StreetAddress' => 'Text',
		'City' => 'Varchar(64)',
		'State' => 'Varchar(64)',
		'PostalCode' => 'Varchar(16)',
	);

	static $summary_fields = array(
		'StreetAddress',
		'City',
		'State',
		'PostalCode'
	);
}