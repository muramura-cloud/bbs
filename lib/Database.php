<?php

class Database
{
    protected $table_name;
    protected $dbh;

    public function __construct()
    {
        $this->dbConnect();
    }

    public function dbConnect()
    {
        $db_host = DB_HOST;
        $db_name = DB_NAME;

        $dbh = new PDO("mysql:host={$db_host};dbname={$db_name};charset=utf8", DB_USER, DB_PASS, [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);

        $this->dbh = $dbh;
    }

    public function insertRecord($inputs)
    {
        $col_names = array_keys($inputs);

        $stmt = $this->dbh->prepare("INSERT INTO {$this->table_name} (" . implode(',', $col_names) . ') VALUES (:' . implode(', :', $col_names) . ')');

        foreach ($inputs as $col_name => $value) {
            $stmt->bindValue(':' . $col_name, $value);
        }

        $stmt->execute();
    }

    public function getRecordById($id)
    {
        return $this->getRecords([
            [
                'col_name' => 'id',
                'operator' => '=',
                'value'    => $id,
            ]
        ])[0];
    }

    public function getRecordCount($wheres = [])
    {
        return $this->getRecords($wheres, [], [], 'COUNT(*) AS cnt')[0]['cnt'];
    }

    public function getRecords($wheres = [], $orders = [], $limit = [], $column = '*')
    {
        $sql    = "SELECT {$column} FROM {$this->table_name}";
        $params = [];

        if (!empty($wheres)) {
            $sql    = $this->addWhereClause($sql, $wheres);
            $params = $this->setWhereParams($params, $wheres);
        }

        if (!empty($orders)) {
            $sql = $this->addOrderByClause($sql, $orders);
        }

        if (!empty($limit)) {
            $sql    = $this->addLimitClause($sql, $limit);
            $params = $this->setLimitParams($params, $limit);
        }

        $stmt = $this->dbh->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }


    public function updateRecordById($id, $inputs)
    {
        $this->updateRecord($inputs, [
            [
                'col_name' => 'id',
                'operator' => '=',
                'value'    => $id,
            ]
        ]);
    }

    public function updateRecord($inputs, $wheres = [])
    {
        $sql    = "UPDATE {$this->table_name} SET";
        $params = [];

        $items = [];
        foreach ($inputs as $col_name => $value) {
            $items[]           = " {$col_name} = :{$col_name}";
            $params[$col_name] = $value;
        }
        $sql .= implode(' ,', $items);

        if (!empty($wheres)) {
            $sql    = $this->addWhereClause($sql, $wheres);
            $params = $this->setWhereParams($params, $wheres);
        }

        $stmt = $this->dbh->prepare($sql);
        $stmt->execute($params);
    }

    public function deleteRecordById($id)
    {
        $this->deleteRecord([
            [
                'col_name' => 'id',
                'operator' => '=',
                'value'    => $id,
            ]
        ]);
    }

    public function deleteRecord($wheres = [])
    {
        $sql    = "DELETE FROM {$this->table_name}";
        $params = [];

        if (!empty($wheres)) {
            $sql    = $this->addWhereClause($sql, $wheres);
            $params = $this->setWhereParams($params, $wheres);
        }

        $stmt = $this->dbh->prepare($sql);
        $stmt->execute($params);
    }

    private function addWhereClause($sql, $wheres)
    {
        $sql .= ' WHERE ';

        $items = [];
        foreach ($wheres as $where) {
            $col_name = $where['col_name'];
            $items[]  = "{$col_name} {$where['operator']} :where_{$col_name}";
        }
        $sql .= implode(' AND ', $items);

        return $sql;
    }

    private function setWhereParams($params, $wheres)
    {
        foreach ($wheres as $where) {
            $params['where_' . $where['col_name']] = $where['value'];
        }

        return $params;
    }

    private function addOrderByClause($sql, $orders)
    {
        $sql .= ' ORDER BY ';

        $items = [];
        foreach ($orders as $col_name => $sort_pattern) {
            $items[] = "{$col_name} {$sort_pattern}";
        }
        $sql .= implode(', ', $items);

        return $sql;
    }

    private function addLimitClause($sql, $limit)
    {
        $sql .= ' LIMIT :limit';
        if (isset($limit['offset'])) {
            $sql .= ' OFFSET :offset';
        }

        return $sql;
    }

    private function setLimitParams($params, $limit)
    {
        $params['limit'] = $limit['limit'];
        if (isset($limit['offset'])) {
            $params['offset'] = $limit['offset'];
        }

        return $params;
    }
}
