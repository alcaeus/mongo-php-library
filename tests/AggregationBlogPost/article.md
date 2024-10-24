# Better Aggregation Pipeline Support in the MongoDB PHP Driver

The aggregation framework is a powerful tool in your MongoDB toolbox. It allows
you to run complex queries on your data, shaping and modifying documents to suit
your needs. This power comes through a lot of different pipeline stages and
operators, which in turn brings a certain learning challenge. MongoDB Compass
includes an aggregation pipeline builder that allows you to see results in real-time
for each stage and fix mistakes early on. Once your pipeline is complete, you
can export the pipeline to your language and use it in your code. In the PHP
driver, that pipeline would live on as an array, completely untyped, and
sometimes with a relatively complex structure of stages and operators. Let's
take this pipeline from one of my projects as an example:

```php
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
```

That's a lot of logic! To better understand what this pipeline does, let's
look at a single source document:

```json
{
  "reportDate": "2024-10-22T13:15:03+02:00",
  "station": {
    "brand": "Acme Corp."
  },
  "fuelType": "diesel",
  "price": "1.759"
}
```

I've left out some fields that we're not using right now. The aggregation
pipeline aggregates all of these documents, producing a document for each month:

```json
{
  "_id": {
    "year": 2024,
    "month": 10
  },
  "brands": [
    {
      "brand": "Acme Corp.",
      "count": 1,
      "prices": {
        "diesel": {
          "lowest": 1.759,
          "highest": 1.759,
          "average": 1.579,
          "span": 0
        }
      }
    }
  ]
}
```

Without going into more details on this, even if we were to comment on parts of
the aggregation pipeline to explain what it does, there will still be a high
cognitive load when going through the aggregation pipeline. One reason for this
is that the only way to express the aggregation framework domain-specific
language (DSL) is through untyped arrays, and any PHP editor can't provide much
help beyond syntax highlighting. Pair that with a few levels of nesting, and
you've got yourself the kind of code that you can write, but not read. We could
start off by refactoring the code, but instead let's try to move away from array
structures and use a better solution.

## Introducing the Aggregation Pipeline Builder

Previously released as a standalone package, version 1.21 of the MongoDB PHP
driver now includes a comprehensive aggregation pipeline builder. Instead of
writing complex arrays, you can use factory methods to generate pipeline stages
and operators. Here is that same pipeline as we had before, this time written
with the aggregation pipeline builder:

```php
use MongoDB\Builder\Accumulator;
use MongoDB\Builder\Expression;
use MongoDB\Builder\Pipeline;
use MongoDB\Builder\Stage;
use function MongoDB\object;

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
```

This is still a complex pipeline, but compared to the original array example
it is now much easier to infer the context of each pipeline component. Operators
are clearly differentiated from field names, and this typing can enable code
editors and tooling to better assist the developer.

To run an aggregation pipeline, you can pass a `Pipeline` instance to any method
that can receive an aggregation pipeline, such as `Collection::aggregate` or
`Collection::watch`. In addition, methods like `Collection::updateMany` and
`Collection::findOneAndUpdate` can receive a `Pipeline` instance to run an
update. Keep in mind that you won't be able to use all available aggregation
pipeline stages in update operations. Since a pipeline instance could be used
everywhere, the builder will allow you to add stages to a pipeline that aren't
supported in updates.

### Builder Design

The builder was designed with ease of use in mind. Most importantly, we wanted
to represent the somewhat flexible type system and give better guidance to users
when writing aggregation pipelines. That's why you will see expressions like
`dateFieldPath`, `doubleFieldPath`, or `arrayFieldPath`. Each expression
resolves to a certain type when it's evaluated. For example, we know that the
`$year` operator expression resolves to an integer, and its argument is an
expression that resolves to a date, timestamp, or ObjectId. While we could use
`$reportDate` to reference the `reportDate` field of the document being evaluated,
`dateFieldPath` is more expressive and shows intent of receiving a date field.
This also allows IDEs like PhpStorm to make better suggestions when offering
code completion, as well as a certain amount of type checking to ensure you're
not creating pipelines that would result in server errors.

