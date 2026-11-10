<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Cemetery;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputArgument, InputInterface, InputOption};
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:cemetery:create', description: 'Create or reuse a cemetery by its exact name and optional locality.')]
final class CreateCemeteryCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $em) { parent::__construct(); }
    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED)->addOption('locality', null, InputOption::VALUE_REQUIRED);
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = trim($input->getArgument('name'));
        $locality = $input->getOption('locality');
        if ($name === '' || mb_strlen($name) > 255 || ($locality !== null && (trim($locality) === '' || mb_strlen($locality) > 255))) throw new \InvalidArgumentException('Name/locality must be nonempty and at most 255 characters.');
        $cemetery = $this->em->getRepository(Cemetery::class)->findOneBy(['name'=>$name, 'locality'=>$locality]);
        if ($cemetery === null) {
            $cemetery = new Cemetery(); $cemetery->name = $name; $cemetery->locality = $locality;
            $this->em->persist($cemetery); $this->em->flush();
        }
        $output->writeln((string) $cemetery->id);
        return Command::SUCCESS;
    }
}
