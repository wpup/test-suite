<?php

/**
 *  Don't continue if `WP_Test_Suite` class exists.
 */
if ( ! class_exists( 'WP_Test_Suite' ) ):

	/**
	 * WordPress Test Suite.
	 */
	class WP_Test_Suite {

		/**
		 * Files to load after `muplugins_loaded` filter.
		 *
		 * @var object
		 */
		protected static $files = array();

		/**
		 * Plugins that should be loaded.
		 *
		 * @var object
		 */
		protected static $plugins = array();

		/**
		 * WordPress test root.
		 *
		 * @var string
		 */
		protected static $test_root = '';

		/**
		 * Find plugin automatically.
		 *
		 * @return array
		 */
		public static function find_plugin() {
			$path = getcwd() . '/';
			$file = defined( 'WTS_PLUGIN_FILE_NAME' ) ? WTS_PLUGIN_FILE_NAME : 'plugin.php';

			if ( empty( $file ) || ! file_exists( $path . $file ) ) {
				return array();
			}

			return array( $path . $file );
		}

		/**
		 * Get test root.
		 *
		 * @return string
		 */
		public static function get_test_root() {
			if ( ! empty( self::$test_root ) || file_exists( self::$test_root ) ) {
				return self::$test_root;
			}

			if ( getenv( 'WP_DEVELOP_DIR' ) !== false ) {
				return getenv( 'WP_DEVELOP_DIR' ) . '/tests/phpunit';
			}

			if ( file_exists( '/tmp/wordpress-develop/tests/phpunit/includes/bootstrap.php' ) ) {
				return '/tmp/wordpress-develop/tests/phpunit';
			}

			if ( file_exists( '/tmp/wordpress-tests-lib/includes/bootstrap.php' ) ) {
				return '/tmp/wordpress-tests-lib';
			}

			if ( file_exists( '/srv/www/wordpress-develop/tests/phpunit/includes/bootstrap.php' ) ) {
				return '/srv/www/wordpress-develop/tests/phpunit';
			}

            if ( file_exists( '/srv/www/wordpress-develop/public_html/tests/phpunit/includes/bootstrap.php' ) ) {
                return '/srv/www/wordpress-develop/public_html/tests/phpunit';
            }

			if ( file_exists( '../../../../wordpress-develop/tests/phpunit/includes/bootstrap.php' ) ) {
				return realpath( '../../../../wordpress-develop/tests/phpunit' );
			}

            if ( file_exists( '../../../../wordpress-develop/public_html/tests/phpunit/includes/bootstrap.php' ) ) {
                return realpath( '../../../../wordpress-develop/public_html/tests/phpunit' );
            }

			return '';
		}

		/**
		 * Load files.
		 *
		 * @param array|string $files
		 */
		public static function load_files( $files ) {
			if ( is_string( $files ) ) {
				if ( ! file_exists( $files ) ) {
					return;
				}
				self::$files[] = $files;
			} else if ( is_array( $files ) ) {
				$files = array_filter( $files, function ( $file ) {
					return file_exists( $file );
				} );
				$files = array_unique( $files );
				self::$files = array_merge( self::$files, $files );
			}
		}

		/**
		 * Load plugins.
		 *
		 * @param array|string $plugins
		 */
		public static function load_plugins( $plugins ) {
			if ( is_string( $plugins ) ) {
				if ( ! file_exists( $plugins ) ) {
					return;
				}

				self::$plugins[] = $plugins;
			} else if ( is_array( $plugins ) ) {
				$plugins = array_filter( $plugins, function ( $plugin ) {
					return file_exists( $plugin );
				} );
				$plugins = array_unique( $plugins );
				self::$plugins = array_merge( self::$plugins, $plugins );
			}
		}

		/**
		 * Run WordPress tests.
		 *
		 * @param object $closure
		 */
		public static function run( $closure = null ) {
			$test_root = self::get_test_root();
			$test_root = rtrim( $test_root, '/' );

			if ( empty( $test_root ) || ! file_exists( $test_root ) ) {
				throw new Exception( 'Empty test root' );
			}

			if ( ! file_exists( $test_root . '/includes/functions.php' ) ) {
				throw new Exception( sprintf( '%s is missing', $test_root . '/includes/functions.php' ) );
			}

			if ( ! function_exists( 'tests_add_filter' ) ) {
				require $test_root . '/includes/functions.php';
			}

			if ( empty( self::$plugins ) ) {
				self::$plugins = self::find_plugin();
			}

			foreach ( self::$plugins as $plugin ) {
				tests_add_filter( 'muplugins_loaded', function () use ( $plugin ) {
					require $plugin;
				} );
			}

			if ( ! file_exists( $test_root . '/includes/bootstrap.php' ) ) {
				throw new Exception( sprintf( '%s is missing', $test_root . '/includes/boostrap.php' ) );
			}

			if ( ! class_exists( 'WP_UnitTestCase' ) ) {
				require $test_root . '/includes/bootstrap.php';
			}

			if ( is_callable( $closure ) ) {
				call_user_func( $closure );
			}

			foreach ( self::$files as $file ) {
				require $file;
			}
		}

		/**
		 * Set test root.
		 *
		 * @param string $test_rot
		 */
		public static function set_test_root( $test_root ) {
			self::$test_root = $test_root;
		}
	}

endif;
