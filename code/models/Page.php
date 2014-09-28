<?php
class Page extends SiteTree {
	/**
	 * Enable or disable main image
	 * @var bool
	 */
	static $enable_main_image = false;

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
	 * Enable or disable related content functionality
	 * @var bool
	 */
	static $enable_related_content = false;

	/**
	 * Enable or disable commenting functionality
	 * @var bool
	 */
	static $enable_comments = false;

	/**
	 * Enable or disable sitemap functionality (CMS only)
	 * @var bool
	 */
	static $enable_sitemap = false;

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
		'ShowInMenus' => false,
		'ShowInSearch' => false,
		'ShowInSitemap' => false,
		'AllowComments' => false
	);

	public function getCMSFields() {
		$fields = parent::getCMSFields();

		// Only add if static::$enable_main_image is true
		if(static::$enable_main_image) {
			$mainImageField = new UploadField('MainImage', 'Main Image');
			$mainImageField->getValidator()->setAllowedExtensions(array('jpg', 'jpeg', 'png', 'gif', 'bmp'));
			$mainImageField->getValidator()->setAllowedMaxFileSize(10 * 1024 * 1024); // 10MB
			$fields->addFieldToTab('Root.Media', $mainImageField);
		}

		// Only add if static::$enable_media is true
		if(static::$enable_media) {
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

		// Only add if static::$enable_feature_banners is true
		if(static::$enable_feature_banners) {
			$fields->addFieldToTab('Root.Features', new GridField('FeatureBanners', 'Feature Banners', $this->FeatureBanners(), GridFieldConfig_RecordEditor::create()));
		}

		// Only add if static::$enable_related_content is true
		if(static::$enable_related_content) {
			$fields->addFieldToTab('Root.RelatedContent', new GridField('RelatedPages', 'Related Pages', $this->RelatedPages(), GridFieldConfig_RecordEditor::create()));
		}

		return $fields;
	}

	public function getSettingsFields() {
		$fields = parent::getSettingsFields();
		
		// Only add if static::$enable_sitemap is true
		if(static::$enable_sitemap) {
			$fields->insertAfter(new CheckBoxField('ShowInSitemap', _t('SiteTree.SHOWINSITEMAP', 'Show in sitemap?')), 'ShowInSearch');
		}

		// Only add if static::$enable_comments is true
		if(!static::$enable_comments) {
			return $fields;
		}

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