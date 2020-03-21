<?php

class LatestData
{
    private const FILE = 'latest-data.php';

    private function getFilePath(): string
    {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . self::FILE;
    }

    private function createFileIfNotExists(): void
    {
        if (!file_exists($this->getFilePath())) {
            $this->write([]);
        }
    }

    private function write(array $content): void
    {
        $arr = var_export($content, true);
        $code = <<<CODE
<?php
return $arr;
CODE;

        file_put_contents($this->getFilePath(), $code);
    }

    public function setLatestData(array $data): void
    {
        $this->createFileIfNotExists();
        $this->write($data);
    }

    public function getLatestData(): array
    {
        $this->createFileIfNotExists();
        return require($this->getFilePath());
    }
}