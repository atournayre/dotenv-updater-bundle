<?php

namespace Atournayre\DotEnvUpdaterBundle\Command;

use Atournayre\Component\DotEnvEditor\DotEnvEditor;
use Atournayre\DotEnvUpdaterBundle\Exception\DotEnvEditionNotAllowedException;
use Atournayre\DotEnvUpdaterBundle\Exception\DotEnvMissingFileException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class DotEnvUpdaterElementCommand extends DotEnvUpdaterCommand
{
    protected function configure(): void
    {
        $this
            ->setName('dotenv:update:element')
            ->setDescription('Update specific element of .env.*.php file')
            ->addArgument('envFile', null, InputArgument::REQUIRED, self::DEFAULT_DOTENV_DOTPHP)
            ->addOption('debug', null, InputOption::VALUE_NONE, 'Dump configuration')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $dotEnvDotPhpFile = $input->getArgument('envFile');
            $isDebug = true === $input->getOption('debug');

            $dotEnvDotPhpPath = $this->kernel->getProjectDir().DIRECTORY_SEPARATOR.$dotEnvDotPhpFile;

            if ($this->dotEnvDotPhpFileEditionIsNotAllowed($dotEnvDotPhpFile)) {
                throw new DotEnvEditionNotAllowedException();
            }

            if ($this->dotEnvDotPhpFileIsMissing($dotEnvDotPhpPath)) {
                throw new DotEnvMissingFileException($dotEnvDotPhpFile);
            }

            $dotEnvDotPhpVariables = $this->dotEnvUpdater->getVariablesFromDotEnvDotPhp($dotEnvDotPhpPath);

            $selectedVariableKey = $this->askUserWitchVariableToUpdate(array_keys($dotEnvDotPhpVariables));

            $dotEnvEditor = new DotEnvEditor($dotEnvDotPhpPath);
            $dotEnvEditor->load();

            $selectedVariableValue = $this->askUserWitchValueVorSelectedVariable($selectedVariableKey, $dotEnvEditor->get($selectedVariableKey));

            $dotEnvEditor->add($selectedVariableKey, $selectedVariableValue);
            $dotEnvEditor->save();

            if ($isDebug) {
                $this->debug($dotEnvDotPhpFile);
            }

            $this->io->success(sprintf('Congrats, your %s is up-to-date!', $dotEnvDotPhpFile));
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
