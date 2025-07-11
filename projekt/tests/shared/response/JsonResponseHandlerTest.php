<?php
	require_once __DIR__ . '/../../base/UnitTestCase.php';
	require_once __DIR__ . '/../../../src/shared/response/JsonResponseHandler.php';
	require_once __DIR__ . '/TestableJsonResponseHandler.php';
	
	use Shared\Response\JsonResponseHandler;
	use Tests\Base\UnitTestCase;
	
	class JsonResponseHandlerTest extends UnitTestCase{
		public function setUp(): void{
			parent::setUp();
		}
		
		public function testSuccessOutputsExpectedJsonAndExits(): void{
			$handler = new class extends TestableJsonResponseHandler{				
				public function runSuccess(array $data, int $code): void{
					parent::success($data, $code);
				}
			};
			
			// Buffer starten
			ob_start();
			$data = ['foo' => 'bar'];
			$handler->runSuccess($data, 201);
			// Buffer auslesen und beenden
			$output = ob_get_clean();
			
			$json = json_decode($output, true);
			
			$this->assertSame(201, http_response_code());
			$this->assertTrue($json['success']);
			$this->assertSame($data, $json['data']);
			$this->assertTrue($handler->hasExited());
		}
		
		public function testSuccessDefaultsToEmptyDataAndStatus200(): void{
			$handler = new class extends TestableJsonResponseHandler{
				public function runSuccess(): void{
					// ohne Argumente
					parent::success();
				}
			};
			
			ob_start();
			$handler->runSuccess();
			$output = ob_get_clean();
			
			$json = json_decode($output, true);
			
			$this->assertSame(200, http_response_code());
			$this->assertTrue($json['success']);
			$this->assertSame([], $json['data']);
			$this->assertTrue($handler->hasExited());
		}
		
		public function testErrorOutputsExpectedJsonAndExits(): void{
			$handler = new class extends TestableJsonResponseHandler{
				public function runError(string $message, int $code): void{
					parent::error($message, $code);
				}
			};
			
			ob_start();
			$handler->runError('Validation failed', 422);
			$output = ob_get_clean();
			
			$json = json_decode($output, true);
			
			$this->assertSame(422, http_response_code());
			$this->assertFalse($json['success']);
			$this->assertSame('Validation failed', $json['message']);
			$this->assertTrue($handler->hasExited());
		}
		
		public function testErrorDefaultsToStatusCode400(): void{
			$handler = new class extends TestableJsonResponseHandler{
				public function runError(): void{
					parent::error('Bad input');
				}
			};
			
			ob_start();
			$handler->runError();
			$output = ob_get_clean();
			
			$json = json_decode($output, true);
			
			$this->assertSame(400, http_response_code());
			$this->assertFalse($json['success']);
			$this->assertSame('Bad input', $json['message']);
			$this->assertTrue($handler->hasExited());
		}
		
		public function testDebugOutputsExpectedJsonWithDetailsAndExits(): void{
			$handler = new class extends TestableJsonResponseHandler{
				public function runDebug(string $message, array $details, int $code): void{
					parent::debug($message, $details, $code);
				}
			};
			
			ob_start();
			$details = ['line' => 42, 'file' => 'somefile.php'];
			$handler->runDebug('Detailed error', $details, 500);
			$output = ob_get_clean();
			
			$json = json_decode($output, true);
			
			$this->assertSame(500, http_response_code());
			$this->assertFalse($json['success']);
			$this->assertSame('Detailed error', $json['message']);
			$this->assertTrue($handler->hasExited());
		}
		
		public function testDebugCanHandleEmptyDetails(): void{
			$handler = new class extends TestableJsonResponseHandler{
				public function runDebug(): void{
					parent::debug('Error occured', []);
				}
			};
			
			ob_start();
			$handler->runDebug();
			$output = ob_get_clean();
			
			$json = json_decode($output, true);
			
			$this->assertSame(500, http_response_code());
			$this->assertFalse($json['success']);
			$this->assertSame('Error occured', $json['message']);
			$this->assertSame([], $json['debug']);
			$this->assertTrue($handler->hasExited());
		}
		
		public function testStatusOutputsExpectedJsonAndExits(): void{
			$handler = new class extends TestableJsonResponseHandler{
				public function runStatus(string $message, bool $success, int $code): void{
					parent::status($message, $success, $code);
				}
			};
			
			ob_start();
			$handler->runStatus('All good', true, 202);
			$output = ob_get_clean();
			
			$json = json_decode($output, true);
			
			$this->assertSame(202, http_response_code());
			$this->assertTrue($json['success']);
			$this->assertSame('All good', $json['status']);
			$this->assertArrayHasKey('timestamp', $json);
			$this->assertNotEmpty($json['timestamp']);
			$this->assertTrue($handler->hasExited());
		}
		
		public function testStatusDefaultsToOkAndSuccessTrue(): void{
			$handler = new class extends TestableJsonResponseHandler{
				public function runStatus(): void{
					parent::status();
				}
			};
			
			ob_start();
			$handler->runStatus();
			$output = ob_get_clean();
			
			$json = json_decode($output, true);
			
			$this->assertSame(200, http_response_code());
			$this->assertTrue($json['success']);
			$this->assertSame('OK', $json['status']);
			$this->assertArrayHasKey('timestamp', $json);
			$this->assertTrue($handler->hasExited());
		}
	}