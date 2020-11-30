<?php
# Boz-MW - Another MediaWiki API handler in PHP
# Copyright (C) 2018, 2019, 2020 Valerio Bozzolan
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

# command line interface
namespace cli;

/**
 * Register command line parameters and read their arguments
 */
class Opts {

	/**
	 * All the registered parameters
	 *
	 * @var array
	 */
	private $params;

	/**
	 * Internal flag cache
	 *
	 * @var bool
	 */
	private $read = false;

	/**
	 * Constructor
	 *
	 * @param $params array Parameters
	 */
	public function __construct( $params = [] ) {
		$this->register( $params );
	}

	/**
	 * Static instance
	 */
	private static $_instance;

	/**
	 * Get a singleton instance
	 *
	 * @param $params array
	 * @return self
	 */
	public static function instance() {
		if( ! isset( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Register multiple parameters
	 *
	 * @param $params array
	 * @return self
	 */
	public function register( $params ) {
		foreach( $params as $param ) {
			$this->add( $param );
		}
		return $this;
	}

	/**
	 * Register a new parameter
	 *
	 * @param $param object Parameter
	 * @return self
	 */
	public function add( Param $param ) {
		$this->params[] = $param;
		$this->read = false; // invalidate the cache
		return $this;
	}

	/**
	 * Populate all parameters with their arguments
	 *
	 * @see getopt()
	 * @return self
	 */
	public function populate() {
		if( ! $this->read ) {
			// prepare getopt() syntax
			$shorts = '';
			$longs  = [];
			foreach( $this->params as $opt ) {
				$shorts .= $opt->getShortOptFormat();
				if( $opt->hasLongName() ) {
					$longs[] = $opt->getLongOptFormat();
				}
			}

			// get command line arguments
			$args = getopt( $shorts, $longs );

			// fill all parameters with their arguments
			foreach( $this->params as $opt ) {

				// try with the short name
				if( $opt->hasShortName() ) {
					$name = $opt->getShortName();
					if( isset( $args[ $name ] ) ) {
						$opt->setValue( $opt->isFlag() ? true : $args[ $name ] );
						continue;
					}
				}

				// try with the long name
				if( $opt->hasLongName() ) {
					$name = $opt->getLongName();
					if( isset( $args[ $name ] ) ) {
						$opt->setValue( $opt->isFlag() ? true : $args[ $name ] );
					}
				}

			}

			$this->read = true;
		}
		return $this;
	}

	/**
	 * Find a registered param from a whatever name, the long or short on
	 *
	 * @param $name string Parameter name (long or short)
	 * @return Param
	 */
	protected function findParamFromName( $name ) {
		foreach( $this->params as $opt ) {
			if( $opt->isName( $name ) ) {
				return $opt;
			}
		}
		throw new \InvalidArgumentException( 'unregistered parameter ' . $name );
	}

	/**
	 * Get all the registered parameters
	 *
	 * @return array
	 */
	public function getParams() {
		return $this->params;
	}

	/**
	 * Get a single argument
	 *
	 * @param $name string Param name (short or long)
	 * @param $default string Default param value
	 * @return string|null
	 */
	public function getArg( $name, $default = null ) {
		return $this->populate()->findParamFromName( $name )->getValue( $default );
	}

	/**
	 * Get all the unnamed arguments
	 *
	 * @return array
	 */
	public static function unnamedArguments() {
		$args = [];
		foreach( $GLOBALS[ 'argv' ] as $arg ) {
			if( '-' !== $arg[ 0 ] ) {
				$args[] = $arg;
			}
		}
		array_shift( $args );
		return $args;
	}

	/**
	 * Print all the parameters
	 */
	public function printParams() {

		foreach( $this->getParams() as $param ) {

			$commands = [];

			if( $param->hasLongName() ) {
				$commands[] = '--' . $param->getLongName();
			}

			if( $param->hasShortName() ) {
				$commands[] = '-' . $param->getShortName();
			}

			$command = implode( '|', $commands );
			if( $command && ! $param->isFlag() ) {
				$command .= $param->isValueOptional()
					? '=[VALUE]'
					: '=VALUE';
			}

			printf( ' % -20s ', $command );

			if( $param->hasDescription() ) {
				echo ' ' . $param->getDescription();
			}

			echo "\n";
		}
	}
}
