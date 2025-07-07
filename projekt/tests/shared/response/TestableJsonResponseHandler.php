<?php
	require_once __DIR__ . '/../../../src/shared/response/JsonResponseHandler.php';

	use Shared\Response\JsonResponseHandler;
	
	class TestableJsonResponseHandler extends JsonResponseHandler{
		private bool $exited = false;
		
		protected function exit(): void{
			$this->exited = true;
		}
		
		public function hasExited(): bool{
			return $this->exited;
		}
	}