<?php

namespace lib\db\file;

use lib\db\DbStorage;
use lib\db\QueryParser;
use lib\utils\Properties;

class FileDbStorage extends DbStorage
{
    /**
     * @param string $query
     * @param integer $limit
     * @param integer $skip
     * @return array
     */
    public function query($query, $limit = -1, $skip = 0)
    {
        $toSort = [];
        $this->queryParser->parse($query, $this->dataType, $this->extraTypes);
        foreach ($this->items as $item) {
            if ($this->queryParser->execute($item)) {
                if ($this->queryParser->hasGroupBy() || $this->queryParser->hasOrderBy()) {
                    $toSort[] = $item;
                } else {
                    if ($limit == 0) {
                        break;
                    }
                    if ($skip > 0) {
                        $skip--;
                        continue;
                    }
                    $toSort[] = $item;
                    if ($limit == -1) {
                        continue;
                    }
                    $limit--;
                }
            }
        }
        if ($this->queryParser->hasGroupBy() || $this->queryParser->hasOrderBy()) {
            $this->queryParser->doSort($toSort);
            return array_slice($this->queryParser->doGroupBy($toSort), $skip, ($limit == -1 ? null : $limit));
        }
        return $toSort;
    }

    /**
     * @param string $query
     * @return integer
     */
    public function count($query)
    {
        $toSort = [];
        $count = 0;
        $this->queryParser->parse($query, $this->dataType, $this->extraTypes);
        foreach ($this->items as $item) {
            if ($this->queryParser->execute($item)) {
                $count++;
                if ($this->queryParser->hasGroupBy()) {
                    $toSort[] = $item;
                }
            }
        }

        if ($this->queryParser->hasGroupBy()) {
            $count = sizeof($this->queryParser->doGroupBy($toSort));
        }
        return $count;
    }

    /**
     * @param string $query
     * @param int $limit
     * @param int $skip
     * @param int $count
     * @return array|mixed
     */
    public function queryAndCount($query, $limit, $skip, &$count)
    {
        $count = 0;
        $toSort = [];
        $this->queryParser->parse($query, $this->dataType, $this->extraTypes);
        foreach ($this->items as $item) {
            if ($this->queryParser->execute($item)) {
                if ($this->queryParser->hasGroupBy() || $this->queryParser->hasOrderBy()) {
                    $toSort[] = $item;
                } else {
                    $count++;
                    if ($limit == 0) {
                        continue;
                    }
                    if ($skip > 0) {
                        $skip--;
                        continue;
                    }
                    $toSort[] = $item;
                    if ($limit == -1) {
                        continue;
                    }
                    $limit--;
                }
            }
        }
        if ($this->queryParser->hasGroupBy() || $this->queryParser->hasOrderBy()) {
            $this->queryParser->doSort($toSort);
            $partial = $this->queryParser->doGroupBy($toSort);
            $count = sizeof($partial);
            return array_slice($partial, $skip, $limit == -1 ? null : $limit);
        }
        return $toSort;
    }

    /**
     * @param mixed $item
     * @param bool $param
     * @return void
     */
    public function save($item, $add)
    {
        throw new \Exception();
    }

    /**
     * @param array $foundedUsers
     * @param string|null $query
     * @return void
     */
    public function delete($foundedUsers, $query)
    {
        throw new \Exception();
    }
}