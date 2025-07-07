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
			
		}
		
		public function testReturnsTokenFromServerGlobal(): void{
			
		}
		
		public function testReturnsTokenFromApacheHeadersFallback(): void{
			
		}
		
		public function testReturnsNullWhenNoValidSourceFound(): void{
			
		}
		
		public function testHandlesMissingApacheFunctionGracefully(): void{
			
		}
	}