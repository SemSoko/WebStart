<?php
	require_once __DIR__ . '/../../base/UnitTestCase.php';
	require_once __DIR__ . '/../../../src/shared/http/DefaultInputProvider.php';
	
	use Shared\Http\DefaultInputProvider;
	use Tests\Base\UnitTestCase;
	
	class DefaultInputProviderTest extends UnitTestCase{
		public function setUp(): void{
			parent::setUp();
		}
		
		public function testGetJsonBodyReturnsParsedArray(): void{
			$provider = new class extends DefaultInputProvider{
				protected function getRawInput(): string{
					return '{"foo": "bar"}';
				}
			};
			
			$json = $provider->getJsonBody();
			$this->assertIsArray($json);
			$this->assertSame(['foo' => 'bar'], $json);
		}
		
		public function testReturnsNullOnInvalidJson(): void{
			$provider = new class extends DefaultInputProvider{
				protected function getRawInput(): string{
					return '{"foo":}';
				}
			};
			
			$json = $provider->getJsonBody();
			$this->assertNull($json);
		}
		
		public function testCachesJsonBody(): void{
			$provider = new class extends DefaultInputProvider{
				public int $calls = 0;
				
				protected function getRawInput(): string{
					$this->calls++;
					return '{"x": 1}';
				}
			};
			
			$first = $provider->getJsonBody();
			$second = $provider->getJsonBody();
			
			$this->assertSame($first, $second);
			$this->assertSame(1, $provider->calls);
		}
	}