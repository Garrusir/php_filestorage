<?php
  session_start();
  $error = false;
  $login_page = $_SERVER['REQUEST_URI']."login";
  if (!isset($_SESSION['userid'])) {
    header('Location:'.$login_page);
  }

  if (isset($_GET['name'])) {
    $filename = $_GET['name'];
    $inList = false;
    $isAllow = false;
    // Читаем список файлов
    $row = 1;
    $handle = fopen("files.csv", "r");
    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
      $num = count($data);
      $row++;
      if ($data[0] == $filename) {
        $inList = true;
        if ($data[1] == $_SESSION['userid']) {
          $isAllow = true;
        }

      }
      // print_r($data);
      // echo "userid:".$data[1];
      // for ($c=0; $c < $num; $c++) {
      //     echo "file: ".$data[$c] . "<br />\n";
      // }
    }
    fclose($handle);
    if (!$isAllow) $error = "Нет прав доступа";
    if (!$inList) $error = "Файл не найден";

    if (!$error) {
      // echo "reading";
    // Читаем содержимое 
    $text = file_get_contents($filename); 
    // Переводим содержимое в видимую форму 
    $text = htmlspecialchars($text); 
    // Выводим содержимое файла 
    echo $text;
    }
  } else {
    $error = "Имя файла не указано";
  }

  if ($error) {
    echo "<h3>$error</h3>";
  }

?>