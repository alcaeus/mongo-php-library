<?php

use MongoDB\Aggregation\PipelineOperator;
use MongoDB\Aggregation\Generator\AggregationClassGenerator;
use MongoDB\Aggregation\Stage;
use Symfony\Component\Yaml\Yaml;

require __DIR__ . '/../vendor/autoload.php';

$configs = [
    'stages' => [
        'configFile' => __DIR__ . '/../src/Aggregation/config/stages.yaml',
        'namespace' => Stage::class,
        'filePath' => __DIR__ . '/../src/Aggregation/Stage/',
        'parentClass' => Stage::class,
    ],
    'pipeline-operators' => [
        'configFile' => __DIR__ . '/../src/Aggregation/config/pipeline-operators.yaml',
        'namespace' => PipelineOperator::class,
        'filePath' => __DIR__ . '/../src/Aggregation/PipelineOperator/',
    ],
];

if (!isset($configs[$argv[1]])) {
    throw new Exception(sprintf('No config "%s"', $argv[1]));
}

$config = $configs[$argv[1]];

$objects = Yaml::parseFile($config['configFile'], Yaml::PARSE_OBJECT_FOR_MAP);
$stageGenerator = new AggregationClassGenerator($config['filePath'], $config['namespace'], $config['parentClass'] ?? null);
$stageGenerator->createClassesForObjects($objects, true);
