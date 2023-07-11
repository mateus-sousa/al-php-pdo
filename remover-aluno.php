<?php 

use Alura\Pdo\Domain\Model\Student;
use Alura\Pdo\Infrastructure\ConnectionCreator;

require_once "vendor/autoload.php";

$pdo = ConnectionCreator::createConnection();

$stmt = $pdo->prepare("DELETE FROM students WHERE id = :id");
$stmt->bindValue(':id', 2);
$stmt->execute();