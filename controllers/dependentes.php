<?php
namespace controllers;

class Dependentes {
    private $PDO;
    private $app;

    // Construtor para inicializar o PDO e o Slim
    public function __construct($app) {
        $this->PDO = new \PDO('mysql:host=104.234.173.105;dbname=candelaria', 'root', 'Ncm@647534');
        $this->PDO->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->app = $app;
    }

    // Lista registros filtrados pelo cpf_titular
    public function lista() {
        $cpf_titular = $this->app->request->get('cpf_titular');

        if (!$cpf_titular) {
            $this->app->response->setStatus(400);
            echo json_encode(["error" => "O parâmetro 'cpf_titular' é obrigatório"]);
            return;
        }

        try {
            $sth = $this->PDO->prepare("SELECT * FROM dependentes WHERE cpf_titular = :cpf_titular");
            $sth->bindValue(':cpf_titular', $cpf_titular);
            $sth->execute();
            $result = $sth->fetchAll(\PDO::FETCH_ASSOC);

            $this->respond($result);
        } catch (\PDOException $e) {
            $this->respondError(500, $e->getMessage());
        }
    }

    // Obtém um registro específico pelo ID
    public function get($id) {
        try {
            $sth = $this->PDO->prepare("SELECT * FROM dependentes WHERE id = :id");
            $sth->bindValue(':id', $id, \PDO::PARAM_INT);
            $sth->execute();
            $result = $sth->fetch(\PDO::FETCH_ASSOC);

            if ($result) {
                $this->respond($result);
            } else {
                $this->respondError(404, "Registro não encontrado");
            }
        } catch (\PDOException $e) {
            $this->respondError(500, $e->getMessage());
        }
    }

    // Adiciona um novo registro
    public function novo() {
        $dados = json_decode($this->app->request->getBody(), true);
        $dados = (sizeof($dados) == 0) ? $_POST : $dados;
        $keys = array_keys($dados);

        try {
            $sth = $this->PDO->prepare("INSERT INTO dependentes (" . implode(',', $keys) . ") VALUES (:" . implode(",:", $keys) . ")");
            foreach ($dados as $key => $value) {
                $sth->bindValue(':' . $key, $value);
            }
            $sth->execute();

            $this->respond(["id" => $this->PDO->lastInsertId()]);
        } catch (\PDOException $e) {
            $this->respondError(500, $e->getMessage());
        }
    }

    // Atualiza um registro específico
    public function editar($id) {
        $dados = json_decode($this->app->request->getBody(), true);
        $dados = (sizeof($dados) == 0) ? $_POST : $dados;
        $sets = [];

        foreach ($dados as $key => $value) {
            $sets[] = $key . " = :" . $key;
        }

        try {
            $sth = $this->PDO->prepare("UPDATE dependentes SET " . implode(',', $sets) . " WHERE id = :id");
            $sth->bindValue(':id', $id, \PDO::PARAM_INT);
            foreach ($dados as $key => $value) {
                $sth->bindValue(':' . $key, $value);
            }

            $status = $sth->execute();
            $this->respond(["status" => $status]);
        } catch (\PDOException $e) {
            $this->respondError(500, $e->getMessage());
        }
    }

    // Remove um registro específico
    public function excluir($id) {
        try {
            $sth = $this->PDO->prepare("DELETE FROM dependentes WHERE id = :id");
            $sth->bindValue(':id', $id, \PDO::PARAM_INT);
            $status = $sth->execute();

            $this->respond(["status" => $status]);
        } catch (\PDOException $e) {
            $this->respondError(500, $e->getMessage());
        }
    }

    // Responde com JSON
    private function respond($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    // Responde com erro e status apropriado
    private function respondError($status, $message) {
        $this->app->response->setStatus($status);
        $this->respond(["error" => $message]);
    }
}
