<?php
//author: brendanmharris@gmail.com
//last mod: 07/09/2023
//purpose: Implements 3 methods of filesize upload checking (html/js/php)

ini_set('display_errors', true);
$max_file_size_kb = 150;
$max_file_size = $max_file_size_kb * 1024;
$num_files = 5;
$valid_exts = array('gif', 'jpg', 'png');
$valid_mimes = array('image/jpeg', 'image/png', 'image/gif');
$upload_path = 'C:\xampp\htdocs\une\cosc260\misc\upload\\';   //absolute path
$upload_dir = 'upload';                                       //relative url dir
$errs = [];
$script_name_elems = explode('/', $_SERVER['SCRIPT_NAME']);
$this_script = $script_name_elems[count($script_name_elems) - 1];

if (count($_FILES) > 0) {
  $i = 1;
  foreach ($_FILES as $key => $file_data) {
    if ($file_data['error'] == 0) {
      //file i upload ok. Check for upload errors 
      $file_name_elems = explode('.', $file_data['name']);
      $file_ext = $file_name_elems[count($file_name_elems) - 1];
      if (in_array($file_ext, $valid_exts) 
          && in_array($file_data['type'], $valid_mimes)) {
        if ($file_data['size'] <= $max_file_size) {
          $file_name = uniqid().'.'.$file_ext;
          $file_path = $upload_path.$file_name;
          if (!file_exists($file_path)
              && move_uploaded_file($file_data['tmp_name'], $file_path)) {
            $errs[] = 'file '.$i.' upload ok: '
              .'<a href="'.$upload_dir.'/'.$file_name.'">'.$upload_dir.'/'.$file_name.'</a>';
          } else {
            $errs[] = 'file '.$i.' copy failed';
          }
        } else {
          $errs[] = 'file '.$i.' too large';
        }
      } else {
        $errs[] = 'file '.$i.' file type not allowed';
      }
    } else {
      //set file i upload error for later display
      switch ($file_data['error']) {
        case UPLOAD_ERR_INI_SIZE:
          $errs[] = 'file '.$i.' exceeds php.ini upload_max_filesize';
          break;
        case UPLOAD_ERR_PARTIAL:
          $errs[] = 'file '.$i.' was only partially uploaded';
          break;
        case UPLOAD_ERR_NO_FILE:
          $errs[] = 'file '.$i.' no file to upload';
          break;
        case UPLOAD_ERR_NO_TMP_DIR:
          $errs[] =  'file '.$i.' couldn\'t find php tmp folder';
          break;
        case UPLOAD_ERR_CANT_WRITE:
          $errs[] =  'file '.$i.' couldn\'t write to disk';
          break;
        case UPLOAD_ERR_EXTENSION:
          $errs[] =  'file '.$i.' was stopped by a php extension';
          break;
        default:
          $errs[] =  'file '.$i.' undefined error code'.$file_data['error'];
          break;
      }
    }
    $i++;
  }
}

?>
<!DOCTYPE html>
<html>
<head>
<title><?php echo $this_script; ?></title>
<script>

window.addEventListener('load', function(e) {
  document.getElementById('file_form').addEventListener('submit', function(e) {
    event.preventDefault();
    let max_size = document.getElementById('max_file_size').value;  //in bytes
    let file = null;
    let file_size = 0;
    let all_good = false;
    /* check filesize for each 'browse...' form input. Alert if too large */
    for (let i = 1; i <= <?php echo $num_files ?>; i++) {
      file = document.getElementById('file' + i);
      if (file.files && file.files.length == 1) {
        file_size = file.files[0].size;
        if (file_size > max_size) {
          alert('file ' + i + ' must be less than ' + (max_size/1024) + ' kilobytes');
          all_good = false;
          break;
        } else all_good = true;
      }
    }
    if (all_good) document.getElementById('file_form').submit();
  });
});

</script>
</head>
<body>
<pre>
<form id='file_form' enctype='multipart/form-data' method='post' action='<?php echo $this_script; ?>'>
<input id='max_file_size' type='hidden' name='max_file_size' value='<?php echo $max_file_size; ?>' /><!-- units in bytes -->

<?php
foreach ($errs as $j => $err) {
  echo $err."\n";
}
?>

max file size: <?php echo $max_file_size/1024; ?> kilobytes

allowed file types: 
<?php foreach ($valid_exts as $k => $ex) { echo $ex."\n"; } ?>

<?php
for ($n = 1; $n <= $num_files; $n++) {
  echo "file $n: <input type='file' name='file$n' id='file$n' />\n\n";
}
?>
<input id='submit_button' type='submit' /> <input type='reset' /> 
</form>
</pre>
  
</body>
</html>