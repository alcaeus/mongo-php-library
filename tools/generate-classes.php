<?php

use MongoDB\Aggregation\Converter\AbstractConverter;
use MongoDB\Aggregation\Converter;
use MongoDB\Aggregation\Factory;
use MongoDB\Aggregation\FactoryFunctions;
use MongoDB\Aggregation\Generator\FactoryClassGenerator;
use MongoDB\Aggregation\Stage;
use MongoDB\Aggregation\Converter\Stage as StageConverter;
use MongoDB\Aggregation\Converter\PipelineOperator as PipelineOperatorConverter;
use MongoDB\Aggregation\Converter\QueryOperator as QueryOperatorConverter;
use MongoDB\Aggregation\Generator\AggregationValueHolderGenerator;
use MongoDB\Aggregation\Generator\ConverterClassGenerator;
use MongoDB\Aggregation\PipelineOperator;
use MongoDB\Aggregation\QueryOperator;
use Symfony\Component\Yaml\Yaml;

require __DIR__ . '/../vendor/autoload.php';

$configs = [
    'stages' => [
        [
            // Stage expression classes
            'configFile' => __DIR__ . '/../src/Aggregation/Generator/config/stages.yaml',
            'overwrite' => true,
            'namespace' => Stage::class,
            'filePath' => __DIR__ . '/../src/Aggregation/Stage/',
            'interfaces' => [Stage::class],
            'classNameSuffix' => 'Stage',
        ],
        [
            // Stage converters
            'configFile' => __DIR__ . '/../src/Aggregation/Generator/config/stages.yaml',
            'generatorClass' => ConverterClassGenerator::class,
            'namespace' => Converter\Stage::class,
            'filePath' => __DIR__ . '/../src/Aggregation/Converter/Stage/',
            'parentClass' => AbstractConverter::class,
            'classNameSuffix' => 'StageConverter',
            'supportingNamespace' => Stage::class,
            'supportingClassNameSuffix' => 'Stage',
            'libraryNamespace' => Converter::class,
            'libraryClassName' => 'StageConverter',
        ],
        [
            // Factory
            'configFile' => __DIR__ . '/../src/Aggregation/Generator/config/stages.yaml',
            'overwrite' => true,
            'generatorClass' => FactoryClassGenerator::class,
            'className' => 'StageFactory',
            'namespace' => Factory::class,
            'filePath' => __DIR__ . '/../src/Aggregation/Factory/',
            'supportingNamespace' => Stage::class,
            'supportingClassNameSuffix' => 'Stage',
            'functionNamespace' => FactoryFunctions\Stage::class,
            'functionFilePath' => __DIR__ .'/../src/Aggregation/FactoryFunctions/',

        ],
    ],
    'pipeline-operators' => [
        [
            'configFile' => __DIR__ . '/../src/Aggregation/Generator/config/pipeline-operators.yaml',
            // These are simple value holders, overwriting is explicitly wanted
            'overwrite' => true,
            'namespace' => PipelineOperator::class,
            'filePath' => __DIR__ . '/../src/Aggregation/PipelineOperator/',
            'classNameSuffix' => 'PipelineOperator',
        ],
        [
            'configFile' => __DIR__ . '/../src/Aggregation/Generator/config/pipeline-operators.yaml',
            'overwrite' => true,
            'generatorClass' => ConverterClassGenerator::class,
            'namespace' => Converter\PipelineOperator::class,
            'filePath' => __DIR__ . '/../src/Aggregation/Converter/PipelineOperator/',
            'parentClass' => AbstractConverter::class,
            'classNameSuffix' => 'PipelineOperatorConverter',
            'supportingNamespace' => PipelineOperator::class,
            'supportingClassNameSuffix' => 'PipelineOperator',
            'libraryNamespace' => Converter::class,
            'libraryClassName' => 'PipelineOperatorConverter',
        ],
        [
            // Factory
            'configFile' => __DIR__ . '/../src/Aggregation/Generator/config/pipeline-operators.yaml',
            'overwrite' => true,
            'generatorClass' => FactoryClassGenerator::class,
            'className' => 'PipelineOperatorFactory',
            'namespace' => Factory::class,
            'filePath' => __DIR__ . '/../src/Aggregation/Factory/',
            'supportingNamespace' => PipelineOperator::class,
            'supportingClassNameSuffix' => 'PipelineOperator',
            'functionNamespace' => FactoryFunctions\PipelineOperator::class,
            'functionFilePath' => __DIR__ .'/../src/Aggregation/FactoryFunctions/',
        ],
    ],
    'query-operators' => [
        [
            'configFile' => __DIR__ . '/../src/Aggregation/Generator/config/query-operators.yaml',
            // These are simple value holders, overwriting is explicitly wanted
            'overwrite' => true,
            'namespace' => QueryOperator::class,
            'filePath' => __DIR__ . '/../src/Aggregation/QueryOperator/',
            'classNameSuffix' => 'QueryOperator',
        ],
        [
            'configFile' => __DIR__ . '/../src/Aggregation/Generator/config/query-operators.yaml',
            'overwrite' => true,
            'generatorClass' => ConverterClassGenerator::class,
            'namespace' => Converter\QueryOperator::class,
            'filePath' => __DIR__ . '/../src/Aggregation/Converter/QueryOperator/',
            'parentClass' => AbstractConverter::class,
            'classNameSuffix' => 'QueryOperatorConverter',
            'supportingNamespace' => QueryOperator::class,
            'supportingClassNameSuffix' => 'QueryOperator',
            'libraryNamespace' => Converter::class,
            'libraryClassName' => 'QueryOperatorConverter',
        ],
        [
            // Factory
            'configFile' => __DIR__ . '/../src/Aggregation/Generator/config/query-operators.yaml',
            'overwrite' => true,
            'generatorClass' => FactoryClassGenerator::class,
            'className' => 'QueryOperatorFactory',
            'namespace' => Factory::class,
            'filePath' => __DIR__ . '/../src/Aggregation/Factory/',
            'supportingNamespace' => QueryOperator::class,
            'supportingClassNameSuffix' => 'QueryOperator',
            'functionNamespace' => FactoryFunctions\QueryOperator::class,
            'functionFilePath' => __DIR__ .'/../src/Aggregation/FactoryFunctions/',
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

    $generator = new $generatorClass($generatorConfig);
    $generator->createClassesForObjects($objects, $generatorConfig['overwrite'] ?? false);
}
