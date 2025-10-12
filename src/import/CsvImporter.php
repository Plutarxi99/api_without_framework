<?php
// src/import/CsvImporter.php
use JetBrains\PhpStorm\ArrayShape;

require_once __DIR__ . '/../repositories/RecipientRepository.php';

/**
 * Для чтения CSV файла и запись в бд
 */
class CsvImporter {
    private int $batchSize;
    private RecipientRepository $repo;

    /**
     * Инициализурем класс
     *
     * @param  int  $batchSize
     */
    public function __construct(int $batchSize = 500) {
        $this->repo = new RecipientRepository();
        $this->batchSize = $batchSize;
    }

    /**
     * Получение генератора по файлу CSV
     *
     * @param string $path Путь до файла
     *
     * @return Generator
     */
    private function csvGenerator(string $path): Generator
    {
        if (is_null($fh = fopen($path, 'r'))) {
            throw new RuntimeException("Cannot open file: $path");
        }

        while (($row = fgetcsv($fh, 0, ',', '"', '\\'))) {
            yield $row;
        }

        fclose($fh);
    }

    /**
     * Нормализация номера телефона
     *
     * @param string $raw Строка номера телефона
     *
     * @return string
     */
    private function normalizePhone(string $raw): string {
        return preg_replace('/\D+/', '', trim($raw));
    }

    /**
     * Вставка значение в бд
     *
     * @param string $path Путь до файла
     *
     * @return array{inserted: int, skipped: int}
     */
    #[ArrayShape(['inserted' => "int", 'skipped' => "int"])]
    public function importFile(string $path): array {
        $batch = [];
        $inserted = 0;
        $skipped = 0;

        foreach ($this->csvGenerator($path) as $row) {

            if (
                empty($phone_raw = rtrim(explode(' ,', $row[0])[0], ',') ?? '') ||
                empty($name = rtrim(explode(' ,', $row[0])[1], ',') ?? '')
            ) {
                $skipped++; continue;
            }

            if (empty($phone = $this->normalizePhone($phone_raw))) {
                $skipped++; continue;
            }

            $batch[] = ['phone' => $phone, 'name' => $name];

            if (count($batch) >= $this->batchSize) {
                $inserted += $this->repo->insertBatch($batch);
                $batch = [];
            }
        }

        if (count($batch)) {
            $inserted += $this->repo->insertBatch($batch);
        }

        if (file_exists($path)) {
            unlink($path);
        }

        return ['inserted' => $inserted, 'skipped' => $skipped];
    }
}
