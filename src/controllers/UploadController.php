<?php

use JetBrains\PhpStorm\NoReturn;

require_once __DIR__ . '/../bootstrap.php';

class UploadController
{
    /**
     * Загрузка файлов
     *
     * @return void
     */
    #[NoReturn]
    public function upload(): void
    {
        $config = require __DIR__ . '/../config.php';

        if (!isset($_FILES['file'])) {
            respond_json(['error'=>'No file uploaded'], 400);
        }

        $f = $_FILES['file'];

        if ($f['type'] !== 'text/csv') {
            respond_json(['error'=>'File must be CSV'], 400);
        }

        $storage = $config['upload_dir'] . '/' . uniqid('csv_', true) . '.csv';

        if (! move_uploaded_file($f['tmp_name'], $storage)) {
            respond_json(['error'=>'Cannot save uploaded file'], 500);
        }

        respond_json(['ok' => true, 'result' => new CsvImporter()->importFile($storage)]);
    }
}