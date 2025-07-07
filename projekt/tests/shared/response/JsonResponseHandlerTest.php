<?php
	require_once __DIR__ . '/../../base/DatabaseTestCasePreparation.php';
	require_once __DIR__ . '/../../../src/shared/response/JsonResponseHandler.php';
	require_once __DIR__ . '/TestableJsonResponseHandler.php';
	
	use Shared\Response\JsonResponseHandler;
	
	class JsonResponseHandlerTest extends DatabaseTestCase{
		public function setUp(): void{
			parent::setUp();
		}
		
		public function testSuccessOutputsExpectedJsonAndExits(): void{
			$handler = new class extends TestableJsonResponseHandler{				
				public function success(array $data = [], int $statusCode = 200): void{
					http_response_code($statusCode);
					echo(json_encode([
						'success' => true,
						'data' => $data
					]));
					$this->exit();
				}
			};
			
			// Buffer starten
			ob_start();
			$data = ['foo' => 'bar'];
			$handler->success($data, 201);
			// Buffer auslesen und beenden
			$output = ob_get_clean();
			
			$json = json_decode($output, true);
			
			$this->assertSame(201, http_response_code());
			$this->assertTrue($json['success']);
			$this->assertSame($data, $json['data']);
			$this->assertTrue($handler->hasExited());
		}
	}