<?php

namespace AWF\Extension\Helpers\Models;

class ObjectToArray
{
    /**
     * @return array
     */
    public function get(): array
    {
        $data = [];

        foreach ($this as $key => $item) {
            if ($item instanceof self) {
                $data[$key] = $item->get();
            }
            else {
                $data[$key] = $item;
            }
        }

        return $data;
    }
}
