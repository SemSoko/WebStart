<?php
	require_once __DIR__ . '/../../base/DatabaseTestCasePreparation.php';
	require_once __DIR__ . '/../../../src/shared/middleware/ValidationMiddleware.php';
	require_once __DIR__ . '/../../../src/shared/validation/FieldValidatorInterface.php';
	require_once __DIR__ . '/../../../src/shared/http/InputProviderInterface.php';
	require_once __DIR__ . '/../../../src/shared/response/JsonResponseHandler.php';
	
	use Shared\Middleware\ValidationMiddleware;
	use PHPUnit\Framework\MockObject\MockObject;
	use Shared\Validation\FieldValidatorInterface;
	use Shared\Http\InputProviderInterface;
	use Shared\Response\ResponseHandlerInterface;
	
	class ValidationMiddlewareTest extends DatabaseTestCase{
		private MockObject $validator;
		private MockObject $response;
		private MockObject $input;
		
		public function setUp(): void{
			parent::setUp();
			
			$this->validator = $this->createMock(FieldValidatorInterface::class);
			$this->response = $this->createMock(ResponseHandlerInterface::class);
			$this->input = $this->createMock(InputProviderInterface::class);
		}
		
		public function testRequireFieldReturnsValueIfValid(): void{
			$jsonInput = ['title' => 'Test-Todo'];
			
			$this->input
				->method('getJsonBody')
				->willReturn($jsonInput);
			
			$this->validator
				->expects($this->once())
				->method('hasRequiredField')
				->with($jsonInput, 'title')
				->willReturn(true);
			
			$this->validator
				->expects($this->once())
				->method('getValue')
				->with($jsonInput, 'title')
				->willReturn('Test-Todo');
			
			// ResponseHandler darf nicht aufgerufen werden bei Erfolg
			$this->response
				->expects($this->never())
				->method('error');
			
			$middleware = new ValidationMiddleware(
				$this->validator,
				$this->response,
				$this->input
			);
			
			$result = $middleware->requireField('title');
			$this->assertSame('Test-Todo', $result);
		}

	}