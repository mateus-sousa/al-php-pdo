<?php 

use Alura\Pdo\Domain\Model\Student;
use Alura\Pdo\Infrastructure\ConnectionCreator;

require_once "vendor/autoload.php";

$pdo = ConnectionCreator::createConnection();

$student = new Student(null, 'Mateus de Sousa e Silva', new \DateTimeImmutable('1996-08-03'));

$sqlInsert = "INSERT INTO students (name, birth_date) VALUES (:name, :birth_date)";
$stmt = $pdo->prepare($sqlInsert);
//bindValue passa a variavel por valor, bindParam passa por referencia.
$stmt->bindValue('name', $student->name());
$stmt->bindValue('birth_date', $student->birthDate()->format('Y-m-d'));

var_dump($stmt->execute());
