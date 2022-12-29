<?php

namespace MatheusV\DBManager\Db;

use \MatheusV\DBManager\Db\Conexao;
use \MatheusV\DBManager\Table\{GetSet, ModeloTabela};
use \PDOStatement;

/**
 * Classe responsável por criar as consultas ao banco
 * 
 * @package DBM_manager
 * 
 * @subpackage Conexao com banco de dados
 * 
 * @author Matheus Vinicius <matheusv.16santos@gmail.com>
 * 
 * @version 1.0.0
 */
class Sql extends Conexao {
  /**
   * Define o nome da tabela
   * 
   * @var string
   */
  private $tabela     = null;

  /**
   * Define a sql que será executada
   * 
   * @var string
   */
  private $sql        = null;

  /**
   * Define os campos da consulta
   * 
   * @var string
   */
  private $fields     = null;
  
  /**
   * Define as condições da query
   * 
   * @var array
   */
  private $wheres     = [];

  /**
   * Define a ordenação da query
   * 
   * @var array
   */
  private $orders     = [];

  /**
   * Define o agrupamento da query
   * 
   * @var array
   */
  private $groups     = [];

  /**
   * Define as tabelas extras diretas da query
   * 
   * @var array
   */
  private $innerJoins = [];
  
  /**
   * Define as tabelas extras indiretas da query
   * 
   * @var array
   */
  private $leftJoins  = [];

  /**
   * Define o limite de dados da consulta
   * 
   * @var int
   */
  private $limit      = null;

  /**
   * Define o offset dos da consulta
   * 
   * @var int
   */
  private $offset     = null;

  /**
   * Construtor da classe
   * 
   * @return void
   */
  public function __construct($tabela) {
    $this->tabela = $tabela;
  }

  /**
   * Método responsável por definir os dados de uma consulta select
   * 
   * @param  string       $campos       Define os campos que vão ser retornados
   * 
   * @return Sql
   */
  public function select($campos = '*') : Sql {
    $this->sql    = 'SELECT {{fields}} FROM {{table}} {{innerJoin}} {{leftJoin}} {{where}} {{group}} {{order}} {{limit}} {{offset}};';
    $this->fields = $campos;

    return $this;
  }

  /**
   * Método responsável por inserir um registro no banco de dados
   * 
   * @param  array|GetSet      $dados               Dados que serão inseridos (Array ou um objeto modelo da tabela)
   * 
   * @param  array             $camposSemAspas      Campos que não serão inseridos com aspas
   * 
   * @param  bool              $ignore              Adiciona o ignore no insert
   * 
   * @return Sql
   */
  public function insert($dados, $camposSemAspas = [], $ignore = false) : Sql {    
    if(is_array($dados) || $dados instanceof ModeloTabela) {
      $campos        = [];
      $valores       = [];
      $sql           = 'INSERT{{ignore}}INTO {{table}} ({{campos}}) VALUES ({{valores}}) {{where}};';

      if($dados instanceof ModeloTabela) $dados = $dados->getAttributes(false);

      foreach($dados as $field => $value) {
        if(!strlen($value)) continue;

        $campos[]  = "`$field`";
        $valores[] = in_array($field, $camposSemAspas) ? $value : '"'.addslashes($value).'"';
      }

      // DEFINE OS DADOS DO INSERT
      $ignore  = $ignore ? ' IGNORE ': ' ';
      $campos  = implode(', ', $campos);
      $valores = implode(', ', $valores);

      // FORMATA A QUERY
      $sql = str_replace('{{ignore}}', $ignore, $sql);
      $sql = str_replace('{{campos}}', $campos, $sql);
      $sql = str_replace('{{valores}}', $valores, $sql);

      $this->sql     = $sql;
      $this->prepare = true;
    }

    return $this;
  }

