<?php
class SiteConfigDecorator extends DataExtension {
	static $db = array(
		'CanCommentType' => 'Enum(array("Anyone", "LoggedInUsers", "OnlyTheseUsers"))',
		'GoogleAnalytics' => 'Varchar(32)',
		'Disqus' => 'Varchar(32)',
		'AddThis' => 'Varchar(32)',
		'Facebook' => 'Varchar(255)',
		'Twitter' => 'Varchar(255)',
		'YouTube' => 'Varchar(255)',
		'GooglePlus' => 'Varchar(255)',
	);

	static $has_one = array(
		'PlaceholderImage' => 'Image'
	);

	static $defaults = array(
		'CanCommentType' => 'Anyone',
		'GoogleAnalytics' => '00-0000000-0',
		'Disqus' => 'hangar18games',
		'AddThis' => 'ra-53d14db329715982',
		'Facebook' => 'Hangar18GamesStudio',
		'Twitter' => 'hangar18games',
		'YouTube' => 'hangar18games',
		'GooglePlus' => 'hangar18games'
	);
	
	public function updateCMSFields(FieldList $fields) {
		// Remove the theme select field
		$fields->removeByName('Theme');

		// Add an image upload field for the placeholder image
		$placeholderImageField = new UploadField('PlaceholderImage', 'Placeholder Image');
		$placeholderImageField->getValidator()->setAllowedExtensions(array('jpg', 'jpeg', 'png', 'gif'));
		$placeholderImageField->getValidator()->setAllowedMaxFileSize(10 * 1024 * 1024); // 10MB
		$fields->addFieldToTab('Root.Main', $placeholderImageField);

		// Add the Google Analytics field
		$fields->addFieldToTab('Root.Main', new TextField('GoogleAnalytics', 'Google Analytics'));

		// Add the AddThis publisher ID field
		$fields->addFieldToTab('Root.Main', new TextField('AddThis', 'AddThis Publisher ID'));

		// Add field to select who can comment on pages on this website
		$commentersOptionsField = new OptionsetField('CanCommentType', _t('SiteTree.COMMENTERHEADER', 'Who can comment on this page?'));
		$fields->addFieldToTab('Root.Access', $commentersOptionsField);
		
		// Get User Groups
		$groupsMap = Group::get()->map('ID', 'Breadcrumbs')->toArray();
		asort($groupsMap);

		// Add field to select which groups can comment on pages acorss
		// this website taking user groups as the source
		$commenterGroupsField = ListboxField::create('CommenterGroups',  _t('SiteTree.COMMENTERGROUPS', 'Commenter Groups'))->setMultiple(true)->setSource($groupsMap);
		$fields->addFieldToTab('Root.Access', $commenterGroupsField);

		$commentersOptionsField->setSource(array(
			'Anyone' => _t('SiteTree.COMMENTERANYONE', 'Anyone'),
			'LoggedInUsers' => _t('SiteTree.COMMENTERLOGGEDIN', 'Logged-in users'),
			'OnlyTheseUsers' => _t('SiteTree.COMMENTERONLYTHESE', 'Only these people (choose from list)')
		));
		
		if(!Permission::check('EDIT_SITECONFIG')) {
			$fields->makeFieldReadonly($commentersOptionsField);
			$fields->makeFieldReadonly($commenterGroupsField);
		}

		// Add fields to enter Social IDs
		$fields->addFieldToTab('Root.Social', new TextField('Facebook', 'Facebook URL or ID'));
		$fields->addFieldToTab('Root.Social', new TextField('Twitter', 'Twitter @'));
		$fields->addFieldToTab('Root.Social', new TextField('YouTube', 'YouTube Channel'));
		$fields->addFieldToTab('Root.Social', new TextField('GooglePlus', 'Google+ URL or ID'));
		$fields->addFieldToTab('Root.Social', new TextField('Disqus', 'Disqus ID'));
	}
	
	/**
	 * Check if given user can comment on pages on this website
	 *
	 * @param Member $member
	 * @return boolean True if member can comment
	 */
	public function canComment($member = null) {
		if(!$member || !(is_a($member, 'Member')) || is_numeric($member)) {
			$member = Member::currentUserID();
		}
		
		// Admin override
		if($member && Permission::checkMember($member, array('ADMIN'))) {
			return true;
		}
		
		// Check for anyone
		if(!$this->CanCommentType || $this->CanCommentType == 'Anyone') {
			return true;
		}
		
		// Check for any logged-in users
		if($this->CanViewType == 'LoggedInUsers' && $member) {
			return true;
		}

		// Check for specific groups
		if($member && is_numeric($member)) {
			$member = DataObject::get_by_id('Member', $member);
		}
		
		return $this->CanViewType == 'OnlyTheseUsers' && $member && $member->inGroups($this->ViewerGroups()) ? true : false;
	}

	public function requireDefaultRecords() {
		parent::requireDefaultRecords();

		Homepage::defaultRecords();

		InformationPage::defaultRecords();

		ContactPage::defaultRecords();

		SearchPage::defaultRecords();

		SitemapPage::defaultRecords();

		FooterLinksHolder::defaultRecords();
	}
}