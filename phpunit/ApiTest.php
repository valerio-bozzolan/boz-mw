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
	public function testItwikiQueryCategoryMembers() {

		$limit = 3;

		$queries =
			itwiki()->createQuery( [
				'action'  => 'query',
				'list'    => 'categorymembers',
				'cmtitle' => 'Categoria:Software con licenza GNU GPL',
				'cmlimit' => $limit,
			] );

		$i = 0;
		foreach( $queries as $query ) {
			foreach( $query->query->categorymembers as $member ) {

				$member->pageid;
				$member->ns;
				$member->title;

				// count the pages
				$i++;
			}

			// it's not necessary to do consecutive queries
			// this is just a test on the first page
			break;
		}

		// check whenever we were able to find $limit pages
		$this->assertEquals( $i, $limit );
	}

}
