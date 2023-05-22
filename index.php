<?php
error_reporting(E_ERROR);
if($_POST){
  if($_POST['exit']){
    setcookie('user', '', time()-60*60*24);
    setcookie('birth', '', time()-60*60*24);
    setcookie('avatar', '', time()-60*60*24);
    die();
  }

  if(empty($_POST['login']) || empty($_POST['password'])){
    echo json_encode([
      'error' => 'Both fields are required'
    ]);
    die();
  }

  $login = $_POST['login'];
  $db = @mysqli_connect('localhost', 'root', '', 'php-auth', 3306) or die('db connection error');
  mysqli_set_charset($db, 'utf8') or die('charset is not set');

  $user = mysqli_query($db, "select * from users where login = '".$login."'");
  if (mysqli_num_rows($user) > 0) {
    $user = mysqli_fetch_assoc($user);

    if(isset($user['timeout'])){
      $now = new DateTime(date('Y-m-d H:i:s'));
      $timeout = new DateTime($user['timeout']);
      $interval = $timeout->diff($now);

      if($interval->h < 1){
        echo json_encode([
          'error' => 'Your account was suspended for 1 hour. Try later'
        ]);
        die();
      } else {
        mysqli_query($db, "update users set timeout = null where login = '".$login."'");
        mysqli_query($db, "update users set attempts = 0 where login = '".$login."'");
      }
    }

    if($user['password'] === md5($_POST['password'])){
      $birthday = $user['birth'] ? date('F d, Y' ,strtotime($user['birth'])) : '';
      setcookie("user", $user['name'], time() + 60 * 60 * 24);
      setcookie("birth", $birthday, time() + 60 * 60 * 24);
      setcookie("avatar", $user['avatar'], time() + 60 * 60 * 24);

      mysqli_query($db, "update users set attempts = 0 where login = '".$login."'");

      echo json_encode([
        'name' => $user['name'],
        'avatar' => $user['avatar'],
        'birth' => $birthday
      ]);
    } else {
      if($user['attempts'] == 2){
        echo json_encode([
          'error' => 'Too many attempts. Your account has been suspended for 1 hour'
        ]);
        mysqli_query($db, "update users set timeout = '".date('Y-m-d H:i:s')."' where login = '".$login."'");
        die();
      }

      echo json_encode([
        'error' => 'Login and/or password is wrong'
      ]);
      $attempts_new = ++$user['attempts'];
      mysqli_query($db, "update users set attempts = '".$attempts_new."' where login = '".$login."'");
      die();
    }
  } else {
    echo json_encode([
      'error' => 'Login and/or password is wrong'
    ]);
    die();
  }
} else {
  echo '
    <!doctype html>
    <html lang="en">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
      <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;500;600;700&display=swap" rel="stylesheet">
      <link rel="stylesheet" href="styles.css">
      <title>Auth</title>
    </head>
    <body>
      <div class="wrapper">
        <div class="container">';

  if ($_COOKIE['user']){
    $html = '<div class="main_container">';
    if(isset($_COOKIE['avatar'])){
      $html .= '<div class = "avatar_block">
                  <img src="'.$_COOKIE['avatar'].'">
                </div>';
    }
    $html .= '<div>'.$_COOKIE['user'].'</div>';
    if(isset($_COOKIE['birth'])){
      $html .= '<div>Date of birth: '.$_COOKIE['birth'].'</div>';
    }
    $html .= '<input class="signout_button" type="submit" value="Sign out">';
    $html .= '</div>';

    echo $html;
  } else {
    echo '
      <div class="form_container">
        <form id="form">
          <div class="form_item">
            <input class="form_input_login" type="text" placeholder="Login">
          </div>
          <div class="form_item">
            <input class="form_input_password" type="password" placeholder="Password">
          </div>
          <input class="signin_button" type="submit" value="Sign in">
        </form> 
      </div>';
  }
  echo '
        </div>    
        </div>
        <script src="script.js"></script>
      </body>
    </html>';
}
