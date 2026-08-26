<?php

abstract class BaseModel
{
    public function __construct(array $data = array())
    {
        $this->fill($data);
    }

    public function fill(array $data)
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    public function toArray()
    {
        return get_object_vars($this);
    }
}
