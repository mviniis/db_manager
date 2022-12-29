<?php

namespace MatheusV\DBManager\Table;

/**
 * Trait responsável por formatar os dados de uma tabela
 * 
 * @package DBM_manager
 * 
 * @subpackage Trait GetSet
 * 
 * @author Matheus Vinicius <matheusv.16santos@gmail.com>
 * 
 * @version 1.0.0
 */
trait GetSet {
  /**
   * Construtor da classe
   * 
   * @return void
   */
  public function __construct() {
    $properties = $this->getProperties();
    
    // SEPARA OS CAMPOS
    $fieldsClass = array_keys($properties);
    $fieldsTable = array_values($properties);

    // FORMATA O RETORNO
    foreach($fieldsTable as $position => $field) {
      $value = $this->{$field};

      // REMOVE O CAMPO ANTIGO
      unset($this->{$field});

      // DEFINE O NOVO CAMPO
      $this->{$fieldsClass[$position]} = $value;
    }
  }

  /**
   * Método responsável por retornar os valores das propriedadas de uma classe
   * 
   * @param  mixed       $value       Nome da propriedade que será retornada
   * 
   * @return mixed
   */
  public function __get($value) {
    $classParams = array_keys(get_object_vars($this));

    // VERIFICA SE O PARÂMETRO EXISTE
    if(in_array($value, $classParams)) return $this->{$value};

    return null;
  }

  /**
   * Método responsável por inserir um dado em uma propriedade da tabela
   * 
   * @param  mixed      $param      Propriedade onde o valor será inserido
   * 
   * @param  mixed      $value      Valor que será inserido
   * 
   * @return void
   */
  public function __set($param, $value) {
    $this->{$param} = $value;
  }

  /**
   * Método responsável por retornar os valores da classe como uma array
   * 
   * @param  bool       $toClass       Retorna os dados formatados como os campos da classe
   * 
   * @param  bool       $others        Retorna os dados de campos que não existem na classe
   * 
   * @return array
   */
  public function getAttributes($toClass = true, $others = false) {
    $fieldsClass = array_keys($this->getProperties());
    $fieldsTable = array_values($this->getProperties());
    $response    = [];
    
    foreach($this as $propertie => $value) {
      if(!in_array($propertie, $fieldsClass) && !$others) continue;

      $position = array_search($propertie, $fieldsClass);
      if(!$toClass && is_numeric($position)) $propertie = $fieldsTable[$position];

      $response[$propertie] = $value;
    }

    return $response;
  }

  /**
   * Método responsável por setar as informações da classe vindas de uma array
   * 
   * @param array       $data       Dados que vão ser inseridos na classe [propertyClass => value]
   * 
   * @return object
   */
  public function setData($data) {
    if(empty($data)) return $this;

    foreach($data as $propertie => $value) {
      if(!in_array($propertie, array_keys(get_object_vars($this)))) continue;

      $this->{$propertie} = $value;
    }

    return $this;
  }
}