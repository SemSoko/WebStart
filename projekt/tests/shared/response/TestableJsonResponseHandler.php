<?php
	namespace Tests\Shared\Response;

	require_once __DIR__ . '/../../../src/shared/response/JsonResponseHandler.php';

	use Shared\Response\JsonResponseHandler;
	
	class TestableJsonResponseHandler extends JsonResponseHandler{
		private bool $exited = false;
		private ?string $lastErrorMessage = null;
		private ?int $lastStatusCode = null;
		
		protected function doExit(): void{
			// Unterdrueckt echten exit-Aufruf im Test
			$this->exited = true;
		}
		
		public function error(string $message, int $statusCode = 400): void{
			$this->lastErrorMessage = $message;
			$this->lastStatusCode = $statusCode;
			
			// Statt exit wird hier eine Exception geworfen
			throw new \RuntimeException("Mocked error: {$message} ({$statusCode})", $statusCode);
		}
		
		public function hasExited(): bool{
			return $this->exited;
		}
		
		public function getLastStatusCode(): ?int{
			return $this->lastStatusCode;
		}
	}