<?php
class InformationPage extends Page {
	/**
	 * Enable or disable file, image, or video galleries
	 * @var bool
	 */
	static $enable_media = true;

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