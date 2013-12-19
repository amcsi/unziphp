<?php
/**
 * With this script you can uncompress .tar.gz or .zip files so you can upload files faster if you either only have FTP available or even no FTP available at all.
 * You can upload a compressed file or uncompress an already uploaded, compressed file.
 * @version 2 Chmodding 
 * @author Attila Szeremi
 **/ 
date_default_timezone_set('Europe/Budapest');
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);
$exec = null;
$doExtract = false;
$writeSuccess = false;
$dest = dirname(realpath(__FILE__)); // target directory. Defaults to directory of this script.
$deleteFileAfter = false;

$enabledStuff = array ();
$enabledStuff['exec'] =
    false === strpos(ini_get("disable_functions"), "exec");
$enabledStuff['zip'] = class_exists('ZipArchive', false);
$enabledStuff['targz'] = false;

if ($enabledStuff['exec']) {
    $command = "tar --version";
    $lastLine = exec($command, $output);
    if ($output) {
        $enabledStuff['targz'] = true;
    }
}

$showHtml = true;
if (getenv('REQUEST_METHOD') === 'POST') {
    if (
        false !== strpos(getenv('CONTENT_TYPE'), 'javascript') ||
        false !== strpos(getenv('CONTENT_TYPE'), 'json')
    ) {
        $post = json_decode(file_get_contents('php://input'));
    } else {
        $post = $_POST;
    }

    if (!empty($post['fileBase64'])) {
        $doExtract = true;
        $deleteFileAfter = true;
        $filename = tempnam(sys_get_temp_dir(), 'unzi');
        file_put_contents($filename, base64_decode($post['fileBase64']));
    }
    if (!empty($post['upload'])) {
        $doExtract = true;
        $deleteFileAfter = true;
        $filename = $_FILES['file']['tmp_name'];
    }
    else if (!empty($post['fromLocal'])) {
        $filename = $post['filename'];
        if (file_exists($filename)) {;
        $doExtract = true;
        }
    }
}
if ($doExtract) {
    $typeFound = false;
    if (file_exists($filename)) {
        try {
            if ($enabledStuff['zip']) {
                $zip = new ZipArchive;
                $res = $zip->open($filename);
                if (true === $res) {
                    $success = $zip->extractTo($dest);
                    $writeSuccess = $success;
                    $chmodFiles = filter_input(INPUT_POST, 'chmodFiles');
                    $chmodDirs = filter_input(INPUT_POST, 'chmodDirs');
                    if (($chmodFiles or $chmodDirs)) {
                        $fileList = array ();
                        for ($i = 0; $i < $zip->numFiles; $i++) {
                            $stat = $zip->statIndex( $i );
                            $name = $stat['name'];
                            $fileList[] = $name;
                        }
                        foreach ($fileList as $file) {
                            $file = "$dest/$file";
                            if (is_file($file) and $chmodFiles) {
                                chmod($file, intval($chmodFiles, 8));
                            }
                            else if (is_dir($file) and $chmodDirs) {
                                chmod($file, intval($chmodDirs, 8));
                            }
                        }          
                    }
                    if ($deleteFileAfter) {
                        unlink($filename);
                    }
                    $zip->close();
                    $typeFound = true;
                }
                else if (ZIPARCHIVE::ER_NOZIP == $res) {

                }
                else {
                    trigger_error("ZipArchive error: $res.");        
                }
            }
        }
        catch (Exception $e) {

        }
        if (!$typeFound) {
            $command = "tar -xzvf \"$filename\" -C \"$dest\"";
            $success = exec(escapeshellcmd($command), $exec, $retVar);
            if ($success && 0 === $retVar) {
                $writeSuccess = true;
                $chmodFiles = filter_input(INPUT_POST, 'chmodFiles');
                $chmodDirs = filter_input(INPUT_POST, 'chmodDirs');
                if (($chmodFiles or $chmodDirs)) {
                    $fileList = explode("\n", $command);
                    foreach ($fileList as $file) {
                        if (is_file($file) and $chmodFiles) {
                            exec("chmod $chmodFiles $file");
                        }
                        else if ($chmodDirs) {
                            exec("chmod $chmodDirs $file");
                        }
                    }
                }
                if ($deleteFileAfter) {
                    unlink($filename);
                }
            }
            else {
                trigger_error("Couldn't uncompress the tar.gz.");
            }

        }
    }
}
$tgzFiles = array();
$files = new DirectoryIterator(dirname(__FILE__));
foreach ($files as $file) {
    if (!$file->isDot() && $file->isFile()) {
        $ext = pathinfo($file->getPathname(), PATHINFO_EXTENSION);
        if (in_array($ext, array('zip', 'gz', 'tgz'))) {
            $tgzFile = array ();
            $tgzFile['pathname'] = realpath($file->getPathname());
            $tgzFile['pathnameEsc'] = htmlspecialchars($tgzFile['pathname'], ENT_QUOTES, 'UTF-8');
            $tgzFile['basename'] = $file->getBasename();
            $tgzFile['basenameEsc'] = htmlspecialchars($tgzFile['basename'], ENT_QUOTES, 'UTF-8');
            $tgzFiles[] = $tgzFile;
        }
    }
}
if (!empty($post['accept'])) {
    if ('json' == $post['accept']) {
        $arr = array();
        $arr['version'] = '1.1.0';
        $arr['success'] = $writeSuccess;
        $arr['data'] = array();
        if ($exec) {
            $arr['data']['command'] = join("\n", $exec);
            $arr['data']['result'] = join("\n", $exec);
        }
        header('Content-Type: application/json');
        echo json_encode($exec);
        exit;
    }
}
?>
<!doctype html>
<html>
  <head>
  <meta charset="utf-8">
  <meta name="author" content="Attila Szeremi">
  <title>Zip extractor 1.1.0</title>
  </head>
  <body>
  <?php if ($writeSuccess): ?>
