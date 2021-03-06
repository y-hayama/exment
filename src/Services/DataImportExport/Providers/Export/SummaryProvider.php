<?php

namespace Exceedone\Exment\Services\DataImportExport\Providers\Export;

class SummaryProvider extends DefaultTableProvider
{
    protected $custom_view;

    protected $is_summary;

    protected $summary_index_and_view_columns;
    
    public function __construct($args = [])
    {
        parent::__construct($args);

        $this->custom_view = array_get($args, 'custom_view');

        $this->is_summary = array_get($args, 'is_summary')?? false;

        $this->summary_index_and_view_columns = $this->custom_view->getSummaryIndexAndViewColumns();
    }

    /**
     * get data
     */
    public function data()
    {
        // get header and body
        $headers = $this->getHeaders(null);

        // if only template, output only headers
        if ($this->template) {
            $bodies = [];
        } else {
            $bodies = $this->getBodies($this->getRecords(), null);
        }
        // get output items
        $outputs = array_merge($headers, $bodies);

        return $outputs;
    }

    /**
     * get export headers
     * contains custom column name, column view name
     */
    protected function getHeaders($columnDefines)
    {
        // create 2 rows.
        $rows = [];
        
        // 1st row, column name
        $rows[] = collect($this->summary_index_and_view_columns)->map(function ($summary_index_and_view_column) {
            $item = array_get($summary_index_and_view_column, 'item');
            return $item->column_item->name() ?? null;
        })->toArray();
        
        $rows[] = collect($this->summary_index_and_view_columns)->map(function ($summary_index_and_view_column) {
            $item = array_get($summary_index_and_view_column, 'item');
            return array_get($item, 'view_column_name')?? $item->column_item->label();
        })->toArray();
        
        return $rows;
    }

    /**
     * get target chunk records
     */
    public function getRecords()
    {
        $this->grid->applyQuickSearch();
        $records = collect($this->grid->getFilter()->execute(false));
        return $records;
    }

    /**
     * get export bodies
     */
    protected function getBodies($records, $columnDefines)
    {
        if (!isset($records)) {
            return [];
        }
        
        $bodies = [];

        foreach ($records as $record) {
            // add items
            $body_items = collect($this->summary_index_and_view_columns)->map(function ($summary_index_and_view_column) use ($record) {
                $index = array_get($summary_index_and_view_column, 'index');
                $item = array_get($summary_index_and_view_column, 'item');

                $options = [
                    'disable_number_format' => true,
                    'disable_currency_symbol' => true,
                    'view_pivot_column' => $item->view_pivot_column_id ?? null,
                    'view_pivot_table' => $item->view_pivot_table_id ?? null,
                ];

                if ($this->is_summary) {
                    $options['summary'] = true;
                    $options['summary_index'] = $index;
                }

                return $item->column_item->options($options)
                    ->setCustomValue($record)->text();
                //return array_get($record, 'column_' . $index);
            })->toArray();

            $bodies[] = $body_items;
        }

        return $bodies;
    }
}
