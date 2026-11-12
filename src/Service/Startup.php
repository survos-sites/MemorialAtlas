<?php
declare(strict_types=1);
namespace App\Service;

use Symfony\Component\Console\Attribute\{AsCommand, Option};
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Process\Process;

final readonly class Startup
{
    public function __construct(private DemoData $demo,
        #[Autowire('%kernel.project_dir%')] private string $directory,
        #[Autowire('%env(DATABASE_URL)%')] private string $databaseUrl,
        #[Autowire('%env(S3_ENDPOINT)%')] private string $endpoint,
        #[Autowire('%env(AWS_S3_BUCKET_NAME)%')] private string $bucket,
        #[Autowire('%env(AWS_S3_ACCESS_ID)%')] private string $accessKey,
        #[Autowire('%env(AWS_S3_ACCESS_SECRET)%')] private string $secret,
    ) {}

    #[AsCommand('app:startup', 'Check configuration, create the app database, apply migrations and load the S3 demo')]
    public function start(SymfonyStyle $io, #[Option('Prepare schema without importing demo data')] bool $skipDemo = false): int
    {
        $start=microtime(true);
        foreach (['DATABASE_URL'=>$this->databaseUrl,'S3_ENDPOINT'=>$this->endpoint,'AWS_S3_BUCKET_NAME'=>$this->bucket,'AWS_S3_ACCESS_ID'=>$this->accessKey,'AWS_S3_ACCESS_SECRET'=>$this->secret] as $name=>$value) {
            if (trim($value)==='' || in_array($value, ['https://your-s3-endpoint', 'your-existing-bucket'], true)) {
                $io->error('Set '.$name.' in .env.local. Copy .env.local.example once and supply your PostgreSQL and S3 settings.');
                return 1;
            }
        }
        if (!str_starts_with($this->databaseUrl,'postgresql://') && !str_starts_with($this->databaseUrl,'postgres://')) {
            $io->error('Configure an existing PostgreSQL server in DATABASE_URL. Startup never creates a server.'); return 1;
        }
        foreach ([['doctrine:database:create','--if-not-exists'],['doctrine:migrations:migrate','--no-interaction']] as $arguments) {
            $process=new Process([PHP_BINARY,$this->directory.'/bin/console',...$arguments],$this->directory,timeout:120);
            $process->run(static fn ($type,$buffer)=>$io->write($buffer));
            if (!$process->isSuccessful()) return 1;
        }
        if (!$skipDemo) {
            $item=$this->demo->load();
            $io->success('Demo ready at /items/'.$item->id.' (saved OCR, descriptions and Jev decisions; no AI calls).');
        }
        $io->success(sprintf('Startup finished in %.1f seconds. Run symfony server:start -d, or php -S 127.0.0.1:8000 -t public.',microtime(true)-$start));
        return 0;
    }
}
