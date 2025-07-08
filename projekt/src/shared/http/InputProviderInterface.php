<?php
	namespace Shared\Http;
	
	interface InputProviderInterface{
		public function getJsonBody(): ?array;
	}