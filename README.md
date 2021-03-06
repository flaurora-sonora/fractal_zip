# fractal_zip

Version 0.2


<table border="1">
<caption>benchmarks table</caption>
<thead>
<tr>
<th scope="col">test</th>
<th scope="col"><a href="https://rarlab.com/">WinRAR</a></th>
<th scope="col"><a href="https://sourceforge.net/projects/freearc/">FreeArc</a></th>
<th scope="col"><a href="https://www.7-zip.org/">7-Zip</a></th>
<th scope="col">fractal_zip</th>
<th scope="col">comments</th>
</tr>
</thead>
<tbody>
<th scope="row">test_files35<br>rafale.bmp<br>4,149,414&nbsp;B</th>
<td>1,143,200&nbsp;B</td>
<td>784,950&nbsp;B</td>
<td>990,773&nbsp;B</td>
<td>990,925&nbsp;B</td>
<td>from <a href="https://www.maximumcompression.com/data/files/index.html">maximumcompression.com</a></td>
<tr>
<th scope="row">test_files49<br>phpinfo.html<br>106,762&nbsp;B</th>
<td>20,691&nbsp;B</td>
<td>20,123&nbsp;B</td>
<td>19,779&nbsp;B</td>
<td><span style="color: green;">19,407&nbsp;B</span><br><span style="color: red;">67.498s</span></td>
<td>fractal_zip was able to find a little more room for compression than the others; cool!</td>
</tr>
<tr>
<th scope="row">test_files29<br><a href="http://flaurora-sonora.000webhostapp.com/fractal_zip/test_files29/showing_off.txt">showing_off.txt</a><br>1,277,926&nbsp;B</th>
<td>11,651&nbsp;B</td>
<td>613&nbsp;B</td>
<td>25,180&nbsp;B</td>
<td><span style="color: green;">102&nbsp;B!<br><span style="color: red;">&infin;!</span></td>
<td>This is a file with highly fractal data; in other words a highly unlikely file to produce naturally. Nevertheless it serves to illustrate that the fractal_zip approach is valid. This file has a fractal recursion level of 30, whereas the most highly compressed things we know of (example: <abbr title="Deoxyribonucleic acid">DNA</abbr> with fractal recursion level of 7) are much less fractal.</td>
</tr>
<tr>
<th scope="row">test_files28<br><a href="http://flaurora-sonora.000webhostapp.com/fractal_zip/test_files28/sf.bmp">sf.bmp</a><br>9,498&nbsp;B</th>
<td>1,195&nbsp;B</td>
<td>1,217&nbsp;B</td>
<td>1,179&nbsp;B</td>
<td>1,192&nbsp;B<br>3.484s</td>
<td>No advantage to using fractal_zip. 7-zip wins for this single BMP.</td>
</tr>
<tr>
<th scope="row">test_files2</th>
<td>796&nbsp;B</td>
<td>496&nbsp;B</td>
<td>378&nbsp;B</td>
<td><span style="color: green;">345&nbsp;B</span><br>0.471s</td>
<td>These short strings (~100&nbsp;B) are somewhat fractal and benefit from fractal_zip.</td>
</tr>
</tbody>
</table>

general comments

<ul>
<li>fractal_zip was only tested on windows 8.</li>
<li>fractal_zip is currently very slow. Other compression code is clearly superior in speed (for the test files fractal_zip alternatives takes an insignificant amount of time ~1 second while fractal zip takes a significant amount of time). There are various reasons for this: it is unoptimized and it is written in PHP and it is doing more than the others.</li>
<li>fractal_zip is currently very basic. The only operation it uses is substring and more operations (translation, rotation, scaling, etc.) would surely add to its compressability.</li>
<li>If fractal_zip used freearc internally as well as 7-zip it would probably be better.</li>
<li>Currently, the file metadata (path, date modified, packed size, etc.) is wastefully encoded twice since fractal_zip does this and 7-zip does this independantly. So, currently, in order to compress more, fractal_zip has to overcome this extra obstacle.</li>
<li>It's funny how compression code is effectively fractal in its development (whether known or unknown to the developers) itself. freearc uses 7-zip and RAR while fractal_zip currently uses 7-zip which uses LZMA which uses...</li>
</ul>

Version 0.1

Currently the zips this code produces are only sometimes as good as other common zipping programs, or in very specific cases a tiny
bit better (mostly dues to lower format overhead). Were this code to be truely fractal, then it would be interesting.
