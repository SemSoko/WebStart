<?php
	require_once __DIR__ . '/../../base/UnitTestCase.php';
	require_once __DIR__ . '/../../../src/shared/http/RequestHelper.php';
	
	use Shared\Http\RequestHelper;
	use Tests\Base\UnitTestCase;
	
	class RequestHelperTest extends UnitTestCase{
		public function setUp(): void{
			parent::setUp();
		}
		
		public function testReturnsTokenWhenHeaderPassedDirectly(): void{
			$token = RequestHelper::getBearerToken('Bearer abc123');
			
			$this->assertSame('abc123', $token);
		}
		
		public function testReturnsNullForInvalidDirectHeader(): void{
			$token = RequestHelper::getBearerToken('Basic abc123');
			$this->assertNull($token);
		}
		
		public function testReturnsTokenFromServerGlobal(): void{
			$_SERVER['HTTP_AUTHORIZATION'] = 'Bearer serverToken456';
			
			$token = RequestHelper::getBearerToken();
			
			$this->assertSame('serverToken456', $token);
			
			// Cleanup, wichtig fuer Testisolation
			unset($_SERVER['HTTP_AUTHORIZATION']);
		}
		
		public function testReturnsTokenFromApacheHeadersFallback(): void{
			// Temporaere Subklasse mit Mock-Verhalten.
			$mockedHelper = new class extends RequestHelper{
				public static function getApacheRequestHeaders(): array{
					return ['Authorization' => 'Bearer fromApache789'];
				}
			};
			
			// $_SERVER bewusst leeren, um Fallback zu erzwingen.
			unset($_SERVER['HTTP_AUTHORIZATION']);
			
			$token = $mockedHelper::getBearerToken();
			
			$this->assertSame('fromApache789', $token);
		}
		
		public function testReturnsNullWhenNoValidSourceFound(): void{
			// $_SERVER explizit leeren (zur Sicherheit)
			$_SERVER = [];
			
			// Subklasse mit leerem apache_headers-Mock
			$mockedHelper = new class extends RequestHelper{
				public static function getApacheRequestHeaders(): array{
					// Simuliert: Keine Fallback-Header
					return [];
				}
			};
			
			// Kein Header uebergeben
			$result = $mockedHelper::getBearerToken();
			$this->assertNull($result);
		}
		
		public function testHandlesMissingApacheFunctionGracefully(): void{
			// Sicherstellen, dass auch dort kein Header ist
			$_SERVER = [];
			
			$mockedHelper = new class extends RequestHelper{
				public static function getApacheRequestHeaders(): array{
					// Simuliert, dass apache_request_headers nicht existiert
					return [];
				}
			};
			
			$result = $mockedHelper::getBearerToken();
			$this->assertNull($result);
		}
	}