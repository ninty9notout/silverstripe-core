<?php
class Page extends SiteTree {
	/**
	 * Enable or disable file, image, or video galleries
	 * @var bool
	 */
	static $enable_media = false;

	/**
	 * Enable or disable the featured banner functionality
	 * @var bool
	 */
	static $enable_feature_banners = false;

	/**
	 * Enable or disable related pages functionality
	 * @var bool
	 */
	static $enable_related_pages = false;

	/**
	 * By default {@link Page} cannot be root
	 * @var bool
	 */
	static $can_be_root = false;

	private static $db = array(
		'CanCommentType' => 'Enum(array("Anyone", "LoggedInUsers", "OnlyTheseUsers", "Inherit"))',
		'AllowComments' => 'Boolean',
		'ShowInSitemap' => 'Boolean'
	);

	private static $has_one = array(
		'MainImage' => 'Image'
	);

	private static $has_many = array(
		'FeatureBanners' => 'FeatureBanner',
		'RelatedPages' => 'RelatedPage'
	);

	private static $many_many = array(
		'Images' => 'Image',
		'Videos' => 'Video',
		'Files' => 'File'
	);

	private static $defaults = array(
		'CanCommentType' => 'Inherit',
		'AllowComments' => false,
		'ShowInSitemap' => true
	);

	public function getCMSFields() {
		$fields = parent::getCMSFields();

		$mainImageField = new UploadField('MainImage', 'Main Image');
		$mainImageField->getValidator()->setAllowedExtensions(array('jpg', 'jpeg', 'png', 'gif', 'bmp'));
		$mainImageField->getValidator()->setAllowedMaxFileSize(10 * 1024 * 1024); // 10MB

		// Add a main image field to the media tab for all classes that are not error page
		if($this->ClassName != 'ErrorPage') {
			$fields->addFieldToTab('Root.Media', $mainImageField);
		}

		// Add media gallery fields to the media tab for all classes that are not
		// error page and static::$enable_media is set to true
		if(static::$enable_media && $this->ClassName != 'ErrorPage') {
			$imagesField = new UploadField('Images', 'Images');
			$imagesField->getValidator()->setAllowedExtensions(array('jpg', 'jpeg', 'png', 'gif', 'bmp'));
			$imagesField->getValidator()->setAllowedMaxFileSize(10 * 1024 * 1024); // 10MB

			$videosField = new GridField('Videos', 'Videos', $this->Videos(), GridFieldConfig_RecordEditor::create());

			$fileField = new UploadField('Files', 'Files');
			$fileField->getValidator()->setAllowedMaxFileSize(100 * 1024 * 1024); // 10MB

			$fields->addFieldsToTab('Root.Media', array(
				$imagesField,
				$videosField,
				$fileField
			));
		}

		// Add feature banner to the features tab for all classes that are not
		// error page and static::$enable_feature_banners is set to true
		if(static::$enable_feature_banners && $this->ClassName != 'ErrorPage') {
			$fields->addFieldToTab('Root.Features', new GridField('FeatureBanners', 'Feature Banners', $this->FeatureBanners(), GridFieldConfig_RecordEditor::create()));
		}

		// Add related pages to the related content tab for all classes 
		// are not error page and static::$enable_related_pages is set to true
		if(static::$enable_related_pages) {
			$fields->addFieldToTab('Root.RelatedContent', new GridField('RelatedPages', 'Related Pages', $this->RelatedPages(), GridFieldConfig_RecordEditor::create()));
		}

		return $fields;
	}

	public function getSettingsFields() {
		$fields = parent::getSettingsFields();

		// Add field to select who can comment on this page
		$commentersOptionsField = new OptionsetField('CanCommentType', _t('SiteTree.COMMENTERHEADER', 'Who can comment on this page?'));
		$fields->addFieldToTab('Root.Settings', $commentersOptionsField);

		// Get User Groups
		$groupsMap = Group::get()->map('ID', 'Breadcrumbs')->toArray();
		asort($groupsMap);

		// Add field to select which groups can comment on this 
		// page taking user groups as the source
		$commenterGroupsField = ListboxField::create('CommenterGroups',  _t('SiteTree.COMMENTERGROUPS', 'Commenter Groups'))->setMultiple(true)->setSource($groupsMap);
		$fields->addFieldToTab('Root.Settings', $commenterGroupsField);


		$commentersOptionsField->setSource(array(
			'Inherit' => _t('SiteTree.INHERIT', 'Inherit from parent page'),
			'Anyone' => _t('SiteTree.COMMENTERANYONE', 'Anyone'),
			'LoggedInUsers' => _t('SiteTree.COMMENTERLOGGEDIN', 'Logged-in users'),
			'OnlyTheseUsers' => _t('SiteTree.COMMENTERONLYTHESE', 'Only these people (choose from list)')
		));

		$fields->insertAfter(new CheckBoxField('ShowInSitemap', _t('SiteTree.SHOWINSITEMAP', 'Show in sitemap?')), 'ShowInSearch');
		$fields->insertAfter(new CheckBoxField('AllowComments', _t('SiteTree.ALLOWCOMMENTS', 'Allow comments?')), 'ShowInSitemap');
		
		if(!Permission::check('SITETREE_GRANT_ACCESS')) {
			$fields->makeFieldReadonly($commentersOptionsField);
			if($this->CanViewType == 'OnlyTheseUsers') {
				$fields->makeFieldReadonly($commenterGroupsField);
			} else {
				$fields->removeByName('CommenterGroups');
			}
		}

		return $fields;
	}

