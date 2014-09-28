<?php
class Homepage extends SectionLandingPage {
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

	/**
	 * Does the layout for this class need to include tertiary content?
	 *
	 * @return boolean True to display tertiary content
	 */
	public function HasTertiaryContent() {
		return true;
	}

	/**
	 * Creates an instance of this page after checking if one already exists
	 */
	public static function defaultRecords() {
		if(SiteTree::get_by_link(RootURLController::get_default_homepage_link())) {
			return false;
		}

		$page = new Homepage();
		$page->Title = 'Home';
		$page->URLSegment = RootURLController::get_default_homepage_link();
		$page->Sort = 1;
		$page->ShowInMenus = true;
		$page->ShowInSearch = false;
		$page->ShowInSitemap = true;
		$page->AllowComments = false;
		$page->write();
		$page->publish('Stage', 'Live');
		$page->flushCache();

		DB::alteration_message('Home page created', 'created');
	}
}