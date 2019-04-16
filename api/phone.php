<?php

class Phone {
  private $conn;
  private $permitedFields = ['phone' => 'string', 'contact_id' => 'int'];
  
  public function __construct(PDO $dbClient) {
    $this->conn = $dbClient;
  }

  public function index() {
    return $this->fetch();
  }

  public function fetch() {
    $sql = 'SELECT * FROM phones';
    return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
  }

  public function get($id=null) {
    if(empty($id)) return false;

    $sql = 'SELECT * FROM phones';
    $sql = $sql . ' WHERE id = :id';
    
    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function post() {
    $data = json_decode(file_get_contents('php://input'), true);

    $sql = 'INSERT INTO phones (contact_id, phone) VALUES (:contact_id, :phone)';
    
    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':contact_id', $data['contact_id'], PDO::PARAM_INT);
    $stmt->bindParam(':phone', $data['phone'], PDO::PARAM_STR);

    return $stmt->execute();
  }

  public function put($id) {
    $data = json_decode(file_get_contents('php://input'), true);

    if(empty($id) || empty($data)) return false;

    $sql = 'UPDATE phones SET';
    $values = [];
    $data = array_intersect_key($data, $this->permitedFields);
    $data = array_filter($data, function($value) {
      return null !== $value;
    });

    foreach($data as $key => $value) {
      $sql .= " {$key} = :{$key},";
      $values[':'.$key] = $value;
    }
    
    $sql = substr($sql, 0, -1);
    $sql .= " WHERE id = :id";
    
    $values[':id'] = $id;
    
    $stmt = $this->conn->prepare($sql);

    return $stmt->execute($values);
  }

  public function delete($id) {
    if(empty($id)) return false;
    
    $sql = 'DELETE FROM phones where id = :id';
    $values[':id'] = $id;

    $stmt = $this->conn->prepare($sql);
    
    return $stmt->execute($values);
  }
}