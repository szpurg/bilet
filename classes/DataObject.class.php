<?php

abstract class DataObject {
    abstract public function getDataIdentifier();
    protected $index = null;
    
    public function save($dataKey = 0, $allowMultiply = false, $index = null) {
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
                if (is_numeric($index)) {
                    $items = array_values($items);
                }
                $list[$dataKey] = $items;
            }
            else {
                unset($list[$dataKey]);
            }
            Application::saveData($this->getDataIdentifier(), $list);
        }
    }
    
    public function getIndex() {
        return $this->index;
    }

    public function setIndex($index) {
        $this->index = $index;
    }

    public static function fetch($identifier = 0, $index = null, array $constructorParams = array()) {
        $class = get_called_class();
        $list = $class::fetchList($constructorParams);
        
        if (isset($list[$identifier])) {
            $item = $list[$identifier];
            if ($index !== null && is_array($item) && isset($item[$index])) {
                $returner = $item[$index];
                if ($returner instanceof DataObject) {
                    $returner->setIndex($index);
                }
                return $returner;
            }
            return $item;
        }
        return null;
    }
    
    public static function fetchList(array $constructorParams = array()) {
        $class = get_called_class();
        if ($constructorParams) {
            $reflector = new ReflectionClass($class);
            $obj = $reflector->newInstanceArgs($constructorParams);
        }
        else {
            $obj = new $class;
        }
        $dataIdentifier = $obj->getDataIdentifier();
        $dataReturner = Application::loadData($dataIdentifier);
        $data = $dataReturner ? $dataReturner : array();
        
        foreach($data as $identifier => $item) {
            $identifierData = &$data[$identifier];
            if (is_array($item)) {
                foreach($item as $index => $dataValue) {
                    $value = &$identifierData[$index];
                    if ($value instanceof DataObject) {
                        $value->setIndex($index);
                    }
                }
            }
        }
        return $data ? $data : null;
    }
}
