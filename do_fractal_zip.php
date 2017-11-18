<?php

// do_fractal_zip separated from fractal_zip so that other programs (fs) may also use it
// also notice that fractal_zip is at its most useful when content is the most duplicated so that AI generated programs are a great candidate for space-saving by fractal zipping

include('fractal_zip.php');

//$fractal_zip = new fractal_zip();
//$fractal_zip = new fractal_zip(1.1);
$fractal_zip = new fractal_zip(10, 20000);
//$fractal_zip->zip_folder('test_files');
//$fractal_zip->zip_folder('test_files14');
//$fractal_zip->zip_folder('test_files14', true); // debug so that the process can be examined
$fractal_zip->zip_folder('test_files8', true); // debug so that the process can be examined
//$fractal_zip->zip_folder('StartOrbz', true); // debug so that the process can be examined
//$fractal_zip->open_container('open_container_test2' . DIRECTORY_SEPARATOR . 'test_files2.fzc');
//$fractal_zip->open_container('open_container_test3' . DIRECTORY_SEPARATOR . 'test_files15.fzc');
//$fractal_zip->zip_folder('..' . DIRECTORY_SEPARATOR . 'zip' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'raw' . DIRECTORY_SEPARATOR . '6 integer series HTML files');

?>