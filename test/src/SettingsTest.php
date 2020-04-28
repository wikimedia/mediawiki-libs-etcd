<?php

namespace ActiveCollab\Etcd\Tests\Etcd;

use ActiveCollab\Etcd\Client;

/**
 * @package ActiveCollab\Etcd\Tests\Etcd
 */
class SettingsTest extends \PHPUnit\Framework\TestCase {
	/**
	 * Test default server value
	 */
	public function testDefaultServer() {
		$this->assertEquals( 'http://127.0.0.1:2379', ( new Client() )->getServer() );
	}

	/**
	 * Test set server
	 */
	public function testSetServer() {
		$this->assertEquals(
			'http://localhost:2379',
				( new Client() )->setServer( 'http://localhost:2379/' )->getServer()
		);
	}

	public function testSetInvalidServerUrlException() {
		$this->expectException( \InvalidArgumentException::class );
		new Client( 'invalid server url' );
	}

	/**
	 * Test automatic detection of HTTPS
	 */
	public function testHttpsDetection() {
		$reflection = new \ReflectionClass( Client::class );
		$is_https_property = $reflection->getProperty( 'is_https' );
		$is_https_property->setAccessible( true );

		$this->assertFalse( $is_https_property->getValue( new Client( 'http://127.0.0.1:2379' ) ) );
		$this->assertTrue( $is_https_property->getValue( new Client( 'https://127.0.0.1:2379' ) ) );
	}

	/**
	 * Test verify SSL peer can be
	 */
	public function testVerifySslPeerCanBeSet() {
		$this->assertTrue( ( new Client() )->getVerifySslPeer() );
		$this->assertFalse( ( new Client() )->verifySslPeer( false )->getVerifySslPeer() );
	}

	public function testExceptionOnMissingCustomCaFile() {
		$this->expectException( \InvalidArgumentException::class );
		( new Client() )->verifySslPeer( true, 'not a file' );
	}

	/**
	 * Test custom CA file can be set
	 */
	public function testCustomCaFileCanBeSet() {
		$this->assertEquals(
			__FILE__,
				( new Client() )->verifySslPeer( true, __FILE__ )->getCustomCaFile()
		);
	}

	public function testExceptionOnCustomCaFileWhenPeerIsNotVeified() {
		$this->expectException( \LogicException::class );
		( new Client() )->verifySslPeer( false, __FILE__ )->getCustomCaFile();
	}

	/**
	 * Test default sandbox path value
	 */
	public function testDefaultSandboxPath() {
		$this->assertEquals( '/', ( new Client() )->getSandboxPath() );
	}

	/**
	 * Test if sandbox path path is properly set
	 */
	public function testSetSandboxPath() {
		$this->assertEquals( '/root', ( new Client() )->setSandboxPath( 'root' )->getSandboxPath() );
		$this->assertEquals( '/root', ( new Client() )->setSandboxPath( 'root/' )->getSandboxPath() );
		$this->assertEquals( '/root', ( new Client() )->setSandboxPath( '/root/' )->getSandboxPath() );
		$this->assertEquals(
			'/a/bit/deeper/path',
				( new Client() )->setSandboxPath( '/a/bit/deeper/path/' )->getSandboxPath()
		);
	}

	/**
	 * Test default API version
	 */
	public function testDefaultApiVersion() {
		$this->assertEquals( 'v2', ( new Client() )->getApiVersion() );
	}

	/**
	 * Test set API version
	 */
	public function testSetApiVersion() {
		$this->assertEquals( 'v7', ( new Client() )->setApiVersion( 'v7' )->getApiVersion() );
	}

	/**
	 * Test if API version is used in key path
	 */
	public function testApiVersionIsUsedInKeyPath() {
		$this->assertEquals( '/v2/keys/path/to/key', ( new Client() )->getKeyPath( 'path/to/key' ) );
		$this->assertEquals(
			'/v7/keys/path/to/key', ( new Client() )->setApiVersion( 'v7' )->getKeyPath( 'path/to/key' )
		);
	}

	/**
	 * Test if sandbox path is used in key path
	 */
	public function testSandboxPathIsUsedInKeyPath() {
		$this->assertEquals( '/v2/keys/path/to/key', ( new Client() )->getKeyPath( 'path/to/key' ) );
		$this->assertEquals(
			'/v2/keys/root/is/cool/path/to/key',
				( new Client() )->setSandboxPath( 'root/is/cool' )->getKeyPath( 'path/to/key' )
		);
	}

	/**
	 * Test how things fit together
	 */
	public function testGetKeyUrl() {
		$client = ( new Client( 'http://localhost:2379', 'v7' ) )->setSandboxPath( 'awesome/root' );
		$this->assertInstanceOf( Client::class, $client );

		$this->assertEquals(
			'http://localhost:2379/v7/keys/awesome/root/path/to/key',
			$client->getKeyUrl( 'path/to/key' )
		);
	}
}
