<?php

use MongoDB\Aggregation\Generator\StageGenerator;
use Symfony\Component\Yaml\Yaml;

require __DIR__ . '/../vendor/autoload.php';

$configFile = __DIR__ . '/../src/Aggregation/config/stages.yaml';

$config = Yaml::parseFile($configFile, Yaml::PARSE_OBJECT_FOR_MAP);
(new StageGenerator())->createClassesForStages($config->stages, true);
