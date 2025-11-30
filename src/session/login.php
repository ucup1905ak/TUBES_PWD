<?php //session router semacam itu :)
include __DIR__ . '/validate.php';

  session_start();


  if(isset($_POST['username'])){ //route ke dash
    if(isValid($_POST['username'],$_POST['password'])){
      $_SESSION['username'] = $_POST['username'];
      $_SESSION['password'] = $_POST['password'];
      exit;
    }
  }

exit;
/*

                <form id="loginForm" action="login.php" method="post">

                    <div class="input-group">
                        <label>Username</label>
                        <input type="text" id="username" placeholder="Enter your username" />
                        <div id="errorBox" class="input-error"></div>
                    </div>

                    <div class="input-group">
                        <label>Password</label>
                        <input type="password" id="password" placeholder="Enter your password" />
                        <div id="errorPassword" class="input-error"></div>
                    </div>

                    <div class="options">
                        <div class="check">
                            <input type="checkbox" id="stay" />
                            <span>Stay logged in</span>
                        </div>

                        <a href="#" class="forgot">Forgot Password?</a>
                    </div>

                    <button type="button" class="btn-login" onclick="login()">SIGN IN</button>

                </form>
*/