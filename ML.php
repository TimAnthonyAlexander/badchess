<?php
namespace chess;


class ML {
    private const FILE = 'learnt.json';

    private array $data = [];
    private array $firstData = [];

    public function __construct() {
        if (!file_exists(self::FILE)) {
            // Create file
            file_put_contents(self::FILE, json_encode([]));
        }
        $this->firstData = $this->data = json_decode(file_get_contents(self::FILE), true);
    }

    public function get(string $key) {
        return $this->data[$key] ?? null;
    }

    public function set(string $key, $value) {
        $this->data[$key] = $value;
    }

    public function save() {
        asort($this->data);
        file_put_contents(self::FILE, json_encode($this->data, JSON_PRETTY_PRINT));
    }

    public function __destruct() {
        if ($this->data !== $this->firstData){
            $this->save();
        }
    }
}
