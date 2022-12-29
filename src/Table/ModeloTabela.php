<?php

namespace MatheusV\DBManager\Table;

/**
 * Interface responsável por definir as funções padrões de um modelo de tabela
 * 
 * @package DBM_manager
 * 
 * @subpackage Interface Tabela
 * 
 * @author Matheus Vinicius <matheusv.16santos@gmail.com>
 * 
 * @version 1.0.0
 */
interface ModeloTabela {
  /**
   * Método responsável por retornar as colunas de uma tabela 
   * 
   * @return array [camposClasse => campos_banco]
   */
  public function getProperties() : array;

  /**
   * Método responsável por retornar os campos sem aspas da tabela
   * 
   * @return array
   */
  public function camposSemAspas() : array;
}