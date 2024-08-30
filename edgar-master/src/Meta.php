<?php

namespace Edgar;

class Meta
{
    protected $collections;
    protected $data = [];

    public function __construct($collections = [])
    {
        $this->collections = $collections;
    }

    public function addDetails($data = [])
    {
        $this->data = array_merge($this->data, $data);
    }

    public function findById($id, $idxName = null)
    {
        $cols = ($idxName !== null) ? array_column($this->data, $idxName) : array_keys($this->data);
        if (in_array($id, $cols)) {
            return $this->data[array_keys($this->data)[array_search($id, $cols)]];
        }
        return false;
    }

    public function findDetail($id)
    {
        if (stristr($id, ":")) {
            $id = explode(":", $id)[1];
        }
        foreach ($this->collections as $collection) {
            if ($collection->hasItem($id)) {
                $output = $collection->findItem($id);
                if (!empty($output)) {
                    return $output;
                }
            }
        }
        return null;
    }

    public function getCollectionItem($id)
    {
        return $this->collections[$id];
    }
}
