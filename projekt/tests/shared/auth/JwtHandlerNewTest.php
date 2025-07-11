<?php
	require_once __DIR__ . '/../../base/IntegrationTestCase.php';
	require_once __DIR__ . '/../../../src/shared/auth/JwtHandler-new.php';
	
	use Shared\Auth\JwtHandlerNew;
	
	class JwtHandlerNewTest extends IntegrationTestCase{
		private JwtHandlerNew $jwt;
		
		protected function setUp(): void{
			parent::setUp();
			$this->jwt = new JwtHandlerNew('test-secret', 'HS256', 3600);
		}
		
		public function testGenerateTokenReturnsString(): void{
			$token = $this->jwt->generateToken(['user_id' => 42]);
			$this->assertIsString($token);
		}
		
		public function testValidateTokenReturnsPayloadForValidToken(): void{
			$token = $this->jwt->generateToken(['user_id' => 1]);
			$payload = $this->jwt->validateToken($token);
			
			$this->assertIsArray($payload);
			$this->assertSame(1, $payload['user_id']);
			$this->assertArrayHasKey('iat', $payload);
			$this->assertArrayHasKey('exp', $payload);
		}
		
		public function testValidateTokenReturnsErrorForInvalidToken(): void{
			$token = $this->jwt->generateToken(['user_id' => 1]);
			$manipulated = $token . 'xyz';
			
			$result = $this->jwt->validateToken($manipulated);
			
			$this->assertIsArray($result);
			$this->assertFalse($result['success']);
			$this->assertArrayHasKey('error', $result);
			$this->assertSame('shared/auth/validateToken', $result['source']);
		}
		
		public function testGetUserIdFromTokenReturnsCorrectId(): void{
			$token = $this->jwt->generateToken(['user_id' => 99]);
			$userId = $this->jwt->getUserIdFromToken($token);
			
			$this->assertSame(99, $userId);
		}
		
		public function testGetUserIdFromTokenReturnsNullIfUserIdMissing(): void{
			$token = $this->jwt->generateToken(['role' => 'admin']);
			$userId = $this->jwt->getUserIdFromToken($token);
			
			$this->assertNull($userId);
		}
		
		public function testGetUserIdFromTokenReturnsNullOnInvalidToken(): void{
			$userId = $this->jwt->getUserIdFromToken('not.a.valid.token');
			$this->assertNull($userId);
		}
	}