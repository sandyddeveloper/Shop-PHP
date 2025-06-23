<?php
require_once __DIR__ . "/../server/db.php";
require_once __DIR__ . "/../comp/functions.php";

if(isset($_GET['email']) && !empty($_GET['email']) AND isset($_GET['hash']) && !empty($_GET['hash'])){


        // Verify data
        $email = mysqli_real_escape_string($conn, $_GET['email']);
        $hash = mysqli_real_escape_string($conn, $_GET['hash']);

        $result = mysqli_query($conn,"SELECT * FROM users WHERE `email`='$email';");
        $row = mysqli_fetch_array($result);
        if(mysqli_num_rows($result) > 0){

            $myArray = explode(',', $row['hash']);
            $DBhash = $myArray[0];
            $DBtime = $myArray[1];


            if($DBhash == $hash){

                if(time() < $DBtime){

                        $length = 10;
                        $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()';
                        $pass = '';
                        $max = mb_strlen($keyspace, '8bit') - 1;
                        for ($i = 0; $i < $length; ++$i) {
                            $pass .= $keyspace[random_int(0, $max)];
                        }

                        $pass;
                    

$website_logo = "";
$website_base = "https://poland.fo/";
$shop_name = "cap.fo";

                    $username = $row['username'];

                    $hashed_password = password_hash($pass, PASSWORD_DEFAULT); 
                    
                    $sql = "UPDATE `users` SET `password`='$hashed_password' WHERE `email`='$email'";
                    if ($stmt = $conn->prepare($sql)) {
                        if ($stmt->execute()) {
                            $sending_email = 'no-reply@poland.fo';

                            $to  = $email; 
                                $subject = 'Password Reset | Success'; 
                                       $message = <<<HTML

<!doctype html>
<html lang="en-US">

<head>
  <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
  <style type="text/css">
    a:hover {text-decoration: none !important;}
    :focus {outline: none;border: 0;}
  </style>
</head>

<body marginheight="0" topmargin="0" marginwidth="0" style="margin: 0px; background-color: #f2f3f8;" bgcolor="#eaeeef"
  leftmargin="0">
  <!--100% body table-->
  <table cellspacing="0" border="0" cellpadding="0" width="100%" bgcolor="#f2f3f8"
    style="@import url(https://fonts.googleapis.com/css?family=Rubik:300,400,500,700|Open+Sans:300,400,600,700); font-family: 'Open Sans', sans-serif;">
    <tr>
      <td>
        <table style="background-color: #f2f3f8; max-width:670px; margin:0 auto;" width="100%" border="0" align="center"
          cellpadding="0" cellspacing="0">
          <tr>
            <td style="height:80px;">&nbsp;</td>
          </tr>
          <tr>
           <td style="text-align:center;">
                            <a href="$website_base" title="logo" target="_blank">
                                <img height="120" width="200" src="$website_logo" title="logo" alt="logo">
                            </a>
                        </td>
          </tr>
          <tr>
            <td height="20px;">&nbsp;</td>
          </tr>
          <tr>
            <td>
              <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0"
                style="max-width:600px; background:#fff; border-radius:3px; text-align:left;-webkit-box-shadow:0 6px 18px 0 rgba(0,0,0,.06);-moz-box-shadow:0 6px 18px 0 rgba(0,0,0,.06);box-shadow:0 6px 18px 0 rgba(0,0,0,.06);">
                <tr>
                  <td style="padding:40px;">
                    <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
                      <tr>
                        <td>
                          <h1 style="color: #1e1e2d; font-weight: 500; margin: 0; font-size: 32px;font-family:'Rubik',sans-serif;" align="center">Hi $username</h1>
                          <p style="font-size:15px; color:#455056; line-height:24px; margin:8px 0 30px;" align="center">Here is your New Password</p>
                        </td>
                      </tr>
                            <tr
                              style="border-bottom:1px solid #ebebeb; margin-bottom:26px; padding-bottom:29px; display:block;">
                              <td width="84">
                            
                              </td>
                              <td style="vertical-align:top;">
                                <h3
                                  style="color: #4d4d4d; font-size: 20px; font-weight: 400; line-height: 30px; margin-bottom: 3px; margin-top:0;">
                                  <strong>UserName :</strong> 
                                <span style="color:#737373; font-size:14px;">$username</span>
                              </td>
                            </tr>
                            <tr style="display:block;">
                              <td width="84">
                              </td>
                              <td style="vertical-align:top;">
                                <h3
                                  style="color: #4d4d4d; font-size: 20px; font-weight: 400; line-height: 30px; margin-bottom: 3px; margin-top:0;">
                                  <strong>Password :</strong>
                                <span style="color:#737373; font-size:14px;"><spoiler>$pass</spoiler></span>
                              </td>
                            </tr>
                            <tr style="display: block;">
                            <td width="84"></td>
                            <td style="vertical-align: top; text-align: center;" align="center">
                            <a align="center" href="https://poland.fo/auth/login.php"
                                   style="background:#20e277; text-decoration:none !important; display:inline-block; font-weight:500; margin-top:24px; color:#fff; text-transform:uppercase; font-size:14px; padding:10px 24px; display:inline-block; border-radius:50px;">Click Here to Login</a>
                            </td>
                        </tr>

                            
                              </table>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td style="height:25px;">&nbsp;</td>
          </tr>
          <tr>
            <td style="height:80px;">&nbsp;</td>
          </tr>
         
        </table>
      </td>
    </tr>
                    
  </table>
  <!--/100% body table-->
</body>

</html>
HTML;
                                
                                $headers = 'MIME-Version: 1.0' . "\r\n";
                                $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
                                $headers .= 'From: ' . $sending_email . "\r\n";
                                $headers .= 'CC: ' . $sending_email . "\r\n";

; // Our message above including the link
										
					mail($to, $subject, $message, $headers); // Send our email

                        $success = "Please check your email for your password.";
            $success = urlencode($success);
            header("location: reset.php?success=".$success);
                        }else {
                            echo "Error";
                        }
                        $stmt->close();
                    }


                }else{
                    die('Verification Error Failed Successfully');
                }

            }else{
                die('Verification Error Failed Successfully');
            }



        }else{
            die('Verification Error Failed Successfully');
        }

    

}else{
    // Invalid approach
}