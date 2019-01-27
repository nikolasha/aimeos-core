<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2019
 */


namespace Aimeos\MW\View\Helper\Number;


class LocaleTest extends \PHPUnit\Framework\TestCase
{
	private $view;


	protected function setUp()
	{
		$this->view = new \Aimeos\MW\View\Standard();
	}


	protected function tearDown()
	{
		unset( $this->view );
	}


	public function testTransform()
	{
		$object = new \Aimeos\MW\View\Helper\Number\Locale( $this->view, 'de' );

		$this->assertEquals( '1,00', $object->transform( 1 ) );
		$this->assertEquals( '1,00', $object->transform( 1.0 ) );
		$this->assertEquals( '1.000,00', $object->transform( 1000.0 ) );
	}


	public function testTransformRule()
	{
		$object = new \Aimeos\MW\View\Helper\Number\Locale( $this->view, 'de_AT', '#,##0.###' );

		$this->assertEquals( '1,000', $object->transform( 1, 3 ) );
		$this->assertEquals( '1,000', $object->transform( 1.0, 3 ) );
		$this->assertEquals( '1 000,000', $object->transform( 1000.0, 3 ) );
	}
}