For all expressions, there are factory classes with methods to create the
expression objects. The use of static methods makes the code a little more
verbose, but using functions was impossible due to conflicts between aggregation
operator names and reserved keywords in PHP (e.g. `and`, `if`, `switch`). I'll
show alternatives to using these static methods later in this blog post.

## Bonus Feature: Query Objects

As a side effect of building the aggregation pipeline builder, there's now also
a builder for query filters. This is because the `$match` stage takes a query
object, and to avoid falling back to query arrays like you would pass them to
`Collection::find`, we also built a builder for query objects. Here you see an
example of a `find` call, along with the same query specified using the builder:

```php
$collection->find(['score' => ['$gt' => 70, '$lt' => 90]]);

$collection->find(
    Query::query(
        score: [
            Query::gt(70),
            Query::lt(90),
        ],
    ),
);
```

While this is a little more verbose, it provides a more expressive API than PHP
array structures and brings the same improvements for IDEs and tooling. It's
up to you to decide which option you like better.

## Refactoring For Better Maintainability

### Extract Fields to Variables

With the basic builder details explained, there's still one problem: the builder
helps you write a pipeline, but it doesn't really make existing pipelines more
maintainable. Yes, it makes them easier to read, but a complex pipeline will
remain just as complex. So, let's discuss some refactorings we can make to both
improve the pipeline's readability and make parts of the pipeline more reusable.
Note that although the following example uses the aggregation builder, the same
suggestions can also be applied to pipelines written as PHP arrays.

Let's look at the first `$group` stage in the original example:

```php
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
);
```

As you can see, we reference the `reportDate` and `price` fields multiple times.
A quick refactoring would be to extract those to variables:

```php
$reportDate = Expression::dateFieldPath('reportDate');
$price = Expression::doubleFieldPath('price');

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
);
```

The `fuelType` and `station.brand` fields could be extracted as well. I opted not
to since they are only used once, but you may want to do so in favor of consistency.

### Comments Or Methods

In complex pipelines, you'll often find comments explaining what a certain
pipeline stage or segment does. You should definitely include comments like
that, but you can also consider extracting parts of a pipeline to your own
builder method. Choosing a descriptive method name that explains what the stage
or segment does can also remove the need for a comment and spare the reader from
having to dive into the internal workings.

```php
public static function groupAndComputeStatistics(
    stdClass $groupBy,
    Expression\ResolvesToDouble $price,
): GroupStage {
    return Stage::group(
        _id: $groupBy,
        lowest: Accumulator::min($price),
        highest: Accumulator::max($price),
        average: Accumulator::avg($price),
        count: Accumulator::sum(1),
    );
}
```

When extracting logic, consider if the method can be reused elsewhere. In this
case, by keeping the `_id` field for the `$group` stage along with the price
field as a parameter, we can reuse this builder method in a different pipeline. 

```php
$reportDate = Expression::dateFieldPath('reportDate');
$price = Expression::doubleFieldPath('price');

self::groupAndComputeStatistics(
    groupBy: object(
        year: Expression::year($reportDate),
        month: Expression::month($reportDate),
        fuelType: Expression::fieldPath('fuelType'),
        brand: Expression::fieldPath('station.brand'),
    ),
    price: $price,
);
```

### Extract multiple stages

We've now refactored the first pipeline stage, but the `$group` stage below is
also relatively complex to read. To make matters worse, this pipeline stage
works together with the `$addFields` stage below: `$group` assembles a list of
fuel types with their prices, which is then converted to an object in
`$addFields`. Ideally, we want to hide this implementation detail and extract
both stages together.

To do so, we once again extract a factory method, except that this time we'll
return a `Pipeline` instance:

```php
public static function groupAndAssembleFuelTypePriceObject(
    stdClass $groupBy,
    Expression\ResolvesToString $fuelType,
    Expression\ResolvesToInt $count,
    Expression\ResolvesToDouble $lowest,
    Expression\ResolvesToDouble $highest,
    Expression\ResolvesToDouble $average,
): Pipeline {
    return new Pipeline(
        Stage::group(
            _id: $groupBy,
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
```

