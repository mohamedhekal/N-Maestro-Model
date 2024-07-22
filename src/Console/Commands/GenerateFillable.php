<?php

namespace Noouh\AutoModelFillable\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GenerateFillable extends Command
{
    protected $signature = 'noouh:generate-fillable {jsonFile}';
    protected $description = 'Generate fillable properties for models based on table definitions from a JSON file';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $jsonFile = $this->argument('jsonFile');

        if (!File::exists($jsonFile)) {
            $this->error("JSON file not found: {$jsonFile}");
            return;
        }

        $jsonContent = File::get($jsonFile);
        $tables = json_decode($jsonContent, true);

        $modelPath = app_path("Models");
        $modelFiles = File::allFiles($modelPath);

        foreach ($modelFiles as $modelFile) {
            $modelContent = File::get($modelFile);
            $tableName = $this->extractTableName($modelContent);

            if ($tableName) {
                $columns = $this->getColumnsFromJson($tables, $tableName);
                if ($columns) {
                    $this->addFillableToModel($modelFile->getPathname(), $columns);
                    $this->info("Fillable properties added to " . $modelFile->getFilename() . " model.");
                } else {
                    $this->warn("Table {$tableName} not found in JSON file.");
                }
            } else {
                $this->warn("Table name not defined in " . $modelFile->getFilename() . " model.");
            }
        }
    }

    protected function extractTableName($modelContent)
    {
        if (preg_match('/protected\s+\$table\s+=\s+\'([^\']+)\'\s*;/', $modelContent, $matches)) {
            return $matches[1];
        }

        return null;
    }

    protected function getColumnsFromJson($tables, $tableName)
    {
        foreach ($tables as $table) {
            if ($table['table'] === $tableName) {
                return array_column($table['columns'], 'name');
            }
        }

        return null;
    }

    protected function addFillableToModel($modelPath, $columns)
    {
        $fileContent = File::get($modelPath);
        $fillableArray = "protected \$fillable = [\n";
        foreach ($columns as $column) {
            $fillableArray .= "        '{$column}',\n";
        }
        $fillableArray .= "    ];";

        if (preg_match('/protected \$fillable\s*=\s*\[.*?\];/s', $fileContent)) {
            $fileContent = preg_replace('/protected \$fillable\s*=\s*\[.*?\];/s', $fillableArray, $fileContent);
        } else {
            $fileContent = preg_replace('/class\s+\w+\s+extends\s+Model\s*\{/', "$0\n    {$fillableArray}\n", $fileContent);
        }

        File::put($modelPath, $fileContent);
    }
}