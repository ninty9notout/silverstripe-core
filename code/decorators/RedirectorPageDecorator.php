<?php
class RedirectorPageDecorator extends DataExtension {
	/**
	 * By default {@link Page} cannot be root
	 * @var bool
	 */
	static $can_be_root = false;
	
	static $defaults = array(
		'ShowInMenus' => true,
		'ShowInSearch' => false,
		'ShowInSitemap' => false,
		'AllowComments' => false
	);
}