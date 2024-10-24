<?php

namespace AggregationBlogPost;

use MongoDB\Builder\Accumulator;
use MongoDB\Builder\Expression;
use MongoDB\Builder\Pipeline;
use MongoDB\Builder\Query;
use MongoDB\Builder\Stage;
use MongoDB\Builder\Stage\GroupStage;
use MongoDB\Builder\Type\ExpressionInterface;
use MongoDB\Tests\TestCase;
use stdClass;
use function MongoDB\object;

class FuelExample1Test extends TestCase
{
    public function testExample1(): void
    {
        $pipeline = [
            [
                '$group' => [
                    '_id' => [
                        'year' => ['$year' => '$reportDate'],
                        'month' => ['$month' => '$reportDate'],
                        'fuelType' => '$fuelType',
                        'brand' => '$station.brand'
                    ],
                    'lowest' => ['$min' => '$price'],
                    'highest' => ['$max' => '$price'],
                    'average' => ['$avg' => '$price'],
                    'count' => ['$sum' => 1],
                ],
            ],
            [
                '$group' => [
                    '_id' => [
                        'year' => '$_id.year',
                        'month' => '$_id.month',
                        'brand' => '$_id.brand',
                    ],
                    'count' => ['$sum' => '$count'],
                    'prices' => [
                        '$push' => [
                            'k' => '$_id.fuelType',
                            'v' => [
                                'lowest' => '$lowest',
                                'highest' => '$highest',
                                'average' => '$average',
                                'span' => ['$subtract' => ['$highest', '$lowest']],
                            ],
                        ],
                    ],
                ],
            ],
            ['$addFields' => ['prices' => ['$arrayToObject' => '$prices']]],
            [
                '$group' => [
                    '_id' => [
                        'year' => '$_id.year',
                        'month' => '$_id.month',
                    ],
                    'brands' => [
                        '$push' => [
                            'brand' => '$_id.brand',
                            'count' => '$count',
                            'prices' => '$prices',
                        ],
                    ],
                ],
            ],
            [
                '$addFields' => [
                    'brands' => [
                        '$sortArray' => [
                            'input' => '$brands',
                            'sortBy' => ['count' => -1],
                        ],
                    ],
                ],
            ],
            ['$sort' => ['_id.year' => 1, '_id.month' => 1]],
        ];
    }

    public function testExample2(): void
    {
        $pipeline = new Pipeline(
            Stage::group(
                _id: object(
                    year: Expression::year(Expression::dateFieldPath('reportDate')),
                    month: Expression::month(Expression::dateFieldPath('reportDate')),
                    fuelType: Expression::fieldPath('fuelType'),
                    brand: Expression::fieldPath('station.brand'),
                ),
                lowest: Accumulator::min(Expression::doubleFieldPath('price')),
                highest: Accumulator::max(Expression::doubleFieldPath('price')),
                average: Accumulator::avg(Expression::doubleFieldPath('price')),
                count: Accumulator::sum(1),
            ),
            Stage::group(
                _id: object(
                    year: Expression::fieldPath('_id.year'),
                    month: Expression::fieldPath('_id.month'),
                    brand: Expression::fieldPath('_id.brand'),
                ),
                count: Accumulator::sum(Expression::intFieldPath('count')),
                prices: Accumulator::push(
                    object(
                        k: Expression::fieldPath('_id.fuelType'),
                        v: object(
                            lowest: Expression::fieldPath('lowest'),
                            highest: Expression::fieldPath('highest'),
                            average: Expression::fieldPath('average'),
                            range: Expression::subtract(
                                Expression::doubleFieldPath('highest'),
                                Expression::doubleFieldPath('lowest'),
                            ),
                        )
                    )
                )
            ),
            Stage::addFields(
                prices: Expression::arrayToObject(
                    Expression::arrayFieldPath('prices'),
                ),
            ),
            Stage::group(
                _id: object(
                    year: Expression::fieldPath('_id.year'),
                    month: Expression::fieldPath('_id.month'),
                ),
                brands: Accumulator::push(
                    object(
                        brand: Expression::fieldPath('_id.brand'),
                        count: Expression::fieldPath('count'),
                        prices: Expression::fieldPath('prices'),
                    ),
                ),
            ),
            Stage::addFields(
                brands: Expression::sortArray(
                    input: Expression::arrayFieldPath('brands'),
                    sortBy: ['count' => -1],
                ),
            ),
        );
    }

