<?php 

namespace Alura\Pdo\Infrastructure\Repository;

use Alura\Pdo\Domain\Repository\StudentRepository;
use Alura\Pdo\Domain\Model\Student;
use Alura\Pdo\Domain\Model\Phone;

class PdoStudentRepository implements StudentRepository
{
	private \PDO $connection;

	public function __construct(\PDO $connection)
	{
		$this->connection = $connection;
	}

	public function allStudents(): array
	{
		//fetchAll lista todos os resultados pegos na query da forma padrao do PDO.
		//FETCH_ASSOC retorna os dados como arrays associativos.
		//FETCH_CLASS, NomedaClass -> retorna os dados instanciando uma classe da nossa aplicação.
		// se os nomes das propriedades da classe nao forem identicos aos dos campos da tabela ira gerar
		// problemas então por padrao é recomendado utilizar o FETCH_ASSOC e instancia a classe na mao atribuindo
		// os valores dos atributos.
		// Se nosso banco tem muitos registros, usando ->fetch e percorremos com while.
		// Um stmt de um ->query ja é executado automaticamente.
		$stmt = $this->connection->query("SELECT * FROM students;");

		return $this->hydrateList($stmt);
	}

	public function studentsBirthAt(\DateTimeInterface $birthDate): array
	{
		$stmt = $this->connection->prepare("SELECT * FROM students WHERE birth_date = :birth_date;");
		$stmt->bindValue(':birth_date', $birth_date);
		$stmt->execute();

		return $this->hydrateList($stmt);
	}

	public function hydrateList(\PDOStatement $stmt): array
	{
		$studentsDataList = $stmt->fetchAll();
		$studentsList = [];
	
		foreach ($studentsDataList as $studentData) {
		    $studentsList[] = $student = new Student(
		    $studentData['id'],
		    $studentData['name'],
		    new \DateTimeImmutable($studentData['birth_date']));

		    //ATENÇÃO, Objetos sempre serão enviados por referencia para uma função.
			$this->fillPhonesOf($student);
		}

		return $studentsList;
	}

    /*
	private function fillPhonesOf(Student $student): void
	{
        $sqlQuery = "SELECT id, area_code, number FROM phones WHERE student_id = :id";
        $stmt = $this->connection->prepare($sqlQuery);
        $stmt->bindValue(':id', $student->id());
        $stmt->execute();

        $phonesDataList = $stmt->fetchAll();
        foreach ($phonesDataList as $phoneData) {
            $phone = new Phone($phoneData['id'],
            $phoneData['area_code'],
            $phoneData['number']);

            $student->addPhone($phone);
        }
	}*/

	public function save(Student $student): bool
	{
		if ($student->id() === null) {
			return $this->create($student);
		}

		return $this->update($student);		
	}

	public function create(Student $student): bool
	{
		$insertQuery = "INSERT INTO students (name, birth_date) VALUES (:name, :birth_date)";
		$stmt = $this->connection->prepare($insertQuery);
		$stmt->bindValue(':name', $student->name());
		$stmt->bindValue(':birth_date', $student->birthDate()->format('Y-m-d'));
		$success = $stmt->exeecute();

		$student->defineId($this->connction->lastInsertId());
	
		return $success;
	}

	public function update(Student $student): bool
	{
		$updateQuery = "UPDATE students SET name = :name, birth_date = :birth_date WHERE id = :id;";
		$stmt = $this->connection->prepare($updateQuery);
		$stmt->bindValue(':id', $student->id(), PDO::PARAM_INT);
		$stmt->bindValue(':name', $student->name());
		$stmt->bindValue(':birth_date', $student->birthDate()->format('Y-m-d'));
		return $stmt->execute();
	}

	public function remove(Student $student): bool
	{
		$deleteQuery = "DELETE FROM students WHERE id = :id";
		$stmt = $this->connection->prepare($deleteQuery);
		$stmt->bindValue(':id', $student->id());
		return $stmt->execute();

	}

	public function studentsWithPhones(): array
	{

		$sqlSelect = "SELECT students.id,
		                     students.name,
		                     students.birth_date,
		                     phones.id AS phone_id,
		                     phones.area_code,
		                     phones.number
		                FROM students
		                JOIN phones ON students.id = phones.student_id;";

		$stmt = $this->connection->prepare($sqlSelect);
		$stmt->execute();

		$result = $stmt->fetchAll();
		$studentsList = [];
		foreach ($result as $row) {
			if(!array_key_exists($row["id"], $studentsList)) {
				$studentsList[$row["id"]] = new Student(
                    $row["id"],
                    $row["name"],
                    new \DateTimeImmutable($row["birth_date"])
				);
			}
			
			$phone = new Phone(
                $row["phone_id"],
                $row["area_code"],
                $row["number"]
			);

			$studentsList[$row["id"]]->addPhone($phone);
		}

		return $studentsList;
	}
}