By once again keeping fields as parameters, the method remains flexible and we
allow using it in a pipeline that produces slightly different documents up to
this point. Since the method works independently of how we group documents, we
also keep the identifier as a parameter. Using this method further simplifies
the pipeline:

```php
self::groupAndAssembleFuelTypePriceObject(
    groupBy: object(
        year: Expression::fieldPath('_id.year'),
        month: Expression::fieldPath('_id.month'),
        brand: Expression::fieldPath('_id.brand'),
    ),
    fuelType: Expression::fieldPath('_id.fuelType'),
    count: Expression::intFieldPath('count'),
    lowest: Expression::fieldPath('lowest'),
    highest: Expression::fieldPath('highest'),
    average: Expression::fieldPath('average'),
);
```

Without going into too much detail, we can do the same with the next stages in
the pipeline. Excluding the extracted factory methods, our pipeline now looks
like this:

```php
$reportDate = Expression::dateFieldPath('reportDate');
$price = Expression::doubleFieldPath('price');

$pipeline = new Pipeline(
    self::groupAndComputeStatistics(
        groupBy: object(
            year: Expression::year($reportDate),
            month: Expression::month($reportDate),
            fuelType: Expression::fieldPath('fuelType'),
            brand: Expression::fieldPath('station.brand'),
        ),
        price: $price,
    ),
    self::groupAndAssembleFuelTypePriceObject(
        groupBy: object(
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
        groupBy: object(
            year: Expression::fieldPath('_id.year'),
            month: Expression::fieldPath('_id.month'),
        ),
        brand: Expression::fieldPath('_id.brand'),
        count: Expression::fieldPath('count'),
        prices: Expression::fieldPath('prices'),
    ),
);
```

### Extract Complex Expressions

So far, we've only extracted entire pipeline stages that contain relatively
simple expressions. Sometimes your aggregation pipeline will contain a more
complex expression. From the same project that yielded the previous example,
there's also this gem that is part of a pipeline that computes the weighted
average price for each day:

```php
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
```

The builder can make this a little more concise, but the complexity remains:

```php
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
```

The main cognitive effort comes from computing the `duration` field. So, this
should be our focus. Together with some comments, we can extract the logic to
separate builder methods:

```php
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
    Expression\ResolvesToDate $startDate,
    Expression\ResolvesToDate $endDate,
): Expression\ResolvesToInt {
    return Expression::min(
        Expression::dateDiff(
            startDate: $startDate,
            endDate: $endDate,
            unit: 'second',
        ),
        self::computeElapsedSecondsOnDay($endDate),
    );
}
```

This reduces the complexity of the pipeline stage tremendously:

```php
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
```

### Create Your Own Builder

One last suggestion for reusable code is to create your own builder. When
extracting complex expressions or stages into separate methods, don't keep those
internal, but make them public as shown above. For one, this allows reusing them
in other places, but more importantly, it allows testing them in isolation.

Think about the complete pipeline shown at the beginning: you would most likely
have tests to ensure you're receiving the correct result, but if that test fails
you'll be forced to evaluate the entire pipeline to figure out what's wrong.
This would include all the logic included in the pipeline. By extracting complex
parts into separate methods, you can test those in isolation under controlled
conditions, knowing that they behave as you intended. Looking at the last
example where we've extracted the expression that computes the duration between
two dates, you can now better verify that all the individual parts of that
complex expression work as expected.

Just beware of premature abstractions: extract complex logic with the goal of
making your code more readable and testable, not to be able to reuse it across
your entire code base.

## Builder Internals

In the examples you've seen so far, we've only ever looked at stages,
expressions, and of course the `Pipeline` objects. But how do we end up with an
aggregation pipeline that the server understands? The objects created with the
various factory methods are value holder objects. They don't contain any logic,
but they allow us to know what types an expression will resolve to.

For an example, let's take the `$hour` operator. When you use
`Expression::hour`, you will receive an instance of an `HourOperator` class.
This class implements an `OperatorInterface`, telling the builder that this is
an operator that can be used in an aggregation pipeline stage. It also
implements a `ResolvesToInt` interface, as we always know that evaluating the
expression results in an integer value. The required `date` parameter of the
operator is a date, which in the builder is one of many things. It could be a
`MongoDB\BSON\UTCDateTime` instance, but it could also be the result of any
operator that returns a date, e.g. `$dateFromString`.

