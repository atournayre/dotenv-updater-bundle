<?php

namespace Atournayre\DotEnvUpdaterBundle\Command;

use Atournayre\Component\DotEnvEditor\DotEnvEditor;
use Atournayre\DotEnvUpdaterBundle\Service\DotEnvUpdaterService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

class DotEnvUpdaterElementCommand extends Command
{
    const DOTENV = '.env';
    /**
     * @var string
     */
    protected static $defaultName = 'dotenv:update:element';

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var DotEnvUpdaterService
     */
    private $dotEnvUpdater;

    /**
     * @var string
     */
    private $dotEnvDotPhpFile;

    /**
     * @var SymfonyStyle
     */
    private $io;

    public function __construct(KernelInterface $kernel, DotEnvUpdaterService $dotEnvUpdater)
    {
        parent::__construct(self::$defaultName);
        $this->kernel = $kernel;
        $this->dotEnvUpdater = $dotEnvUpdater;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Update specific element of .env.*.php file')
            ->addArgument('envFile', null, InputArgument::REQUIRED, '.env.local.php')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->io = new SymfonyStyle($input, $output);
        $this->dotEnvDotPhpFile = $input->getArgument('envFile');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $dotEnvDotPhpPath = $this->kernel->getProjectDir().DIRECTORY_SEPARATOR.$this->dotEnvDotPhpFile;

            if ($this->dotEnvDotPhpFile === self::DOTENV) {
                throw new \Exception(sprintf('%s edition is not allowed!', self::DOTENV));
            }

            if (!file_exists($dotEnvDotPhpPath)) {
                throw new \Exception(sprintf('Impossible to update %s cause file is missing.', $this->dotEnvDotPhpFile));
            }

            $dotEnvDotPhpVariables = $this->dotEnvUpdater->getVariablesFromDotEnvDotPhp($dotEnvDotPhpPath);

            $selectedVariableKey = $this->askUserWitchVariableToUpdate(array_keys($dotEnvDotPhpVariables));

            $dotEnvEditor = new DotEnvEditor();
            $dotEnvEditor->load($dotEnvDotPhpPath);

            $selectedVariableValue = $this->askUserWitchValueVorSelectedVariable($selectedVariableKey, $dotEnvEditor->get($selectedVariableKey));

            $dotEnvEditor->add($selectedVariableKey, $selectedVariableValue);
            $dotEnvEditor->save();

            $this->io->success(sprintf('Congrats, your %s is up-to-date!', $this->dotEnvDotPhpFile));
        } catch (\Exception $exception) {
            $this->io->error($exception->getMessage());
        }
        return 1;
    }

    public function askUserWitchVariableToUpdate(array $choices): string
    {
        $question = new ChoiceQuestion('Please select a variable to update', $choices);
        $question->setErrorMessage('Variable %s is invalid.');
        return $this->io->askQuestion($question);
    }

    public function askUserWitchValueVorSelectedVariable(string $selectedVariableKey, string $defaultValue): string
    {
        $question = new Question(
            'Please enter the value for "'.$selectedVariableKey.'"',
            $defaultValue
        );
        $question->setTrimmable(true);
        return $this->io->askQuestion($question);

    }
}