<div>
<pre>Uncompression was successful. Filename was: <strong><?php echo $filename ?></strong></pre>
</div>
  <?php endif; ?>
  <?php if ($exec): ?>
<div>
<p>Command:</p>
<pre><?php echo $command; ?></pre> 
<p>Result:</p>
<pre>
<?php echo join("\n", $exec); ?>
</pre>
<?php endif; ?>
</div>
<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>" enctype="multipart/form-data">
<div>Upload a zip or tar.gz file<input type="file" name="file"></div>
<div>tar.gz support
<?php if ($enabledStuff['targz']): ?>
<span style="color: green">YES</span>
<?php else: ?>
<span style="color: red">NO</span>
<?php endif ?>
</div>
<div>zip support
<?php if ($enabledStuff['zip']): ?>
<span style="color: green">YES</span>
<?php else: ?>
<span style="color: red">NO</span>
<?php endif ?>
</div>
<div><input id="unpackCheckbox" type="checkbox" checked="checked" name="unpack" readonly="readonly"> <label for="unpackCheckbox">Uncompress after upload</label></div>
<div>Chmod files (e.g. 666, 644): <input type="text" name="chmodFiles"></div>
<div>Chmod directories (e.g. 777, 755): <input type="text" name="chmodDirs"></div>
<div><button type="submit" name="upload" value="1">Upload</button></div>
</form>
<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
<div>
    <select name="filename">
        <option value="0">Choose an uploaded file</option>
        <?php foreach ($tgzFiles as $tgzFile): ?>
        <option value="<?php echo $tgzFile['pathnameEsc']; ?>"><?php echo $tgzFile['basenameEsc']; ?></option>
        <?php endforeach; ?>
    </select>
</div>
<div>Chmod files (e.g. 666, 644): <input type="text" name="chmodFiles"></div>
<div>Chmod directories (e.g. 777, 755): <input type="text" name="chmodDirs"></div>
<div><button type="submit" name="fromLocal" value="1">Uncompress already uploaded archive</button></div>
</form>
  </body>
</html>

