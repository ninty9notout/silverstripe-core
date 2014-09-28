<?php
class Video extends DataObject {
	static $youtube_api_key = 'AIzaSyCZrRdfYrq5DBm06y8xR1nDUBYNcDTrv40';

	static $allowed_sources = array(
		'YouTube' => '//www.youtube.com/embed/%s?rel=0&wmode=transparent',
		'Vimeo' => '//player.vimeo.com/video/%s'
	);

	static $db = array(
		'Title' => 'Varchar(255)',
		'Description' => 'Text',
		'Source' => 'Enum(array("YouTube", "Vimeo"))',
		'URL' => 'Varchar(2083)',
		'Embed' => 'Varchar(16)'
	);

	static $has_one = array(
		'Thumbnail' => 'Image'
	);

	static $belongs_many_many = array(
		'Pages' => 'Page'
	);

	static $casting = array(
		'Description' => 'HTMLText'
	);

	static $summary_fields = array(
		'Thumbnail.StripThumbnail' => 'Image',
		'Title' => 'Title',
		'Description' => 'Description'
	);

	static $searchable_fields = array(
		'Title',
		'Description',
		'Source'
	);

	public function getCMSFields() {
		$fields = parent::getCMSFields();

		// Remove the default URL field
		$fields->removeByName('URL');

		// Add either a video URL field if this is a new record or a video URL field if this 
		// record already exists
		if(!$this->record['ID']) {
			$fields->insertBefore(new VideoURLField('URL', 'Media URL'), 'Title');
		} else {
			$fields->insertBefore(new ReadonlyField('URL', 'Media URL'), 'Title');
		}

		// Remove the default description field and replace with an editable textarea field
		// $fields->removeByName('Description');
		// $fields->insertAfter(new TextareaField('Description', 'Description'), 'Title');

		// Remove the default thumbnail field and replace with an validated image upload field
		$thumbnailField = $fields->dataFieldByName('Thumbnail');
		$thumbnailField->setAllowedExtensions(array('jpg', 'jpeg', 'png', 'gif'));
		$thumbnailField->getValidator()->setAllowedMaxFileSize(10 * 1024 * 1024); // 10MB

		// Remove title, description, and thumbnail fields for new records
		if(!$this->record['ID']) {
			$fields->removeByName('Title');
			$fields->removeByName('Description');
			$fields->removeByName('Thumbnail');
		}

		// Remove source and embed fields
		$fields->removeByName('Source');
		$fields->removeByName('Embed');

		return $fields;
	}

	/**
	 * Return the URL to be used with HTML iframe tags
	 *
	 * @return string Iframe embed URL
	 */
	public function EmbedURL() {
		if(array_key_exists($this->Source, static::$allowed_sources)) {
			return sprintf(static::$allowed_sources[$this->Source], $this->Embed);
		}
		
		return $this->URL;
	}

	protected function onBeforeWrite() {
		parent::onBeforeWrite();

		// Don't do anything unless the URL has been edited otherwise this resource-heavy function
		// will be called everytime
		if(!$this->isChanged('URL')) {
			return false;
		}

		// Fetch video details
		$video_details = static::fetch_video_details($this->URL);

		// Fetch the video thumbnail
		$thumbnail = static::getFileByURL($video_details['thumbnail'], $video_details['id']);

		// Update the fields with details from the video
		$this->Title = $video_details['title'];
		$this->Description = $video_details['description'];
		$this->Source = $video_details['source'];
		$this->Embed = $video_details['id'];

		// Set the thumbnail to that of the video
		$this->ThumbnailID = $thumbnail->ID;
	}

	/**
	 * Fetch video details from one of the APIs
	 *
	 * @param string $url YouTube or Vimeo video URL
	 * @return array Array of video details
	 */
	public static function fetch_video_details($url) {
		$return = array();

		foreach(static::$allowed_sources as $source=>$embed) {
			$id_from_url = strtolower($source) . '_id_from_url';

			if($id = static::{$id_from_url}($url)) {
				$video_details = strtolower($source) . '_video_details';

				return array_merge(
					array(
						'source' => $source,
						'id' => $id
					),
					static::{$video_details}($id)
				);
			}
		}

		return false;
	}

