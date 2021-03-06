<?php
namespace Exceedone\Exment\Services\Installer;

/**
 *
 */
trait EnvTrait
{
    protected function setEnv($data = [], $matchRemove = false)
    {
        if (empty($data)) {
            return false;
        }

        // Read .env-file
        $env = file(path_join(base_path(), '.env'), FILE_IGNORE_NEW_LINES);

        $newEnvs = [];

        
        // Loop through .env-data
        foreach ($env as $env_value) {

            // Turn the value into an array and stop after the first split
            // So it's not possible to split e.g. the App-Key by accident
            $entry = explode("=", $env_value, 2);

            if (count($entry) == 0) {
                $newEnvs[] = $entry;
                continue;
            }

            $env_key = $entry[0];

            // find same key
            $hasKey = false;
            foreach ($data as $key => $value) {
                if ($env_key != $key) {
                    continue;
                }

                array_forget($data, $key);
                $hasKey = true;

                if (!$matchRemove) {
                    $newEnvs[] = $key . "=" . $value;
                }
            }
            if (!$hasKey) {
                $newEnvs[] = $env_value;
            }
        }

        
        // Loop through given data
        foreach ((array)$data as $key => $value) {
            if (array_has($newEnvs, $key)) {
                continue;
            }
            $newEnvs[] = $key . "=" . $value;
        }

        // Turn the array back to an String
        $env = implode("\n", $newEnvs);

        // And overwrite the .env with the new data
        file_put_contents(base_path() . '/.env', $env);
    }
    
    protected function removeEnv($data = [])
    {
        if (empty($data)) {
            return false;
        }
        $this->setEnv($data, true);
    }

    protected function getEnv($key, $path = null, $matchPrefix = false)
    {
        if (empty($key)) {
            return null;
        }

        if (is_null($path)) {
            $path = path_join(base_path(), '.env');
        }

        if (!\File::exists($path)) {
            return null;
        }

        // Read .env-file
        $env = file($path, FILE_IGNORE_NEW_LINES);

        if (empty($env)) {
            return null;
        }

        // Loop through .env-data
        $lists = [];
        foreach ($env as $env_value) {

            // Turn the value into an array and stop after the first split
            // So it's not possible to split e.g. the App-Key by accident
            $entry = explode("=", $env_value, 2);

            if (count($entry) == 0) {
                continue;
            }

            if ($matchPrefix) {
                if (strpos($entry[0], $key) === false) {
                    continue;
                }
            } else {
                if ($key != $entry[0]) {
                    continue;
                }
            }

            $lists[] = $entry;
        }

        return $lists;
    }
}
