<?php declare(strict_types=1);

namespace XoopsModules\Publisher;

/**
 * Class TrelloDBController
 */
class TrelloDBController
{
    /** @var \XoopsMySQLDatabase*/
    private $db;

    /**
     * TrelloDBController constructor.
     * @param \XoopsMySQLDatabase $xoopsDb
     */
    public function __construct($xoopsDb)
    {
        $this->db = $xoopsDb;
    }

    /**
     * @param string $query
     * @return mixed
     */
    public function runBaseQuery($query)
    {
        $resultset = [];
        $result    = $this->db->conn->query($query);
        if ($result->num_rows > 0) {
            while (null !== ($row = $result->fetch_assoc())) {
                $resultset[] = $row;
            }
        }

        return $resultset;
    }

    /**
     * @param string $query
     * @param string $paramType
     * @param array $paramValueArray
     * @return mixed
     */
    public function runQuery($query, $paramType, $paramValueArray)
    {
        $sql = $this->db->conn->prepare($query);
        $this->bindQueryParams($sql, $paramType, $paramValueArray);
        $sql->execute();
        $result = $sql->get_result();

        if ($result->num_rows > 0) {
            while (null !== ($row = $result->fetch_assoc())) {
                $resultset[] = $row;
            }
        }

        if (!empty($resultset)) {
            return $resultset;
        }

        return false;
    }

    /**
     * @param mysqli_stmt $sql
     * @param string $paramType
     * @param array $paramValueArray
     */
    public function bindQueryParams($sql, $paramType, $paramValueArray)
    {
        $paramValueReference = [];
        $paramValueReference[] = &$paramType;
        foreach ($paramValueArray as $i => $iValue) {
            $paramValueReference[] = &$paramValueArray[$i];
        }
        \call_user_func_array(
            [
                $sql,
                'bind_param',
            ],
            $paramValueReference
        );
    }

    /**
     * @param string $query
     * @param string $paramType
     * @param array $paramValueArray
     */
    public function insert($query, $paramType, $paramValueArray)
    {
        $sql = $this->db->conn->prepare($query);
        $this->bindQueryParams($sql, $paramType, $paramValueArray);
        $sql->execute();
    }

    /**
     * @param string $query
     * @param string $paramType
     * @param array $paramValueArray
     */
    public function update($query, $paramType, $paramValueArray)
    {
        $sql = $this->db->conn->prepare($query);
        $this->bindQueryParams($sql, $paramType, $paramValueArray);
        $sql->execute();
    }
}
