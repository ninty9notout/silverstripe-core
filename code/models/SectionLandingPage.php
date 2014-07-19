<?php
class SectionLandingPage extends Page {
	/**
	 * Enable or disable the featured banner functionality
	 * @var bool
	 */
	static $enable_feature_banners = true;

	/**
	 * By default {@link Page} cannot be root
	 * @var bool
	 */
	static $can_be_root = true;
}