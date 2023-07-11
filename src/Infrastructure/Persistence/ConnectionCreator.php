<?php

namespace Alura\Pdo\Infrastructure\Persistence;

use PDO;

class ConnectionCreator
{
	public static function createConnection(): PDO
	{        
		// __DIR__ é um comando do PHP que nos reporta o diretorio do arquivo atual.
        $databasePath = __DIR__ . '/../../../banco.sqlite';

        $connection = new PDO('sqlite:'. $databasePath);
	    // No fracaso as minhas ações com PDO ele nao vai mais retornar false.
	    // Vai retornar uma PDOException, qual eu posso tratar.
	    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	    //Torna o FETCH_ASSOC como padrao das buscas do PDO.
        $connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return $connection;
	}
}
