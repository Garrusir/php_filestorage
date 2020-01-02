<?php
    const MAIN_PAGE_URI = '/labs/labka/php_filestorage/';
    function kama_parse_csv_file( $file_path, $file_encodings = ['cp1251','UTF-8'], $col_delimiter = '', $row_delimiter = "" ){
      // echo "file_path: $file_path <br>";
      if( ! file_exists($file_path) ) {
        echo "not exist";
        return false;
      }

      $cont = trim( file_get_contents( $file_path ) );
      $encoded_cont = mb_convert_encoding( $cont, 'UTF-8', mb_detect_encoding($cont, $file_encodings) );

      unset( $cont );

    // определим разделитель
      if( ! $row_delimiter ){
        $row_delimiter = "\r\n";
        if( false === strpos($encoded_cont, "\r\n") )
          $row_delimiter = "\n";
      }

      $lines = explode( $row_delimiter, trim($encoded_cont) );
      $lines = array_filter( $lines );
      $lines = array_map( 'trim', $lines );

    // авто-определим разделитель из двух возможных: ';' или ','. 
    // для расчета берем не больше 30 строк
      if( ! $col_delimiter ){
        $lines10 = array_slice( $lines, 0, 30 );

      // если в строке нет одного из разделителей, то значит другой точно он...
        foreach( $lines10 as $line ){
          if( ! strpos( $line, ',') ) $col_delimiter = ';';
          if( ! strpos( $line, ';') ) $col_delimiter = ',';

          if( $col_delimiter ) break;
      }

      // если первый способ не дал результатов, то погружаемся в задачу и считаем кол разделителей в каждой строке.
      // где больше одинаковых количеств найденного разделителя, тот и разделитель...
        if( ! $col_delimiter ){
          $delim_counts = array( ';'=>array(), ','=>array() );
          foreach( $lines10 as $line ){
            $delim_counts[','][] = substr_count( $line, ',' );
            $delim_counts[';'][] = substr_count( $line, ';' );
          }

          $delim_counts = array_map( 'array_filter', $delim_counts ); // уберем нули
          // кол-во одинаковых значений массива - это потенциальный разделитель
          $delim_counts = array_map( 'array_count_values', $delim_counts );

          $delim_counts = array_map( 'max', $delim_counts ); // берем только макс. значения вхождений

          if( $delim_counts[';'] === $delim_counts[','] )
            return array('Не удалось определить разделитель колонок.');

          $col_delimiter = array_search( max($delim_counts), $delim_counts );
      }

    }
    $data = [];
    foreach( $lines as $key => $line ){
      $data[] = str_getcsv( $line, $col_delimiter ); // linedata
      unset( $lines[$key] );
    }

      return $data;
  }

  function in_list($list, $field) {
    foreach ($list as $index => $user ) {
      if ( $user[0] === $field) {
        return $index;
      }
    }
    return false;
  }

  function go_to_main($uri) {
    header('Location:'.$uri);
  }

  session_start();
  if (isset($_GET['logout']) && isset($_SESSION['userid'])) {
    unset($_SESSION['userid']);
    session_destroy();
  }
  if (isset($_SESSION['userid'])) {
    go_to_main(MAIN_PAGE_URI);
  }

  if (!empty($_POST)&&isset($_POST['send'])){
    $login = $_POST['login'];
    $password = $_POST['password'];
    $data = kama_parse_csv_file( 'users.csv' );
    $error = null;

    $index = in_list($data, $login);
    if (is_numeric($index)) {
      if ($data[$index][1] == $password) {
        $_SESSION['userid'] = $data[$index][2];
        go_to_main(MAIN_PAGE_URI);
      } else {
        $error = "Неверный пароль";
      }
    } else {
      $error = 'Пользователя с таким именем не существует';
    }
  }
  ?>
<!DOCTYPE html>
<html>
<head>
  <title>Луткова</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  <link rel="stylesheet" href="fonts/css/all.css">
</head>
<body>
  <div class="d-flex justify-content-center">
        <form method="POST">
          <div class="form-group mt-5">
            <label for="exampleInputEmail1">Login</label>
            <input type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Enter login" name="login" required>
            <small id="emailHelp" class="form-text text-muted">We'll never share your login with anyone else.</small>
          </div>
          <div class="form-group">
            <label for="exampleInputPassword1">Password</label>
            <input type="password" class="form-control" id="exampleInputPassword1" placeholder="Password" name="password" required>
          </div>
          <button type="submit" class="btn btn-primary" name="send">Submit</button>
        </form>
  </div>
        <?php
          if (isset($error) && $error) {
            echo "
            <div class='container mt-5'>
              <div class='alert alert-danger' role='alert'>Ошибка: $error </div>
            </div>
            ";
          }
        ?>
</body>
</html>