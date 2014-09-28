<?php
class Category extends DataObject {
	static $db = array(
		'URLSegment' => 'Varchar(255)',
		'Title' => 'Varchar(255)',
		'Content' => 'HTMLText',
	);

	static $defaults = array(
		'URLSegment' => 'new-category',
		'Title' => 'New Category'
	);

	static $summary_fields = array(
		'Title',
		'Content.Summary' => 'Description'
	);

	static $searchable_fields = array(
		'Title',
		'Content',
	);

	public function getCMSFields() {
		$fields = parent::getCMSFields();

		// Remove the default URL field
		$fields->removeByName('URLSegment');
		
		// Add the title field
		$fields->addFieldToTab('Root.Main', new TextField('Title'));

		if($this->record['ID']) {
			$fields->addFieldToTab('Root.Main', new TextField('URLSegment'));
		}

		// Add the content field
		$fields->addFieldToTab('Root.Main', new HTMLEditorField('Content'));

		return $fields;
	}

	/**
	 * Return the link for this object, with the {@link Director::baseURL()} included.
	 *
	 * @param string $action Optional controller action (method)
	 * @return string
	 */
	public function Link($action = null) {
		if($this->URLPrefix()) {
			return Controller::join_links($this->URLPrefix(), 'category', $this->RelativeLink($action));
		}

		return Controller::join_links(Director::baseURL(), 'category', $this->RelativeLink($action));
	}

	/**
	 * Return the link for this object relative to the SilverStripe root.
	 * 
	 * @param string $action See {@link Link()}
	 * @return string
	 */
	public function RelativeLink($action = null) {
		$base = $this->URLSegment;

		$this->extend('updateRelativeLink', $base, $action);

		// Legacy support: If $action === true, retain URLSegment for homepages, but don't 
		// append any action
		if($action === true) {
			$action = null;
		}

		return Controller::join_links($base, '/', $action);
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
			$t = 'category-%s' . $this->ID;
		}
		
		$this->extend('updateURLSegment', $t, $title);
		
		return $t;
	}

	/**
	 * Returns TRUE if this object has a URLSegment value that does not conflict with any other 
	 * objects. This methods checks for:
	 *   - A page with the same URLSegment that has a conflict.
	 *   - Conflicts with actions on the parent page.
	 *   - A conflict caused by a root page having the same URLSegment as a class name.
	 *
	 * @return bool
	 */
	public function validURLSegment() {
		$segment = Convert::raw2sql($this->URLSegment);
		$IDFilter = $this->ID ? sprintf('AND DataObjectPage.ID <> %d', $this->ID) : '';

		return !DataObject::get_one('DataObjectPage', sprintf("URLSegment = '%s' %s", $segment, $IDFilter));
	}

	public function onBeforeWrite() {
		parent::onBeforeWrite();

		// Generate a new URLSegment based on the title
		if((!$this->URLSegment || $this->URLSegment == 'new-category') && $this->Title) {
			$this->URLSegment = $this->generateURLSegment($this->Title);
		} else if($this->isChanged('URLSegment')) {
			$filter = URLSegmentFilter::create();
			$this->URLSegment = $filter->filter($this->URLSegment);

			if(!$this->URLSegment) {
				$this->URLSegment = 'category-' . $this->ID;
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