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
		
		public function testReturnsTokenFromServerGlobal(): void{
			$_SERVER['HTTP_AUTHORIZATION'] = 'Bearer server456';
			
			$reader = new RequestTokenReader();
			$token = $reader->getBearerToken();
			
			$this->assertSame('server456', $token);
			
			// Cleanup
			unset($_SERVER['HTTP_AUTHORIZATION']);
		}
		
		public function testReturnsTokenFromApacheFallback(): void{
			// Leeren, um Fallback zu erzwingen
			$_SERVER = [];
			
			$reader = new class extends RequestTokenReader{
				protected function getApacheRequestHeaders(): array{
					return ['Authorization' => 'Bearer apache789'];
				}
			};
			
			$token = $reader->getBearerToken();
			$this->assertSame('apache789', $token);
		}
		
		public function testReturnsNullWhenNoTokenFound(): void{
			$_SERVER = [];
			
			$reader = new class extends RequestTokenReader{
				protected function getApacheRequestHeaders(): array{
					return [];
				}
			};
			
			$token = $reader->getBearerToken();
			$this->assertNull($token);
		}
	}
	