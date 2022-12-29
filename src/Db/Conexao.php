<?php

namespace MatheusV\DBManager\Db;

use \PDOStatement;
use MatheusV\DBManager\Sistem\DecodeEnv;

/**
 * Classe responsável por conectar a um banco de dados
 * 
 * @package DBM_manager
 * 
 * @subpackage Conexao com banco de dados
 * 
 * @author Matheus Vinicius <matheusv.16santos@gmail.com>
 * 
 * @version 1.0.0
 */
abstract class Conexao {
  /**
   * Guarda a conexão com o banco de dados
   * 
   * @var PDO
   */
  protected $con = false;

  /**
   * Responsável por definir se os erros de query serão mostrados na íntegra
   * 
   * @var bool
   */
  private $protectedMode = false;

  /**
   * Informa como o erro será mostrado
   * 
   * @var string
   */
  private $errorOutput = null;

  /**
   * Guarda o id do último registro inserido
   * 
   * @var int
   */
  protected $lastInsertId = null;

  /**
   * Método responsável por executar uma querie
   * 
   * @param  string       $sql           Query que será executada
   * 
   * @param  bool         $prepare       Define se será usado o método prepare do PDO
   * 
   * @return PDOStatement|bool
   */
  protected function executar($sql, $prepare = false) {
    $resultado = null;
    $this->slug($sql);
    
    // REALIZA CONEXÃO COM O BANCO
    $this->conectar();

    try {
      $this->con->beginTransaction();

      // REALIZA A CONSULTA
      if($prepare) {
        $resultado          = $this->con->prepare($sql);
        $resultado          = $resultado->execute();
        $this->lastInsertId = $this->con->lastInsertId();
      } else {
        $resultado = $this->con->query($sql);
      }

      $this->con->commit();
    } catch (\PDOException $ex) {
      $this->con->rollBack();
      
      $resultado = new PDOStatement;
      $code      = (int)$ex->errorInfo[1];
      $message   = $this->protectedMode ? 'Você não possui permissão para fazer isso': $ex->getMessage();

      $this->setOutputError($code, $message);
    }

    return $resultado;
  }

  /**
   * Método responsável por realizar a conexão com o banco de dados
   * 
   * @return bool
   */
  private function conectar() : bool {
    $dotenv = new DecodeEnv;

    // DADOS DE CONEXÃO
    $host    = $dotenv->DB_HOST;
    $dbname  = $dotenv->DB_DATABASE;
    $user    = $dotenv->DB_USERNAME;
    $pass    = $dotenv->DB_PASSWORD;
    $port    = $dotenv->DB_PORT;
    $driver  = $dotenv->DB_CONNECTION;
    $charset = $dotenv->DB_CHARSET;

    // COMO INFORMAR O ERRO
    $this->errorOutput   = $dotenv->DB_OUTPUT_MESSAGE;
    $this->protectedMode = $dotenv->DB_PROTECTED_MODE;

    try {
      // CRIA A CONEXÃO
      $dsn     = $driver.':host='.$host.';dbname='.$dbname.';charset='.$charset.';port='.$port.';';
      $conexao = new \PDO($dsn, $user, $pass);

      // DEFINE AS OPÇÕES DE CONEXÃO
      $opcoes = "SET SESSION group_concat_max_len = 1000000,sql_mode='NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';";
      $conexao->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
      $conexao->setAttribute(\PDO::ATTR_TIMEOUT, 20);
      $conexao->prepare($opcoes)->execute();

      $this->con = $conexao;

      return true;
    } catch (\PDOException $e) {
      $code    = (int)$e->errorInfo[1];
      $message = $this->protectedMode ? 'Você não possui permissão para fazer isso': $e->getMessage();

      $this->setOutputError($code, $message);

      return false;
    }
  }

  /**
   * Método responsável por mostrar mensagem de erro de conexão
   * 
   * @param string       $code          Código do erro
   * 
   * @param string       $message       Mensagem de erro
   * 
   * @param void
   */
  private function setOutputError($code, $message) : void {
    $output = '';

    switch(strtolower($this->errorOutput)) {
      case 'html':
        $output = '<div><strong>Error: ('.$code.')</strong></br><b>Mensagem:</b> '.$message.'<div>';
        break;
      case 'json':
        header('Access-Control-Allow-Origin: *');
        header('Content-type: application/json; charset=utf-8');
        $data   = ['code' => $code, 'message' => $message];
        $output = json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        break;
      default:
        $output = '(ERROR '.$code.') '.$message;
        break;
    }

    die($output);
  }
  
  /**
   * Método responsável por remover caracteres inválidos da query
   * 
   * @return void
   */
  private function slug(&$query) : void {
    $query = trim($query);
    $query = preg_replace("/[\\\\]+\'/",'\\\'',$query);
  }

  /**
   * Método responsável por retornar o id do último registro adicionado
   * 
   * @return int
   */
  public function getLastInsertId() : int {
    return $this->lastInsertId;
  }
}