<?php
class PageLink extends DataExtension {
	static $db = array(
		'RedirectionType' => 'Enum(array("Internal", "External"))',
		'ExternalURL' => 'Varchar(2083)'
	);

	static $has_one = array(
		'LinkTo' => 'SiteTree'
	);

	static $defaults = array(
		'RedirectionType' => 'Internal'
	);

	static $summary_fields = array(
		'LinkToTitle' => 'Page'
	);

	/**
	 * Return the URL for the redirected page
	 *
	 * @return string URL of the redirected page
	 */
	public function Link() {
		if($this->owner->RedirectionType == 'External' && $this->owner->ExternalURL) {
			return $this->owner->ExternalURL;
		}

		if($linkTo = $this->owner->LinkTo()) {
			return $linkTo->Link();
		}

		return Director::baseURL();
	}

	/**
	 * Return the title of the redirected page
	 *
	 * @return string Title of the redirected page
	 */
	public function LinkToTitle() {
		if($this->owner->RedirectionType == 'External' && $this->owner->ExternalURL) {
			return $this->owner->ExternalURL;
		}

		if($linkTo = $this->owner->LinkTo()) {
			return $linkTo->Title;
		}

		return null;
	}

	public function onBeforeWrite() {
		parent::onBeforeWrite();

		// Prefix the URL with "http://" if no prefix is found
		if($this->owner->ExternalURL && (strpos($this->owner->ExternalURL, '://') === false)) {
			$this->owner->ExternalURL = 'http://' . $this->owner->ExternalURL;
		}
	}
}