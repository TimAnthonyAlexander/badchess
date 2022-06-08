<?php
namespace chess;


class ML {
    private const FILE = 'learnt.json';

    private array $data = [];
    private array $firstData = [];

    public function __construct() {
        if (!file_exists(self::FILE)) {
            // Create file
            $this->save();
        }
        $this->firstData = $this->data = json_decode(file_get_contents(self::FILE), true, 512, JSON_THROW_ON_ERROR);
    }

    public function get(string $key) {
        return $this->data[$key] ?? null;
    }

    public function set(string $key, $value) {
        $this->data[$key] = $value;
    }

    public function save() {
        asort($this->data);
        @file_put_contents(self::FILE, json_encode($this->data, JSON_THROW_ON_ERROR));
    }

    public function __destruct() {
        if ($this->data !== $this->firstData){
            $this->save();
        }
    }
}