    public function testMatch(): void
    {
        Stage::match(
            Query::or(
                Query::query(
                    score: [
                        Query::gt(70),
                        Query::lt(90),
                    ],
                ),
                Query::query(
                    views: Query::gte(1000),
                ),
            ),
        );
    }

    public function testExtractVariables(): void
    {
        $reportDate = Expression::dateFieldPath('reportDate');
        $price = Expression::doubleFieldPath('price');

        $pipeline = new Pipeline(
            Stage::group(
                _id: object(
                    year: Expression::year($reportDate),
                    month: Expression::month($reportDate),
                    fuelType: Expression::fieldPath('fuelType'),
                    brand: Expression::fieldPath('station.brand'),
                ),
                lowest: Accumulator::min($price),
                highest: Accumulator::max($price),
                average: Accumulator::avg($price),
                count: Accumulator::sum(1),
            ),
            Stage::group(
                _id: object(
                    year: Expression::fieldPath('_id.year'),
                    month: Expression::fieldPath('_id.month'),
                    brand: Expression::fieldPath('_id.brand'),
                ),
                count: Accumulator::sum(Expression::intFieldPath('count')),
                prices: Accumulator::push(
                    object(
                        k: Expression::fieldPath('_id.fuelType'),
                        v: object(
                            lowest: Expression::fieldPath('lowest'),
                            highest: Expression::fieldPath('highest'),
                            average: Expression::fieldPath('average'),
                            range: Expression::subtract(
                                Expression::doubleFieldPath('highest'),
                                Expression::doubleFieldPath('lowest'),
                            ),
                        )
                    )
                )
            ),
            Stage::addFields(
                prices: Expression::arrayToObject(
                    Expression::arrayFieldPath('prices'),
                ),
            ),
            Stage::group(
                _id: object(
                    year: Expression::fieldPath('_id.year'),
                    month: Expression::fieldPath('_id.month'),
                ),
                brands: Accumulator::push(
                    object(
                        brand: Expression::fieldPath('_id.brand'),
                        count: Expression::fieldPath('count'),
                        prices: Expression::fieldPath('prices'),
                    ),
                ),
            ),
            Stage::addFields(
                brands: Expression::sortArray(
                    input: Expression::arrayFieldPath('brands'),
                    sortBy: ['count' => -1],
                ),
            ),
        );
    }

    public static function groupAndComputeStatistics(
        stdClass $_id,
        Expression\ResolvesToDouble $price,
    ): GroupStage {
        return Stage::group(
            _id: $_id,
            lowest: Accumulator::min($price),
            highest: Accumulator::max($price),
            average: Accumulator::avg($price),
            count: Accumulator::sum(1),
        );
    }

    public function testExtractStageToFactory(): void
    {
        $reportDate = Expression::dateFieldPath('reportDate');
        $price = Expression::doubleFieldPath('price');

        $pipeline = new Pipeline(
            self::groupAndComputeStatistics(
                _id: object(
                    year: Expression::year($reportDate),
                    month: Expression::month($reportDate),
                    fuelType: Expression::fieldPath('fuelType'),
                    brand: Expression::fieldPath('station.brand'),
                ),
                price: $price,
            ),
            Stage::group(
                _id: object(
                    year: Expression::fieldPath('_id.year'),
                    month: Expression::fieldPath('_id.month'),
                    brand: Expression::fieldPath('_id.brand'),
                ),
                count: Accumulator::sum(Expression::intFieldPath('count')),
                prices: Accumulator::push(
                    object(
                        k: Expression::fieldPath('_id.fuelType'),
                        v: object(
                            lowest: Expression::fieldPath('lowest'),
                            highest: Expression::fieldPath('highest'),
                            average: Expression::fieldPath('average'),
                            range: Expression::subtract(
                                Expression::doubleFieldPath('highest'),
                                Expression::doubleFieldPath('lowest'),
                            ),
                        )
                    )
                )
            ),
            Stage::addFields(
                prices: Expression::arrayToObject(
                    Expression::arrayFieldPath('prices'),
                ),
            ),
            Stage::group(
                _id: object(
                    year: Expression::fieldPath('_id.year'),
                    month: Expression::fieldPath('_id.month'),
                ),
                brands: Accumulator::push(
                    object(
                        brand: Expression::fieldPath('_id.brand'),
                        count: Expression::fieldPath('count'),
                        prices: Expression::fieldPath('prices'),
                    ),
                ),
            ),
            Stage::addFields(
                brands: Expression::sortArray(
                    input: Expression::arrayFieldPath('brands'),
                    sortBy: ['count' => -1],
                ),
            ),
        );
    }

