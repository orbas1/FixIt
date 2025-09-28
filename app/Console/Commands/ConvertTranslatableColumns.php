<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;

class ConvertTranslatableColumns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'convert:translatables';
    

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert existing columns to translatable JSON format';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = storage_path('app/translatables_converted.lock');

        if (File::exists($filePath)) {
            $this->info('The conversion process has already been completed.');
            $this->info("The lock file indicating the completion is located at: {$filePath}");
            $this->info('If you wish to re-run the conversion, please delete the lock file and execute the command again.');
            return;
        }

        $locale = app()->getLocale();
        $models = config('translatable.models');
        $this->info("Starting conversion process...");
        foreach ($models as $modelClass) {
            $this->info("Processing model: {$modelClass}");

            $model = new $modelClass;

            if (!property_exists($model, 'translatable')) {
                $this->warn("Model {$modelClass} does not have translatable fields. Skipping.");
                continue;
            }

            $table = $model->getTable();
            $translatableFields = $model->translatable;

            foreach ($translatableFields as $field) {
                if (Schema::hasColumn($table, $field)) {
                    $this->info("Checking field '{$field}' in table '{$table}'...");
                    DB::table($table)->get()->each(function ($item) use ($table, $field, $locale) {
                        $currentValue = json_decode($item->{$field}, true);

                        // If the current value is null or already in JSON format, skip the conversion
                        if (is_null($item->{$field}) || is_array($currentValue)) {
                            return;
                        }

                        $translatedValue = json_encode([$locale => $item->{$field}]);

                        DB::table($table)
                            ->where('id', $item->id)
                            ->update([$field => $translatedValue]);

                        $this->info("Updated '{$field}' in record ID {$item->id}.");
                    });
                } else {
                    $this->warn("Field '{$field}' not found in table '{$table}'.");
                }
            }
        }

        $this->info("Updating custom_properties column in the media table...");

        DB::table('media')->get()->each(function ($item) use ($locale) {
            $customProperties = json_decode($item->custom_properties, true);
        
            // Check if custom_properties is valid JSON and if the 'language' key exists
            if (is_array($customProperties) && isset($customProperties['language'])) {
                $this->info("Skipping media record ID {$item->id} as it already has a 'language' key.");
                return; // Skip this record but continue processing others
            }
        
            // Add the 'language' key with the current locale
            $customProperties = is_array($customProperties) ? $customProperties : []; // Ensure it's an array
            $customProperties['language'] = $locale;
        
            // Update the database record
            DB::table('media')
                ->where('id', $item->id)
                ->update(['custom_properties' => json_encode($customProperties)]);
        
            $this->info("Updated custom_properties for media record ID {$item->id}.");
        });

        if (!File::exists($filePath)) {
            File::put($filePath, 'done');
        }

        $this->info('Translatable fields conversion completed!');
    }
}
