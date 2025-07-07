<?php
	require_once __DIR__ . '/../../base/DatabaseTestCasePreparation.php';
	require_once __DIR__ . '/../../../src/shared/validation/InputHelper.php';
	
	use Shared\Validation\InputHelper;
	
	class InputHelperTest extends DatabaseTestCase{
		public function setUp(): void{
			parent::setUp();
		}
		
		public function testGetJsonBodyReturnsParsedArray(): void{
			// Subklasse mit Ueberschreibung
			$mockedHelper = new class extends InputHelper{
				public static function getRawInput(): string{
					return '{"name": "Max"}';
				}
			};
			
			// Reset des Caches fuer isolierten Test auf der
			// Basis-Klasse, nicht der Subklasse
			$ref = new \ReflectionClass(InputHelper::class);
			$prop = $ref->getProperty('cachedInput');
			$prop->setAccessible(true);
			$prop->setValue(null);
			
			// Aufruf ueber die Subklasse (fuer das Mocking)
			$jsonBody = $mockedHelper::getJsonBody();
			
			$this->assertIsArray($jsonBody);
			$this->assertSame(['name' => 'Max'], $jsonBody);
		}
	}