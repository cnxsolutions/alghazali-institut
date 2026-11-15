<?php
/**
 * Autoloader PSR-4 minimal : AlGhazzali\Core\Content\Course => src/Content/Course.php.
 *
 * @package AlGhazzali\Core
 */

defined( 'ABSPATH' ) || exit;

spl_autoload_register(
	static function ( string $class_name ): void {
		$prefix = 'AlGhazzali\\Core\\';

		if ( 0 !== strpos( $class_name, $prefix ) ) {
			return;
		}

		$relative = str_replace( '\\', '/', substr( $class_name, strlen( $prefix ) ) );
		$file     = __DIR__ . '/' . $relative . '.php';

		if ( is_readable( $file ) ) {
			require_once $file;
		}
	}
);
