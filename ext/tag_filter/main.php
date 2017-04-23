<?php
/*
 * Name: Global Tag Filter
 * Author: John Brooks <john@fastquake.com>
 * Description: Apply a permanent tag filter, based on Host header
 */

class GlobalTagFilter extends Extension {
	private $filters = array();

	public function onInitExt(InitExtEvent $event) {
		global $config;
		$this->filters = GlobalTagFilter::parseFilterSpec($config->get_string('tagFilterSpec'));
	}

	public function onImageRetrieval(ImageRetrievalEvent $event) {
		$host = $_SERVER['HTTP_HOST'];
		foreach($this->filters as $domain => $tags) {
			$match = false;
			if(strpos($domain, '.', strlen($domain)-1) !== false) {
				if($host === $domain || $host === substr($domain, 0, -1)) {
					$match = true;
				}
			} else {
				if(strpos($host, $domain) === 0) {
					$match = true;
				}
			}

			if($match) {
				foreach($tags as $tag) {
					$event->add_term($tag);
				}
			}
		}
	}

	public function onSetupBuilding(SetupBuildingEvent $event) {
		$sb = new SetupBlock('Global Tag Filter');
		$sb->add_longtext_option('tagFilterSpec', 'Filter specification');

		$event->panel->add_block($sb);
	}

	private static function parseFilterSpec($text) {
		$lines = explode("\n", $text);
		$filters = array();

		foreach($lines as $line) {
			$match = preg_match("/^(\S+)\s+(.+)$/", $line, $matches);
			if(!$match)
				continue;

			$domain = $matches[1];
			$tags = Tag::explode($matches[2], false);
			$filters[$domain] = $tags;
		}

		return $filters;
	}
}
?>
