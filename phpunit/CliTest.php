<?php
// use the phpunit framework
use PHPUnit\Framework\TestCase;

use cli\Log;

/**
 * Test Query class
 */
final class CliTest extends TestCase {

	/**
	 * Test the bracket and glue
	 */
	public function testAddArgsWithDefaults() {

		$options = new \cli\Opts();

		// register some dummy parameters
		$options
			->addFlag(   "luser", "l", "Check if you are a luser or not"  )
			->addValued( "asd",   "a", "Set your asd message", "FUCK YOU" )
			->addFlag(   "lamer", "z", "Check if you are a lamer or not"  );

		// check the asd argument (unpresent, so get default)
		$asd = $options->get( 'asd' );

		$this->assertEquals( $asd, "FUCK YOU" );
	}

	/**
	 * Test the bracket and glue
	 */
	public function testAddArgsWithoutDefaultButSuggestedLater() {

		$options = new \cli\Opts();

		// register some dummy parameters
		$options->addValued( "asd", "a", "Set your asd message" );

		// check the asd argument (unpresent, so get default)
		$asd = $options->get( 'asd', "DEFAULT ASD" );

		$this->assertEquals( $asd, "DEFAULT ASD" );
	}

	/**
	 * Test the bracket and glue
	 */
	public function testAddArgsWithoutDefaultAtAll() {

		$options = new \cli\Opts();

		// register some dummy parameters
		$options->addValued( "asd", "a", "Set your asd message" );

		// check the asd argument (unpresent, so get default)
		$asd = $options->get( 'asd' );

		$this->assertEquals( $asd, null );
	}

	/**
	 * Test the bracket and glue
	 */
	public function testAddArgsAndGetWithDefault() {

		$options = new \cli\Opts();

		// register some dummy parameters
		$options->addFlag(   "luser", "l", "Check if you are a luser or not" );

		// NOTE: the getArg() is deprecated but should be available
		$asd = $options->getArg( 'luser', 'miao' );

		$this->assertEquals( $asd, "miao" );
	}

	/**
	 * Test the bracket and glue
	 */
	public function testGetAll() {

		$options = new \cli\Opts();

		// register some dummy parameters
		$options
			->addFlag(   "luser", "l",  "Check if you are a luser or not"  )
			->addValued( "asd",   "a",  "Set your asd message", "FUCK YOU" )
			->addFlag(   "lamer", null, "Check if you are a lamer or not"  );

		// check the asd argument
		$all = $options->getAll();

		$this->assertEquals( 3, count( $all ) );
	}

	public function testCliShortcut() {
		$this->assertEquals( cli_options() instanceof \cli\Opts, true );
	}

	/**
	 * Test that terminal columns can be parsed.
	 */
	public function testCliTerminalColumns() {
		putenv('COLUMNS=17');
		$columns = Log::parseAvailableTerminalColumns();
		$this->assertEquals(17, $columns);

		putenv('COLUMNS=18');
		$columns = Log::parseAvailableTerminalColumns();
		$this->assertEquals(18, $columns);

		// Test that in a small terminal the first line is capped exactly
		// at that terminal limit, and the remaining lines are smaller than
		// that.
		putenv('COLUMNS=80');
		$columns = Log::availableTerminalColumns();
		$this->assertEquals(80, $columns);
		$min_lines = 5;
		$long_message = str_repeat('VeryLongString', $columns * $min_lines);
		ob_start();
		Log::info($long_message);
		$logged_content = ob_get_clean();
		$logged_content_lines = explode("\n", $logged_content);
		$this->assertGreaterThan($min_lines, count($logged_content_lines), "Test lines are splitted");
		$first_line = array_shift($logged_content_lines);
		$this->assertEquals($columns, mb_strlen($first_line), "Test first line fitting the whole terminal");
		foreach ($logged_content_lines as $logged_content_line) {
			$this->assertTrue(mb_strlen($logged_content_line) <= $columns, "Test other lines not exceeding the space");
		}

		Log::doNotFillTerminalColumns();
		$long_message = str_repeat('VeryLongString', 1000);
		ob_start();
		Log::info($long_message);
		$logged_content = trim(ob_get_clean());
		$logged_content_lines = explode("\n", $logged_content);
		$this->assertEquals(1, count($logged_content_lines), "Test single line to adhere options");
	}

}
