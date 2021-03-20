<?php

namespace Atournayre\DotEnvUpdaterBundle\Command;

use Atournayre\DotEnvUpdaterBundle\Service\DotEnvUpdaterService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

class DotEnvUpdaterCommand extends Command
{
    const DOTENV = '.env';
    const DEFAULT_DOTENV_DOTPHP = '.env.local.php';

    /**
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * @var DotEnvUpdaterService
     */
    protected $dotEnvUpdater;

    /**
     * @var SymfonyStyle
     */
    protected $io;

    public function __construct(KernelInterface $kernel, DotEnvUpdaterService $dotEnvUpdater)
    {
        parent::__construct();
        $this->kernel = $kernel;
        $this->dotEnvUpdater = $dotEnvUpdater;
    }

    protected function configure(): void
    {
        $this->setName('dotenv');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function dotEnvDotPhpFileEditionIsNotAllowed(string $dotEnvDotPhpFile): bool
    {
        return $dotEnvDotPhpFile === self::DOTENV;
    }

    protected function dotEnvDotPhpFileIsMissing(string $dotEnvDotPhpPath): bool
    {
        return !file_exists($dotEnvDotPhpPath);
    }

    protected function debug(string $envPath): void
    {
        $variablesFromDotEnv = $this->dotEnvUpdater->getVariablesFromDotEnvDotPhp($envPath);
        $debugVariables = [];
        foreach ($variablesFromDotEnv as $key => $value) {
            $debugVariables[] = [$key, $value];
        }

        $this->io->table(
            ['Key', 'Value'],
            $debugVariables
        );
    }
}
