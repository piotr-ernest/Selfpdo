<?php

/**
 * Description of Selfpdo
 *
 * @author rnest
 */
class Selfpdo
{

    /**
     *
     * @instance Selfpdo
     */
    private static $instance = null;
    private $pdo = null;
    private $select = null;
    private $table = null;
    private $from = null;
    private $where = array();
    private $limit = null;
    private $offset = null;
    private $order = null;
    private $innerJoin = null;
    private $leftJoin = null;
    private $rightJoin = null;

    private function __construct()
    {
        $configuration = ConfigHandler::getMain();
        $config = $configuration['db'];

        if (empty($config)) {
            throw new Exception('Error: Empty configuration.');
        }

        $dsn = $config['pdo'] . ':host=' . $config['host'] . ';dbname=' . $config['dbname'];
        $username = $config['username'];
        $password = $config['password'];
        $charset = $config['charset'];
        $options = array(
            PDO::ATTR_PERSISTENT => true
        );

        try {
            $this->pdo = new PDO($dsn, $username, $password, $options);
            $this->pdo->exec('SET NAMES ' . $charset);
        } catch (PDOException $ex) {
            echo "Some errors occured: " . $ex->getMessage() . '.';
            exit;
        }
    }

    public function __clone()
    {
        
    }

    /**
     * 
     * @return Selfpdo
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new Selfpdo();
        }
        return self::$instance;
    }

    public function select($cols)
    {
        $this->select = 'SELECT ' . $cols . ' ';
        return $this;
    }

    public function from($table)
    {
        $this->table = $table;
        $this->from = 'FROM ' . $table . ' ';
        return $this;
    }

    public function where($condition, $rowValue)
    {

        if (is_string($rowValue)) {
            $value = $this->pdo->quote($rowValue, PDO::PARAM_STR);
        } else {
            $value = $rowValue;
        }

        $exploded = explode(' ', $condition);

        if (empty($this->where)) {

            if (preg_match('/^IN$/', $exploded[1])) {
                if (!is_array($value)) {
                    throw new InvalidArgumentException('Value should be an array.');
                }
                $this->where[] = 'WHERE ' . $exploded[0] . ' IN(' . implode(',', $value) . ') ';
            } else {
                $this->where[] = 'WHERE ' . $exploded[0] . ' ' . $exploded[1] . ' ' . $value . ' ';
            }
        } else {

            if (preg_match('/^IN$/', $exploded[1])) {
                if (!is_array($value)) {
                    throw new InvalidArgumentException('Value should be an array.');
                }
                $this->where[] = 'AND ' . $exploded[0] . ' IN(' . implode(',', $value) . ') ';
            } else {
                $this->where[] = 'AND ' . $exploded[0] . ' ' . $exploded[1] . ' ' . $value . ' ';
            }
        }

        return $this;
    }

    public function orWhere($condition, $rowValue)
    {

        if (is_string($rowValue)) {
            $value = $this->pdo->quote($rowValue, PDO::PARAM_STR);
        } else {
            $value = $rowValue;
        }

        $exploded = explode(' ', $condition);

        if (preg_match('/^IN$/', $exploded[1])) {
            if (!is_array($value)) {
                throw new InvalidArgumentException('Value should be an array.');
            }
            $this->where[] = 'OR ' . $exploded[0] . ' IN(' . implode(',', $value) . ') ';
        } else {
            $this->where[] = 'OR ' . $exploded[0] . ' ' . $exploded[1] . ' ' . $value . ' ';
        }

        return $this;
    }

    public function order($cols, $type = 'ASC')
    {
        if(is_array($cols)){
            
            $order = '';
            
            while($c = each($cols)){
                $order .= $c['key'] . ' ' . $c['value'] . ', ';
            }
            
            $this->order = 'ORDER BY ' . rtrim($order, ', ') . ' ';
        } else {
            $this->order = 'ORDER BY ' . $cols . ' ' . $type . ' ';
        }
        
        return $this;
    }

    public function limit($limit, $offset = null)
    {
        if (!is_integer($limit)) {
            throw new InvalidArgumentException('Error: Limit must be an integer.');
        }

        if (null !== $offset && !is_integer($offset)) {
            throw new InvalidArgumentException('Error: Offset must be an integer.');
        }

        $this->limit = $limit;
        $this->offset = $offset;

        return $this;
    }
    
    public function innerJoin($tableName, $condition)
    {
        $this->innerJoin = 'INNER JOIN ' . $tableName . ' ON ' . $condition . ' ';
        return $this;
    }
    
    public function rightJoin($tableName, $condition)
    {
        $this->rightJoin = 'RIGHT JOIN ' . $tableName . ' ON ' . $condition . ' ';
        return $this;
    }
    
    public function leftJoin($tableName, $condition)
    {
        $this->leftJoin = 'LEFT JOIN ' . $tableName . ' ON ' . $condition . ' ';
        return $this;
    }

    /**
     * 
     * @return PDOStatement
     */
    public function createQuery()
    {
        $table = isset($this->table) ? $this->table : '';

        $from = isset($this->from) ? $this->from : '';
        $where = empty($this->where) ? array() : $this->where;
        $order = isset($this->order) ? $this->order : '';
        $innerJoin = isset($this->innerJoin) ? $this->innerJoin : '';
        $rightJoin = isset($this->rightJoin) ? $this->rightJoin : '';
        $leftJoin = isset($this->leftJoin) ? $this->leftJoin : '';

        $rowLimit = isset($this->limit) ? $this->limit : '';
        if (isset($rowLimit)) {
            $offset = isset($this->offset) ? ' OFFSET ' . $this->offset : '';
        }
        $limit = ($rowLimit !== '') ? 'LIMIT ' . $rowLimit . $offset . ' ' : '';

        $query = $this->select .
                $from .
                $innerJoin .
                $leftJoin .
                $rightJoin .
                implode('', $where) .
                $order .
                $limit;
        //desc($query,1);
        $results = $this->pdo->query($query);

        if ($results instanceof PDOStatement) {
            return $results;
        }

        return $this->pdo->prepare($query);
    }

    public function fetchAssoc(PDOStatement $ps)
    {
        return $ps->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchAll(PDOStatement $ps, $style = PDO::FETCH_ASSOC)
    {
        return $ps->fetchAll($style);
    }

    public function fetchObject(PDOStatement $ps)
    {
        return $ps->fetchAll(PDO::FETCH_OBJ);
    }

    public function fetchRow(PDOStatement $ps, $style = PDO::FETCH_ASSOC)
    {
        return $ps->fetch($style);
    }

    public function fetchIndexedArray(PDOStatement $ps)
    {
        return $ps->fetchAll(PDO::FETCH_NUM);
    }

    public function getLastInsertedID()
    {
        return $this->pdo->lastInsertId();
    }

    public function getLastInfo(PDOStatement $ps)
    {
        return array(
            'rows' => $ps->rowCount(),
            'cols' => $ps->columnCount(),
            'query' => $ps->queryString,
            'errors' => $ps->errorInfo()
        );
    }

}
