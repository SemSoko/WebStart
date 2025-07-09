<?php
	require_once __DIR__ . '/../../base/DatabaseTestCasePreparation.php';
	require_once __DIR__ . '/../../../src/shared/middleware/AuthMiddleware.php';
	require_once __DIR__ . '/../../../src/shared/auth/AuthServiceInterface.php';
	require_once __DIR__ . '/../../../src/shared/http/RequestTokenReaderInterface.php';
	require_once __DIR__ . '/../response/TestableJsonResponseHandler.php';
	
	use Shared\Middleware\AuthMiddleware;
	use PHPUnit\Framework\MockObject\MockObject;
	use Shared\Auth\AuthServiceInterface;
	use Shared\Http\RequestTokenReaderInterface;
	use Shared\Response\ResponseHandlerInterface;
	use Tests\Shared\Response\TestableJsonResponseHandler;
	
	class AuthMiddlewareTest extends DatabaseTestCase{
		private MockObject $authService;
		private MockObject $tokenReader;
		private MockObject $response;
		
		public function setUp(): void{
			parent::setUp();
			
			$this->authService = $this->createMock(AuthServiceInterface::class);
			$this->tokenReader = $this->createMock(RequestTokenReaderInterface::class);
			$this->response = $this->createMock(ResponseHandlerInterface::class);
		}
		
		public function testReturnsDebugWhenNoTokenProvided(): void{
			$this->tokenReader
				->method('getBearerToken')
				->willReturn(null);
				
			$response = new TestableJsonResponseHandler();
			
			$middleware = new AuthMiddleware(
				$this->authService,
				$response,
				$this->tokenReader
			);
			
			$this->expectException(\RuntimeException::class);
			$this->expectExceptionMessage('Mocked debug: Kein Token uebergeben (401)');
			
			// Test: Methode aufrufen -> erwartet, dass debug() aufgerufen wird
			$middleware->handle();
		}
		
		public function testReturnsDebugWhenTokenInvalid(): void{
			$this->tokenReader
				->method('getBearerToken')
				->willReturn('invalid-token');
			
			$this->authService
				->method('getUserId')
				->with('invalid-token')
				->willReturn(null);
			
			$response = new TestableJsonResponseHandler();
			
			$middleware = new AuthMiddleware(
				$this->authService,
				$response,
				$this->tokenReader
			);
			
			$this->expectException(\RuntimeException::class);
			$this->expectExceptionMessage('Mocked debug: Ungueltiger oder abgelaufener Token (401)');
			
			$middleware->handle();
		}
		
		public function testReturnsUserIdWhenTokenIsValid(): void{
			$this->tokenReader
				->method('getBearerToken')
				->willReturn('valid-token');
			
			$this->authService
				->method('getUserId')
				->with('valid-token')
				->willReturn(42);
			
			$middleware = new AuthMiddleware(
				$this->authService,
				new TestableJsonResponseHandler(),
				$this->tokenReader
			);
			
			$result = $middleware->handle();
			$this->assertSame(42, $result);
		}
		
		public function testTokenReaderIsCalledDuringHandle(): void{
			$this->tokenReader
				->expects($this->once())
				->method('getBearerToken')
				->willReturn('test-token');
			
			$this->authService
				->method('getUserId')
				->willReturn(42);
			
			$middleware = new AuthMiddleware(
				$this->authService,
				new TestableJsonResponseHandler(),
				$this->tokenReader
			);
			
			$middleware->handle();
		}
	}