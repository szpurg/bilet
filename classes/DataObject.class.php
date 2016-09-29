<?php

abstract class DataObject {
    abstract public function getDataIdentifier();
    
    public function save($dataKey, $allowMultiply = false, $index = null) {
        $data = Application::loadData($this->getDataIdentifier());
            if ($allowMultiply) {
                if (!isset($data[$dataKey])) {
                    $data[$dataKey] = array();
                }
                if ($index !== null) {
                    $data[$dataKey][$index] = $this;
                }
                else {
                    $data[$dataKey][] = $this;
                }
            }
            else {
                $data[$dataKey] = $this;
            }
        Application::saveData($this->getDataIdentifier(), $data);
    }
    
    public function delete($dataKey, $index = null) {
        $class = get_called_class();
        $list = $class::fetchList();
        if (isset($list[$dataKey])) {
            $items = $list[$dataKey];
            if ($index !== null && isset($items[$index])) {
                unset($items[$index]);
                $items = array_values($items);
                $list[$dataKey] = $items;
            }
            else {
                unset($list[$dataKey]);
            }
            Application::saveData($this->getDataIdentifier(), $list);
        }
    }
    
    public static function fetch($identifier, $index = null) {
        $class = get_called_class();
        $list = $class::fetchList();
        
        if (isset($list[$identifier])) {
            $item = $list[$identifier];
            if ($index !== null && is_array($item) && isset($item[$index])) {
                return $item[$index];
            }
            return $item;
        }
        return null;
    }
    
    public static function fetchList() {
        $class = get_called_class();
        $obj = new $class;
        $dataIdentifier = $obj->getDataIdentifier();
        return Application::loadData($dataIdentifier);
    }
}
