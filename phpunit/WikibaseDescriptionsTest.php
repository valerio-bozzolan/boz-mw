<?php
// use the phpunit framework
use PHPUnit\Framework\TestCase;

/**
 * Test Query class
 */
final class WikibaseDescriptionsTest extends TestCase {

	public function testCreateEmpty() {
		$labels = new \wb\Descriptions();
		$this->assertEquals( count( $labels->getAll() ), 0 );
	}

	public function testGetLanguageValueEmpty() {
		$labels = new \wb\Descriptions();
		$this->assertEquals( $labels->getLanguageValue( 'it' ), null );
	}

	public function testSetGetLanguageValue() {
		$labels = new \wb\Descriptions();
		$labels->setLanguageValue( 'it', 'Pizza' );
		$this->assertEquals( $labels->getLanguageValue( 'it' ), 'Pizza' );
	}

	public function testSetOne() {
		$labels = new \wb\Descriptions();
		$label  = new \wb\Description( 'it', 'Asd' );
		$this->assertEquals( $labels->have( 'it' ), false );
		$this->assertEquals( count( $labels->getAll() ), 0 );

		$labels->set( $label );
		$this->assertEquals( $labels->have( 'it' ), true );
		$this->assertEquals( count( $labels->getAll() ), 1 );
	}

	public function testSetOverride() {
		$labels = new \wb\Descriptions();
		$label1 = new \wb\Description( 'it', 'Asd1' );
		$label2 = new \wb\Description( 'it', 'Asd2' );
		$label3 = new \wb\Description( 'it', 'Asd3' );
		$labels->set( $label1 );
		$labels->set( $label2 );
		$labels->set( $label3 );
		$this->assertEquals( count( $labels->getAll() ), 1 );
	}

}
