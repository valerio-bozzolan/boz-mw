<?php
// use the phpunit framework
use PHPUnit\Framework\TestCase;

use wb\DataModel;

/**
 * Test Query class
 */
final class WikibaseDataModelTest extends TestCase {

	public function testSetLabel() {
		$data = new DataModel();
		$data->setLabelValue( 'it', 'pizza' );
		$asd = $data->getLabelValue( 'it' );
		$this->assertEquals( $asd, 'pizza' );
	}

	public function testSetDescription() {
		$data = new DataModel();
		$data->setDescriptionValue( 'it', 'pizza' );
		$asd = $data->getDescriptionValue( 'it' );
		$this->assertEquals( $asd, 'pizza' );
	}

}
