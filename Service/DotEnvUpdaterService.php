<?php

namespace Atournayre\DotEnvUpdaterBundle\Service;

use Atournayre\Component\DotEnvEditor\DotEnvEditor;
use Symfony\Component\Dotenv\Dotenv;

class DotEnvUpdaterService
{
    public function getVariablesFromDotEnv(string $envPath): array
    {
        return (new Dotenv())
            ->parse(file_get_contents($envPath), $envPath);
    }

    public function getVariablesFromDotEnvDotPhp(string $envPath): array
    {
        $dotEnvEditor = new DotEnvEditor();
        $dotEnvEditor->load($envPath);
        return $dotEnvEditor->toArray();
    }

    public function getMissingVariables(string $dotEnvPath, string $dotEnvDotPhpPath): array
    {
        return array_diff_key(
            $this->getVariablesFromDotEnv($dotEnvPath),
            $this->getVariablesFromDotEnvDotPhp($dotEnvDotPhpPath)
        );
    }

    public function updateDotEnvDotPhp(array $variables, string $envPath): void
    {
        $dotEnvEditor = new DotEnvEditor();
        $dotEnvEditor->load($envPath);

        foreach ($variables as $key => $value) {
            $dotEnvEditor->add($key, $value);
        }

        $dotEnvEditor->save();
    }
}
