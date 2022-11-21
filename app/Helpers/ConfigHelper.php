<?php

namespace App\Helpers;

use App\Constant\ConfigKey;
use App\Repositories\AppConfigRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class ConfigHelper {
	private $config;

	public function __construct() {
		$this->config = new AppConfigRepository();
	}

	public function saveList($list) {
		$this->config->updateConfig($list);
		$this->refreshCache();
	}

	public function refreshCache() {
		Cache::forget('settings');
		Cache::remember('settings', now()->addDay(1), function() {
			$settings = $this->config->getConfig();
			$notCacheKeys = ConfigKey::getNotToBeCacheKey();
			$settingCount = count($settings);
			for ($i = 0; $i < $settingCount; $i++) {
				if (in_array($settings[$i]->name, $notCacheKeys)) {
					unset($settings[$i]);
					continue;
				}
				$settings[$i]->value = $settings[$i]->getValue();
			}
			return Arr::pluck($settings->toArray(), 'value', 'name');
		});
	}

	private static function getSettings() {
		$settings = Cache::get('settings');
		if (!$settings) {
			$configHelper = new ConfigHelper();
			$configHelper->refreshCache();
			$settings = Cache::get('settings');
		}
		return $settings;
	}

	public static function bulkAdd($list) {
		$configHelper = new ConfigHelper();
		$configHelper->saveList($list);
	}

	/**
	 * @param $key
	 * @param null $default
	 * @return array|float|string|null
	 */
	public static function get($key, $default = null) {
		$settings = ConfigHelper::getSettings();
		return (is_array($key)) ? Arr::only($settings, $key) : (isset($settings[$key]) ? $settings[$key] : $default);
	}

	public static function getWithDynamicKey($key, array $replace = [], $default = null) {
		$tmp = [];
		foreach (array_keys($replace) as $k) {
			$tmp['{' . $k . '}'] = $replace[$k];
		}
		$key = str_replace(array_keys($tmp), array_values($replace), $key);
		return self::get($key, $default);
	}

	public static function clear() {
		Cache::forget('settings');
	}
}
