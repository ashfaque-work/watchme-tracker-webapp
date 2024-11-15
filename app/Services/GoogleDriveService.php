<?php

namespace App\Services;

use App\Livewire\Forms\LoginForm;
use App\Models\AccessToken;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Google\Client as GoogleClient;


class GoogleDriveService
{
    public function uploadToDrive($formattedName, $filename, $imageBinary)
    {
        try {

            $access_token = AccessToken::getAccessToken()->access_token;

            $parentFolderId = \Config('services.google.folder_id');

            //create a new Google Drive client
            $client = new GoogleClient();
            $client->setAccessToken($access_token);
            $client->addScope(\Google\Service\Drive::DRIVE);
            $service = new \Google_Service_Drive($client);

            // check if the user folder already exists
            $userFolderName = $formattedName;
            $userFolderId = $this->getUserFolderId($service, $userFolderName, $parentFolderId);

            if (!$userFolderId) {
                // create a new folder with the name based on user id inside parent folder
                $fileMetadata = new \Google_Service_Drive_DriveFile([
                    'name' => $userFolderName,
                    'mimeType' => 'application/vnd.google-apps.folder',
                    'parents' => [$parentFolderId]
                ]);
                $folder = $service->files->create($fileMetadata, ['fields' => 'id']);
                $userFolderId = $folder->id;
                // Log::info("New user folder id: " . $userFolderId);
            }

            //create a new subfolder inside the user id fodler
            $dateFolderName = now()->format('Y_m_d');
            $dateSubfolderId = $this->getOrCreateSubfolder($service, $dateFolderName, $userFolderId);

            //upload screenshot
            $fileMetadata = new \Google_Service_Drive_DriveFile([
                'name' => $filename,
                'parents' => [$dateSubfolderId]
            ]);
            $file = $service->files->create($fileMetadata, [
                'data' => $imageBinary,
                'uploadType' => 'media',
                'fields' => 'id'
            ]);
            // Log::info("file id: " . $file->id);

            //set permissions to make the file publicly accessible
            $permission = new \Google_Service_Drive_Permission([
                'type' => 'anyone',
                'role' => 'reader',
            ]);

            $service->permissions->create($file->id, $permission);

            return $file->id;
        } catch (Exception $e) {
            Log::error($e);
            return false;
        }
    }

    private function getUserFolderId($service, $folderName, $parentFolderId)
    {
        // check if the user folder already exists
        $results = $service->files->listFiles([
            'q' => "name='{$folderName}' and mimeType='application/vnd.google-apps.folder' and '{$parentFolderId}' in parents",
            'spaces' => 'drive'
        ]);

        if (count($results->getFiles()) > 0) {
            // Log::info("Existing user folder id " . $results->getFiles()[0]->getId());
            return $results->getFiles()[0]->getId(); //return existing folder id
        }

        return null;
    }

    private function getOrCreateSubfolder($service, $folderName, $parentFolderId)
    {
        //check if the subfolder already exists
        $results = $service->files->listFiles([
            'q' => "name='{$folderName}' and mimeType='application/vnd.google-apps.folder' and '{$parentFolderId}' in parents",
            'spaces' => 'drive'
        ]);

        if (count($results->getFiles()) > 0) {
            // Log::info("Existing subfolder id " . $results->getFiles()[0]->getId());
            return $results->getFiles()[0]->getId(); //return existing folder id
        }

        //create a new subfolder
        $fileMetadata = new \Google_Service_Drive_DriveFile([
            'name' => $folderName,
            'parents' => [$parentFolderId],
            'mimeType' => 'application/vnd.google-apps.folder'
        ]);
        $folder = $service->files->create($fileMetadata, ['fields' => 'id']);
        // Log::info("New subfolder id " . $folder->id);
        return $folder->id;
    }
}
