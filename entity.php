<?php

$json = json_decode(file_get_contents("entity.json"), true);
foreach($json['entities'] as $key=>$entity)
{
    unset($json['entities'][$key]['columns']['data']);
    foreach($json['entities'][$key]['columns'] as $key2=>$column)
    {
        print_r($json['entities'][$key]['columns'][$key2]);
        unset($json['entities'][$key]['columns'][$key2]['description']);
        unset($json['entities'][$key]['columns'][$key2]['nullable']);
        unset($json['entities'][$key]['columns'][$key2]['values']);
        unset($json['entities'][$key]['columns'][$key2]['length']);
        unset($json['entities'][$key]['columns'][$key2]['default']);
        unset($json['entities'][$key]['columns'][$key2]['autoIncrement']);
    }
}
file_put_contents("entity.json", json_encode($json, JSON_PRETTY_PRINT));