  /**
   * Método responsável por inserir ou atualizar um registro no banco de dados
   * 
   * @param  array|GetSet   $dados            Dados que serão inseridos/atualizados (Array ou um objeto modelo da tabela)
   * 
   * @param  array          $camposSemAspas   Campos que não serão inseridos/atualizados com aspas
   * 
   * @param  bool           $ignore           Adiciona o ignore no insert
   * 
   * @return Sql
   */
  public function replace($dados, $camposSemAspas = [], $ignore = false) : Sql {    
    if(is_array($dados) || $dados instanceof ModeloTabela) {
      $campos        = [];
      $valores       = [];
      $sql           = 'REPLACE{{ignore}}INTO {{table}} ({{campos}}) VALUES ({{valores}});';

      if($dados instanceof ModeloTabela) $dados = $dados->getAttributes(false);

      foreach($dados as $field => $value) {
        if(!strlen($value)) continue;

        $campos[]  = "`$field`";
        $valores[] = in_array($field, $camposSemAspas) ? $value : '"'.addslashes($value).'"';
      }

      // DEFINE OS DADOS DO INSERT
      $ignore  = $ignore ? ' IGNORE ': ' ';
      $campos  = implode(', ', $campos);
      $valores = implode(', ', $valores);

      // FORMATA A QUERY
      $sql = str_replace('{{ignore}}', $ignore, $sql);
      $sql = str_replace('{{campos}}', $campos, $sql);
      $sql = str_replace('{{valores}}', $valores, $sql);

      $this->sql     = $sql;
      $this->prepare = true;
    }

    return $this;
  }

  /**
   * Método responsável por atualizar um registro
   * 
   * @param  array|GetSet      $dados               Dados que serão inseridos (Array ou um objeto modelo da tabela)
   * 
   * @param  array             $camposSemAspas      Campos que não serão inseridos com aspas
   * 
   * @param  bool              $ignore              Adiciona o ignore no insert
   * 
   * @return Sql
   */
  public function update($dados, $camposSemAspas = [], $ignore = false) : Sql {
    if(is_array($dados) || $dados instanceof ModeloTabela) {
      $sql     = 'UPDATE{{ignore}} {{table}} SET {{valores}} {{where}};';
      $ignore  = $ignore ? ' IGNORE' : '';
      $valores = [];

      if($dados instanceof ModeloTabela) $dados = $dados->getAttributes(false);
      
      foreach($dados as $field => $value) {
        if(is_null($value)) continue;

        $valor     = !in_array($field, $camposSemAspas) ? '"'.addslashes($value).'"': $value;
        $valores[] = "`$field`=$valor";
      }

      // DEFINE OS DADOS DO UPDATE
      $sql = str_replace('{{valores}}', implode(', ', $valores), $sql);
      $sql = str_replace('{{ignore}}', $ignore, $sql);

      $this->sql     = $sql;
      $this->prepare = true;
    }

    return $this;
  }

  /**
   * Método responsável por remover um registro do banco
   * 
   * @return Sql
   */
  public function delete() : Sql {
    $this->sql     = 'DELETE FROM {{table}} {{where}};';
    $this->prepare = true;

    return $this;
  }

  /**
   * Método responsável por definir as condições da consulta
   * 
   * @param  array      $condicoes      Condições da consulta
   * 
   * @return Sql
   */
  public function where($condicoes = []) : Sql {
    if(empty($condicoes)) return $this;

    $total = count($condicoes) - 1;
    foreach($condicoes as $nextOperador => $condicao) {
      $operador = is_numeric($nextOperador) ? ' AND ': ' ' . $nextOperador . ' ';

      if($total == 0) $operador = '';

      $this->wheres[] = $condicao . $operador;
      $total--;
    }

    return $this;
  }

  /**
   * Método responsável por definir as ordenação dos dados
   * 
   * @param  array      $orders      Ordenações da consulta
   * 
   * @return Sql
   */
  public function order($orders = []) : Sql {
    if(empty($orders)) return $this;

    foreach($orders as $ordem => $campo) {
      if(is_numeric($ordem)) continue;
      
      $this->orders[] = "$campo $ordem";
    }

    return $this;
  }

  /**
   * Método responsável por definir o agrupamento dos dados
   * 
   * @param  array      $groups      Agrupamento da consulta
   * 
   * @return Sql
   */
  public function group($groups = []) : Sql {
    if(empty($groups)) return $this;

    foreach($groups as $agrupamento) $this->groups[] = $agrupamento;

    return $this;
  }

