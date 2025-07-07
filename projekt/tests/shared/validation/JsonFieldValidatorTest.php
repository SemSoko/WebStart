<?php
	require_once __DIR__ . '/../../base/DatabaseTestCasePreparation.php';
	require_once __DIR__ . '/../../../src/shared/validation/JsonFieldValidator.php';
	
	use Shared\Validation\JsonFieldValidator;
	
	class JsonFieldValidatorTest extends DatabaseTestCase{
		public function setUp(): void{
			parent::setUp();
		}
		
		// === Tests für hasRequiredField() ======================================
		
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
		
		public function testHasRequiredFieldReturnsFalseWhenFieldIsOnlyWhitespace(): void{
			$validator = new JsonFieldValidator();
			$input = ['title' => '   '];
			
			$result = $validator->hasRequiredField($input, 'title');
			
			$this->assertFalse($result);
		}
		
		public function testHasRequiredFieldReturnsFalseWhenFieldIsNull(): void{
			$validator = new JsonFieldValidator();
			$input = ['title' => null];
			
			$result = $validator->hasRequiredField($input, 'title');
			
			$this->assertFalse($result);
		}
		
		public function testHasRequiredFieldReturnsTrueWhenFieldIsZero(): void{
			$validator = new JsonFieldValidator();
			$input = ['title' => 0];
			
			$result = $validator->hasRequiredField($input, 'title');
			
			$this->assertTrue($result);
		}
		
		public function testHasRequiredFieldReturnsTrueWhenFieldIsNumeric(): void{
			$validator = new JsonFieldValidator();
			$input = ['title' => 123];
			
			$result = $validator->hasRequiredField($input, 'title');
			
			$this->assertTrue($result);
		}
		
		// === Tests für getValue() ==============================================
		
		public function testGetValueReturnsTrimmedStringWhenFieldIsSet(): void{
			$validator = new JsonFieldValidator();
			$input = ['title' => 'My Todo'];
			
			$result = $validator->getValue($input, 'title');
			
			$this->assertSame('My Todo', $result);
		}
		
		public function testGetValueReturnsTrimmedStringWhenFieldHasWhitespaces(): void{
			$validator = new JsonFieldValidator();
			$input = ['title' => '    My Todo    '];
			
			$result = $validator->getValue($input, 'title');
			
			$this->assertSame('My Todo', $result);
		}
		
		public function testGetValueReturnsNullWhenFieldIsMissing(): void{
			$validator = new JsonFieldValidator();
			// 'title' fehlt
			$input = ['description' => 'Irgendwas'];
			
			$result = $validator->getValue($input, 'title');
			
			$this->assertNull($result);
		}
		
		public function testGetValueReturnsEmptyStringWhenFieldIsEmpty(): void{
			$validator = new JsonFieldValidator();
			$input = ['title' => ''];
			
			$result = $validator->getValue($input, 'title');
			
			$this->assertSame('', $result);
		}
		
		public function test(): void{
			$validator = new JsonFieldValidator();
			$input = ['title' => '     '];
			
			$result = $validator->getValue($input, 'title');
			
			$this->assertSame('', $result);
		}
		
		public function testGetValueReturnsStringWhenFieldIsNummeric(): void{
			$validator = new JsonFieldValidator();
			$input = ['title' => 123];
			
			$result = $validator->getValue($input, 'title');
			
			$this->assertSame('123', $result);
		}
	}