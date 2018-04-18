<?php
# Boz-MW - Another MediaWiki API handler in PHP
# Copyright (C) 2018 Valerio Bozzolan
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU Affero General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU Affero General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program. If not, see <http://www.gnu.org/licenses/>.

# MediaWiki
namespace mw;

/**
 * Handle MediaWiki API tokens
 *
 * @see https://it.wikipedia.org/w/api.php?action=help&modules=query%2Btokens
 */
class Tokens {

	/**
	 * To create an account
	 */
	const CREATE_ACCOUNT = 'createaccount';

	/**
	 * To do some actions like edit
	 */
	const CSRF = 'csrf';

	/**
	 * To delete the global account
	 */
	const DELETE_GLOBAL_ACCOUNT = 'deleteglobalaccount';

	/**
	 * To login
	 */
	const LOGIN = 'login';

	/**
	 * To patrol
	 */
	const PATROL = 'patrol';

	/**
	 * To do a rollback
	 */
	const ROLLBACK = 'rollback';

	/**
	 * To set the global account status
	 */
	const SET_GLOBAL_ACCOUNT_STATUS = 'setglobalaccountstatus';

	/**
	 * To set user rights
	 */
	const USER_RIGHTS = 'userrights';

	/**
	 * To watch a page
	 */
	const WATCH = 'watch';

	/**
	 * MediaWiki API object to be used to retrieve the tokens
	 *
	 * @var \mw\API
	 */
	private $api;

	/**
	 * An associative array of obtained token values
	 *
	 * @var array
	 */
	private $tokensCache = [];

	/**
	 * A whitelist of all the available tokens
	 */
	private $whitelist;

	/**
	 * Create a Tokens handler
	 *
	 * @param $api \mw\API MediaWiki API object
	 */
	public function __construct( \mw\API $api ) {
		// Dependency injection
		$this->api = $api;

		// populate the whitelist
		$self = new \ReflectionClass( $this );
		$this->whitelist = $self->getConstants();
	}

	/**
	 * Get the value of a token, eventually fetching it
	 *
	 * @param $token string Token name
	 * @return string Token value
	 */
	public function get( $token ) {
		return $this->require( [ $token ] )->tokensCache[ $token ];
	}

	/**
	 * Check if a token exists
	 *
	 * @param $token Token name
	 * @return bool
	 */
	public function exists( $token ) {
		return in_array( $token, $this->whitelist, true );
	}

	/**
	 * Require multiple tokens
	 *
	 * This will fetch only the uncached
	 *
	 * @param $tokens array Token names
	 * @return self
	 */
	public function require( $tokens ) {
		$missings = [];
		foreach( $tokens as $token ) {
			if( ! $this->exists( $token ) ) {
				throw new \InvalidArgumentException( 'formally unexisting token' );
			}
			if( ! isset( $this->tokensCache[ $token ] ) ) {
				$missings[] = $token;
			}
		}
		if( $missings ) {
			$this->fetch( $missings );
		}
		return $this;
	}

	/**
	 * Invalidate the value of a token
	 *
	 * Useful to force a future re-fetch of it
	 *
	 * @param $token string Token name
	 * @return self
	 */
	public function invalidate( $token ) {
		unset( $this->tokensCache[ $token ] );
		return $this;
	}

	/**
	 * Fetch tokens and cache
	 *
	 * @param $tokens array Token names
	 * @see https://it.wikipedia.org/w/api.php?action=help&modules=query%2Btokens
	 */
	private function fetch( $tokens ) {
		$response_tokens = $this->api->fetch( [
			'action' => 'query',
			'meta'   => 'tokens',
			'type'   => $tokens,
		] )->query->tokens;
		foreach( $tokens as $token ) {
			$token_name = $token . 'token';
			if( ! isset( $response_tokens->{ $token_name } ) ) {
				throw new \Exception( 'the server is not giving correctly one of the asked token' );
			}
			$this->tokensCache[ $token ] = $response_tokens->{ $token_name };
		}
	}
}
