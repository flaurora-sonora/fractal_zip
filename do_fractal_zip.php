<?php

// do_fractal_zip separated from fractal_zip so that other programs (fs) may also use it
// also notice that fractal_zip is at its most useful when content is the most duplicated so that AI generated programs are great candidates for space-saving by fractal zipping

include('fractal_zip.php');

//$fractal_zip = new fractal_zip();
//$fractal_zip = new fractal_zip(1.1);
//$fractal_zip = new fractal_zip(10, 20000);
//$fractal_zip = new fractal_zip(10, 1000); // improvement factor is now sort of dynamic
$fractal_zip = new fractal_zip(140);
//$fractal_zip = new fractal_zip(1000);

//$fractal_zip->create_fractal_file('test_files21' . DS . '2Dfractal.txt', '<l9><0"35><0"35><35"35><0"35><35"35><70"35><35"35><70"35><70"35>', 'aaa<s15>aaa<s15>aaa<s15>aaa<s15>aaabbb<s15>bbb<s15>bbb<s15>bbb<s15>bbbccc<s15>ccc<s15>ccc<s15>ccc<s15>ccc');
//$fractal_zip->create_fractal_file('test_files21' . DS . '2Dfractal.txt', '<l9><0"35><0"35><35"35><0"35><35"35><70"35><35"35><70"35><70"35><0"35><0"35><35"35><0"35><35"35><70"35><35"35><70"35><70"35><0"35><0"35><35"35><0"35><35"35><70"35><35"35><70"35><70"35><0"35><0"35><35"35><0"35><35"35><70"35><35"35><70"35><70"35><0"35><0"35><35"35><0"35><35"35><70"35><35"35><70"35><70"35>', 'aaa<s15>aaa<s15>aaa<s15>aaa<s15>aaabbb<s15>bbb<s15>bbb<s15>bbb<s15>bbbccc<s15>ccc<s15>ccc<s15>ccc<s15>ccc'); // lengthen it up up to reach critical compressive mass
// what about nested 2-d fractal?
//$fractal_zip->create_fractal_file('test_files22' . DS . 'potentially_infinite_string.txt', 'a<12"17"4>aaaa', 'a<12"17>aaaabb<0"12>b<0"12>bb'); // recursion of 20 takes almost no time and recusion of 30 takes about 50 seconds. 51 seconds and 62% size for recursion of 16 when completely unoptimized and barely just gotten working
//$fractal_zip->create_fractal_file('test_files23' . DS . 'dehacking_fractal_substring.txt', 'aaaaa<20"25"30>aaaaaaaa', 'aaaaa<20"25>aaaaaaaabbbbbb<0"20>b<0"20>bbbbbb');
//$fractal_zip->create_fractal_file('test_files24' . DS . 'replace_operation.txt', '<0"25><rb"c><0"25></r><rb"d><0"25></r><rb"e><0"25></r><rb"f><0"25></r><rb"g><0"25></r>', 'aaaaaaaaaaaaaaaaaaaabbbaa'); // replace operation 'aaaza', 'bbbzb', 'ccczc', 'dddzd'
//$fractal_zip->create_fractal_file('test_files25' . DS . 'gradient_operation.txt', '<g4"1"w>', ''); // gradient
//$fractal_zip->create_fractal_file('test_files26' . DS . 'tuple_operation.txt', '<0"12*12>', 'abcdefghijkl'); // tuple. this is another parameter on how the balance of which substrings to work from versus which substrings to not even attempt working from since inevitably the substring of which we want to make a tuple can be neither the shortest nor the longest
//$fractal_zip->create_fractal_file('test_files27' . DS . 'scale_operation.txt', '<0"12s0.25><0"12s0.5><0"12s2><0"12s8>', 'aaabbccbbaaa'); // scale
// in combination with linear translation, a fractal could be drawn by these two operations. another set of operations could be chosen to to be able to draw all fractals; bringing up the question of whether we should choose a merely sufficient set (such as lossy compression: jpeg) or use a more than sufficient set
// rotation, translation, etc.
//$fractal_zip->create_fractal_file('test_files29' . DS . 'showing_off.txt', 'aaaaa<20"25"30>aaaaaaaa', 'aaaaa<20"25>aaaaaaaabbbbbb<0"20>b<0"20>bbbbbb');

//$fractal_zip->zip_folder('test_files23', true); // debug set to true so that the process can be examined
//$fractal_zip->zip_folder('test_files20', true); // kroz maps
//$fractal_zip->zip_folder('test_files28', true); // sf.bmp
//$fractal_zip->zip_folder('test_files29', true); // showing off
//$fractal_zip->zip_folder('test_files30', true); // DNA
//$fractal_zip->zip_folder('test_files32', true);

//$fractal_zip->zip_folder('test_files35', true); // rafale.bmp
//$fractal_zip->zip_folder('test_files36', true); // MSO97.DLL
//$fractal_zip->zip_folder('test_files37', true); // ohs.doc
//$fractal_zip->zip_folder('test_files38', true); // AcroRd32.exe
//$fractal_zip->zip_folder('test_files39', true); // vcfiu.hlp
//$fractal_zip->zip_folder('test_files40', true); // FP.LOG
//$fractal_zip->zip_folder('test_files41', true); // FlashMX.pdf
//$fractal_zip->zip_folder('test_files42', true); // english.dic
//$fractal_zip->zip_folder('test_files43', true); // world95.txt

$fractal_zip->zip_folder('test_files2', true);

//$fractal_zip->zip_folder('test_files19', true);

//$fractal_zip->open_container('open_container_test6' . DIRECTORY_SEPARATOR . 'test_files2.fzc');

?>