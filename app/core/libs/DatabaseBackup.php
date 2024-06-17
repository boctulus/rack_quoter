<?php

namespace boctulus\SW\core\libs;

class DatabaseBackup
{      

    /*
        https://chatgpt.com/c/38ee451b-f84d-4095-be78-b1ef839999ac

        Ej:

        DatabaseBackup::export('localhost', 'dydcolombiacom', 'LFmkvV-,~#oS', 'dydpage', Constants::ETC_PATH . 'backup.sql');
    */    
    static function export(string $host, string $username, string $password, string $databaseName, string $outputFile)
    {
        // Construir el comando mysqldump
        $command = sprintf(
            'mysqldump -h %s -u %s -p%s %s > %s',
            escapeshellarg($host),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($databaseName),
            escapeshellarg($outputFile)
        );

        // Ejecutar el comando
        return shell_exec($command);
    }

    /*
        Crear el archivo .my.cnf:

        [client]
        user = your_username
        password = your_password

        chmod 600 ~/.my.cnf

        y ...

        DatabaseBackup::export('your_database', 'backup.sql');

        @param string $cnf_file_path ruta al archivo my.cnf
    */
    static function exportUsingCnf(string $cnf_file_path, string $databaseName, string $outputFile): void
    {
        // Construir el comando mysqldump utilizando el archivo de configuración .my.cnf
        $command = sprintf(
            "mysqldump --defaults-file=$cnf_file_path %s > %s",
            escapeshellarg($databaseName),
            escapeshellarg($outputFile)
        );

        // Ejecutar el comando
        shell_exec($command);
    }
}

