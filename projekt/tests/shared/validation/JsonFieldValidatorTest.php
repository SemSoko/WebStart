<?php
	require_once __DIR__ . '/../../base/DatabaseTestCasePreparation.php';
	require_once __DIR__ . '/../../../src/shared/validation/JsonFieldValidator.php';
	
	use Shared\Validation\JsonFieldValidator;
	
	class JsonFieldValidatorTest extends DatabaseTestCase{
		public function setUp(): void{
			parent::setUp();
		}
		
		public function testHasRequiredFieldReturnsTrueFieldIsSetAndNotEmpty(): void{
			$validator = new JsonFieldValidator();
			$input = ['title' => 'My Todo'];
			
			$result = $validator->hasRequiredField($input, 'title');
			
			$this->assertTrue($result);
		}
		
		public function testHasRequiredFieldReturnsFalseWhenFieldIsMissing(): void{
			$validator = new JsonFieldValidator();
			$input = ['description' => 'Falsches Feld'];
			
			$result = $validator->hasRequiredField($input, 'title');
			
			$this->assertFalse($result);
		}
		
		public function testHasRequiredFieldReturnsFalseWhenFieldIsEmptyString(): void{
			$validator = new JsonFieldValidator();
			$input = ['title' => ''];
			
			$result = $validator->hasRequiredField($input, 'title');
			
			$this->assertFalse($result);
		}
	}