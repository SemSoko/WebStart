<?php
	require_once __DIR__ . '/../../base/IntegrationTestCase.php';
	require_once __DIR__ . '/../../../src/shared/auth/JwtAuthService.php';
	require_once __DIR__ . '/../../../src/shared/auth/JwtHandler-new.php';
	
	use Shared\Auth\JwtHandlerNew;
	use Shared\Auth\JwtAuthService;
	
	class JwtAuthServiceTest extends IntegrationTestCase{
		private JwtHandlerNew $jwt;
		private JwtAuthService $jwtAuthService;
		
		public function setUp(): void{
			parent::setUp();
			$this->jwt = new JwtHandlerNew('test-secret', 'HS256', 3600);
			$this->jwtAuthService = new JwtAuthService($this->jwt);
		}
		
		public function testGetUserIdReturnsCorrectIdForValidToken(): void{
			$token = $this->jwt->generateToken(['user_id' => 23]);
			$userId = $this->jwtAuthService->getUserId($token);
			
			$this->assertSame(23, $userId);
		}
		
		public function testGetUserIdThrowsExceptionForInvalidToken(): void{
			$token = $this->jwt->generateToken(['user_id' => 44]);
			$manipulated = $token . '213s';
			
			$this->expectException(RuntimeException::class);
			$this->expectExceptionMessage('Token ungueltig oder User-ID fehlt.');
			
			$userId = $this->jwtAuthService->getUserId($manipulated);
		}
		
		public function testGetUserIdThrowsExceptionIfIdIsMissing(): void{
			$token = $this->jwt->generateToken(['role' => 'admin']);
			
			$this->expectException(RuntimeException::class);
			$this->expectExceptionMessage('Token ungueltig oder User-ID fehlt.');
			
			$userId = $this->jwtAuthService->getUserId($token);
		}
	}