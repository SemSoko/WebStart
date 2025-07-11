<?php
	require_once __DIR__ . '/../../base/UnitTestCase.php';
	require_once __DIR__ . '/../../../src/shared/validation/InputHelper.php';
	
	use Shared\Validation\InputHelper;
	use Tests\Base\UnitTestCase;
	
	/**
	 * @coversDefaultClass \Shared\Validation\InputHelper
	 */
	class InputHelperTest extends UnitTestCase{
		public function setUp(): void{
			parent::setUp();
		}
		
		private function resetCache(): void{
			$ref = new \ReflectionClass(InputHelper::class);
			$prop = $ref->getProperty('cachedInput');
			$prop->setAccessible(true);
			$prop->setValue(null);
		}
		
		/** @covers ::getJsonBody */
		public function testGetJsonBodyReturnsParsedArray(): void{
			$this->resetCache();
			
			// Subklasse mit Ueberschreibung
			$mockedHelper = new class extends InputHelper{
				public static function getRawInput(): string{
					return '{"name": "Max"}';
				}
			};
			
			// Aufruf ueber die Subklasse (fuer das Mocking)
			$jsonBody = $mockedHelper::getJsonBody();
			
			$this->assertIsArray($jsonBody);
			$this->assertSame(['name' => 'Max'], $jsonBody);
		}
		
		public function testGetJsonBodyReturnsNullForInvalidJson(): void{
			$this->resetCache();
			
			$mockedHelper = new class extends InputHelper{
				public static function getRawInput(): string{
					return '{"name": }';
				}
			};
			
			// Aufruf ueber die Subklasse (fuer das Mocking)
			$jsonBody = $mockedHelper::getJsonBody();
			
			// $this->assertIsArray($jsonBody);
			$this->assertNull($jsonBody);
		}
		
		public function testGetJsonBodyUsesCacheOnSecondCall(): void{
			$this->resetCache();
			
			$mockedHelper = new class extends InputHelper{
				public static int $callCount = 0;
				
				public static function getRawInput(): string{
					self::$callCount++;
					return '{"cached": true}';
				}
			};
			
			$firstCall = $mockedHelper::getJsonBody();
			$secondCall = $mockedHelper::getJsonBody();
			$this->assertSame(['cached' => true], $firstCall);
			$this->assertSame(['cached' => true], $secondCall);
			// Nur einmal gelesen
			$this->assertSame(1, $mockedHelper::$callCount);
		}
	}