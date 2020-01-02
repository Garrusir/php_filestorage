<?php
  function saveFile() {

  }

  function delDir($dir) {
    $files = array_diff(scandir($dir), ['.','..']);
    foreach ($files as $file) {
        (is_dir($dir.'/'.$file)) ? delDir($dir.'/'.$file) : unlink($dir.'/'.$file);
    }
    return rmdir($dir);
  }

  function showTree($folder, $space) {
    if (!is_dir($folder)) { // если нет папки
          mkdir($folder, 0700, true); // созаем
    };
    /* Получаем полный список файлов и каталогов внутри $folder */
    $files = scandir($folder);
    foreach($files as $file) {
      /* Отбрасываем текущий и родительский каталог */
      if (($file == '.') || ($file == '..')) continue;
      $f0 = $folder.'/'.$file; //Получаем полный путь к файлу
      /* Если это директория */
      if (is_dir($f0)) {
        /* Выводим, делая заданный отступ, название директории */
        echo "<div class='tree__item tree__dir'>".$space."<i class='fas fa-folder-open'></i>
".$file."</div>";
        /* С помощью рекурсии выводим содержимое полученной директории */
        showTree($f0, $space.'&nbsp;&nbsp;&nbsp;&nbsp;');
      }
      /* Если это файл, то просто выводим название файла */
      else echo "<a target='_blank' href='viewer.php?name=$f0' class='tree__item tree__file'>
                  ".$space."<i class='fas fa-file-alt'></i>
                  <div>".$file."</div>
                </a>";
    }
  }

  function getFileName($folder) {
    $file_name = 1;
    // $isUnic = false;
    $files = scandir($folder);
    // do {
      foreach($files as $file) {
          if (($file == '.') || ($file == '..')) continue;
          $f0 = $folder.'/'.$file;
          if (is_dir($f0)) continue;
          $file_name++;
      }
    // } while ( !$isUnic );

    return $file_name;
  }

  function getPath() {
    $path_dir = $_POST['path'];
    if (strripos($path_dir, '/') !== strlen($path_dir) - 1) {
      $path_dir = $path_dir."/";
    }
    if (stripos($path_dir, '/') !== 0) {
     $path_dir = "/".$path_dir;
    } 
    return 'filestorage'.$path_dir;
  }

  function getExtension( $filename ) {
    $array = explode('.', $filename );
    return end($array);
  }

  session_start();

  $login_page = $_SERVER['REQUEST_URI']."login";
  if (!isset($_SESSION['userid'])) {
    header('Location:'.$login_page);
  }

?>
<!DOCTYPE html>
<html>
<head>
  <title>Луткова</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  <link rel="stylesheet" href="fonts/css/all.css">
  <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
  <nav class="navbar navbar-dark bg-dark">
    <a class="navbar-brand" href="#">File Storage</a>
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" href="<?php echo $login_page."?logout" ?>">Log out</a>
      </li>
    </ul>
  </nav>
      <!-- <div class="nav-wrapper">
        <a href="#" class="brand-logo">Каталог файлов</a>
        <ul id="nav-mobile" class="right hide-on-med-and-down">
          <li><a href="<?php echo $login_page."?logout" ?>">Выход</a></li>
        </ul>
      </div> -->
  <div class="container">
    <div class="tree mt-3">
      <?php
        /* Запускаем функцию для текущего каталога */
        showTree("filestorage", "");
      ?>
    </div>
    <?php
  if (isset($_FILES['file']) && isset($_POST['send'])) {
      $path = getPath(); // путь прописанный пользователем в форме
      if ($_FILES['file']['name'] == '') {// если файл не загружен, значит нужно удалить всю папку
        if (is_dir($path)) { // если папка существует
          $new_csv = []; // создаем массив для нового csv, без удаленной папки
          $handle = fopen("files.csv","r"); // открываем файл
          while (($data = fgetcsv($handle, 1024, ";")) != false) { // построчно смотрим
            if (!stristr($data[0],$path)) { // если в первой ячейке  не содержится путь удаляемой папки
              $new_csv[] = $data; // добавляем такую папку в новый csv
            }
          }
          fclose($handle);

          $handle = fopen("files.csv", "w"); // открываем файл для записи
          foreach ($new_csv as $key => $value) { 
              fputcsv($handle, $value, ';'); // записываем все новые поля в csv
          }
          fclose($handle);

          delDir($path); // удаляем папку
        } else {
          echo "<br> error";
        }
      } else {// если файл загружен
        if (!is_dir($path)) { // если нет папки
          mkdir($path, 0700, true); // созаем
        };

        if ($_FILES['file']['name']) {
          $mime = getExtension($_FILES['file']['name']);// получаем расшширение загруженного файла
        }

        $filename = getFileName($path);// получаем имя загруженного файла
        $file = $path.$filename.".".$mime;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $file)) { // переносим файл
          $fields = array( trim($file),  trim($_SESSION['userid'])); // создаем поля для csv
          $hand = fopen("files.csv","a"); // откываем csv
          fputcsv($hand, $fields, ';'); // записываем
          fclose($hand);
        };
      }
    header("Location: ".$_SERVER["REQUEST_URI"]);
    exit;
  }
    ?>
    <div class="mt-3">
      <form method="POST" enctype='multipart/form-data'>
        <div class="form-group">
          <label for="exampleInputEmail1">Path</label>
          <input type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Enter path" name="path">
          <small id="emailHelp" class="form-text text-muted">Path to dir</small>
        </div>
        <div class="form-group">
          <label for="exampleFormControlFile1">Example file input</label>
          <input type="file" class="form-control-file" id="exampleFormControlFile1" name="file">
        </div>
        <button type="submit" class="btn btn-primary" name="send">Submit</button>
      </form>
        <!-- <form class="col s12" method="POST" enctype='multipart/form-data'>
          <div class="row">
            <h4 class="col s12">Загрузка файла</h4>
          </div>
          <div class="row">
            <div class="input-field col s12">
              <input id="first_name" type="text" class="validate" name="path">
              <label for="first_name">Path</label>
            </div>
          </div>
          <div class="row">
            <div class="col s12">
             <div class = "file-field input-field">
                <div class = "btn">
                   <span>Browse</span>
                   <input name="file" type = "file" />
                </div>
                <div class = "file-path-wrapper">
                   <input class = "file-path validate" type = "text"
                      placeholder = "Upload file" />
                </div>
             </div>
            </div>
          </div>
          <div class="row">
            <div class="col s12">
                <button class="btn waves-effect waves-light col s12" type="submit" name="send">Загрузить
                </button>
            </div>
          </div>
        </form> -->
    </div>
  </div>
</body>
</html>

