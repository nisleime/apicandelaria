<?php
namespace controllers;

class TestConnection {
    private $PDO;

    public function __construct() {
        $host = $_ENV['CODEEASY_GERENCIADOR_MYSQL_HOST'];
        $dbname = $_ENV['CODEEASY_GERENCIADOR_MYSQL_DBNAMO'];
        $user = $_ENV['CODEEASY_GERENCIADOR_MYSQL_USER'];
        $password = $_ENV['CODEEASY_GERENCIADOR_MYSQL_PASSWORD'];
             
        $this->PDO = new \PDO("mysql:host=$host;dbname=$dbname", $user, $password);
        $this->PDO->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public function test($request, $response, $args) {
        try {
            // Testa a conexão com uma consulta simples
            $query = $this->PDO->query('SELECT DATABASE() AS current_db');
            $result = $query->fetch(\PDO::FETCH_ASSOC);

            if ($result) {
                $data = [
                    'success' => true,
                    'message' => 'Database connected successfully!',
                    'database' => $result['current_db'] ?? 'Unknown'
                ];
            } else {
                $data = [
                    'success' => false,
                    'message' => 'Database connection test query returned no results.'
                ];
            }

            $response->getBody()->write(json_encode($data));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\PDOException $e) {
            $error = [
                'success' => false,
                'message' => 'Database query failed: ' . $e->getMessage()
            ];

            $response->getBody()->write(json_encode($error));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
