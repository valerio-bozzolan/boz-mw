<?php
// use the phpunit framework
use PHPUnit\Framework\TestCase;

/**
 * Test Query class
 */
final class ApiTest extends TestCase {

	/**
	 * Test the itwiki API with Category members
	 */
	public function testItWikiCategoryMembers() {

		$queries =
			itwiki()->createQuery( [
				'action'  => 'query',
				'list'    => 'categorymembers',
				'cmtitle' => 'Categoria:Software con licenza GNU GPL',
				'cmlimit' => 3,
			] );

		$i = 0;

		foreach( $queries as $query ) {

			foreach( $query->query->categorymembers as $member ) {

				$member->pageid;
				$member->ns;
				$member->title;

				$i++;
			}

			// don't do other requests
			break;
		}

		// it should return just 3 elements for this series
		$this->assertEquals( $i, 3 );
	}

}