    public static function groupAndAssembleFuelTypePriceObject(
        stdClass $_id,
        Expression\ResolvesToString $fuelType,
        Expression\ResolvesToInt $count,
        Expression\ResolvesToDouble $lowest,
        Expression\ResolvesToDouble $highest,
        Expression\ResolvesToDouble $average,
    ): Pipeline {
        return new Pipeline(
            Stage::group(
                _id: $_id,
                count: Accumulator::sum($count),
                prices: Accumulator::push(
                    object(
                        k: $fuelType,
                        v: object(
                            lowest: $lowest,
                            highest: $highest,
                            average: $average,
                            range: Expression::subtract(
                                $highest,
                                $lowest,
                            ),
                        )
                    )
                )
            ),
            Stage::addFields(
                prices: Expression::arrayToObject(
                    Expression::arrayFieldPath('prices'),
                ),
            ),
        );
    }

    public function testExtractMultipleStages(): void
    {
        $reportDate = Expression::dateFieldPath('reportDate');
        $price = Expression::doubleFieldPath('price');

        $pipeline = new Pipeline(
            self::groupAndComputeStatistics(
                _id: object(
                    year: Expression::year($reportDate),
                    month: Expression::month($reportDate),
                    fuelType: Expression::fieldPath('fuelType'),
                    brand: Expression::fieldPath('station.brand'),
                ),
                price: $price,
            ),
            self::groupAndAssembleFuelTypePriceObject(
                _id: object(
                    year: Expression::fieldPath('_id.year'),
                    month: Expression::fieldPath('_id.month'),
                    brand: Expression::fieldPath('_id.brand'),
                ),
                fuelType: Expression::fieldPath('_id.fuelType'),
                count: Expression::intFieldPath('count'),
                lowest: Expression::fieldPath('lowest'),
                highest: Expression::fieldPath('highest'),
                average: Expression::fieldPath('average'),
            ),
            Stage::group(
                _id: object(
                    year: Expression::fieldPath('_id.year'),
                    month: Expression::fieldPath('_id.month'),
                ),
                brands: Accumulator::push(
                    object(
                        brand: Expression::fieldPath('_id.brand'),
                        count: Expression::fieldPath('count'),
                        prices: Expression::fieldPath('prices'),
                    ),
                ),
            ),
            Stage::addFields(
                brands: Expression::sortArray(
                    input: Expression::arrayFieldPath('brands'),
                    sortBy: ['count' => -1],
                ),
            ),
        );
    }

    public static function groupBrandsAndSort(
        stdClass $_id,
        Expression\ResolvesToString $brand,
        Expression\ResolvesToInt $count,
        Expression\ResolvesToObject $prices,
    ): Pipeline {
        return new Pipeline(
            Stage::group(
                _id: $_id,
                brands: Accumulator::push(
                    object(
                        brand: $brand,
                        count: $count,
                        prices: $prices,
                    ),
                ),
            ),
            Stage::addFields(
                brands: Expression::sortArray(
                    input: Expression::arrayFieldPath('brands'),
                    sortBy: ['count' => -1],
                ),
            ),
        );
    }

    public function testFinalRefactoring(): void
    {
        $reportDate = Expression::dateFieldPath('reportDate');
        $price = Expression::doubleFieldPath('price');

        $pipeline = new Pipeline(
            self::groupAndComputeStatistics(
                _id: object(
                    year: Expression::year($reportDate),
                    month: Expression::month($reportDate),
                    fuelType: Expression::fieldPath('fuelType'),
                    brand: Expression::fieldPath('station.brand'),
                ),
                price: $price,
            ),
            self::groupAndAssembleFuelTypePriceObject(
                _id: object(
                    year: Expression::fieldPath('_id.year'),
                    month: Expression::fieldPath('_id.month'),
                    brand: Expression::fieldPath('_id.brand'),
                ),
                fuelType: Expression::fieldPath('_id.fuelType'),
                count: Expression::intFieldPath('count'),
                lowest: Expression::fieldPath('lowest'),
                highest: Expression::fieldPath('highest'),
                average: Expression::fieldPath('average'),
            ),
            self::groupBrandsAndSort(
                _id: object(
                    year: Expression::fieldPath('_id.year'),
                    month: Expression::fieldPath('_id.month'),
                ),
                brand: Expression::fieldPath('_id.brand'),
                count: Expression::fieldPath('count'),
                prices: Expression::fieldPath('prices'),
            ),
        );
    }

