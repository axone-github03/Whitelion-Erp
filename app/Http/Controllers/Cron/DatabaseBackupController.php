<?php

namespace App\Http\Controllers\Cron;

use App\Http\Controllers\Controller;
use App\Models\DatabaseBackup;
use Config;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Storage;

class DatabaseBackupController extends Controller {
	//

	public function index(Request $request) {

		$spacesFolder = getSpacesFolder();
		$databaseBackUpFolder = "database-backups";

		// $directories = Storage::disk('spaces')->directories($spacesFolder . "/" . $databaseBackUpFolder);

		$countDatabaseBackups = DatabaseBackup::count();

		if ($countDatabaseBackups == 120) {

			$lastDatabase = DatabaseBackup::orderBy('id', 'asc')->first();
			if ($lastDatabase) {

				Storage::disk('spaces')->delete($spacesFolder . "/" . $databaseBackUpFolder . "/" . $lastDatabase->file_name);
				$lastDatabase->delete();
				unlink(storage_path() . "/app/" . $databaseBackUpFolder . "/" . $lastDatabase->file_name);

			}

		}

		$file_name = "backup-" . Carbon::now()->format('Y-m-d-h-i-s') . ".sql";
		//mysqldump --user root --password=root@2018 erp_whitelion > /var/www/html/encodework-apps/whitelion-erp/storage/app/database-backups/backup-2022-08-02-05-49-09.sql

		$command = "mysqldump --user  " . Config::get('database.connections.mysql.username') . " --password=" . Config::get('database.connections.mysql.password') . " " . Config::get('database.connections.mysql.database') . "  >  " . storage_path() . "/app/" . $databaseBackUpFolder . "/" . $file_name;
		// print_r($command);
		// die;

		$returnVar = NULL;
		$output = NULL;
		exec($command, $output, $returnVar);
		$disk = Storage::disk('spaces');
		$response = $disk->put($spacesFolder . "/" . $databaseBackUpFolder . "/" . $file_name, @file_get_contents(storage_path() . "/app/" . $databaseBackUpFolder . "/" . $file_name));
		$DatabaseBackup = new DatabaseBackup();
		$DatabaseBackup->file_name = $file_name;
		$DatabaseBackup->save();

	}

}
