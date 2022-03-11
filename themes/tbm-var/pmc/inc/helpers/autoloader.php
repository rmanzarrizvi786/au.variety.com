<?php

/**
 * Autoloader for PHP classes inside theme
 *
 * @author Amit Gupta <agupta@pmc.com>
 * @since  2017-05-18
 *
 * @version 2017-07-27 Hau
 *
 */

/**
 *
 * Child theme usage example add to child theme's functions.php
 *

	add_action( 'pmc_core_autoloader_register_namespace', function( $instance ) {
		$instance->register_namespace( 'ChildThemeNamespace', __DIR__ );
	} );

	add_action( 'after_setup_theme', function() {
		// Manually instantiate any classes as needed here
		\ChildThemeNamespace\Inc\Theme::get_instance();
	} );

 */

namespace PMC\Core\Inc\Helpers;

use PMC\Global_Functions\Traits\Singleton;

/**
 * Make it a final class to prevent class from extends elsewhere.
 * We shouldn't have multiple version of autoloader elsewhere.
 */

final class Autoloader
{
	use Singleton;

	protected $_namespaces_mapping = [];

	protected function __construct()
	{
		$this->register_namespace('PMC\Core', dirname(dirname(__DIR__)));

		// Trigger action and pass object to allow child theme to register additional namespace.
		do_action('pmc_core_autoloader_register_namespace', $this);
	}

	/**
	 * Register a namespace to root path mapping
	 * @param  string $namespace The root namespace
	 * @param  string $root      The root path
	 */
	public function register_namespace($namespace, $root)
	{
		if (!file_exists($root)) {
			throw new \Exception('Folder not found ' . $root);
		}
		$this->_namespaces_mapping[$namespace] = $root;
	}

	/**
	 * Look up the resource
	 * @param  string $resource The class to look up
	 * @return mixed            The object containing the root path & array paths of the resource
	 */
	private function _lookup_resource($resource)
	{
		if (empty($resource) || strpos($resource, '\\') === false) {
			return false;
		}
		foreach ($this->_namespaces_mapping as $ns => $root) {
			if (strpos($resource, $ns . '\\') === 0) {

				// Knock off the prefix of namespace
				$resource = substr($resource, strlen($ns) + 1);

				return (object)[
					'root'     => $root,
					'paths'    => explode('\\', str_replace('_', '-', strtolower($resource))),
				];
			}
		}
		return false;
	}

	/**
	 * Resolve the resource into physical path
	 * @param  string $resource The class
	 * @return string           The full path class file
	 */
	private function _resolve_resource_path($resource = '')
	{
		$result = $this->_lookup_resource($resource);
		if (!$result || empty($result->paths)) {
			return;
		}
		$paths  = $result->paths;
		$root   = $result->root;

		/*
		 * Time to determine which type of resource path it is,
		 * so that we can deduce the correct file path for it.
		 */
		if ((!empty($paths[0]) && 'inc' === $paths[0]) && (!empty($paths[1]) && 'helpers' !== $paths[1])) {

			/*
			 * Theme resource for 'inc/classes' dir
			 * The path need 'classes' dir injected into it as all classes,
			 * services, traits, interfaces etc will be in 'classes' dir
			 */

			$class_path = untrailingslashit(implode('/', array_slice($paths, 1)));

			// $resource_path = sprintf('%s/inc/classes/%s.php', untrailingslashit($root), $class_path);
			$resource_path = sprintf('%s/pmc/inc/classes/%s.php', untrailingslashit($root), $class_path);
		} elseif ((!empty($paths[0]) && 'plugins' === $paths[0]) && (!empty($paths[1]) && 'config' !== $paths[1])) {

			/*
			 * Plugin resource paths need 'classes' dir injected into the path as all
			 * plugin classes, interfaces & traits must be in 'classes' dir in plugin root.
			 */

			if (empty($paths[2])) {
				$plugin_name = $paths[1];
				// Take care of [ROOT]\Plugins\Name => /plugins/name/class-name.php
				$resource_path = sprintf('%s/plugins/%s/class-%s.php', untrailingslashit($root), $plugin_name, $plugin_name);
			} else {
				$plugin_name = untrailingslashit(implode('/', array_slice($paths, 0, 2)));
				$class_path = strtolower(implode('/', array_slice($paths, 2)));
				$resource_path = sprintf('%s/%s/classes/%s.php', untrailingslashit($root), $plugin_name, $class_path);
			}
		} else {

			/*
			 * All other resource paths are translated as-is in lowercase
			 */

			if (!empty($paths[1]) && 'config' === $paths[1]) {
				$paths[1] = '_config';
			}

			$resource_path = sprintf('%s/%s.php', untrailingslashit($root), implode('/', $paths));
		}

		$file_prefix = '';

		if (strpos($resource_path, 'traits') > 0) {
			$file_prefix = 'trait';
		} elseif (strpos($resource_path, 'interfaces') > 0) {
			$file_prefix = 'interface';
		} elseif (strpos($resource_path, '_config') > 0) {
			$file_prefix = 'class';
		} elseif (strpos($resource_path, 'classes') > 0) {  // this has to be the last
			$file_prefix = 'class';
		}

		if (!empty($file_prefix)) {

			$resource_parts = explode('/', $resource_path);
			$resource_parts = (!empty($resource_parts) && is_array($resource_parts)) ? $resource_parts : [];

			// inject prefix to class name
			$resource_parts[count($resource_parts) - 1] = sprintf(
				'%s-%s',
				strtolower($file_prefix),
				$resource_parts[count($resource_parts) - 1]
			);

			$resource_path = implode('/', $resource_parts);
		}

		return $resource_path;
	}

	/**
	 * Load the class resource if found
	 * @param  string $resource The class
	 */
	public function load_resource($resource)
	{
		$resource_path = $this->_resolve_resource_path($resource);
		if (!$resource_path || !file_exists($resource_path) || validate_file($resource_path) !== 0) {
			return;
		}
		return require_once $resource_path;
	}
}

spl_autoload_register([Autoloader::get_instance(), 'load_resource']);

//EOF
