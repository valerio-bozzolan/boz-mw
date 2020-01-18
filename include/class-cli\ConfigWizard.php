<?php
# Boz-MW - Another MediaWiki API handler in PHP
# Copyright (C) 2019 Valerio Bozzolan
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

namespace cli;

/**
 * Configuration loader and wizard
 *
 * It can help generating a persistent configuration file
 */
class ConfigWizard {

	/**
	 * Require a configuration file or create one
	 *
	 * @param string $configuration_path
	 */
	public static function requireOrCreate( $configuration_path ) {
		if( !file_exists( $configuration_path ) ) {
			echo " _____________________________________ \n";
			echo "|                                     |\n";
			echo "| WELCOME IN THE CONFIGURATION WIZARD!|\n";
			echo "|_____________________________________|\n";
			echo "\n";

			$variables = [];

			$variables[] = self::askVariableContent(
				'mw\API::$DEFAULT_USERNAME',
				"Generated username from [[Special:BotPasswords]]",
				"Please paste here your generated username from [[Special:BotPasswords]]",
				[
					"First of all you must choose an user to be used for your activities.",
					"If you don't know if you need a new dedicated user first read your wiki bot policies.",
					"If you work very slowly I think you can just use your real account. Anyway a dedicated account is better.",
					"",
					"Now please login in your wiki, with THAT user, and visit the page [[Special:BotPasswords]].",
					"",
					"From that page fill the \"Create a new bot password\" form with a useful name",
					"Example name:",
					"  VolleyImportStuff\"",
					"",
					"Now from check the permissions you will use and then click again on \"Create\".",
					"",
					"Example generated username (note that it has '@'):",
					"  JhonDoo@VolleyImportStuff",
				]
			);

			$variables[] = self::askVariableContent(
				'mw\API::$DEFAULT_PASSWORD',
				"Generated bot password",
				"Please paste here your generated bot password", [
					"Note that it shold be very long and without '@'.",
				]
			);

			$content = implode( "\n", $variables );
			$content = "<?php\n" . $content;

			// write the configuration file
			echo "Writing '$configuration_path'...\n";
			file_put_contents( $configuration_path, $content );
		}

		// include the configuration file
		require( $configuration_path );
	}

	/**
	 * Do a question about a variable value
	 *
	 * @param string $variable
	 * @param string $question
	 * @param string $comment
	 */
	public static function askVariableContent( $variable, $title, $question, $comment_lines = [] ) {

		// start printing some hints
		echo $title . "\n\n" . implode( $comment_lines, "\n" ) . "\n\n";

		// ask the variable content
		do {
			$value = Input::askInput( $question . " - Then press ENTER:" );
		} while( !$value );

		// put the question above the comment lines, with an empty line before
		$comment_lines_with_question = array_merge( [ $title, '' ], $comment_lines );

		// create a comment to put above the variable
		$text = self::toComment( $comment_lines_with_question );

		// save in form of $foo="bar";
		$text .= sprintf(
			"%s = \"%s\";\n",
			$variable,
			addcslashes( $value, '"')
		);

		// draw a cosmetic separation line
		echo str_repeat( "-", 50 ) . "\n\n\n\n";

		return $text;
	}

	/**
	 * Put some text lines inside a PHP comment
	 *
	 * @param $lines array
	 * @return       string
	 */
	private static function toComment( $lines ) {
		foreach( $lines as &$line ) {
			if( $line ) {
				$line = " * $line";
			} else {
				$line = " *";
			}
		}
		$text = implode( "\n", $lines );
		return "/**\n$text\n */\n";
	}
}