    public function testExtractExpressions(): void
    {
        $pipeline = [
            [
                '$addFields' => [
                    'weightedPrices' => [
                        '$map' => [
                            'input' => '$prices',
                            'as' => 'priceReport',
                            'in' => [
                                'duration' => [
                                    '$min' => [
                                        [
                                            '$dateDiff' => [
                                                'startDate' => '$$priceReport.previous.reportDate',
                                                'endDate' => '$$priceReport.reportDate',
                                                'unit' => 'second',
                                            ],
                                        ],
                                        [
                                            '$add' => [
                                                [
                                                    '$multiply' => [
                                                        ['$hour' => '$$priceReport.reportDate'],
                                                        3600,
                                                    ],
                                                ],
                                                [
                                                    '$multiply' => [
                                                        ['$minute' => '$$priceReport.reportDate'],
                                                        60,
                                                    ],
                                                ],
                                                ['$second' => '$$priceReport.reportDate'],
                                            ],
                                        ],
                                    ],
                                ],
                                'price' => '$$priceReport.previous.price',
                            ],
                        ],
                    ],
                    'lastPrice' => ['$last' => '$prices'],
                ],
            ],
        ];

        $prices = Expression::arrayFieldPath('prices');
        $reportDate = Expression::variable('priceReport.reportDate');
        $previousReportDate = Expression::variable('priceReport.previous.reportDate');

        $pipeline = new Pipeline(
            Stage::addFields(
                weightedPrices: Expression::map(
                    input: $prices,
                    in: object(
                        duration: Expression::min(
                            Expression::dateDiff(
                                startDate: $previousReportDate,
                                endDate: $reportDate,
                                unit: 'second',
                            ),
                            Expression::add(
                                Expression::multiply(
                                    Expression::hour($reportDate),
                                    3600,
                                ),
                                Expression::multiply(
                                    Expression::minute($reportDate),
                                    60,
                                ),
                                Expression::second($reportDate),
                            )
                        ),
                        price: Expression::variable('priceReport.previous.price'),
                    ),
                    as: 'priceReport',
                ),
                lastPrice: Expression::last($prices),
            ),
        );
    }

    public static function computeElapsedSecondsOnDay(
        Expression\ResolvesToDate $date,
    ): Expression\ResolvesToInt {
        return Expression::add(
            Expression::multiply(
                Expression::hour($date),
                3600,
            ),
            Expression::multiply(
                Expression::minute($date),
                60,
            ),
            Expression::second($date),
        );
    }

    /**
     * Returns the time that has elapsed between two dates, in seconds.
     *
     * If the dates are on the same day, the difference is computed between the
     * two times. If the dates fall on different dates, the duration returned is
     * the number of seconds since the start of the day from the second date
     * argument.
     */
    public static function computeDurationBetweenDates(
        Expression\ResolvesToDate $previousReportDate,
        Expression\ResolvesToDate $reportDate,
    ): Expression\ResolvesToInt {
        return Expression::min(
            Expression::dateDiff(
                startDate: $previousReportDate,
                endDate: $reportDate,
                unit: 'second',
            ),
            self::computeElapsedSecondsOnDay($reportDate),
        );
    }

    public function testExtractExpressionsExtractedDurationComputation(): void
    {
        $prices = Expression::arrayFieldPath('prices');
        $reportDate = Expression::variable('priceReport.reportDate');
        $previousReportDate = Expression::variable('priceReport.previous.reportDate');

        $pipeline = new Pipeline(
            Stage::addFields(
                weightedPrices: Expression::map(
                    input: $prices,
                    in: object(
                        duration: self::computeDurationBetweenDates($previousReportDate, $reportDate),
                        price: Expression::variable('priceReport.previous.price'),
                    ),
                    as: 'priceReport',
                ),
                lastPrice: Expression::last($prices),
            ),
        );
    }

    public function testFirstClassCallableSyntax(): void
    {
        $reportDate = Expression::dateFieldPath('reportDate');
        $price = Expression::doubleFieldPath('price');
        $fuelType = Expression::fieldPath('fuelType');
        $brand = Expression::fieldPath('station.brand');

        $group = Stage::group(...);
        $year = Expression::year(...);
        $month = Expression::month(...);
        $min = Accumulator::min(...);
        $max = Accumulator::max(...);
        $avg = Accumulator::avg(...);
        $sum = Accumulator::sum(...);

        $group(
            _id: object(
                year: $year($reportDate),
                month: $month($reportDate),
                fuelType: $fuelType,
                brand: $brand,
            ),
            lowest: $min($price),
            highest: $max($price),
            average: $avg($price),
            count: $sum(1),
        );
    }
}
