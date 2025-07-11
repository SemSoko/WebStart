<?php
	require_once __DIR__ . '/../../base/UnitTestCase.php';
	require_once __DIR__ . '/../../../src/shared/response/JsonResponseHandler.php';
	require_once __DIR__ . '/TestableJsonResponseHandler.php';
	
	use Tests\Shared\Response\TestableJsonResponseHandler;
	use Tests\Base\UnitTestCase;
	
	class JsonResponseHandlerTest extends UnitTestCase{		
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
			
			$this->expectException(\RuntimeException::class);
			$this->expectExceptionMessage('Mocked error: Validation failed (422)');
			
			ob_start();
			try{
				$handler->runError('Validation failed', 422);
			}finally{
				ob_end_clean();
			}
		}
		
		public function testErrorDefaultsToStatusCode400(): void{
			$handler = new class extends TestableJsonResponseHandler{
				public function runError(string $message): void{
					parent::error('Bad input');
				}
			};
			
			$this->expectException(\RuntimeException::class);
			$this->expectExceptionMessage('Mocked error: Bad input (400)');
			
			ob_start();
			try{
				$handler->runError('Bad input');
			}finally{
				ob_get_clean();
			}
		}
		
		public function testDebugOutputsExpectedJsonWithDetailsAndExits(): void{
			$handler = new class extends TestableJsonResponseHandler{
				public function runDebug(string $message, array $details, int $code): void{
					parent::debug($message, $details, $code);
				}
			};
			
			$details = ['line' => 42, 'file' => 'somefile.php'];
			
			$this->expectException(\RuntimeException::class);
			$this->expectExceptionMessage('Mocked debug: Detailed error (500)');
			
			ob_start();
			try{
				$handler->runDebug('Detailed error', $details, 500);
			}finally{
				ob_get_clean();
			}
		}
		
		public function testDebugCanHandleEmptyDetails(): void{
			$handler = new class extends TestableJsonResponseHandler{
				public function runDebug(string $message): void{
					parent::debug($message);
				}
			};
			
			$this->expectException(\RuntimeException::class);
			$this->expectExceptionMessage('Mocked debug: Detailed error (500)');
			
			$handler->runDebug('Detailed error');
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