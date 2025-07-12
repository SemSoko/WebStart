<?php
	namespace Tests\Base;

	use PHPUnit\Framework\TestCase;
	use PDO;
	
	abstract class IntegrationTestCase extends TestCase{
		protected ?PDO $pdo = null;
		
		/*
		 * Wird in konkreten Tests oder Subklassen ueberschrieben.
		 */
		protected function createSchema(): void{
			
		}
		
		/*
		 * Optional: kann von konkreten Tests genutzt werden.
		 */
		protected function seedTestData(): void{
			
		}
		
		protected function setUp(): void{
			parent::setUp();
			
			try{
				$this->pdo = new PDO("sqlite::memory:");
				$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$this->pdo->exec('PRAGMA foreign_keys = ON;');
			
				$this->createSchema();
				$this->seedTestData();
			}catch(\PDOException $e){
				$this->fail('DB-Verbindung fehlgeschlagen: ', $e->getMessage());
			}
		}
		
		/*
		 * Gibt die PDO-Verbindung nach jedem Testlauf explizit frei.
		 * Optional bei SQlite, aber saubere Praxis fuer alle PDO-basierten Tests.
		 */
		protected function tearDown(): void{
			$this->pdo = null;
			parent::tearDown();
		}
	}