<?php
class InformationPage extends Page {
	/**
	 * Enable or disable main image
	 * @var bool
	 */
	static $enable_main_image = true;

	/**
	 * Enable or disable file, image, or video galleries
	 * @var bool
	 */
	static $enable_media = true;

	/**
	 * Enable or disable related content functionality
	 * @var bool
	 */
	static $enable_related_content = true;

	/**
	 * Enable or disable sitemap functionality (CMS only)
	 * @var bool
	 */
	static $enable_sitemap = true;

	/**
	 * By default {@link Page} cannot be root
	 * @var bool
	 */
	static $can_be_root = true;

	static $defaults = array(
		'ShowInMenus' => true,
		'ShowInSearch' => true,
		'ShowInSitemap' => true,
		'AllowComments' => false
	);

	/**
	 * Creates the about us page after checking if only one page exists (homepage)
	 */
	public static function defaultRecords() {
		if(DB::query('SELECT COUNT(*) FROM SiteTree')->value() != 1) {
			return false;
		}

		$page = new InformationPage();
		$page->Title = 'About Us';
		$page->Sort = 30;
		$page->write();
		$page->publish('Stage', 'Live');
		$page->flushCache();
		
		DB::alteration_message('About Us page created', 'created');
	}
}