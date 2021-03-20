<?php

namespace Atournayre\DotEnvUpdaterBundle\Command;

use Atournayre\DotEnvUpdaterBundle\Exception\DotEnvEditionNotAllowedException;
use Atournayre\DotEnvUpdaterBundle\Exception\DotEnvMissingFileException;
use Atournayre\DotEnvUpdaterBundle\Exception\DotEnvNoUpdateNeededException;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class DotEnvUpdaterUpdateCommand extends DotEnvUpdaterCommand
{
    protected function configure(): void
    {
        $this
            ->setName('dotenv:update')
            ->setDescription('Update .env.*.php files for your application')
            ->addArgument('envFile', null, InputArgument::REQUIRED, self::DEFAULT_DOTENV_DOTPHP)
            ->addOption('debug', null, InputOption::VALUE_NONE, 'Dump configuration')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $dotEnvDotPhpFile = $input->getArgument('envFile');
            $isDebug = true === $input->getOption('debug');

            $dotEnvPath = $this->kernel->getProjectDir().DIRECTORY_SEPARATOR. self::DOTENV;
            $dotEnvDotPhpPath = $this->kernel->getProjectDir().DIRECTORY_SEPARATOR. $dotEnvDotPhpFile;

            if ($this->dotEnvDotPhpFileEditionIsNotAllowed($dotEnvDotPhpFile)) {
                throw new DotEnvEditionNotAllowedException();
            }

            if ($this->dotEnvDotPhpFileIsMissing($dotEnvDotPhpPath)) {
                throw new DotEnvMissingFileException($dotEnvDotPhpFile);
            }

            $dotEnvDotPhpMissingVariables = $this->dotEnvUpdater->getMissingVariables($dotEnvPath, $dotEnvDotPhpPath);

            if (count($dotEnvDotPhpMissingVariables) === 0 && !$isDebug) {
                throw new DotEnvNoUpdateNeededException($dotEnvDotPhpFile);
            }

            $variablesToAdd = [];
            foreach ($dotEnvDotPhpMissingVariables as $missingVariableKey => $missingVariableValue) {
                $variablesToAdd[$missingVariableKey] = $this->askUserForValueOfMissingVariable($missingVariableKey, $missingVariableValue);
            }

            $this->dotEnvUpdater->updateDotEnvDotPhp($variablesToAdd, $dotEnvDotPhpPath);

            if ($isDebug) {
                $this->debug($dotEnvDotPhpFile);
            }

            $this->io->success(sprintf('Congrats, your %s is up-to-date!', $dotEnvDotPhpFile));
        } catch (DotEnvNoUpdateNeededException $exception) {
            $this->io->caution($exception->getMessage());
        } catch (\Exception $exception) {
            $this->io->error($exception->getMessage());
        }

        $this->io->writeln('<comment>Need to change an option value ? Use <fg=green>dotenv:update:element</> command.</comment>');
        return 1;
    }

    private function askUserForValueOfMissingVariable($missingVariableKey, string $default)
    {
        $question = new Question('Please enter the value for "'.$missingVariableKey.'"', $default);
        $question->setAutocompleterValues([$default]);
        $question->setTrimmable(true);
        return $this->io->askQuestion($question);
    }
}
