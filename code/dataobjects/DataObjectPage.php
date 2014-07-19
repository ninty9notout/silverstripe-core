<?php
class DataObjectPage extends DataObject {
	static $db = array(
		'URLSegment' => 'Varchar(255)',
		'Title' => 'Varchar(255)',
		'Content' => 'HTMLText',
	);

	static $indexes = array(
		'URLSegment' => true
	);
	
	static $defaults = array(
		'Title' => 'New Item',
		'URLSegment' => 'new-item'
	);
	
	static $summary_fields = array(
		'Title' => 'Title',
		'URLSegment' => 'URLSegment'
	);

	static $searchable_fields = array(
		'Title',
		'Content',
	);
	
	static $default_sort = 'Created DESC';

	public function getCMSFields() {
		$fields = parent::getCMSFields();

		// Remove the default URL field
		$fields->removeByName('URLSegment');
		
		// Add the title field
		$fields->addFieldToTab('Root.Main', new TextField('Title'));	

		// Display the URL field only if this is not a new record
		if($this->ID) {
			$urlsegment = new SiteTreeURLSegmentField('URLSegment', 'URL Segment');
			$urlsegment->setURLPrefix($this->AbsoluteURLPrefix());
			$urlsegment->setHelpText(_t('SiteTreeURLSegmentField.HelpChars', ' Special characters are automatically converted or removed.'));
			$fields->addFieldToTab('Root.Main', $urlsegment);
		}

		// Add the content field
		$fields->addFieldToTab('Root.Main', new HTMLEditorField('Content'));
		
		return $fields;
	}

	/**
	 * Generate custom metatags to display on the DataObject page
	 *
	 * @return string HTML meta tags
	 */ 
	public function MetaTags($includeTitle = true) {
		$tags = array();

		if($includeTitle === true || $includeTitle == 'true') {
			$tags[] = '<title>' . $this->Title . '</title>';
		}

		$generator = trim(Config::inst()->get('SiteTree', 'meta_generator'));

		if(!empty($generator)) {
			$tags[] = '<meta name="generator" content="' . Convert::raw2att($generator) . '">';
		}

		$tags[] = '<meta http-equiv="Content-type" content="text/html; charset=' . Config::inst()->get("ContentNegotiator", "encoding") . '">';

		$tags[] = '<meta name="description" content="' . Convert::raw2att($this->Content()->FirstParagraph()) . '">';

		$tags = implode("\n", $tags) . "\n";

		$this->extend('MetaTags', $tags);

		return $tags;
	}

	/**
	 * Return the URL prefix for this {@link DataObjectPage} object
	 * with "category" action prefixed
	 *
	 * @return string The URL prefix
	 */
	public function URLPrefix() {
		return DataObject::get_one('Homepage')->Link('category/');
	}

	/**
	 * Return the absolute URL prefix for this {@link DataObjectPage}
	 * object with "category" action prefixed
	 * @used DataObjectPage::URLPrefix
	 *
	 * @return string The absolute URL prefix
	 */
	public function AbsoluteURLPrefix() {
		return Controller::join_links(Director::absoluteBaseURL(), $this->URLPrefix());
	}

	/**
	 * Return the link for this {@link DataObjectPage} object, with the {@link Director::baseURL()} included.
	 *
	 * @param string $action Optional controller action (method). 
	 *  Note: URI encoding of this parameter is applied automatically through template casting,
	 *  don't encode the passed parameter.
	 *  Please use {@link Controller::join_links()} instead to append GET parameters.
	 * @return string
	 */
	public function Link($action = null) {
		return Controller::join_links(Director::baseURL(), 'category', $this->RelativeLink($action));
	}

	/**
	 * Return the link for this {@link DataObjectPage} object relative to the SilverStripe root.
	 *
	 * @uses RootURLController::get_homepage_link()
	 * 
	 * @param string $action See {@link Link()}
	 * @return string
	 */
	public function RelativeLink($action = null) {
		$base = $this->URLSegment;

		$this->extend('updateRelativeLink', $base, $action);

		// Legacy support: If $action === true, retain URLSegment for homepages,
		// but don't append any action
		if($action === true) $action = null;

		return Controller::join_links($base, '/', $action);
	}

	/**
	 * Returns TRUE if this object has a URLSegment value that does not conflict with any other objects. This methods
	 * checks for:
	 *   - A page with the same URLSegment that has a conflict.
	 *   - Conflicts with actions on the parent page.
	 *   - A conflict caused by a root page having the same URLSegment as a class name.
	 *
	 * @return bool
	 */
	public function validURLSegment() {
		$segment = Convert::raw2sql($this->URLSegment);
		$IDFilter = $this->ID ? sprintf('AND DataObjectPage.ID <> %d', $this->ID) : "";

		return !DataObject::get_one('DataObjectPage', sprintf("URLSegment = '%s' %s", $segment, $IDFilter));
	}

	/**
	 * Generate a URL segment based on the title provided.
	 * 
	 * If {@link Extension}s wish to alter URL segment generation, they can do so by defining
	 * updateURLSegment(&$url, $title).  $url will be passed by reference and should be modified.
	 * $title will contain the title that was originally used as the source of this generated URL.
	 * This lets extensions either start from scratch, or incrementally modify the generated URL.
	 * 
	 * @param string $title Page title.
	 * @return string Generated url segment
	 */
	public function generateURLSegment($title) {
		$filter = URLSegmentFilter::create();
		$t = $filter->filter($title);

		if(!$t || $t == '-' || $t == '-1') {
			$t = 'item-%s' . $this->ID;
		}
		
		$this->extend('updateURLSegment', $t, $title);
		
		return $t;
	}

	public function onBeforeWrite() {
		parent::onBeforeWrite();

		// Generate a new URLSegment based on the page title
		if((!$this->URLSegment || $this->URLSegment == 'new-item') && $this->Title) {
			$this->URLSegment = $this->generateURLSegment($this->Title);
		} else if($this->isChanged('URLSegment')) {
			$filter = URLSegmentFilter::create();
			$this->URLSegment = $filter->filter($this->URLSegment);

			if(!$this->URLSegment) {
				$this->URLSegment = 'item-' . $this->ID;
			}
		}

		// Ensure that this object has a non-conflicting URLSegment value.
		$count = 2;
		while(!$this->validURLSegment()) {
			$this->URLSegment = preg_replace('/-[0-9]+$/', null, $this->URLSegment) . '-' . $count;
			$count++;
		}
	}
}