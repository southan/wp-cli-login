<?php

use WP_CLI\MU_Plugin;
use WP_CLI\Remote;
use WP_CLI\Utils;

class WP_CLI_Login {

	public ?string $user;

	public int $timeout = 30;

	/**
	 * Instant WordPress login as any user.
	 * 
	 * ## OPTIONS
	 *
	 * [<target>...]
	 * : WP-CLI alias, path, or SSH target (see global parameter --ssh). Defaults to current install.
	 *
	 * [--user=<id|login|email>]
	 * : Log in as specific user. Defaults to first administrator.
	 *
	 * [--timeout=<timeout>]
	 * : Time until expiration in seconds or units e.g. 1d 6h 30m
	 * ---
	 * default: 30
	 * ---
	 *
	 * [--open]
	 * : Automatically open the login URL in your system browser. Default true (unless --generate).
	 *
	 * [--url=<url>]
	 * : Use the <key> placeholder for exact control over the generated login URL.
	 *
	 * ## EXAMPLES
	 *
	 *     # Log in as bert
	 *     $ wp login --user=bert
	 *
	 *     # Log in to remote WordPress over SSH
	 *     $ wp login user@host
	 *
	 *     # Log in to WP-CLI alias @dev
	 *     $ wp login @dev
	 *
	 *     # Print login URL instead of opening it & set timeout to 5 minutes
	 *     $ wp login --no-open --timeout=300
	 *     https://example.com/login/ce24f50a0126d75694b3cf2dedb5a64d6f3636cb0ae3d08e2c8c509b511c7acc
	 *
	 *     # Generate login script for manual installation
	 *     $ wp login --generate --url=https://example.com
	 *     https://example.com/login/f4c42e9f5d854827bf47364d817025dc0da27b3c447bd70152de61d27234a9b3
	 *     <?php
	 *     ...
	 *
	 *     # Use query string i.e. if permalinks not supported
	 *     $ wp login --url='https://example.com/?login-key=<key>'
	 *
	 * @when before_wp_load
	 */
	public function __invoke( array $args, array $assoc_args ) : void {
		$assoc_args['url'] ??= WP_CLI::get_config( 'url' );

		$user = $assoc_args['user'] ?? WP_CLI::get_config( 'user' );
		$timeout = $assoc_args['timeout'] ?? null;
		$generate = $assoc_args['generate'] ?? false;
		$open = $assoc_args['open'] ?? ! $generate;

		if ( $generate && $open ) {
			WP_CLI::error( 'You cannot supply --generate and --open at the same time.' );
		}

		$this->user = $user;

		if ( $timeout && preg_match_all( '/([\d.]+)([dhms]?)/i', $timeout, $matches, PREG_SET_ORDER ) ) {
			$timeout = 0;

			foreach ( $matches as $match ) {
				$timeout += match ( strtolower( $match[2] ) ) {
					'd' => floatval( $match[1] ) * 3600 * 24,
					'h' => floatval( $match[1] ) * 3600,
					'm' => floatval( $match[1] ) * 60,
					default  => (float) $match[1],
				};
			}
		}

		$this->timeout = intval( $timeout );

		$mu_plugin = new MU_Plugin(
			name: 'Login',
			description: 'Automatic login.'
		);

		if ( $this->user ) {
			$mu_plugin->description = sprintf(
				'Automatic login for user <code>%s</code>',
				htmlentities( $this->user ),
			);
		}

		$remotes = Remote::resolve( $args ?: null, [
			'debug' => 'login',
		]);

		foreach ( $remotes as $remote ) {
			$url = $assoc_args['url'] ?? $remote->get_url();

			if ( ! parse_url( $url, PHP_URL_SCHEME ) ) {
				$url = 'https://' . ltrim( $url, '/' );
			}

			if ( ! str_contains( $url, '<key>' ) ) {
				if ( str_contains( $url, '?' ) ) {
					$url .= '&login=<key>';
				} else {
					$url = Utils\trailingslashit( $url ) . 'login/<key>';
				}
			}

			$key = bin2hex( random_bytes( 32 ) );

			$url = str_replace( '<key>', $key, $url );

			$code = $this->get_code( $url );

			$mu_plugin->slug = "login-$key";

			if ( $generate ) {
				WP_CLI::line( $url );
				WP_CLI::line( $mu_plugin->generate( $code ) );

			} elseif ( ! $mu_plugin->install( $code, $remote ) ) {
				continue;

			} elseif ( ! $open ) {
				WP_CLI::line( $url );

			} elseif ( ! Utils\open( $url ) ) {
				WP_CLI::warning( "Failed to open $url" );
			}
		}
	}

	public function get_code( string $url ) : string {
		$url = parse_url( $url );

		$uri = $url['path'] ?? '/';
		if ( isset( $url['query'] ) ) {
			$uri .= "?{$url['query']}";
		}

		$timeout = $this->timeout ? time() + $this->timeout : 0;
		$uri_var  = var_export( $uri, true );
		$user_var = var_export( $this->user, true );

		return <<<PHP
		add_action( 'parse_request', function () {
			\$uri = \$_SERVER['REQUEST_URI'] ?? null;

			if ( \$uri === null || ! hash_equals( $uri_var, \$uri ) ) {
				return;
			}

			@unlink( __FILE__ );

			if ( $timeout && time() > $timeout ) {
				wp_die( 'Login timed out.' );
			}

			if ( $user_var ) {
				foreach ( [ 'id', 'login', 'email' ] as \$field ) {
					\$user = get_user_by( \$field, $user_var );

					if ( \$user ) {
						break;
					}
				}

				if ( ! \$user ) {
					wp_die( 'User not found.' );
				}

			} else {
				\$user = current( get_users([
					'role'    => 'administrator',
					'number'  => 1,
					'order'   => 'ASC',
					'orderby' => 'ID',
				]));

				if ( ! \$user ) {
					wp_die( 'No administrator found.' );
				}
			}

			wp_set_auth_cookie( \$user->ID, true );

			wp_redirect( admin_url() );
			exit;
		}, 0 );
		PHP;
	}
}

if ( class_exists( WP_CLI::class ) ) {
	WP_CLI::add_command( 'login', WP_CLI_Login::class );
}
