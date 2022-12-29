# DB MANAGER
  * [Gerenciador de conexões com banco de dados](#gerenciador-de-consultas);
  * [Modelagem de tabelas](#modelagem-de-tabelas);
  * Versionador de banco de dados;

# REQUISITOS
  * `php`: 7.2^
  * `composer`: 2.4.4^

# UTILIZAÇÃO

## CONFIGURAÇÃO
  * Realize a configuração do arquivo `.env`, no mesmo nível do arquivo `composer.json`;

  * **PARÂMETROS REQUERIDOS:**
    * *DB_HOST:* Host onde banco de dados do projeto está armazenado;  
    * *DB_DATABASE:* Nome do banco de dados;
    * *DB_USERNAME:* Nome do usuário do banco; 
    * *DB_PASSWORD:* Senha do banco de dados
    * *DB_PORT:* Porta de conexão com o banco;
    * *DB_CONNECTION:* Driver de conexão; 
    * *DB_CHARSET:* Tipo da colação dos dados adicionados ao banco; 
    * *DB_OUTPUT_MESSAGE:* Define como as mensagens de erro de execução de consultas serão exibidas (html, json, default);
    * *DB_PROTECTED_MODE:* Define se as mensagens de erro serão exibidas na íntegra. 

  ```env
    DB_HOST=localhost
    DB_DATABASE=dbname
    DB_USERNAME=user
    DB_PASSWORD=123
    DB_PORT=3306
    DB_CONNECTION=mysql
    DB_CHARSET=utf8
    DB_OUTPUT_MESSAGE=default
    DB_PROTECTED_MODE=true
  ```

## **Inicializando a classe:**
```php
  <?php

  use \MatheusV\DBManager\Db\Sql;

  $sql = new Sql('name_table');
```

---

# GERENCIADOR DE CONSULTAS

## **Realizando uma consulta:**

#### **function select():**
* *PARÂMETROS:*
  * **campo:** Define os campos que serão retornados na consulta;
    * *DEFAULT:*  Todos os campos da tabela;
    * *OBRIGRATÓRIO:* não;

```php
  <?php

  use \MatheusV\DBManager\Db\Sql;

  $sql        = new Sql('pessoa');
  $resultados = $sql->select('id, nome_pessoa')->send()->fetchAll(PDO::FETCH_ASSOC);

  print_r($resultados);
```

```html
  Array
  (
    [0] => Array
        (
          [id] => 1
          [nome_pessoa] => Pessoa 1
        )

    [1] => Array
        (
          [id] => 2
          [nome_pessoa] => Pessoa 2
        )
)
```

---

## **Inserindo ou atualizando um registro:**

#### **function insert():**
#### **function replace():**
#### **function update():**
* *PARÂMETROS:*
  * **dados:** Define quais os dados serão inseridos;
    * *REQUISITOS:* Uma array com os dados e os campos da tabela, ou um objeto da tabela implementado por `\MatheusV\DBManager\Table\ModeloTabela`;
    * *OBRIGRATÓRIO:* sim;

  * **camposSemAspas:** Define quais os campos que não terão aspas inclusas quando a query for gerada;
    * *REQUISITOS:* Uma array informando os campos sem aspas;
    * *OBRIGRATÓRIO:* não;
    * *DEFAULT:* array();
  
  * **ignore:** Adiciona o `ignore` a query;
    * *REQUISITOS:* Uma array informando os campos sem aspas;
    * *OBRIGRATÓRIO:* não;
    * *DEFAULT:* false;

```php
  <?php

  use \MatheusV\DBManager\Db\Sql;
  use \MatheusV\DBManager\Table\ModeloTabela;

  class Pessoa implements ModeloTabela {
    private $id   = null;
    private $nome = null;

    public function getProperties() : array {
      return [
        'id'         => 'id',
        'nomePessoa' =>  'nome_pessoa'
      ];
    }

    public function camposSemAspas(): array {
      return ['id']; 
    }
  }

  $sql            = new Sql('pessoa');
  $dados          = ['id' => 1, 'nome' => 'Pessoa 3'];
  $camposSemAspas = ['id'];

  // INSERE UM NOVO REGISTRO VIA ARRAY DE DADOS
  $sql->insert($dados, $camposSemAspas)->send();
  // ATUALIZA UM REGISTRO
  $sql->update($dados, $camposSemAspas)->where(['id = 2'])->send();
  // ATUALIZA OU INSERE UM REGISTRO
  $sql->replace($dados, $camposSemAspas)->send();

  // OU
  $obUsuario            = new Pessoa;
  $obPessoa->id         = 1;
  $obPessoa->nomePessoa = "Pessoa 3";

  // INSERE UM NOVO REGISTRO COM UM OBJETO DE PESSOA
  $sql->insert($obPessoa, $obPessoa->camposSemAspas(), true)->send();
  // ATUALIZA UM REGISTRO
  $sql->update($obPessoa, $obPessoa->camposSemAspas(), true)->where(['id = 2'])->send();
  // ATUALIZA OU INSERE UM REGISTRO
  $sql->replace($obPessoa, $obPessoa->camposSemAspas(), true)->send();
```

---

## **Removendo um registro:**

### **function delete():**

```php
  <?php

  use \MatheusV\DBManager\Db\Sql;

  $sql = new Sql('pessoa');
  $sql->delete()->where(['id = 1'])->send();
```

---

## OPÇÕES AVANÇADAS

### **function send():**
* *PARÂMETROS:*
  * **prepare:** Força a utilização do método `prepare()` do PDO na consulta;
    * *DEFAULT:* false;
    * *OBRIGRATÓRIO:* não;

```php
  <?php

  use \MatheusV\DBManager\Db\Sql;

  $sql = new Sql('pessoa');
  $sql->select()->send(true);
```

---

### **function where():**
* *PARÂMETROS:*
  * **condicoes:** Define as condições para a consulta;
    * *REQUISITOS:* Uma array com as condições da consulta;
    * *DEFAULT:* array();
    * *OBRIGRATÓRIO:* não;

```php
  <?php

  use \MatheusV\DBManager\Db\Sql;

  $sql       = new Sql('pessoa');
  $condicoes = [
    'id > 1', 'nome LIKE "Pessoa%"'
  ];

  $sql->select()->where($condicoes)->send();
```
---

### **function order():**
* *PARÂMETROS:*
  * **orders:** Define as ordenações dos dados consultados;
    * *REQUISITOS:* Uma array com as ordenações dos dados;
    * *DEFAULT:* array();
    * *OBRIGRATÓRIO:* não;

```php
  <?php

  use \MatheusV\DBManager\Db\Sql;

  $sql   = new Sql('pessoa');
  $order = [
    'ASC'  => 'id',
    'DESC' => 'nome'
  ];

  $sql->select()->order($order)->send();
```

---

### **function group():**
* *PARÂMETROS:*
  * **groups:** Define o agrupamento de dados;
    * *REQUISITOS:* Uma array com os agrupamentos;
    * *DEFAULT:* array();
    * *OBRIGRATÓRIO:* não;

```php
  <?php

  use \MatheusV\DBManager\Db\Sql;

  $sql   = new Sql('pessoa');
  $group = ['id_profissao'];

  $sql->select()->group($group)->send();
```

---

### **function innerJoin():**
### **function leftJoin():**
* *PARÂMETROS:*
  * **joins:** Define as tabelas que serão adicionadas a uma consulta;
    * *REQUISITOS:* Uma array, definindo a tabela e as condições de adição;
    * *DEFAULT:* array();
    * *OBRIGRATÓRIO:* não;

```php
  <?php

  use \MatheusV\DBManager\Db\Sql;

  $sql  = new Sql('pessoa');
  $join = [
    'profissao' => 'profissao.id = pessoa.id_profissao'
  ];

  $sql->select()->innerJoin($join)->send();
  $sql->select()->leftJoin($join)->send();
```

---

### **function setLimit():**
* *PARÂMETROS:*
  * **limit:** Define o limite de registros que vão ser buscados;
    * *REQUISITOS:* Número inteiro do limite de registros;
    * *OBRIGRATÓRIO:* sim;

```php
  <?php

  use \MatheusV\DBManager\Db\Sql;

  $sql = new Sql('pessoa');
  $sql->select()->setLimit(10)->send();
```

---

### **function setOffset():**
* *PARÂMETROS:*
  * **offset:** Define o deslocamento dos dados da consulta;
    * *REQUISITOS:* Número inteiro, que define o deslocamento de dados;
    * *OBRIGRATÓRIO:* sim;

```php
  <?php

  use \MatheusV\DBManager\Db\Sql;

  $sql = new Sql('pessoa');
  $sql->select()->setOffset(10)->send();
```

# MODELAGEM DE TABELAS

Nesse pacote, é possível definir classes modelos de tabelas, tanto para utilizar no código, quanto para realizar consultas utilizando o gerenciador de conexões.

Para definir um modelo de tabela, é necessário utilizar as seguintes classes:
  * `use \MatheusV\DBManager\Table\ModeloTabela`;
  * `use \MatheusV\DBManager\Table\GetSet`;

**EX:**
```
Tabela: pessoa

| # | Nome         | Tipo de dados | Permitir NULL | Padrão         |
| - | ------------ | ------------- | ------------- | -------------- |
| 1 | id           | INT           | false         | AUTO_INCREMENT |
| 2 | nome         | VARCHAR       | false         | Nenhum padrão  |
| 3 | id_profissao | INT           | true          | Nenhum padrão  |
```

```php
  <?php

  use \MatheusV\DBManager\Table\{GetSet, ModeloTabela};

  // CLASSE MODELO DA TABELA `pessoa`
  class Pessoa implements ModeloTabela {
    use GetSet;

    private $id          = null;
    private $nome        = null;
    private $idProfissao = null;

    public function getProperties() : array {
      // [propriedadeClasse => campo_tabela]
      return [
        'id'          => 'id',
        'nome'        => 'nome',
        'idProfissao' => 'id_profissao'
      ];
    }

    public function camposSemAspas() : array {
      return ['id', 'id_profissao'];
    }
  }

```
---

## DEFININDO VALORES PARA A CLASSE MODELO

É possível adicionar valores a classe de duas maneiras:
  * Adicionando o valor a propriedade da classe;
  * Passando uma array com as propriedades e valores da classe;

**EX:**
```php
  <?php
  $obPessoa = new Pessoa;

  // ADICIONANDO VALORES ATRAVÉS DAS PROPRIEDADES DA CLASSE
  $obPessoa->id          = 1;
  $obPessoa->nome        = 'Pessoa 1';
  $obPessoa->idProfissao = 1;
  print_r($obPessoa);

  // ADICIONANDO VALORES ATRAVÉS DE UMA ARRAY DE DADOS
  $dados = [
    'id'          => 2,
    'nome'        => 'Pessoa 2',
    'idProfissao' => 2
  ];
  $obPessoa->setData($dados);
  print_r($obPessoa);

```

```
  Pessoa Object
  (
    [id:Pessoa:private] => 1
    [nome:Pessoa:private] => Pessoa 1
    [idProfissao:Pessoa:private] => 1
  )

  Pessoa Object
  (
    [id:Pessoa:private] => 2
    [nome:Pessoa:private] => Pessoa 2
    [idProfissao:Pessoa:private] => 2
  )
```

---

## RETORNANDO OS DADOS DA CLASSE

Também é possível retornar valores atribuídos a classe em forma de array, utilizando o método `getAttributes()`. Ele possui dois parâmetros:
  * **toClass:** Retorna os índices da array, formatados como os parâmetros da classe;
    * ***DEFAULT:*** true.

  * **others:** Retorna os campos que não existirem nos parâmentros da classe;
    * ***DEFAULT:*** false.

**EX:**
```php
  <?php
  $obPessoa              = new Pessoa;
  $obPessoa->id          = 1;
  $obPessoa->nome        = 'Pessoa 1';
  $obPessoa->idProfissao = 1;
  $obPessoa->ativo       = 's';

  print_r($obPessoa->getAttributes());
  print_r($obPessoa->getAttributes(true, true));
  print_r($obPessoa->getAttributes(false));
  print_r($obPessoa->getAttributes(false, true));
```

```
  Array
  (
    [id] => 1
    [nome] => Pessoa 1
    [idProfissao] => 1
  )

  Array
  (
    [id] => 1
    [nome] => Pessoa 1
    [idProfissao] => 1
    [ativo] => s
  )

  Array
  (
    [id] => 1
    [nome] => Pessoa 1
    [id_profissao] => 1
  )

  Array
  (
    [id] => 1
    [nome] => Pessoa 1
    [id_profissao] => 1
    [ativo] => s
  )
```

