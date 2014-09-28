<?php
class UserDefinedFormDecorator extends DataExtension {
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
	 * Enable or disable the featured banner functionality
	 * @var bool
	 */
	static $enable_feature_banners = true;

	/**
	 * Enable or disable related content functionality
	 * @var bool
	 */
	static $enable_related_content = true;

	/**
	 * Enable or disable commenting functionality
	 * @var bool
	 */
	static $enable_comments = false;

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
}