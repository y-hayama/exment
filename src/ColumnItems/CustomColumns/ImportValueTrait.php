<?php

namespace Exceedone\Exment\ColumnItems\CustomColumns;

trait ImportValueTrait
{
    /**
     * replace value for import
     *
     * @param mixed $value
     * @param array $setting
     * @return void
     */
    public function getImportValue($value, $setting = [])
    {
        $isMultiple = is_array($value) || boolval(array_get($this->custom_column, 'options.multiple_enabled'));
        $result = true;
        $options = $this->getImportValueOption();
        
        ///// not default value check
        // to array
        $value = stringToArray($value);

        // replace value
        $list = [];
        foreach ($value as $v) {
            $k = $this->matchValue($v, $options);
            if ($k === null) {
                break;
            }
            $list[] = $k;
        }

        if (count($value) == count($list)) {
            $value = $isMultiple ? $list : $list[0];
        } else {
            $result = false;
        }

        return [
            'result' => $result,
            'value' =>  $value,
            'message' => !$result ? exmtrans('custom_value.import.message.select_item_not_found', [
                'column_view_name' => $this->label(),
                'value_options' => implode(exmtrans('common.separate_word'), collect($options)->keys()->toArray())
            ]) : null
        ];
    }

    /**
     * Match options with key
     *
     * @param [type] $v
     * @param [type] $options
     * @return void
     */
    protected function matchValue($v, $options){
        // find value function
        $findFunc = function($v, ...$keys){
            foreach($keys as $key){
                if(strcmp($v, $key) == 0){
                    return $key;
                }
            }

            return null;
        };

        foreach($options as $key => $option){
            $matchV = $findFunc($v, $key, $option);
            // if(is_vector($options)){
            //     // is vector (Ex. ['a', 'b', 'c',]), only value
            //     $matchV = $findFunc($v, $option);
            // }
            // else{
            //     // is not vector (Ex. ['foo' => 0, 'bar' => 1]), key and value
            //     $matchV = $findFunc($v, $key, $option);
            // }

            if(!is_null($matchV)){
                return $matchV;
            }
        }

        return null;
    }
}
