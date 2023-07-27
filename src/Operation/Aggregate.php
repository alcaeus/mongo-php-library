<?php
/*
 * Copyright 2015-present MongoDB, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace MongoDB\Operation;

use MongoDB\Driver\Command;
use MongoDB\Driver\Cursor;
use MongoDB\Driver\Exception\RuntimeException as DriverRuntimeException;
use MongoDB\Driver\Server;
use MongoDB\Exception\InvalidArgumentException;
use MongoDB\Exception\UnexpectedValueException;
use MongoDB\Exception\UnsupportedException;
use MongoDB\Options\AggregateOptions;

use function MongoDB\is_last_pipeline_operator_write;
use function MongoDB\is_pipeline;

/**
 * Operation for the aggregate command.
 *
 * @see \MongoDB\Collection::aggregate()
 * @see https://mongodb.com/docs/manual/reference/command/aggregate/
 */
class Aggregate implements Executable, Explainable
{
    private string $databaseName;

    private ?string $collectionName = null;

    private array $pipeline;

    private AggregateOptions $options;

    private bool $isWrite;

    /**
     * Constructs an aggregate command.
     *
     * Supported options:
     *
     *  * allowDiskUse (boolean): Enables writing to temporary files. When set
     *    to true, aggregation stages can write data to the _tmp sub-directory
     *    in the dbPath directory.
     *
     *  * batchSize (integer): The number of documents to return per batch.
     *
     *  * bypassDocumentValidation (boolean): If true, allows the write to
     *    circumvent document level validation. This only applies when an $out
     *    or $merge stage is specified.
     *
     *  * collation (document): Collation specification.
     *
     *  * comment (mixed): BSON value to attach as a comment to this command.
     *
     *    Only string values are supported for server versions < 4.4.
     *
     *  * explain (boolean): Specifies whether or not to return the information
     *    on the processing of the pipeline.
     *
     *  * hint (string|document): The index to use. Specify either the index
     *    name as a string or the index key pattern as a document. If specified,
     *    then the query system will only consider plans using the hinted index.
     *
     *  * let (document): Map of parameter names and values. Values must be
     *    constant or closed expressions that do not reference document fields.
     *    Parameters can then be accessed as variables in an aggregate
     *    expression context (e.g. "$$var").
     *
     *    This is not supported for server versions < 5.0 and will result in an
     *    exception at execution time if used.
     *
     *  * maxTimeMS (integer): The maximum amount of time to allow the query to
     *    run.
     *
     *  * readConcern (MongoDB\Driver\ReadConcern): Read concern.
     *
     *  * readPreference (MongoDB\Driver\ReadPreference): Read preference.
     *
     *    This option is ignored if an $out or $merge stage is specified.
     *
     *  * session (MongoDB\Driver\Session): Client session.
     *
     *  * typeMap (array): Type map for BSON deserialization. This will be
     *    applied to the returned Cursor (it is not sent to the server).
     *
     *  * writeConcern (MongoDB\Driver\WriteConcern): Write concern. This only
     *    applies when an $out or $merge stage is specified.
     *
     * Note: Collection-agnostic commands (e.g. $currentOp) may be executed by
     * specifying null for the collection name.
     *
     * @param string                 $databaseName   Database name
     * @param string|null            $collectionName Collection name
     * @param array                  $pipeline       Aggregation pipeline
     * @param array|AggregateOptions $options        Command options
     * @throws InvalidArgumentException for parameter/option parsing errors
     */
    public function __construct(string $databaseName, ?string $collectionName, array $pipeline, $options = [])
    {
        if (! is_pipeline($pipeline, true /* allowEmpty */)) {
            throw new InvalidArgumentException('$pipeline is not a valid aggregation pipeline');
        }

        if (! $options instanceof AggregateOptions) {
            $options = AggregateOptions::fromArray($options);
        }

        $this->isWrite = is_last_pipeline_operator_write($pipeline) && ! $options->isExplain();

        if ($this->isWrite) {
            $this->options = $options->withBatchSize(null);
        } else {
            $this->options = $options->withWriteConcern(null);
        }

        $this->databaseName = $databaseName;
        $this->collectionName = $collectionName;
        $this->pipeline = $pipeline;
    }

    /**
     * Execute the operation.
     *
     * @see Executable::execute()
     * @return Cursor
     * @throws UnexpectedValueException if the command response was malformed
     * @throws UnsupportedException if read concern or write concern is used and unsupported
     * @throws DriverRuntimeException for other driver errors (e.g. connection errors)
     */
    public function execute(Server $server)
    {
        $command = new Command(
            $this->createCommandDocument(),
            $this->options->createCommandOptions(),
        );

        $cursor = $this->executeCommand($server, $command);

        $typeMap = $this->options->getTypeMap();
        if ($typeMap) {
            $cursor->setTypeMap($typeMap);
        }

        return $cursor;
    }

    /**
     * Returns the command document for this operation.
     *
     * @see Explainable::getCommandDocument()
     * @return array
     */
    public function getCommandDocument()
    {
        $cmd = $this->createCommandDocument();

        if ($this->options->getReadConcern()) {
            $cmd['readConcern'] = $this->options->getReadConcern();
        }

        return $cmd;
    }

    /**
     * Create the aggregate command document.
     */
    private function createCommandDocument(): array
    {
        $cmd = [
            'aggregate' => $this->collectionName ?? 1,
            'pipeline' => $this->pipeline,
        ];

        return $this->options->appendAggregateOptions($cmd);
    }

    /**
     * Execute the aggregate command using the appropriate Server method.
     *
     * @see https://php.net/manual/en/mongodb-driver-server.executecommand.php
     * @see https://php.net/manual/en/mongodb-driver-server.executereadcommand.php
     * @see https://php.net/manual/en/mongodb-driver-server.executereadwritecommand.php
     */
    private function executeCommand(Server $server, Command $command): Cursor
    {
        $options = $this->options->createCommandExecutionOptions();

        if (! $this->isWrite) {
            return $server->executeReadCommand($this->databaseName, $command, $options);
        }

        /* Server::executeReadWriteCommand() does not support a "readPreference"
         * option, so fall back to executeCommand(). This means that libmongoc
         * will not apply any client-level options (e.g. writeConcern), but that
         * should not be an issue as PHPLIB handles inheritance on its own. */
        if (isset($options['readPreference'])) {
            return $server->executeCommand($this->databaseName, $command, $options);
        }

        return $server->executeReadWriteCommand($this->databaseName, $command, $options);
    }
}
