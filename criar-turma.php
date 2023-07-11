<?php

use Alura\Pdo\Infrastructure\Persistence\ConnectionCreator;
use Alura\Pdo\Infrastructure\Repository\PdoStudentRepository;
use Alura\Pdo\Domain\Model\Student;

require_once "vendor/autoload.php";

$connection = ConnectionCreator::createConnection();
$pdoRepository = new PdoStudentRepository($connection);

$connection->beginTransaction();
try {
    $aStudent = new Student(null, 'Lucas Casella', new \DateTimeImmutable('1985-11-11'));

    $pdoRepository->save($aStudent);

    $connection->commit();	
} catch (\PDOException $e) {
	echo $e->getMessage();
	$connection->rollback();
}
