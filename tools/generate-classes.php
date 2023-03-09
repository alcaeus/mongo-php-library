<?php

use MongoDB\Aggregation\Converter\AbstractConverter;
use MongoDB\Aggregation\Stage;
use MongoDB\Aggregation\Converter\Stage as StageConverter;
use MongoDB\Aggregation\Generator\AggregationValueHolderGenerator;
use MongoDB\Aggregation\Generator\ConverterClassGenerator;
use MongoDB\Aggregation\PipelineOperator;
use Symfony\Component\Yaml\Yaml;

require __DIR__ . '/../vendor/autoload.php';

$configs = [
    'stages' => [
        [
            'configFile' => __DIR__ . '/../src/Aggregation/config/stages.yaml',
            // These are simple value holders, overwriting is explicitly wanted
            'overwrite' => true,
            'namespace' => Stage::class,
            'filePath' => __DIR__ . '/../src/Aggregation/Stage/',
            'interfaces' => [Stage::class],
        ],
        [
            'configFile' => __DIR__ . '/../src/Aggregation/config/stages.yaml',
            'generatorClass' => ConverterClassGenerator::class,
            'namespace' => StageConverter::class,
            'filePath' => __DIR__ . '/../src/Aggregation/Converter/Stage/',
            'parentClass' => AbstractConverter::class,
        ],
    ],
    'pipeline-operators' => [
        [
            'configFile' => __DIR__ . '/../src/Aggregation/config/pipeline-operators.yaml',
            // These are simple value holders, overwriting is explicitly wanted
            'overwrite' => true,
            'namespace' => PipelineOperator::class,
            'filePath' => __DIR__ . '/../src/Aggregation/PipelineOperator/',
        ],
    ],
];

$configName = $argv[1] ?? 'stages';
if (!isset($configs[$configName])) {
    throw new Exception(sprintf('No config "%s"', $configName));
}

$generators = $configs[$configName];

foreach ($generators as $generatorConfig) {
    $generatorClass = $generatorConfig['generatorClass'] ?? AggregationValueHolderGenerator::class;
    $objects = Yaml::parseFile($generatorConfig['configFile'], Yaml::PARSE_OBJECT_FOR_MAP);

    $generator = new $generatorClass(
        $generatorConfig['filePath'],
        $generatorConfig['namespace'],
        $generatorConfig['parentClass'] ?? null,
        $generatorConfig['interfaces'] ?? []
    );
    $generator->createClassesForObjects($objects, $generatorConfig['overwrite'] ?? false);
}
