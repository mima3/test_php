<?php

function makeData(int $n): array {
    $rows = [];
    for ($i = 0; $i < $n; $i++) {
        $text = str_repeat("item_" . $i . "_", 20);
        $rows[] = [
            'id' => $i,
            'name' => md5($text),
            'value' => random_int(1, 100000),
            'text' => $text,
        ];
    }
    return $rows;
}

function sortData(array $rows): array {
    usort($rows, function ($a, $b) {
        return $a['value'] <=> $b['value'];
    });
    return $rows;
}

function transformData(array $rows): array {
    $out = [];
    foreach ($rows as $row) {
        $score = 0;
        for ($i = 0; $i < 200; $i++) {
            $score += strlen(sha1($row['text'] . $i));
        }
        $row['score'] = $score;
        $out[] = $row;
    }
    return $out;
}

function searchData(array $rows): array {
    $result = [];
    foreach ($rows as $row) {
        if (preg_match('/a[0-9]/', $row['name'])) {
            $result[] = $row;
        }
    }
    return $result;
}

function encodeDecode(array $rows): array {
    $json = json_encode($rows);
    return json_decode($json, true);
}

$start = hrtime(true);

$data = makeData(8000);
$data = sortData($data);
$data = transformData($data);
$data = searchData($data);
$data = encodeDecode($data);

$end = hrtime(true);

echo "count=" . count($data) . PHP_EOL;
echo "total ms=" . (($end - $start) / 1e6) . PHP_EOL;