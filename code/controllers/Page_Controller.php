<?php
class Page_Controller extends ContentController {
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

		$template = URLSegmentFilter::create()->filter($this->singular_name());

		$classes[] = 'template-' . $template;

		$classes[] = 'page-' . $this->URLSegment;

		return implode(' ', $classes);
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

			$templates[] = $name . '_{$action}_SecondaryContent';
			$templates[] = $name . '_SecondaryContent';
			$templates[] = 'SecondaryContent';

			if('DataObject' == $name) {
				break;
			}
		}

		return $this->renderWith($templates);
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
			$templates[] = $name . '_TertiaryContent';
			$templates[] = 'TertiaryContent';

			if('DataObject' == $name) {
				break;
			}
		}

		return $this->renderWith($templates);
	}

	/**
	 * Look for and display class-specific feature slider template
	 * or fallback to default template
	 * 
	 * @return HTMLText The rendered feature slider
	 */
	public function FeatureSlider() {
		$templates = array();

		foreach(array_reverse(ClassInfo::ancestry(get_class($this->dataRecord))) as $name) {
			$action = $this->urlParams['Action'];

			$templates[] = $name . '_Feature';
			$templates[] = 'Feature';

			if('DataObject' == $name) {
				break;
			}
		}

		return $this->renderWith($templates);
	}
}