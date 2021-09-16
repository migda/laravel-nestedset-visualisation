<?php

namespace Migda\LaravelNestedsetVisualisation\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Kalnoy\Nestedset\NodeTrait;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Visualize extends Command
{
    public const ALLOWED_MIME_TYPES = [
        'png',
        'jpg',
        'gif',
        'svg',
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravel-nestedset:visualize
        {model? : Model name with the namespace}
        {property? : Property of the model}
        {--output=./graph.jpg}
        {--format=jpg}
        {--direction=LR}
        {--dot-path=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates an image of the nestedset graph';

    /**
     * Execute the console command.
     *
     * @return int
     * @throws Exception
     */
    public function handle(): int
    {
        // Model
        if (!$this->argument('model')) {
            $this->askForModel();
        }

        $model = $this->argument('model');

        // Check if model exists
        if (!class_exists($model)) {
            throw new Exception('Model ' . $model . ' not found!');
        }

        // Label
        if (!$this->argument('property')) {
            $this->askForProperty($model);
        }
        $property = $this->argument('property');

        // Generate graph
        $this->generateNestedsetInDotFormat($model, $property);

        return 0;
    }

    /**
     * Ask for a model name if not provided as argument
     */
    protected function askForModel(): void
    {
        $choices = $this->getModelsUsingNodeTrait();

        $choice = $this->choice('Which model would you like select?', $choices, 0);
        $this->info('You have selected: ' . $choice);

        $this->input->setArgument('model', $choice);
    }

    /**
     * Ask for a property if not provided as argument
     */
    protected function askForProperty(string $model): void
    {
        $object = new $model();
        $choices = $object->getFillable();

        $choice = $this->choice('Which property would you like select?', $choices, 0);
        $this->info('You have selected: ' . $choice);

        $this->input->setArgument('property', $choice);
    }

    /**
     * @param string $model
     * @param string $property
     * @throws Exception
     */
    protected function generateNestedsetInDotFormat(string $model, string $property): void
    {
        // Set format and check if allowed
        $format = strtolower($this->option('format'));

        if (!in_array($format, self::ALLOWED_MIME_TYPES)) {
            throw new Exception(sprintf("Format '%s' is not supported", $format));
        }

        // Set dot-path, dot is default
        $dotPath = $this->option('dot-path') ?? 'dot';

        // Set output
        $outputImage = $this->option('output');

        // Run dot process
        $process = new Process([$dotPath, '-T', $format, '-o', $outputImage]);
        $process->setInput($this->buildDotFile($model, $property));
        $process->run();

        // Check if command successfully finished the process
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    /**
     * @param string $model
     * @param string $property
     * @return string
     */
    protected function buildDotFile(string $model, string $property): string
    {
        // Display settings
        $layout = $this->option('direction') === 'LR' ? 'LR' : 'TB';

        // Get nodes from the model
        $nodes = $model::get()->toTree();

        // Start graph with some definition
        $graph = [];
        $graph[] = 'digraph unix';
        $graph[] = '{';
        $graph[] = "rankdir={$layout};";
        $graph[] = 'node [color=lightblue2, style=filled];';

        // Sub graphs
        $subGraphs = [];
        $subGraphs = $this->buildTree($nodes, $subGraphs, $property);
        $graph = array_merge($graph, $subGraphs);

        // Close graph
        $graph[] = '}';

        return implode(PHP_EOL, $graph);
    }

    /**
     * @param Collection $nodes
     * @param array $result
     * @param string $property
     * @return array
     */
    private function buildTree(Collection $nodes, array &$result, string $property): array
    {
        // Iterate through the tree nodes
        foreach ($nodes as $node) {
            // Start parent
            if ($node->isRoot()) {
                $result[] = 'subgraph cluster_' . $node->getKey();
                $result[] = "{";
                $result[] = "style=invis;";
            }

            // Tree
            if (!$node->isRoot()) {
                $result[] = 'n_' . $node->getParentId() . ' -> ' . 'n_' . $node->getKey() . ' ;';
            }

            $result[] = 'n_' . $node->getKey() . ' [label="' . $node->$property . '"] ;';

            // Build tree for children
            array_merge($result, $this->buildTree($node->children, $result, $property));

            // Close parent
            if ($node->isRoot()) {
                $result[] = '}';
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    private function getModelsUsingNodeTrait(): array
    {
        $models = collect(File::allFiles(app_path()))
            ->map(function ($item) {
                $path = $item->getRelativePathName();

                return sprintf('\%s%s',
                    Container::getInstance()->getNamespace(),
                    strtr(substr($path, 0, strrpos($path, '.')), '/', '\\'));;
            })
            ->filter(function ($class) {
                $valid = false;

                if (class_exists($class)) {
                    $reflection = new \ReflectionClass($class);
                    $valid = $reflection->isSubclassOf(Model::class) &&
                        in_array(NodeTrait::class, $reflection->getTraitNames());
                }

                return $valid;
            });


        return $models->values()->toArray();
    }
}
