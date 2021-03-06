<?php
class SearchPage extends Page {
	/**
	 * By default {@link Page} cannot be root
	 * @var bool
	 */
	static $can_be_root = true;

	static $defaults = array(
		'ShowInMenus' => false,
		'ShowInSearch' => false,
		'ShowInSitemap' => false,
		'AllowComments' => false
	);

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
	
	public function HasSecondaryContent() {
		return false;
	}

	public function showInSitemap() {
		return false;
	}

	/**
	 * Creates an instance of this page after checking if one already exists
	 */
	public static function defaultRecords() {
		if(DataObject::get_one('SearchPage')) {
			return false;
		}

		$page = new SearchPage();
		$page->Title = 'Search';
		$page->write();
		$page->publish('Stage', 'Live');
		$page->flushCache();
		
		DB::alteration_message('Search page created', 'created');
	}
}