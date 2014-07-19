<?php
class UserDefinedFormDecorator extends DataExtension {
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
	 * Enable or disable related pages functionality
	 * @var bool
	 */
	static $enable_related_pages = true;

	/**
	 * By default {@link Page} cannot be root
	 * @var bool
	 */
	static $can_be_root = true;
}