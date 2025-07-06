<?php
	require_once __DIR__ . '/../../base/DatabaseTestCasePreparation.php';
	require_once __DIR__ . '/../../../src/shared/auth/JwtHandler-new.php';
	
	use Shared\Auth\JwtHandlerNew;
	
	class JwtHandlerNewTest extends DatabaseTestCase{
		private JwtHandlerNew $jwt;
		
		protected function setUp(): void{
			parent::setUp();
			$this->jwt = new JwtHandlerNew('test-secret', 'HS256', 3600);
		}
		
		public function testGenerateTokenReturnsString(): void{
			$token = $this->jwt->generateToken(['user_id' => 42]);
			$this->assertIsString($token);
		}
	}