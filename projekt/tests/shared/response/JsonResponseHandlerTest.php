<?php
	require_once __DIR__ . '/../../base/DatabaseTestCasePreparation.php';
	require_once __DIR__ . '/../../../src/shared/response/JsonResponseHandler.php';
	
	use Shared\Response\JsonResponseHandler;
	
	class JsonResponseHandlerTest extends DatabaseTestCase{
		public function setUp(): void{
			parent::setUp();
		}
	}