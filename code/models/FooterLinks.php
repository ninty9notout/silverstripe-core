<?php
class FooterLinks extends RedirectorPage {
	/**
	 * By default {@link Page} cannot be root
	 * @var bool
	 */
	static $can_be_root = true;

	/**
	 * Check if a page with this class exists
	 *
	 * @param Member $member
	 * @return boolean True if page with this class exists
	 */
	public function canCreate($member = null) {
		return !DataObject::get_one($this->class);
	}

	/**
	 * Check if a this page can be deleted
	 *
	 * @param Member $member
	 * @return boolean False as this page shouldn't be deleted
	 */
	public function canDelete($member = null) {
		return false;
	}
}