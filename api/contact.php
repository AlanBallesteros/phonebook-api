<?php

class Contact {
  private $conn;
  private $permitedFields = ['name' => 'string', 'lastname' => 'string'];
  private $permitedTerms = [
    'name' => 'string',
    'lastname' => 'string',
    'email' => 'string',
    'phone' => 'string'
  ];
  
  public function __construct(PDO $dbClient) {
    $this->conn = $dbClient;
  }

  public function index() {
    $terms = !empty($_GET['term']) ? $_GET['term'] : null;
    
    if($terms === null) {
      return $this->fetch();
    }
    
    $terms = array_intersect_key($terms, $this->permitedTerms);
    $terms = array_filter($terms, function($value) {
      return null !== $value;
    });
    
    if(!empty($terms)) {
      return $this->fetchByFields($terms);
    }

    return false;

  }

  public function fetch() {
    $sql = 'SELECT * FROM contacts';
    $contacts = $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    
    if(empty($contacts)) return false; 
    
    foreach($contacts as $key => $contact)  {
      $contacts[$key]['emails'] =  $this->conn->query('SELECT * FROM emails where contact_id='.$contact['id'])->fetchAll(PDO::FETCH_ASSOC); 
      $contacts[$key]['phones'] =  $this->conn->query('SELECT * FROM emails where contact_id='.$contact['id'])->fetchAll(PDO::FETCH_ASSOC); 
    }

    return $contacts;
  }

  public function fetchByFields($params) {

    $sql = 'SELECT * FROM contacts c';
    $joins = ['email' => ' INNER JOIN emails e ON e.contact_id = c.id','phone' => ' INNER JOIN phones p ON p.contact_id = c.id'];
    $value = [];
    
    if(!empty($params['phone'])) {
      $joins['phone'] .= ' AND p.phone LIKE :phone';
      $sql .= $joins['phone']; 
      $values[':phone'] = '%'.$params['phone'].'%';
    }
    
    if(!empty($params['email'])) {
      $joins['email'] .= ' AND e.email LIKE :email';
      $sql .= $joins['email'];
      $values[':email'] = '%'.$params['email'].'%';
    }

    $sql .= ' WHERE 1=1';
    if(!empty($params['name'])) {
      $sql .= ' AND c.name LIKE :name';
      $values[':name'] = '%'.$params['name'].'%';
    }

    if(!empty($params['lastname'])) {
      $sql .= ' AND c.lastname LIKE :lastname';
      $values[':lastname'] = '%'.$params['lastname'].'%';
    }
      
    $stmt = $this->conn->prepare($sql);
    $stmt->execute($values);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function get($id=null) {
    if(empty($id)) return false;
    $sql = 'SELECT * FROM contacts WHERE id = :id';

    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
  
  public function post() {
    $data = json_decode(file_get_contents('php://input'), true);

    $sql = 'INSERT INTO contacts (name, lastname) VALUES(:name, :lastname)';
    
    $stmt = $this->conn->prepare($sql);
    
    $stmt->bindParam(':name', $data['name'], PDO::PARAM_INT);
    $stmt->bindParam(':lastname', $data['lastname'], PDO::PARAM_STR);

    return $stmt->execute();
    
  }

  public function put($id) {
    $data = json_decode(file_get_contents('php://input'), true);

    if(empty($id) || empty($data)) return false;

    $sql = 'UPDATE contacts SET';
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
    
    $sql = 'DELETE FROM contacts where id = :id';
    $values[':id'] = $id;

    $stmt = $this->conn->prepare($sql);
    
    return $stmt->execute($values);
  }
}