	/**
	 * Extract video ID from the given YouTube URL
	 * See {@link http://blog.luutaa.com/php/extract-youtube-and-vimeo-video-id-from-link/}
	 *
	 * @param string $url YouTube video URL
	 * @return string The YouTube video ID
	 */
	private static function youtube_id_from_url($url) {
		$regexstr = '~
			# Match Youtube URL and embed code
			(?:								# Group to match embed codes
				(?:<iframe [^>]*src=")?		# If iframe match up to first quote of src
				|(?:						# Group to match if older embed
					(?:<object .*>)?	 	# Match opening Object tag
					(?:<param .*</param>)* 	# Match all param tags
					(?:<embed [^>]*src=")? 	# Match embed tag to the first quote of src
				)?							# End older embed code group
			)?								# End embed code groups
			(?:								# Group youtube url
				https?:\/\/					# Either http or https
				(?:[\w]+\.)*				# Optional subdomains
				(?:							# Group host alternatives.
				youtu\.be/					# Either youtu.be,
				| youtube\.com				# or youtube.com
				| youtube-nocookie\.com		# or youtube-nocookie.com
				)							# End Host Group
				(?:\S*[^\w\-\s])?			# Extra stuff up to VIDEO_ID
				([\w\-]{11})				# $1: VIDEO_ID is numeric
				[^\s]*						# Not a space
			)								# End group
			"?								# Match end quote if part of src
			(?:[^>]*>)?						# Match any extra stuff up to close brace
			(?:								# Group to match last embed code
				</iframe>					# Match the end of the iframe
				|</embed></object>			# or Match the end of the older embed
			)?								# End Group of last bit of embed code
			~ix';

		if(preg_match($regexstr, $url, $matches) && !empty($matches)) {
			return $matches[1];
		}
		
		return false;
	}

	/**
	 * Extract video ID from the given Vimeo URL
	 * See {@link http://blog.luutaa.com/php/extract-youtube-and-vimeo-video-id-from-link/}
	 *
	 * @param string $url Vimeo video URL
	 * @return string The Vimeo video ID
	 */
	private static function vimeo_id_from_url($url) {
		$regexstr = '~
			# Match Vimeo url and embed code
			(?:<iframe [^>]*src=")?		# If iframe match up to first quote of src
			(?:							# Group vimeo url
				https?:\/\/				# Either http or https
				(?:[\w]+\.)*			# Optional subdomains
				vimeo\.com				# Match vimeo.com
				(?:[\/\w]*\/videos?)?	# Optional video sub directory this handles groups links also
				\/						# Slash before Id
				([0-9]+)				# $1: VIDEO_ID is numeric
				[^\s]*					# Not a space
			)							# End group
			"?							# Match end quote if part of src
			(?:[^>]*></iframe>)?		# Match the end of the iframe
			(?:<p>.*</p>)?				# Match any title information stuff
			~ix';

		if(preg_match($regexstr, $url, $matches) && !empty($matches)) {
			return $matches[1];
		}
		
		return false;
	}

	/**
	 * Use the YouTube v3 API to fetch video details
	 *
	 * @param string $id The YouTube video ID
	 * @return array Array of video details
	 */
	private static function youtube_video_details($id) {
		$url = 'https://www.googleapis.com/youtube/v3/videos?';
		$url.= sprintf('key=%s', static::$youtube_api_key);
		$url.= sprintf('&id=%s', $id);
		$url.= '&fields=items(id,snippet(title,description,thumbnails))';
		$url.= '&part=snippet';

		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL  => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_BINARYTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_TIMEOUT => 5
		));
		$response = json_decode(curl_exec($ch))->items[0]->snippet;
		curl_close($ch);

		$return = array(
			'title' => $response->title,
			'description' => $response->description,
			// Use the default thumbnail for now
			'thumbnail' => $response->thumbnails->default->url
		);

		// Check for higher quality thumbnails and use them instead
		if($response->thumbnails->medium) {
			$return['thumbnail'] = $response->thumbnails->medium->url;
		}

		if($response->thumbnails->high) {
			$return['thumbnail'] = $response->thumbnails->high->url;
		}

		// This, along with maxres, may or may not be present, so check for them anyway
		if($response->thumbnails->standard) {
			$return['thumbnail'] = $response->thumbnails->standard->url;
		}

		if($response->thumbnails->maxres) {
			$return['thumbnail'] = $response->thumbnails->maxres->url;
		}

		return $return;
	}

	/**
	 * Use the Vimeo v2 API to fetch video details
	 *
	 * @param string $id The Vimeo video ID
	 * @return array Array of video details
	 */
	private static function vimeo_video_details($id) {
		$url = sprintf('http://vimeo.com/api/v2/video/%s.json', $id);

		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_TIMEOUT => 30
		));
		$response = json_decode(curl_exec($ch))[0];
		curl_close($ch);

		$return = array(
			'title' => $response->title,
			'description' => str_ireplace(array('<br />','<br>','<br/>'), '\n', $response->description),
			// Use the lowest quality thumbnail
			'thumbnail' => $response->thumbnail_small
		);

		// See if higher thumbnails are available
		if($response->thumbnail_medium) {
			$return['thumbnail'] = $response->thumbnail_medium;
		}

		if($response->thumbnail_large) {
			$return['thumbnail'] = $response->thumbnail_large;
		}

		return $return;
	}

	/**
	 * Fetch and save the given thumbnail file
	 *
	 * @param string $url The URL of the image to save
	 * @param string $filename New name to give the saved image
	 * @return Image The saved image file
	 */
	private static function getFileByURL($url, $filename) {
		$filename = $filename . '.' . pathinfo($url, PATHINFO_EXTENSION);

		$url = str_replace('https://', 'http://', $url);

		$basePath = Director::baseFolder() . DIRECTORY_SEPARATOR;
		$folder = Folder::find_or_make('Videos');
		$relativeFilePath = $folder->Filename . $filename;
		$fullFilePath = $basePath . $relativeFilePath;

		if(!file_exists($fullFilePath)) {
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
			$rawdata = curl_exec($ch);
			curl_close ($ch);

			$fp = fopen($fullFilePath, 'x');
			fwrite($fp, $rawdata);
			fclose($fp);
		}

		$file = new Image();
		$file->ParentID = $folder->ID;
		$file->OwnerID = (Member::currentUser()) ? Member::currentUser()->ID : 0;
		$file->Name = basename($relativeFilePath); 
		$file->Filename = $relativeFilePath; 
		$file->Title = str_replace('-', ' ', substr($filename, 0, (strlen ($filename)) - (strlen (strrchr($filename,'.')))));
		$file->write();

		return $file;
	}
}