	// public function requireDefaultRecords() {
	// 	parent::requireDefaultRecords();

	// 	if(!SiteTree::get_by_link(SiteTree::get_by_link(RootURLController::get_default_homepage_link()))) {
	// 		$homepage = new Homepage();
	// 		$homepage->Title = _t('SiteTree.DEFAULTHOMETITLE', 'Home');
	// 		$homepage->Content = _t('SiteTree.DEFAULTHOMECONTENT', '<p>Welcome to SilverStripe! This is the default homepage. You can edit this page by opening <a href="admin/">the CMS</a>. You can now access the <a href="http://doc.silverstripe.org">developer documentation</a>, or begin <a href="http://doc.silverstripe.org/doku.php?id=tutorials">the tutorials.</a></p>');
	// 		$homepage->URLSegment = RootURLController::get_default_homepage_link();
	// 		$homepage->Sort = 1;
	// 		$homepage->ShowInSearch = false;
	// 		$homepage->write();
	// 		$homepage->publish('Stage', 'Live');
	// 		$homepage->flushCache();
	// 		DB::alteration_message('Home page created', 'created');
	// 	}

	// 	if(DB::query('SELECT COUNT(*) FROM "SiteTree"')->value() == 1) {
	// 		$aboutUs = new InformationPage();
	// 		$aboutUs->Title = _t('SiteTree.DEFAULTABOUTTITLE', 'About Us');
	// 		$aboutUs->Content = _t('SiteTree.DEFAULTABOUTCONTENT', '<p>You can fill this page out with your own content, or delete it and create your own pages.<br /></p>');
	// 		$aboutUs->Sort = 2;
	// 		$homepage->ShowInSearch = false;
	// 		$aboutUs->write();
	// 		$aboutUs->publish('Stage', 'Live');
	// 		$aboutUs->flushCache();
	// 		DB::alteration_message('About Us page created', 'created');

	// 		$contactUs = new UserDefinedForm();
	// 		$contactUs->Title = _t('SiteTree.DEFAULTCONTACTTITLE', 'Contact Us');
	// 		$contactUs->Content = _t('SiteTree.DEFAULTCONTACTCONTENT', '<p>You can fill this page out with your own content, or delete it and create your own pages.<br /></p>');
	// 		$contactUs->Sort = 3;
	// 		$homepage->ShowInSearch = false;
	// 		$contactUs->write();
	// 		$contactUs->publish('Stage', 'Live');
	// 		$contactUs->flushCache();
	// 		DB::alteration_message('Contact Us page created', 'created');
	// 	}
	// }

	/**
	 * Check if this object is of {@link Page} class
	 *
	 * @param Member $member
	 * @return boolean True if this is a page class
	 */
	public function canCreate($member = null) {
		return get_class($this) !== 'Page';
	}

	/**
	 * Check if given user can comment on this page
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

		// Check for inheritance from parent
		if($this->CanCommentType == 'Inherit') {
			return $this->ParentID ? $this->Parent()->canComment($member) : $this->getSiteConfig()->canComment($member);
		}

		// Check for any logged-in users
		if($this->CanViewType == 'LoggedInUsers' && $member) {
			return true;
		}

		// Check for specific groups
		if($member && is_numeric($member)) {
			$member = DataObject::get_by_id('Member', $member);
		}

		return $this->CanViewType == 'OnlyTheseUsers' && $member && $member->inGroups($this->ViewerGroups());
	}

	/**
	 * Overload the default FeatureBanners function to only include
	 * banners that have been selected to be displayed
	 *
	 * @return DataList The filtered feature banners
	 */
	public function FeatureBanners() {
		return DataList::create('FeatureBanner')
			->where(sprintf('PageID = %s AND Display = 1', $this->ID));
	}

	/**
	 * Check if this page is allowed to be displayed in the sitemap
	 *
	 * @return boolean True if page can be displayed in the sitemap
	 */
	public function ShowInSitemap() {
		if($this instanceof ErrorPage) {
			return false;
		}

		return $this->getField('ShowInSitemap');
	}

	/**
	 * Returns a placeholder image that can be uploaded via the site settings
	 *
	 * @return Image Placeholder image object
	 */
	public function PlaceholderImage() {
		return SiteConfig::current_site_config()->PlaceholderImage();
	}

	/**
	 * Does the layout for this class need to include secondary content?
	 *
	 * @return boolean True to display secondary content
	 */
	public function HasSecondaryContent() {
		$hasSecondaryContent = true;

		$this->extend('HasSecondaryContent', $hasSecondaryContent);

		return $hasSecondaryContent;
	}

	/**
	 * Does the layout for this class need to include tertiary content?
	 *
	 * @return boolean True to display tertiary content
	 */
	public function HasTertiaryContent() {
		$hasTertiaryContent = false;

		$this->extend('HasTertiaryContent', $hasTertiaryContent);

		return $hasTertiaryContent;
	}

	/**
	 * Does the layout for this class need to include feature slider?
	 *
	 * @return boolean True to display feature slider
	 */
	public function HasFeatureSlider() {
		$hasFeatureSlider = static::$enable_feature_banners && $this->FeatureBanners()->count();

		$this->extend('HasFeatureSlider', $hasFeatureSlider);

		return $hasFeatureSlider;
	}
}