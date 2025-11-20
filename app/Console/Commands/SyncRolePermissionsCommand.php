<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Spatie\Permission\Models\Role;

class SyncRolePermissionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roles:sync-permissions
                            {--path=database/data/roleWithPermissions.json : Path to JSON file}
                            {--force : Overwrite file without prompt}
                            {--dry-run : Do not write file, only print stats}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export current roles and permissions from DB to JSON file to keep roleWithPermissions snapshot in sync.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(Filesystem $filesystem): int
    {
        $pathOption = $this->option('path');
        $path = base_path($pathOption ?: 'database/data/roleWithPermissions.json');

        $this->info("Reading roles & permissions from database...");

        $roles = Role::with('permissions')->get();

        $data = $roles->map(function (Role $role) {
            return [
                'name' => $role->name,
                'guard_name' => $role->guard_name,
                'permissions' => $role->permissions->pluck('name')->sort()->values()->toArray(),
            ];
        })->sortBy('name')->values()->toArray();

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $roleCount = count($data);
        $permCount = array_sum(array_map('count', array_column($data, 'permissions')));

        $this->info("Found {$roleCount} roles and {$permCount} permissions (total per role listing) in DB.");

        if ($this->option('dry-run')) {
            $this->info('Dry run: JSON payload preview (first 2 roles):');
            $preview = array_slice($data, 0, 2);
            $this->line(json_encode($preview, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            return 0;
        }

        if ($filesystem->exists($path)) {
            if (! $this->option('force')) {
                $this->line("File {$path} already exists.");
                if (! $this->confirm('Do you want to overwrite the file?')) {
                    $this->comment('Cancelled. No changes made.');
                    return 0;
                }
            }
            // create a backup copy with timestamp
            $backupPath = $path . '.bak.' . now()->format('YmdHis');
            $filesystem->copy($path, $backupPath);
            $this->info("Backup created at: {$backupPath}");
        }

        $dir = dirname($path);
        if (! $filesystem->isDirectory($dir)) {
            $filesystem->makeDirectory($dir, 0755, true);
        }
        $filesystem->put($path, $json . PHP_EOL);

        $this->info("Wrote {$roleCount} roles with permissions to {$path}");

        return 0;
    }
}
