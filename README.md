# fractal_zip

Version 0.2

benchmarks table

<table border="1">
<thead>
<tr>
<td></td>
<th scope="col">test</th>
<th scope="col"><a href="https://rarlab.com/">WinRAR</a></th>
<th scope="col"><a href="https://sourceforge.net/projects/freearc/">FreeArc</a></th>
<th scope="col"><a href="https://www.7-zip.org/">7-Zip</a></th>
<th scope="col">fractal_zip</th>
<th scope="col">comments</th>
</tr>
</thead>
<tbody>
<th rowspan="9" scope="rowgroup">from <a href="https://www.maximumcompression.com/data/files/index.html">maximumcompression.com</a></th>
<th scope="row">test_files35<br>rafale.bmp<br>4,149,414&nbsp;B</th>
<td>1,143,200&nbsp;B</td>
<td>784,950&nbsp;B</td>
<td>990,773&nbsp;B</td>
<td>990,925&nbsp;B</td>
<td></td>
<tr>

test 	        WinRAR 	      FreeArc 	  7-Zip 	    fractal_zip 	comments

test_files35
rafale.bmp
4,149,414 B 	1,143,200 B 	784,950 B 	990,773 B 	990,925 B
from maximumcompression.com

test_files49
phpinfo.html
106,762 B 	  20,691 B 	    20,123 B 	  19,779 B 	  19,407 B
fractal_zip was able to find a little more room for compression than the others; cool! (in 67.498s)

test_files29
showing_off.txt
1,277,926 B 	11,651 B 	    613 B 	    25,180 B 	  102 B!
This is a file with highly fractal data; in other words a highly unlikely file to produce naturally. Nevertheless it serves to illustrate that the fractal_zip approach is valid. This file has a fractal recursion level of 30, whereas the most highly compressed things we know of (example: DNA with fractal recursion level of 7) are much less fractal. (may take an infinite time to compress!)

test_files28
sf.bmp
9,498 B 	    1,195 B 	    1,217 B 	  1,179 B 	  1,192 B   No advantage to using fractal_zip. 7-zip wins for this single BMP. (in 3.484s)

test_files2 	796 B 	      496 B 	    378 B 	    345 B     These short strings (~100 B) are somewhat fractal and benefit from fractal_zip. (in 0.471s)
 	

general comments

fractal_zip was only tested on windows 8.
fractal_zip is currently very slow. Other compression code is clearly superior in speed (for the test files fractal_zip alternatives takes an insignificant amount of time ~1 second while fractal zip takes a significant amount of time). There are various reasons for this: it is unoptimized and it is written in PHP and it is doing more than the others.
fractal_zip is currently very basic. The only operation it uses is substring and more operations (translation, rotation, scaling, etc.) would surely add to its compressability.
If fractal_zip used freearc internally as well as 7-zip it would probably be better.
Currently, the file metadata (path, date modified, packed size, etc.) is wastefully encoded twice since fractal_zip does this and 7-zip does this independantly. So, currently, in order to compress more, fractal_zip has to overcome this extra obstacle.
It's funny how compression code is effectively fractal in its development (whether known or unknown to the developers) itself. freearc uses 7-zip and RAR while fractal_zip currently uses 7-zip which uses LZMA which uses...

Version 0.1

Currently the zips this code produces are only sometimes as good as other common zipping programs, or in very specific cases a tiny
bit better (mostly dues to lower format overhead). Were this code to be truely fractal, then it would be interesting.
