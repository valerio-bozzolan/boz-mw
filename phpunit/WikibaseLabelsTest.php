<?php
// use the phpunit framework
use PHPUnit\Framework\TestCase;

/**
 * Test Query class
 */
final class WikibaseLabelsTest extends TestCase {

	public function testCreateEmpty() {
		$labels = new \wb\Labels();
		$this->assertEquals( count( $labels->getAll() ), 0 );
	}

	public function testSetOne() {
		$labels = new \wb\Labels();
		$label  = new \wb\Label( 'it', 'Asd' );
		$this->assertEquals( $labels->have( 'it' ), false );
		$this->assertEquals( count( $labels->getAll() ), 0 );

		$labels->set( $label );
		$this->assertEquals( $labels->have( 'it' ), true );
		$this->assertEquals( count( $labels->getAll() ), 1 );
	}

	public function testSetOverride() {
		$labels = new \wb\Labels();
		$label1 = new \wb\Label( 'it', 'Asd1' );
		$label2 = new \wb\Label( 'it', 'Asd2' );
		$label3 = new \wb\Label( 'it', 'Asd3' );
		$labels->set( $label1 );
		$labels->set( $label2 );
		$labels->set( $label3 );
		$this->assertEquals( count( $labels->getAll() ), 1 );
	}

}
