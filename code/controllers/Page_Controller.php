<?php
class Page_Controller extends ContentController {
	/**
	 * Add 'rss' to the list of allowed action so the controller won't throw any errors for when an
	 * RSS feed is requested.
	 *
	 * Core doesn't have anything that uses rss as of this moment.
	 */
	private static $allowed_actions = array(
		'rss'
	);

	/**
	 * Generate slugs based on ClassName, root parent Title, and page
	 * URLSegment to be used in the HTML body tag
	 *
	 * @return string Space-separated list of classes
	 */
	public function BodyClass() {
		$classes = array();

		$page = Director::get_current_page();

		while($page) {
			$section = $page->URLSegment;
			$page = $page->Parent;
		}

		$classes[] = 'section-' . $section;

		$template = URLSegmentFilter::create()->filter($this->dataRecord->singular_name());

		$classes[] = 'template-' . $template;

		$classes[] = 'page-' . $this->dataRecord->URLSegment;

		return implode(' ', $classes);
	}

	/**
	 * Output the comments ONLY if comments have been enabled
	 * for the current page AND valid Disqus ID has been provided
	 *
	 * @return mixed The rendered comments if enabled or false
	 */
	public function Comments() {
		if(!$this->dataRecord->AllowComments) {
			return false;
		}

		$disqus = SiteConfig::current_site_config()->Disqus;

		if(!$disqus) {
			return false;
		}

		return $this->customise(array(
			'Disqus' => $disqus
		))->renderWith('Comments');
	}

	/**
	 * Output the Facebook Like box if Facebook ID is provided
	 *
	 * @return mixed The rendered Facebook Like box or false
	 */
	public function FacebookLikeBox() {
		$facebook = SiteConfig::current_site_config()->Facebook;

		if(!$facebook) {
			return false;
		}

		return $this->customise(array(
			'Facebook' => $facebook
		))->renderWith('FacebookLikeBox');
	}

	/**
	 * Display the attached feature banners as a slider
	 * 
	 * @return mixed The rendered feature slider or false
	 */
	public function FeatureBanners() {
		if(!$this->dataRecord->FeatureBanners()->count()) {
			return false;
		}

		return $this->renderWith('FeatureBanners');
	}

	/**
	 * Output any attached files
	 *
	 * @return mixed The rendered file list or false
	 */
	public function FileAttachments() {
		if(!$this->dataRecord->Files()->count()) {
			return false;
		}

		return $this->renderWith('FileAttachments');
	}

	/**
	 * Return a list of pages that are children of the {@link FooterLinksHolder} page
	 *
	 * @return SS_List
	 */
	public function FooterLinks() {
		return DataObject::get_one('FooterLinksHolder')->Children();
	}

	/**
	 * Output any attached images
	 *
	 * @return mixed The rendered image gallery or false
	 */
	public function ImageGallery() {
		if(!$this->dataRecord->Images()->count()) {
			return false;
		}

		return $this->renderWith('ImageGallery');
	}

	/**
	 * Output the main image for the page, if one exists
	 *
	 * @return mixed The rendered main image or false
	 */
	public function MainImage() {
		if(!$this->dataRecord->MainImageID) {
			return false;
		}

		return $this->customise($this->dataRecord->MainImage())->renderWith('MainImage');
	}

	/**
	 * Output the main logo as an H1 for homepage or div for all others
	 *
	 * @return HTMLText The rendered main logo
	 */
	public function MainLogo() {
		return $this->renderWith('MainLogo');
	}

	/**
	 * Output the main navigation
	 *
	 * @return HTMLText The rendered primary navigation
	 */
	public function PrimaryNavigation() {
		return $this->renderWith('PrimaryNavigation');
	}

	/**
	 * Output a list of pages that are selected to be related to this page
	 *
	 * @return mixed The rendered related pages or false
	 */
	public function RelatedPages() {
		if(!$this->dataRecord->RelatedPages()->count()) {
			return false;
		}

		$related = new ArrayList();

		foreach($this->dataRecord->RelatedPages() as $page) {
			$related->add($page->LinkTo());
		}

		return $this->customise(array(
			'RelatedPages' => $related
		))->renderWith('RelatedPages');
	}

	/**
	 * Look for and display class-specific secondary content template
	 * or fallback to default template
	 * 
	 * @return HTMLText The rendered secondary content
	 */
	public function SecondaryContent() {
		$templates = array();
		
		foreach(array_reverse(ClassInfo::ancestry(get_class($this->dataRecord))) as $name) {
			$action = $this->urlParams['Action'];

			$templates[] = sprintf('%s_%s_SecondaryContent', $name, $action);
			$templates[] = sprintf('%s_SecondaryContent', $name);

			if('Page' == $name) {
				$templates[] = 'SecondaryContent';

				break;
			}
		}

		return $this->renderWith($templates);
	}

	/**
	 * Output share toolbox if a valid pub ID has been provided
	 *
	 * @return mixed The rendered share toolbox or false
	 */
	public function SharingToolbox() {
		$addThis = SiteConfig::current_site_config()->AddThis;

		if(!$addThis) {
			return false;
		}

		return $this->customise(array(
			'AddThis' => $addThis
		))->renderWith('SharingToolbox');
	}

	/**
	 * Output the skip links for devices with styles disabled
	 *
	 * @return HTMLText The rendered skip links
	 */
	public function SkipLinks() {
		return $this->renderWith('SkipLinks');
	}

	/**
	 * Output social links and newsletter signup form
	 *
	 * @return HTMLText The rendered social links and newsletter signup
	 */
	public function StayInTouch() {
		return $this->customise(array(
			'Facebook' => SiteConfig::current_site_config()->Facebook,
			'Twitter' => SiteConfig::current_site_config()->Twitter,
			'YouTube' => SiteConfig::current_site_config()->YouTube,
			'GooglePlus' => SiteConfig::current_site_config()->GooglePlus
		))->renderWith('StayInTouch');
	}

	/**
	 * Look for and display class-specific tertiary content template
	 * or fallback to default template
	 * 
	 * @return HTMLText The rendered tertiary content
	 */
	public function TertiaryContent() {
		$templates = array();
		
		foreach(array_reverse(ClassInfo::ancestry(get_class($this->dataRecord))) as $name) {
			$templates[] = sprintf('%s_TertiaryContent', $name);

			if('Page' == $name) {
				$templates[] = 'TertiaryContent';
				
				break;
			}
		}

		return $this->renderWith($templates);
	}

	/**
	 * Output any attached videos
	 *
	 * @return mixed The rendered video gallery or false
	 */
	public function VideoGallery() {
		if(!$this->dataRecord->Videos()->count()) {
			return false;
		}

		return $this->renderWith('VideoGallery');
	}
}