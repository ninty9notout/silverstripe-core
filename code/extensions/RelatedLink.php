<?php
class RelatedLink extends DataExtension {
	static $has_one = array(
		'LinkTo' => 'SiteTree',
		'SiteTree' => 'SiteTree'
	);

	static $summary_fields = array(
		'LinkTo.MainImage.StripThumbnail' => 'Image',
		'LinkTo.Title' => 'Site Tree'
	);

	public function updateCMSFields(FieldList $fields) {
		$fields->removeByName('LinkToID');
		$fields->removeByName('SiteTreeID');
	}

	/**
	 * Get the URL for the related page
	 *
	 * @return string The URL of the related page
	 */
	public function Link() {
		if($linkTo = $this->owner->LinkTo()) {
			return $linkTo->Link();
		}

		return Director::baseURL();
	}
}