<?php

namespace Uzhlaravel\Maishapay\Commands;

use Illuminate\Console\Command;

class MaishapayCommand extends Command
{
    public $signature = 'maishapay:install {--force : Force overwrite existing files}';

    public $description = 'Install Maishapay package - publishes config and migrations';

    public function handle(): int
    {
        $this->displayWelcome();
        $this->info('Installing Maishapay package...');

        // Publish configuration file
        $this->comment('Publishing configuration file...');
        $this->call('vendor:publish', [
            '--tag' => 'maishapay-config',
            '--force' => $this->option('force'),
        ]);

        // Publish migrations
        $this->comment('Publishing migrations...');
        $this->call('vendor:publish', [
            '--tag' => 'maishapay-migrations',
            '--force' => $this->option('force'),
        ]);

        // Run migrations
        if ($this->confirm('Would you like to run the migrations now?', true)) {
            $this->comment('Running migrations...');
            $this->call('migrate');
        }

        $this->newLine();
        $this->info('Maishapay package installed successfully!');
        $this->newLine();

        // Display next steps
        $this->comment('Next steps:');
        $this->line('1. Add your Maishapay credentials to your .env file:');
        $this->line('   MAISHAPAY_PUBLIC_KEY=your_public_key');
        $this->line('   MAISHAPAY_SECRET_KEY=your_secret_key');
        $this->line('   MAISHAPAY_GATEWAY_MODE=0  # 0 for sandbox, 1 for production');
        $this->newLine();
        $this->line('2. Check the published config file at: config/maishapay.php');
        $this->line('3. Review the published migrations in: database/migrations/');
        $this->newLine();

        // Ask for GitHub star
        $this->askForGitHubStar();

        $this->info('Happy coding!');

        return self::SUCCESS;
    }

    protected function displayWelcome(): void
    {
        $this->info('');
        $this->info('╔══════════════════════════════════════╗');
        $this->info('║          MaishaPay Package           ║');
        $this->info('║     Laravel Payment Integration      ║');
        $this->info('╚══════════════════════════════════════╝');
        $this->info('');
    }

    /**
     * Ask user to star the repository on GitHub
     */
    protected function askForGitHubStar(): void
    {
        $this->newLine();
        $this->line('┌─────────────────────────────────────────────────────────────┐');
        $this->line('│   Love this package? Give us a star on GitHub!              │');
        $this->line('│                                                             │');
        $this->line('│   Your support helps us maintain and improve this package   │');
        $this->line('│     https://github.com/uzziahlukeka/maishapay               │');
        $this->line('└─────────────────────────────────────────────────────────────┘');
        $this->newLine();

        if ($this->confirm('Would you like to open the GitHub repository in your browser?', false)) {
            $this->openGitHubRepo();
        }

        $this->newLine();
    }

    /**
     * Open GitHub repository in the default browser
     */
    protected function openGitHubRepo(): void
    {
        $githubUrl = 'https://github.com/uzziahlukeka/maishapay';

        try {
            if (PHP_OS_FAMILY === 'Windows') {
                exec("start {$githubUrl}");
            } elseif (PHP_OS_FAMILY === 'Darwin') { // macOS
                exec("open {$githubUrl}");
            } else { // Linux and others
                exec("xdg-open {$githubUrl}");
            }

            $this->info('Opening GitHub repository in your browser...');
            $this->comment('Don\'t forget to click that ⭐ star button!');
        } catch (\Exception $e) {
            $this->warn('Could not automatically open browser. Please visit:');
            $this->line($githubUrl);
        }
    }
}
