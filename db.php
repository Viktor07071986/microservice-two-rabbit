<?php

/* Требуется включить отчёт об ошибках для модуля mysqli, прежде чем пытаться установить соединение */
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$mysqli = mysqli_connect('localhost', 'root', '', 'rest_api_db_rabbit');

if (mysqli_connect_errno()) {
    throw new RuntimeException('ошибка соединения mysqli: ' . mysqli_connect_error());
}

/* Установите желаемую кодировку после установления соединения */
mysqli_set_charset($mysqli, 'utf8mb4');

if (mysqli_errno($mysqli)) {
    throw new RuntimeException('ошибка mysqli: ' . mysqli_error($mysqli));
}