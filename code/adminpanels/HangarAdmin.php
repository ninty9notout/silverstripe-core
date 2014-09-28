<?php
class HangarAdmin extends ModelAdmin {
	static $url_segment = 'hangar';

	static $menu_title = 'Hangar';

	/**
	 * An array of models to manage that gets populated via config files
	 * @var array
	 */
	static $managed_models = array(
		'Video' => array(
			'title' => 'Videos'
		)
	);

	public static function add_managed_model($model, array $options = null) {
		if(is_array($options)) {
			$managed_model = array(
				$model => $options
			);
		} else {
			$managed_model = array($model);
		}

		Config::inst()->update(get_called_class(), 'managed_models', $managed_model);
	}

	public static function remove_managed_model($model) {
		$managed_models = Config::inst()->get(get_called_class(), 'managed_models');

		if(isset($managed_models[$model])) {
			unset($managed_models[$model]);

			Config::inst()->remove(get_called_class(), 'managed_models');

			Config::inst()->update(get_called_class(), 'managed_models', $managed_models);
		}
	}
}