<?php
// use the phpunit framework
use PHPUnit\Framework\TestCase;

/**
 * Test Wikis classes
 */
final class WikisTest extends TestCase {

	/**
	 * Test that the wikis can be listed
	 */
	public function testListWikisSingleton() {

		$all = [];
		$wikis = web\MediaWikis::all();
		foreach( $wikis as $wiki ) {

			$classname = get_class( $wiki );

			// register it once (not called on duplicate)
			if( !array_key_exists( $classname, $all ) ) {
				$all[ $classname ] = 0;
			}

			// increase the counter (called on duplicates)
			$all[ $classname ]++;
		}


		$n = 0;
		foreach( $all as $wiki => $count ) {
			$n += $count;
		}

		// all the wikis must be presented just once, no more, no less
		$this->assertEquals( $n, count( $all ) );
	}

}

