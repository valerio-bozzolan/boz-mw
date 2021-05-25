<?php
// use the phpunit framework
use PHPUnit\Framework\TestCase;

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

}
