<?php
class FooterLinksHolder extends RedirectorPage {
	/**
	 * By default {@link Page} cannot be root
	 * @var bool
	 */
	static $can_be_root = true;

	static $defaults = array(
		'ShowInMenus' => false,
		'ShowInSearch' => false,
		'ShowInSitemap' => true,
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

	/**
	 * Creates an instance of this page after checking if one already exists
	 */
	public static function defaultRecords() {
		if(DataObject::get_one('FooterLinksHolder')) {
			return false;
		}

		$page = new FooterLinksHolder();
		$page->Title = 'Footer Links';
		$page->URLSegment = 'site';
		$page->LinkToID = SiteTree::get_by_link(RootURLController::get_default_homepage_link())->ID;
		$page->write();
		$page->publish('Stage', 'Live');
		$page->flushCache();

		DB::alteration_message('Footer Links page created', 'created');

		// Create other footer links
		$about = new RedirectorPage();
		$about->Title = 'About Us';
		$about->ParentID = $page->ID;
		$about->LinkToID = DataObject::get_one('InformationPage')->ID;
		$about->write();
		$about->publish('Stage', 'Live');
		$about->flushCache();

		$contact = new RedirectorPage();
		$contact->Title = 'Contact Us';
		$contact->ParentID = $page->ID;
		$contact->LinkToID = DataObject::get_one('ContactPage')->ID;
		$contact->write();
		$contact->publish('Stage', 'Live');
		$contact->flushCache();

		$terms = new InformationPage();
		$terms->ParentID = $page->ID;
		$terms->Title = 'Terms & Conditions';
		$terms->write();
		$terms->publish('Stage', 'Live');
		$terms->flushCache();

		$privacy = new InformationPage();
		$privacy->ParentID = $page->ID;
		$privacy->Title = 'Privacy & Cookies';
		$privacy->write();
		$privacy->publish('Stage', 'Live');
		$privacy->flushCache();
	}
}