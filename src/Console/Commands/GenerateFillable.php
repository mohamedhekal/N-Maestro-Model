<?php

namespace Noouh\AutoModelFillable\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class GenerateFillable extends Command
{
    protected $signature = 'noouh:generate-fillable';
    protected $description = 'Generate fillable properties for models based on table definitions';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $tables = DB::connection()->getDoctrineSchemaManager()->listTableNames();

        foreach ($tables as $table) {
            $columns = DB::getSchemaBuilder()->getColumnListing($table);
            $modelName = ucfirst(str_singular($table));
            $modelPath = app_path("Models/{$modelName}.php");

            if (File::exists($modelPath)) {
                $this->addFillableToModel($modelPath, $columns);
                $this->info("Fillable properties added to {$modelName} model.");
            } else {
                $this->warn("Model {$modelName} does not exist.");
            }
        }
    }

    protected function addFillableToModel($modelPath, $columns)
    {
        $fileContent = File::get($modelPath);
        $fillableArray = "protected $fillable = [\n";
        foreach ($columns as $column) {
            $fillableArray .= "        '{$column}',\n";
        }
        $fillableArray .= "    ];";

        if (preg_match('/protected $fillable\s*=\s*\[.*?\];/s', $fileContent)) {
            $fileContent = preg_replace('/protected $fillable\s*=\s*\[.*?\];/s', $fillableArray, $fileContent);
        } else {
            $fileContent = preg_replace('/class\s+\w+\s+extends\s+Model\s*\{/', "$0\n    {$fillableArray}\n", $fileContent);
        }

        File::put($modelPath, $fileContent);
    }
}
