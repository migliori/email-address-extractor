<?php
$eml_folders_val  = '';
$from_checked     = '';
$to_checked       = '';
$content_checked  = '';

if (isset($_POST['eml-folder'])) {
    function findEmlFiles($path)
    {
        $directory = new \RecursiveDirectoryIterator($path, \FilesystemIterator::FOLLOW_SYMLINKS);
        $filter = new \RecursiveCallbackFilterIterator($directory, function ($current) {
            // Skip hidden files and directories.
            if ($current->getFilename()[0] === '.') {
                return false;
            }
            return true;
        });
        $iterator = new \RecursiveIteratorIterator($filter);
        $files = array();
        foreach ($iterator as $info) {
            $files[] = $info->getPathname();
        }

        return $files;
    }

    $output = [
        'success' => false,
        'emails'  => [],
        'error'   => ''
    ];

    if (!isset($_POST['options'])) {
        $output['error'] = '<p class="alert alert-warning" role="alert"> You must choose at least one option. </p>';
    } else {
        $eml_folders_val = $_POST['eml-folder'];

        $options = ['from', 'to', 'content'];
        foreach ($options as $opt) {
            if (in_array($opt, $_POST['options'])) {
                ${$opt . '_checked'} = ' checked';
            }
        }

        $path      = rtrim($_POST['eml-folder'], '/') . '/';

        if (file_exists($path)) {
            $output['success'] = true;

            $eml_files = findEmlFiles($path);

            // From: emails
            if (in_array('from', $_POST['options'])) {
                foreach($eml_files as $eml) {
                    $eml_content = file_get_contents($eml);
                    preg_match_all('`^From:[^\n]+<([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,6})>`mi', $eml_content, $matches, PREG_PATTERN_ORDER);
                    for ($i = 0; $i < count($matches[1]); $i++) {
                        $output['emails'][] .= $matches[1][$i];
                    }
                }
            }

            // To: emails
            if (in_array('to', $_POST['options'])) {
                foreach($eml_files as $eml) {
                    $eml_content = file_get_contents($eml);
                    preg_match_all('`^To:[^\n]+<([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,6})>`mi', $eml_content, $matches, PREG_PATTERN_ORDER);
                    for ($i = 0; $i < count($matches[1]); $i++) {
                        $output['emails'][] .= $matches[1][$i];
                    }
                }
            }

            // emails content
            if (in_array('content', $_POST['options'])) {
                foreach($eml_files as $eml) {
                    $eml_content = file_get_contents($eml);
                    if (preg_match('/^[\s]*boundary="([a-zA-Z0-9_=-]+)"/m', $eml_content, $matches_0)) {
                        $boundary = $matches_0[1];
                        if (preg_match('`--' . $boundary . '(.*)--' . $boundary . '--`', str_replace("\n", '', $eml_content), $matches_1)) {
                            $message_content = $matches_1[1];
                            preg_match_all('`([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,6})`mi', $message_content, $matches_2, PREG_PATTERN_ORDER);
                    for ($i = 0; $i < count($matches_2[1]); $i++) {
                        $output['emails'][] .= $matches_2[1][$i];
                    }
                        }
                    }
                }
            }

            $output['emails'] = array_unique($output['emails']);
            natsort($output['emails']);
        } else {
            $output['error'] = '<p class="alert alert-danger" role="alert"> folder <em>' . $_POST['eml-folder'] . '</em> not found. </p>';
        }
    }
}
?>
<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">

    <!-- Ladda loading buttons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Ladda/1.0.6/ladda-themeless.min.css" integrity="sha512-EOY99TUZ7AClCNvbnvrhtMXDuWzxUBXV7SFovruHvYf2dbvRB5ya+jgDPk5bOyTtZDbqFH3PTuTWl/D7+7MGsA==" crossorigin="anonymous" />

    <style>textarea{font-size:0.75rem !important;}</style>

    <title>Email Address Extractor</title>
</head>

<body>

    <div class="container">
        <h1 class="font-weight-light text-center mt-4">Email Address Extractor</h1>
        <p class="lead text-center mb-5">Enter the path to the folder on your hard drive that contains your <em>.eml</em> files then launch the process.</p>
        <form id="email-extractor-form" action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="POST">
            <div class="form-group row">
                <label for="eml-folder" class="col-sm-2 col-form-label">Folder path</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="eml-folder" value="<?php echo $eml_folders_val; ?>" required>
                    <small class="form-text text-muted">ie: C:/Users/Me/Documents/Emails/</small>
                </div>
            </div>
            <div class="form-group row my-4">
                <div class="col-sm-10 offset-sm-2">
                    <div class="form-check mb-3">
                        <input name="options[]" class="form-check-input" type="checkbox" value="from" id="from" <?php echo $from_checked ?>>
                        <label class="form-check-label" for="from">
                            Extract <span class="badge badge-secondary">From:</span> emails
                        </label>
                    </div>
                    <div class="form-check mb-3">
                        <input name="options[]" class="form-check-input" type="checkbox" value="to" id="to" <?php echo $to_checked ?>>
                        <label class="form-check-label" for="to">
                            Extract <span class="badge badge-secondary">To:</span> emails
                        </label>
                    </div>
                    <div class="form-check mb-3">
                        <input name="options[]" class="form-check-input" type="checkbox" value="content" id="content" <?php echo $content_checked ?>>
                        <label class="form-check-label" for="content">
                            Extract emails from content
                        </label>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-10 text-center">
                    <button type="submit" class="btn btn-primary" data-style="expand-right">
                        Find email addresses
                    </button>
                </div>
            </div>
        </form>

        <hr class="my-4">

        <?php
    if (isset($_POST['eml-folder'])) {
        ?>
        <h2 class="font-weight-light text-center">Result</h2>
        <?php
        if ($output['success'] !== true) {
            echo $output['error'];
        } else {
            $emails_count = count($output['emails']);
            if ($emails_count < 1) {
                // if no email found
                ?>
        <p class="alert alert-warning" role="alert"> No email address found. </p>
        <?php
            } else {
                ?>
        <p>The extractor found <strong><?php echo $emails_count ?></strong> unique email address</p>
        <textarea name="extracted-emails" cols="30" rows="10" class="form-control small"><?php echo implode("\n", $output['emails']); ?></textarea>
        <?php
            } // end if
        } // end if
    } // end if
    ?>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Ladda/1.0.6/spin.min.js" integrity="sha512-FzwLmClLNd77zi/Ke+dYlawHiPBAWhk8FzA4pwFV2a6PIR7/VHDLZ0yKm/ekC38HzTc5lo8L8NM98zWNtCDdyg==" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Ladda/1.0.6/ladda.min.js" integrity="sha512-fK8kfclYYyRUN1KzdZLVJrAc+LmdsZYH+0Fp3TP4MPJzcLUk3FbQpfWSbL/uxh7cmqbuogJ75pMmL62SiNwWeg==" crossorigin="anonymous"></script>
    <script>
        Ladda.bind('button[type=submit]');
    </script>
</body>

</html>
