<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateFaIcons extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'icons:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the FaIcon Enum automatically based on available FontAwesome Icons';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sets = [
            'fas' => 'solid',
            'far' => 'regular',
            'fab' => 'brands',
        ];

        $enumCases = [];

        foreach ($sets as $prefix => $directory) {
            $path = base_path("vendor/owenvoke/blade-fontawesome/resources/svg/{$directory}");

            if(! File::exists($path)) {
                $this->error("Directory not found: {$path}");
                continue;
            }

            $files = File::files($path);

            foreach ($files as $file) {
                $name = str_replace('.svg', '', $file->getFilename());

                // Convert `arrow-left` to `arrowLeft`
                $enumKey = strtoupper(
                    str_replace('-', '_', $name)
                );

                // If the enum key starts with a number, prefix it
                if (preg_match('/^[0-9]/', $enumKey)) {
                    $enumKey = 'NUMBER_' . $enumKey;
                }

                // Add suffix for style to avoid conflicts
                $suffix = match($prefix) {
                    'far' => '_REGULAR',
                    'fab' => '_BRAND',
                    default => ''
                };

                $enumKey .= $suffix;

                $enumCases[] = "    case {$enumKey} = '{$prefix}-{$name}';";
            }
        }

        $enumBody = implode("\n", $enumCases);

        $enumTemplate = <<<PHP
<?php

    namespace App\Enums;

    enum FaIcon: string
    {
    {$enumBody}

        public function icon(): string
        {
            return \$this->value;
        }
    }
PHP;

        File::put(app_path('Enums/FaIcon.php'), $enumTemplate);

        $this->info('FaIcon enum generated successfully');
    }
}
