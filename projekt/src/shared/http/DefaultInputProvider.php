<?php
	namespace Shared\Http;
	
	require_once __DIR__ . '/InputProviderInterface.php';
	
	use Shared\Http\InputProviderInterface;
	
	class DefaultInputProvider implements InputProviderInterface{
		private ?array $cachedInput = null;
		
		public function getJsonBody(): ?array{
			if($this->cachedInput === null){
				$raw = $this->getRawInput();
				$this->cachedInput = json_decode($raw, true);
			}
			
			return $this->cachedInput;
		}
		
		protected function getRawInput(): string{
			return file_get_contents('php://input');
		}
	}