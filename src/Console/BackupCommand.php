<?php

namespace Exceedone\Exment\Console;

use Illuminate\Support\Facades\Storage;
use Illuminate\Console\Command;
use Exceedone\Exment\Model\System;
use Exceedone\Exment\Enums\BackupTarget;
use \File;

class BackupCommand extends Command
{
    use CommandTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exment:backup {--target=} {--schedule}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup database definition, table data, files in selected folder';

    /**
     * console command start time (YmdHis)
     *
     * @var string
     */
    protected $starttime;

    /**
     * temporary folder path store files for archive
     *
     * @var string
     */
    protected $tempdir;

    /**
     * list folder path store backup files
     *
     * @var string
     */
    protected $listdir;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->initExmentCommand();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->starttime = date('YmdHis');

        $target = $this->option("target") ?? BackupTarget::arrays();

        if (is_string($target)) {
            $target = collect(explode(",", $target))->map(function ($t) {
                return new BackupTarget($t) ?? null;
            })->filter()->toArray();
        }

        $this->getBackupPath();

        // backup database tables
        if (in_array(BackupTarget::DATABASE, $target)) {
            \DB::backupDatabase($this->tempdir);
        }

        // backup directory
        if (!$this->copyFiles($target)) {
            return -1;
        }

        // archive whole folder to zip
        $this->createZip();

        // delete temporary folder
        $success = \File::deleteDirectory($this->tempdir);

        $this->removeOldBackups();

        return 0;
    }

    /**
     * backup table data except virtual generated column.
     *
     * @param string backup target table
     */
    private function backupTable($table)
    {
        // create tsv file
        $file = new \SplFileObject(path_join($this->tempdir, $table.'.tsv'), 'w');
        $file->setCsvControl("\t");

        // get column definition
        $columns = \Schema::getColumnDefinitions($table);

        // get output field name list (not virtual column)
        $outcols = [];
        foreach ($columns as $column) {
            if (!boolval($column['virtual'])) {
                $outcols[] = strtolower($column['column_name']);
            }
        }
        // write column header
        $file->fputcsv($outcols);

        // execute backup. contains soft deleted table
        \DB::table($table)->orderBy('id')->chunk(1000, function ($rows) use ($file, $outcols) {
            foreach ($rows as $row) {
                $array = (array)$row;
                $row = array_map(function ($key) use ($array) {
                    return $array[$key];
                }, $outcols);
                // write detail data
                $file->fputcsv($row);
            }
        });
    }
    /**
     * get and create backup folder path
     *
     */
    private function getBackupPath()
    {
        // edit temporary folder path for store archive file
        $this->tempdir = storage_paths('app', 'backup', 'tmp', $this->starttime);
        // edit zip folder path
        $this->listdir = storage_paths('app', 'backup', 'list');
        // create temporary folder if not exists
        if (!File::exists($this->tempdir)) {
            File::makeDirectory($this->tempdir, 0755, true);
        }
        // create zip folder if not exists
        if (!File::exists($this->listdir)) {
            File::makeDirectory($this->listdir, 0755, true);
        }
    }
    /**
     * copy folder to temp directory
     *
     * @return bool true:success/false:fail
     */
    private function copyFiles($target)
    {
        // get directory paths
        $settings = collect($target)->map(function ($val) {
            return BackupTarget::dir($val);
        })->filter(function ($val) {
            return isset($val);
        })->toArray();
        $settings = array_merge(
            config('exment.backup_info.copy_dir', []),
            $settings
        );
        
        if (is_array($settings)) {
            foreach ($settings as $setting) {
                $from = base_path($setting);
                if (!\File::exists($from)) {
                    continue;
                }
                $to = path_join($this->tempdir, $setting);
                if (!File::exists($from)) {
                    continue;
                }
                if (!File::exists($to)) {
                    File::makeDirectory($to, 0755, true);
                }

                $success = \File::copyDirectory($from, $to);
                if (!$success) {
                    return false;
                }
            }
        }
        return true;
    }
    /**
     * archive whole folder(sql and tsv only) to zip.
     *
     */
    private function createZip()
    {
        // set last directory name to zipfile name
        $filename = $this->starttime . '.zip';

        // open new zip file
        $zip = new \ZipArchive();
        $res = $zip->open(path_join($this->listdir, $filename), \ZipArchive::CREATE);

        if ($res === true) {
            // iterator all files in folder
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->tempdir));
            foreach ($files as $name => $file) {
                if ($file->isDir()) {
                    continue;
                }
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($this->tempdir) + 1);
                $zip->addFile($filePath, $relativePath);
            }
            $zip->close();
        }
    }
    
    protected function removeOldBackups()
    {
        // get history file counts
        $backup_history_files = System::backup_history_files();
        if (!isset($backup_history_files) || $backup_history_files <= 0) {
            return;
        }

        // check whether batch
        $schedule = boolval($this->option("schedule"));
        if (!$schedule) {
            return;
        }

        $disk = Storage::disk('backup');

        // get files
        $filenames = $disk->files('list');

        // get file infos
        $files = collect($filenames)->map(function ($filename) use ($disk) {
            return [
                'name' => $filename,
                'lastModified' => $disk->lastModified($filename),
            ];
        })->sortByDesc('lastModified');

        // remove file
        foreach ($files->values()->all() as $index => $file) {
            if ($index < $backup_history_files) {
                continue;
            }

            $disk->delete(array_get($file, 'name'));
        }
    }
}