Now that we know about these value holder objects, we still need to make sure
the server knows what we're talking about. When you call `Collection::aggregate`
with the pipeline you built, what happens internally to it? Here, a series of
encoders spring into action. We use a single entry point, the `BuilderEncoder`
class. This class contains multiple encoders that are able to handle all
pipeline stages, operators, and accumulators and transform them into their BSON
representations.

This allows us to keep the logic customizable. For example, the Doctrine MongoDB
ODM allows users to specify different names for fields in the database. In turn,
it needs to convert the name of a property in the mapped class to the name of
the field in the database. Such a feature could be built by creating a custom
encoder for all `fieldPath` expressions, and changing the field path
accordingly.

When creating a `MongoDB\Client` instance, you can now pass an additional
`builderEncoder` option in the `$driverOptions` argument. This specifies the
encoder used to encode aggregation pipelines, but also query objects. All
`Database` and `Collection` instances inherit this value from the client, but you
can override it through the options when selecting those objects. This allows
you to have your custom logic applied whenever pipelines or queries are encoded
for the server.

With factories, value holders, and encoders, we wanted to ensure that creating
the builder does not turn into a repetitive chore. As you can imagine, many
operators will mostly consist of the same logic, resulting in tons of code
duplication. To make matters worse, every new server version may introduce new
operators and stages, so we wanted to make sure that we can quickly expand
the builder.

We could try to rely on generative AI to help us with this, but this only goes
so far. Instead, we leverage code generation to make the task easier. All
factories, value holders, and encoders are generated from a configuration. When
a new operator is introduced, we create a config file with all of its details:
input types, what the operator resolves to, documentation for parameters, and
even examples from the MongoDB documentation. We then run the
generator, and are given all code necessary to use the operator.

As if that wasn't good enough, the generator also takes the examples we added
before and adds them to the tests. In these tests, we manually write the builder
code that would generate the pipeline or expression from the example. This
ensures that the generated logic behaves as we expect, is protected from
regression should we make any changes to the generator, and it allows us to use
the builder and feel what it's like. Ultimately, we love to add this builder
code to the server documentation, similar to how we add other language-specific
code snippets, but we're not quite there yet.

## Builder Syntax - Static Methods or Functions?

I've mentioned before that the builder uses static methods because of naming
conflicts. It really comes down to a few names, for example the `$match` stage
and `$and` or `$switch` operators. While PHP allows us to use reserved keywords
as names for methods in a class, they are not allowed for function names. We
have considered using suffixes for namespaced functions:

```php
function andQuery(...) {}
function matchStage(...) {}
function switchOperator(...) {}
```

Adding a suffix to every stage and operator doesn't really reduce verbosity, so
we decided against it. Another option was to use an underscore prefix for all
reserved names:

```php
function _and(...) {}
function _match(...) {}
function _switch(...) {}
```

We also decided against this option, partly because the underscore marks (or at
least used to mark) private functions or methods, but also because the
underscore would break alphabetical sorting in code completion.

A less-than-serious approach was to use emojis, which I learned was valid in
PHP:

```php
function 🔍and(...) {}
function 💲match(...) {}
```

In all seriousness, there are alternatives if you dislike the static methods.
PHP's
[first class callable syntax](https://www.php.net/manual/en/functions.first_class_callable_syntax.php)
allows you to create a closure from an existing method and store it in a
variable to later invoke the closure:

```php
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
```

Again, PHP's use of `$` to mark variables plays nice with aggregation pipeline
using it for stages and operators. We have considered adding a method to each
factory class that would return an array of closures to make this easier:

```php
['year' => $year, 'month' => $month] = Accumulator::accumulators();

// Alternatively, if you want to use all accumulators as variables:
extract(Accumulator::accumulators());
```

We have not implemented this for now, but if you prefer this syntax to static
methods, or if you have alternative suggestions, please let us know about them!