  /**
   * Método responsável por definir os inner joins da consulta
   * 
   * @param  array      $joins      Joins da consulta
   * 
   * @return Sql
   */
  public function innerJoin($joins = []) : Sql {
    if(empty($joins)) return $this;

    foreach($joins as $tabela => $condicao) {
      if(is_numeric($tabela)) continue;

      $this->innerJoins[] = $tabela . ' ON ' . $condicao;
    }

    return $this;
  }

  /**
   * Método responsável por definir os left joins da consulta
   * 
   * @param  array      $joins      Joins da consulta
   * 
   * @return Sql
   */
  public function leftJoin($joins = []) : Sql {
    if(empty($joins)) return $this;

    foreach($joins as $tabela => $condicao) {
      if(is_numeric($tabela)) continue;

      $this->leftJoins[] = $tabela . ' ON ' . $condicao;
    }

    return $this;
  }

  /** 
   * Método responsável por definir o limite de dados de uma consulta
   * 
   * @param  int      $limit      Limite máximo de dados
   * 
   * @return Sql
   */
  public function setLimit($limit) : Sql {
    if(is_numeric($limit)) $this->limit = $limit;

    return $this;
  }

  /**
   * Método responsável por definir o deslocamento dos dados de uma consulta
   * 
   * @param  int      $offset      Deslocamento dos dados
   * 
   * @return Sql
   */
  public function setOffset($offset) : Sql {
    if(is_numeric($offset)) $this->offset = $offset;

    return $this;
  }

  /**
   * Método responsável por realizar a consulta
   * 
   * @param  bool       $prepare       Executar a query com o método `prepare` do PDO
   * 
   * @return PDOStatement|bool
   */
  public function send($prepare = false) {
    // VERIFICA SE FOI DEFINIDO UM TIPO DE CONSULTA
    if(!strlen($this->sql)) return new PDOStatement;

    $fields    = $this->fields;
    $where     = !empty($this->wheres) ? 'WHERE ' . implode(' ', $this->wheres) : '';
    $innerJoin = !empty($this->innerJoins) ? 'INNER JOIN ' . implode(' ', $this->innerJoins) : '';
    $leftJoin  = !empty($this->leftJoins) ? 'LEFT JOIN ' . implode(' ', $this->leftJoins) : '';
    $group     = !empty($this->groups) ? 'GROUP BY ' . implode(',', $this->groups) : '';
    $order     = !empty($this->orders) ? 'ORDER BY ' . implode(',', $this->orders) : '';
    $limit     = !is_null($this->limit) ? 'LIMIT '.$this->limit: '';
    $offset    = !is_null($this->offset) ? 'OFFSET '.$this->offset : '';

    // FORMATA A SQL
    $sql = $this->sql;
    $this->setSqlData('fields', $fields, $sql);
    $this->setSqlData('table', $this->tabela, $sql);
    $this->setSqlData('where', $where, $sql);
    $this->setSqlData('innerJoin', $innerJoin, $sql);
    $this->setSqlData('leftJoin', $leftJoin, $sql);
    $this->setSqlData('group', $group, $sql);
    $this->setSqlData('order', $order, $sql);
    $this->setSqlData('limit', $limit, $sql);
    $this->setSqlData('offset', $offset, $sql);

    // VERFICA A UTILIZAÇÃO DO PREPARE
    if(isset($this->prepare)) $prepare = $this->prepare;

    return $this->executar($sql, $prepare);
  }

  /**
   * Método responsável por formatar uma sql
   * 
   * @param  string       $campo        Campo que será substituído
   * 
   * @param  string       $valor        Valor que será aplicado
   * 
   * @param  string       $target       Sql que será formatada
   * 
   * @return void
   */
  private function setSqlData($campo, $valor, &$baseSql) : void {
    $campo   = "{{".$campo."}}";
    $baseSql = str_replace($campo, $valor, $baseSql);
  }
}