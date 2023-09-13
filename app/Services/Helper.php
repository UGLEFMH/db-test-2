<?php

namespace App\Services;


class Helper
{
    /**
     * @param $handle
     * @param $chunkSize
     * @return bool|array
     */
    public static function readChunk($handle, $chunkSize): bool|array
    {
        $chunk = [];

        for ($i = 0; $i < $chunkSize; $i++) {
            $row = fgetcsv($handle);
            if ($row === false) {
                return empty($chunk) ? false : $chunk;
            }
            $chunk[] = $row;
        }

        return $chunk;
    }

}
