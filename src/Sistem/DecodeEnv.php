<?php

namespace MatheusV\DBManager\Sistem;

use Dotenv\Dotenv;

/**
 * Classe responsável por formatar os dados aplicados no arquivo .env
 * @author Matheus Vinicius <matheusv.16santos@gmail.com>
 */
class DecodeEnv {
  /**
   * Valores setados no arquivo .env do projeto
   * @var array $values
   */
  private $values = [];

  /**
   * Método construtor da classe
   * @return void
   */
  public function __construct() {
    $dotenv       = Dotenv::createImmutable($this->getBashPath());
    $this->values = $dotenv->load();
  }

  /**
   * Método responsável por retornar o valor de un índice do .env
   * @param  string     $index     Índice do valor buscado no arquivo .env
   * @return mixed      Retorna o valor do índice | Se 'false', caso não exista o valor no arquivo .env
   */
  public function __get($index) {
    if(!isset($this->values[$index])) return false;

    return $this->values[$index];
  }

  /**
   * Método responsável por retornar o caminho do arquivo .env do projeto
   * @return string
   */
  private function getBashPath() {
    // REQUIRE .ENV DEV
    if(file_exists(dirname(__FILE__) . '/../../.env')) return dirname(__FILE__) . '/../../';

    // REQUIRE .ENV PRODUCTION
    if(file_exists(dirname(__FILE__) . '/../../../../../.env')) return dirname(__FILE__) . '/../../../../../';
  }
}