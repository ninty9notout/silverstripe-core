<?php
class SiteConfigDecorator extends DataExtension {
	static $db = array(
		'CanCommentType' => 'Enum(array("Anyone", "LoggedInUsers", "OnlyTheseUsers"))',
		'GoogleAnalyticsID' => 'Varchar',
		'DisqusID' => 'Varchar'
	);

	static $has_one = array(
		'PlaceholderImage' => 'Image'
	);

	static $defaults = array(
		'CanCommentType' => 'Anyone',
		'GoogleAnalyticsID' => '00-0000000-0',
		'DisqusID' => 'hangar18games'
	);
	
	public function updateCMSFields(FieldList $fields) {
		// Remove the theme select field
		$fields->removeByName('Theme');

		// Add an image upload field for the placeholder image
		$placeholderImageField = new UploadField('PlaceholderImage', 'Placeholder Image');
		$placeholderImageField->getValidator()->setAllowedExtensions(array('jpg', 'jpeg', 'png', 'gif'));
		$placeholderImageField->getValidator()->setAllowedMaxFileSize(10 * 1024 * 1024); // 10MB
		$fields->addFieldToTab('Root.Main', $placeholderImageField);

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

		// Add fields to enter Google Analytic and Disqus IDs
		$fields->addFieldToTab('Root.Social', new TextField('GoogleAnalyticsID', 'Google Analytics ID'));
		$fields->addFieldToTab('Root.Social', new TextField('DisqusID', 'Disqus ID'));
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

		if(!SiteTree::get_by_link(RootURLController::get_default_homepage_link())) {
			$homepage = new Homepage();
			$homepage->Title = _t('SiteTree.DEFAULTHOMETITLE', 'Home');
			$homepage->Content = _t('SiteTree.DEFAULTHOMECONTENT', '<p>Welcome to SilverStripe! This is the default homepage. You can edit this page by opening <a href="admin/">the CMS</a>. You can now access the <a href="http://doc.silverstripe.org">developer documentation</a>, or begin <a href="http://doc.silverstripe.org/doku.php?id=tutorials">the tutorials.</a></p>');
			$homepage->URLSegment = RootURLController::get_default_homepage_link();
			$homepage->Sort = 1;
			$homepage->ShowInSearch = false;
			$homepage->write();
			$homepage->publish('Stage', 'Live');
			$homepage->flushCache();
			DB::alteration_message('Home page created', 'created');
		}

		if(DB::query("SELECT COUNT(*) FROM SiteTree")->value() == 1) {
			$aboutUs = new InformationPage();
			$aboutUs->Title = _t('SiteTree.DEFAULTABOUTTITLE', 'About Us');
			$aboutUs->Content = _t('SiteTree.DEFAULTABOUTCONTENT', '<p>You can fill this page out with your own content, or delete it and create your own pages.<br /></p>');
			$aboutUs->Sort = 2;
			$aboutUs->write();
			$aboutUs->publish('Stage', 'Live');
			$aboutUs->flushCache();
			DB::alteration_message('About Us page created', 'created');

			$contactUs = new UserDefinedForm();
			$contactUs->Title = _t('SiteTree.DEFAULTCONTACTTITLE', 'Contact Us');
			$contactUs->Content = _t('SiteTree.DEFAULTCONTACTCONTENT', '<p>You can fill this page out with your own content, or delete it and create your own pages.<br /></p>');
			$contactUs->ShowInSearch = false;
			$contactUs->Sort = 3;
			$contactUsID = $contactUs->write();
			$contactUs->publish('Stage', 'Live');
			$contactUs->flushCache();

				$nameField = new EditableTextField();
				$nameField->Name = 'EditableTextField1';
				$nameField->Title = 'Name';
				$nameField->Sort = 1;
				$nameField->Required = true;
				$nameField->CustomSettings = serialize(array(
					'Rows' => 1,
					'ShowOnLoad' => 'Show'
				));
				$nameField->ParentID = $contactUsID;
				$nameField->write();
				$nameField->publish('Stage', 'Live');
				$nameField->flushCache();

				$emailField = new EditableEmailField();
				$emailField->Name = 'EditableEmailField2';
				$emailField->Title = 'Email';
				$emailField->Sort = 2;
				$emailField->Required = true;
				$emailField->CustomSettings = serialize(array(
					'ShowOnLoad' => 'Show'
				));
				$emailField->ParentID = $contactUsID;
				$emailField->write();
				$emailField->publish('Stage', 'Live');
				$emailField->flushCache();

				$subjectField = new EditableDropdown();
				$subjectField->Name = 'EditableDropdown3';
				$subjectField->Title = 'Subject';
				$subjectField->Sort = 3;
				$subjectField->Required = false;
				$subjectField->CustomSettings = serialize(array(
					'ShowOnLoad' => 'Show'
				));
				$subjectField->ParentID = $contactUsID;
				$subjectFieldID = $subjectField->write();
				$subjectField->publish('Stage', 'Live');
				$subjectField->flushCache();

					$generalEnquiriesOption = new EditableOption();
					$generalEnquiriesOption->Name = 'option1';
					$generalEnquiriesOption->Title = 'General Enquiries';
					$generalEnquiriesOption->ParentID = $subjectFieldID;
					$generalEnquiriesOption->write();
					$generalEnquiriesOption->publish('Stage', 'Live');
					$generalEnquiriesOption->flushCache();

					$feedbackOption = new EditableOption();
					$feedbackOption->Name = 'option2';
					$feedbackOption->Title = 'Feedback';
					$feedbackOption->ParentID = $subjectFieldID;
					$feedbackOption->write();
					$feedbackOption->publish('Stage', 'Live');
					$feedbackOption->flushCache();

					$recruitmentOption = new EditableOption();
					$recruitmentOption->Name = 'option3';
					$recruitmentOption->Title = 'Recruitment';
					$recruitmentOption->ParentID = $subjectFieldID;
					$recruitmentOption->write();
					$recruitmentOption->publish('Stage', 'Live');
					$recruitmentOption->flushCache();

					$pressOption = new EditableOption();
					$pressOption->Name = 'option4';
					$pressOption->Title = 'Press';
					$pressOption->ParentID = $subjectFieldID;
					$pressOption->write();
					$pressOption->publish('Stage', 'Live');
					$pressOption->flushCache();

				$messageField = new EditableTextField();
				$messageField->Name = 'EditableTextField4';
				$messageField->Title = 'Message';
				$messageField->Sort = 4;
				$messageField->Required = true;
				$messageField->CustomSettings = serialize(array(
					'Rows' => 10,
					'ShowOnLoad' => 'Show'
				));
				$messageField->ParentID = $contactUsID;
				$messageField->write();
				$messageField->publish('Stage', 'Live');
				$messageField->flushCache();

			DB::alteration_message('Contact Us page created', 'created');
		}

		if(!DataObject::get_one('SearchPage')) {
			$searchPage = new SearchPage();
			$searchPage->Title = _t('SearchPage.SEARCH', 'Search');
			$searchPage->ShowInSearch = false;
			$searchPage->write();
			$searchPage->publish('Stage', 'Live');
			$searchPage->flushCache();
			DB::alteration_message('Search page created', 'created');
		}

		if(!DataObject::get_one('SitemapPage')) {
			$sitemap = new SitemapPage();
			$sitemap->Title = _t('SitemapPage.SITEMAP', 'Sitemap');
			$sitemap->Content = _t('SitemapPage.DEFAULTCONTENT', '<p>This page displays a sitemap of the pages in your site.</p>');
			$sitemap->ShowInSearch = false;
			$sitemap->write();
			$sitemap->publish('Stage', 'Live');
			$sitemap->flushCache();
			DB::alteration_message('Sitemap page created', 'created');
		}

		if(!DataObject::get_one('FooterLinks')) {
			$footerLinks = new FooterLinks();
			$footerLinks->Title = _t('FooterLinks.FOOTERLINKS', 'Footer Links');
			$footerLinks->URLSegment = "site";
			$footerLinks->ShowInMenus = false;
			$footerLinks->ShowInSearch = false;
			$footerLinks->LinkToID = 1;
			$footerLinks->write();
			$footerLinks->publish('Stage', 'Live');
			$footerLinks->flushCache();
			DB::alteration_message('Footer Links page created', 'created');
		}
	}
}