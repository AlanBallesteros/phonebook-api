<?php

class DBConnection extends PDO{
  private $host = '127.0.0.1';
  private $dbname = 'phonebook';
  private $user = 'phonebook_user';
  private $pass = 'phonebookPass';

  public function __construct() {
    parent::__construct("mysql:host=$this->host;dbname=$this->dbname", $this->user, $this->pass);
    $this->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
  
}