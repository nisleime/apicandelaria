<?php
namespace controllers;

class Plano {
    private $PDO;
    private $app;

    // Conexão com o banco de dados
    public function __construct($app) {
        $this->PDO = new \PDO('mysql:host=104.234.173.105;dbname=candelaria', 'root', 'Ncm@647534');
        $this->PDO->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->app = $app;
    }

    // Lista todos os registros da tabela plano
    public function lista() {
        try {
            $sth = $this->PDO->prepare("SELECT * FROM plano");
            $sth->execute();
            $result = $sth->fetchAll(\PDO::FETCH_ASSOC);

            $this->respond($result);
        } catch (\PDOException $e) {
            $this->respondError(500, $e->getMessage());
        }
    }

    // Lista registros filtrados por data e atualiza a situação, se necessário
    public function listar() {
        $data_inicial = $this->app->request->get('data_inicial');
        $data_final = $this->app->request->get('data_final');
        $palavra_chave = $this->app->request->get('querystring');

        if (!$data_inicial || !$data_final) {
            $this->respondError(400, 'Datas são obrigatórias');
            return;
        }

        try {
            $sth = $this->PDO->prepare(
                "SELECT * 
                 FROM empresas 
                 WHERE situacao = 'N' 
                   AND data_licenca BETWEEN :data_inicial AND :data_final"
            );
            $sth->bindValue(':data_inicial', $data_inicial);
            $sth->bindValue(':data_final', $data_final);
            $sth->execute();
            $result = $sth->fetchAll(\PDO::FETCH_ASSOC);

            if ($palavra_chave === 'spl' && !empty($result)) {
                $sthUpdate = $this->PDO->prepare(
                    "UPDATE empresas 
                     SET situacao = 'S' 
                     WHERE situacao = 'N' 
                       AND data_licenca BETWEEN :data_inicial AND :data_final"
                );
                $sthUpdate->bindValue(':data_inicial', $data_inicial);
                $sthUpdate->bindValue(':data_final', $data_final);
                $sthUpdate->execute();
            }

            $this->respond($result);
        } catch (\PDOException $e) {
            $this->respondError(500, $e->getMessage());
        }
    }

    // Obtém um registro específico pelo ID
    public function get($id) {
        try {
            $sth = $this->PDO->prepare("SELECT * FROM plano WHERE id = :id");
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

        try {
            $keys = array_keys($dados);
            $sth = $this->PDO->prepare("INSERT INTO plano (" . implode(',', $keys) . ") VALUES (:" . implode(",:", $keys) . ")");
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

        try {
            $sets = [];
            foreach ($dados as $key => $value) {
                $sets[] = $key . " = :" . $key;
            }

            $sth = $this->PDO->prepare("UPDATE plano SET " . implode(',', $sets) . " WHERE id = :id");
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
            $sth = $this->PDO->prepare("DELETE FROM plano WHERE id = :id");
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
