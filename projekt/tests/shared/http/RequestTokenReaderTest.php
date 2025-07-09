<?php
	require_once __DIR__ . '/../../base/DatabaseTestCasePreparation.php';
	require_once __DIR__ . '/../../../src/shared/http/RequestTokenReader.php';
	
	use Shared\Http\RequestTokenReader;
	
	class RequestTokenReaderTest extends DatabaseTestCase{
		public function setUp(): void{
			parent::setUp();
		}
		
		public function testReturnsTokenFromDirectHeader(): void{
			$reader = new RequestTokenReader();
			$token = $reader->getBearerToken('Bearer abc123');
			
			$this->assertSame('abc123', $token);
		}
		
		public function testReturnsNullForInvalidHeaderSame(): void{
			$reader = new RequestTokenReader();
			$token = $reader->getBearerToken('Basic abc123');
			
			$this->assertNull($token);
		}
	}
	