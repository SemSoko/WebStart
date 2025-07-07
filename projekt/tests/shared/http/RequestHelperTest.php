<?php
	require_once __DIR__ . '/../../base/DatabaseTestCasePreparation.php';
	require_once __DIR__ . '/../../../src/shared/http/RequestHelper.php';
	
	use Shared\Http\RequestHelper;
	
	class RequestHelperTest extends DatabaseTestCase{
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
			
		}
		
		public function testHandlesMissingApacheFunctionGracefully(): void{
			
		}
	}