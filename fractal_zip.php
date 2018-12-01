<?php

// dimensional breaks and other fractal string operations: encode video game map files, fractal_zip self-extractor .fzsx.php, compressed data that has zero size by referring to a point on a stream of compressed data and 
// decompressing a certain length of it; the stream source being? something like run time in reverse decompresses matter from a singularity

/*
zip_string = 'sujwnBJWEOFsdncJFND84nvcv*JH4H7hdha7g7f75d6576f8fg8G8g9G7554d67FC9g9G'

<folder>
<folder name="aa">8FFGF8966Ghg75dVjU5ERDCf345UmjHdf347h</folder>
<folder name="ab">
	<folder name="ba">767ggh866fgGu88KKo000PlU5433wDf66</folder>
	<folder name="bb">[3-20]</folder>
	<folder name="bc">443ScCFd444EDfGbh</folder>
	<file name="bd.ext">98h99B6FnK,o974weSD343dfG66Uij99OkBHf6</file>
	<file name="be.ext">this is other text in a file</file>
</folder>
<folder name="ac">[3-20]</folder>
<file name="ad.ext">98h99B6FnK,o974weSD343dfG66Uij99OkBHf6</file>
<file name="ae.ext">this is some text in a file</file>
</folder>

levels of encoding: zipped, marked (with operators), unzipped. and this requires multipass or recursive, whatever you call it
may prefer to use zip markers (or nested arrays, not sure) of file and folder operators (along with other new operators) instead of XML
*/

/*
aabb  ccccdddd
aabb  ccccdddd
bbaa  ccccdddd
bbaa  ccccdddd
      ddddcccc
	  ddddcccc
	  ddddcccc
	  ddddcccc
	  
fractally encoded by the mapping a => cc
									  cc
and b => dd
		 dd
and the original zoom

every c is the whole first pattern? need a way to achieve fixed values at a specific resolution (like assigning a pixel a value when looking at a fractal) does this qualify as lossless? would be pretty good for lossy compression
need an operator for fractal?
steps in the generation of an example 1-D fractal: aa, abbbba, abbccccccbba, abbcccddddddddcccbbaa, etc.
or: aaaa, aabbbbaa, aabbaaaabbaa, aabbaabbbbaabbaa, etc.
or, simple operation amid fractal growth: aaaa, aabbbbaa, aabb{inserted string}bbaa, aabb{inserted string}aaaabbaa, aabb{inserted string}aaaabbaabbaa, etc.
could be quite useful to determine all the equivalences between operators and fractal interactions; for example: zooming in a certain amount is equivalent to rotating or flipping, like on a spiral
test against palette swap of an image. anything else? of course operations like rotation, slide, scale, flip, animation frames? 3d: vector, vertex, mesh...
segways, fade in, fade out, other effects: namely?
*/

class fractal_zip {

//function __construct($improvement_factor_threshold = 10, $segment_length = 20000, $multipass = false) {
function __construct($segment_length = 20000, $multipass = false) {
	$this->initial_time = time();
	$this->initial_micro_time = microtime(true);
	define('DS', DIRECTORY_SEPARATOR);
	$this->var_display_max_depth = 12;
	$this->var_display_max_depth = 62;
	//$this->var_display_max_children = 8;
	ini_set('xdebug.var_display_max_depth', $this->var_display_max_depth);
	//ini_set('xdebug.var_display_max_children', $this->var_display_max_children);
	ini_set('xdebug.max_nesting_level', $segment_length + 30); // enough room for recursion?
	//error_reporting(E_ALL);
	//include('..' . DS . 'diff' . DS . 'class.Diff.php');
	$this->fractal_zip_marker = 'FZ';
	$this->fractal_zip_file_extension = '.fractalzip';
	$this->fractal_zip_container_file_extension = '.fzc';
	$this->program_path = substr(__FILE__, 0, fractal_zip::strpos_last(__FILE__, DS));
	//print('__FILE__, get_included_files(), $this->program_path: ');var_dump(__FILE__, get_included_files(), $this->program_path);exit(0);
	$this->fractal_strings = array();
	$this->fractal_string = '';
	$this->equivalences = array();
	$this->branch_counter = 0;
	$this->fractal_path_branch_trimming_score = 0.8;
	//$this->fractal_path_branch_trimming_score = 1;
	//$this->fractal_path_branch_trimming_score = 0.8;
	//$this->fractal_path_branch_trimming_score = 0.6;
	//$this->fractal_path_branch_trimming_score = 1.1;
	$this->fractal_path_branch_trimming_multiplier = 0.618 * 2;
	//$this->fractal_path_branch_trimming_multiplier = 1.618 * 2;
	//$this->fractal_path_branch_trimming_multiplier = 1;
	//$this->improvement_factor_threshold = 2; // requiring an improvement of twice as good is stringent
	$this->improvement_factor_threshold = 10;
	//$this->improvement_factor_threshold = 1;
	//$this->improvement_factor_threshold = 0.5; // good joke
	//$this->improvement_factor_threshold = $improvement_factor_threshold;
	//$this->multipass = false; // https://www.youtube.com/watch?v=JljZbjpvjmE
	$this->multipass = $multipass;
	//$this->segment_length = 140; // in honor of twitter
	//$this->segment_length = 20; // heavily impacts performance; smaller is faster
	$this->segment_length = $segment_length;
	// to put into words how multipass and improvement_factor_threshold affect speed and compression: requiring higher improvement means that range information is only substituted for the juicier segments and smaller 
	// duplicated segments for which range information could be substituted are left as is to simply be compressed. the improvement threshold does not signficantly affect speed but whether to use multipass and its 
	// sub-property of a longer segment length to look for matches in makes the process much slower for questionable gain in compression.
	$this->files_counter = 0;
	$this->lazy_fractal_strings = array();
	$this->lazy_fractal_string = '';
	$this->lazy_equivalences = array();
	// it's interesting to consider whether markers could be chosen according to a file's content rather than attempting to choose markers that will never occur in any file ever
	// choosing and managing these dynamic markers would be more complex but offer better compression
	// it would also require changing the program by adding a pre-pass over all the files to be able to find strings to use as markers 
	// also note that markers can never have as their last or first character any character used to express a portion of the fractal_string
	$this->fractal_zipping_pass = 0;
	//$this->left_fractal_zip_marker = 'X';
	//$this->mid_fractal_zip_marker = 'Y';
	//$this->right_fractal_zip_marker = 'Z';
	//$this->left_fractal_zip_marker = 'XXX9o9left9o9XXX';
	//$this->mid_fractal_zip_marker = 'XXX9o9mid9o9XXX';
	//$this->right_fractal_zip_marker = 'XXX9o9right9o9XXX';
	$this->left_fractal_zip_marker = '<';
	$this->mid_fractal_zip_marker = '"';
	$this->right_fractal_zip_marker = '>';
	$this->range_shorthand_marker = '*';
	// effectively all that's been done is intelligently using a short syntax for replacement operations. with more operators more gains could be had but it's worth pointing out that finite programming instructions can generate
	// infinite outputs so that a great compression system could intelligently use programming instructions, but much better AI is needed first
	$this->strings_for_fractal_zip_markers = array();
	//$this->common_limiter_pairs = array(array('(', ')'), array('[', ']'), array('{', '}'), array('<', '>'), );
	$this->common_limiter_pairs = array(array('<', '>'), ); // hey look, we're biased towards HTML again...
	//$this->common_limiters = array(',', ' ', '.', ); // being careful with space...
	$this->common_limiters = array(',', '.', ';', ':', '/', '\\', '|');
}

function recursive_zip_folder($dir, $debug = false) {
	$handle = opendir($dir);
	while(($entry = readdir($handle)) !== false) {
		if($entry === '.' || $entry === '..') {
			
		} elseif(is_dir($dir . DS . $entry)) {
			fractal_zip::recursive_zip_folder($dir . DS . $entry, $debug = false);
		} else {
			$entry_filename = $dir . DS . $entry;
			$contents = file_get_contents($entry_filename);
			fractal_zip::zip($contents, $entry_filename, $debug);
			if($debug) {
				fractal_zip::validate_fractal_zip($entry_filename);
			}
			$this->files_counter++;
		}
	}
	closedir($handle);
}

function recursive_get_strings_for_fractal_zip_markers($dir) {
	$handle = opendir($dir);
	while(($entry = readdir($handle)) !== false) {
		if($entry === '.' || $entry === '..') {
			
		} elseif(is_dir($dir . DS . $entry)) {
			fractal_zip::recursive_get_strings_for_fractal_zip_markers($dir . DS . $entry);
		} else {
			$entry_filename = $dir . DS . $entry;
			$contents = file_get_contents($entry_filename);
			$this->strings_for_fractal_zip_markers[] = $contents;
		}
	}
	closedir($handle);
}

function maximum_substr_expression_length() {
	// assumptions: recursion_counter < 10
	return (5 + (2 * strlen((string)strlen($this->fractal_string))));
}

function maximum_scale_expression_length() {
	// assumptions: recursion_counter < 10
	return (4 + (2 * strlen((string)strlen($this->fractal_string))) + strlen((string)round((1 / 3), 6))); // limiting infinitely expressed fractions (0.3333..., etc.) that arise in decimal due to incompatibilities between the expressed value nature and the limited number of factors in the base (10) choice
}

function create_fractal_zip_markers($dir, $debug = false) {
	$this->minimum_overhead_length = 5; // <#"#>
	fractal_zip::warning_once('need the ability to have a recursive fractal zip such that the structure of what is compressed (such as a file structure or data structure) could be selectively decompressed according to what in the fractal zip we are interested in. self-extracting file could also thus be self-navigating');
	return true; // these are static (<, ", >)
	fractal_zip::recursive_get_strings_for_fractal_zip_markers($dir);
	// we want to avoid characters before 58 since this includes numbers and the dash which are used in expressing ranges
	// making the character range end at 126 is kind of arbitrary but using such a small range of ubiquitously supported characters could help in debugging by easily seeing (these printable) characters as opposed to having a 
	// larger range that could slightly facilitate better compression
	// it's also worth considering that by using rand we could get different levels of compression when doing the same job just by lucky choice of markers. 
	$character_range_start = 58;
	$character_range_end = 126;
	//$character_range_end = 96;
	//fractal_zip::warning_once('hacking the character ranges to avoid an unknown problem using single characters for all markers seems to be less problematic than using more than one character for some; N0ND0-123ND0I
	//problem is when mid marker includes left marker or right marker');
	//fractal_zip::warning_once('what happens when there are no characters in the range that don\'t appear in the files?!');
	// left marker
	$this->left_fractal_zip_marker = chr(rand($character_range_start, $character_range_end));
	$found_in_strings = true;
	while($found_in_strings) {
		$found_in_strings = false;
		$left_fractal_zip_marker_characters = str_split($this->left_fractal_zip_marker);
		foreach($this->strings_for_fractal_zip_markers as $string_for_fractal_zip_markers) {
			if(strpos($string_for_fractal_zip_markers, $this->left_fractal_zip_marker) !== false) {
				$found_in_strings = true;
				$this->left_fractal_zip_marker .= chr(rand($character_range_start, $character_range_end));
				break;
			}
			/*foreach($left_fractal_zip_marker_characters as $left_fractal_zip_marker_character) {
				if(strpos($string_for_fractal_zip_markers, $left_fractal_zip_marker_character) !== false) {
					$found_in_strings = true;
					$this->left_fractal_zip_marker .= chr(rand($character_range_start, $character_range_end));
					//$this->left_fractal_zip_marker = chr(rand($character_range_start, $character_range_end));
					break 2;
				}
			}*/
		}
	}
	if($debug) {
		print('$this->left_fractal_zip_marker: ');var_dump($this->left_fractal_zip_marker);
	}
	// right marker
	if(strrev($this->left_fractal_zip_marker) === $this->left_fractal_zip_marker) {
		$this->right_fractal_zip_marker = chr(rand($character_range_start, $character_range_end));
		$found_in_strings = true;
	} else {
		$this->right_fractal_zip_marker = strrev($this->left_fractal_zip_marker);
		$found_in_strings = false;
		foreach($this->strings_for_fractal_zip_markers as $string_for_fractal_zip_markers) {
			if(strpos($string_for_fractal_zip_markers, $this->right_fractal_zip_marker) !== false) {
				$found_in_strings = true;
				$this->right_fractal_zip_marker = chr(rand($character_range_start, $character_range_end));
				break;
			}
		}
	}
	while($found_in_strings) {
		$found_in_strings = false;
		$right_fractal_zip_marker_characters = str_split($this->right_fractal_zip_marker);
		foreach($this->strings_for_fractal_zip_markers as $string_for_fractal_zip_markers) {
			if(strpos($string_for_fractal_zip_markers, $this->right_fractal_zip_marker) !== false) {
				$found_in_strings = true;
				$this->right_fractal_zip_marker .= chr(rand($character_range_start, $character_range_end));
				continue 2;
			}
			/*foreach($right_fractal_zip_marker_characters as $right_fractal_zip_marker_character) {
				if(strpos($string_for_fractal_zip_markers, $right_fractal_zip_marker_character) !== false) {
					$found_in_strings = true;
					$this->right_fractal_zip_marker .= chr(rand($character_range_start, $character_range_end));
					//$this->right_fractal_zip_marker = chr(rand($character_range_start, $character_range_end));
					continue 3;
				}
			}*/
		}
		if(strpos($this->left_fractal_zip_marker, $this->right_fractal_zip_marker) !== false || strpos($this->right_fractal_zip_marker, $this->left_fractal_zip_marker) !== false) {
			$found_in_strings = true;
			$this->right_fractal_zip_marker .= chr(rand($character_range_start, $character_range_end));
		}
	}
	if($debug) {
		print('$this->right_fractal_zip_marker: ');var_dump($this->right_fractal_zip_marker);
	}
	// mid marker
	if($this->multipass) {
		$this->mid_fractal_zip_marker = chr(rand($character_range_start, $character_range_end));
		$found_in_strings = true;
		while($found_in_strings) {
			$found_in_strings = false;
			$mid_fractal_zip_marker_characters = str_split($this->mid_fractal_zip_marker);
			foreach($this->strings_for_fractal_zip_markers as $string_for_fractal_zip_markers) {
				if(strpos($string_for_fractal_zip_markers, $this->mid_fractal_zip_marker) !== false) {
					$found_in_strings = true;
					$this->mid_fractal_zip_marker .= chr(rand($character_range_start, $character_range_end));
					continue 2;
				}
				/*foreach($mid_fractal_zip_marker_characters as $mid_fractal_zip_marker_character) {
					if(strpos($string_for_fractal_zip_markers, $mid_fractal_zip_marker_character) !== false) {
						$found_in_strings = true;
						$this->mid_fractal_zip_marker .= chr(rand($character_range_start, $character_range_end));
						//$this->mid_fractal_zip_marker = chr(rand($character_range_start, $character_range_end));
						continue 3;
					}
				}*/
			}
			if(strpos($this->left_fractal_zip_marker, $this->mid_fractal_zip_marker) !== false || strpos($this->right_fractal_zip_marker, $this->mid_fractal_zip_marker) !== false || 
			strpos($this->mid_fractal_zip_marker, $this->left_fractal_zip_marker) !== false || strpos($this->mid_fractal_zip_marker, $this->right_fractal_zip_marker) !== false) {
				$found_in_strings = true;
				$this->mid_fractal_zip_marker .= chr(rand($character_range_start, $character_range_end));
			}
		}
	} else {
		$this->mid_fractal_zip_marker = '';
	}
	if($debug) {
		print('$this->mid_fractal_zip_marker: ');var_dump($this->mid_fractal_zip_marker);
	}
	if($this->multipass) {
		$this->minimum_overhead_length = strlen($this->left_fractal_zip_marker) + 1 + strlen($this->mid_fractal_zip_marker) + 3 + strlen($this->mid_fractal_zip_marker) + 1 + strlen($this->right_fractal_zip_marker);
	} else {
		$this->minimum_overhead_length = strlen($this->left_fractal_zip_marker) + 3 + strlen($this->right_fractal_zip_marker);
	}
	//exit(0);
}

function zip_folder($dir, $debug = false) {
	print('Fractal zipping folder: ' . $dir . '<br>');
	// debug mode should leave the uncompressed fractal-zipped data to be worked upon by other programs (music)
	//$files_created = array();
	fractal_zip::create_fractal_zip_markers($dir, $debug);
	fractal_zip::recursive_zip_folder($dir, $debug);
	
	// more passes on the fractal zipped strings
	// perhaps this code could be adopted for the initial pass as well once we are already going through all the strings before-hand to figure out what would be good markers... or a hybrid? 
	// meh, it comes down to a performance versus compression consideration; a nice balance must be sought and this is done by limiting the scope of the string comparison (segment_length) so that it may take multiple passes
	// and be compressed slightly less but will finish in a reasonable amount of time.
	// currently (2017-05-11) takes twenty times longer and makes a worse result..... always surprising. maybe effort should be redirected to endeavoring to keep "content" parts of templated content together

	if($this->multipass) {
		$last_pass_made_an_improvement = true;
		while($last_pass_made_an_improvement) {
			$last_pass_made_an_improvement = false;
			$improving_replaces = array();
			$this->fractal_zipping_pass++;
			if($debug) {
				print('$this->fractal_zipping_pass: ');var_dump($this->fractal_zipping_pass);
			}
			//$cat_string = '';
			$strings = array();
			foreach($this->equivalences as $equivalence_index => $equivalence) { // this apparently inefficient array format seems to be a vestige of earlier efforts but meh
				//$this->array_fractal_zipped_strings_of_files[$equivalence[1]] = $equivalence[2];
				$string = $equivalence[2];
				$strings[] = $string;
				//preg_match_all('/' . $this->left_fractal_zip_marker . '([0-9]+)' . $this->mid_fractal_zip_marker . '([0-9]+)\-([0-9]+)' . $this->mid_fractal_zip_marker . '([0-9]+)' . $this->right_fractal_zip_marker . '/is', $string, $fractal_zipped_ranges);
				// here's where it gets hairy (number of array entries triangularly? proportional to the number of ranges)
			}
			foreach($this->equivalences as $equivalence_index => $equivalence) {
				$string = $equivalence[2];
				$counter = 0;
				$last_start_matches = 0;
				while($counter < strlen($string)) {
					$length_counter = strlen($string) - $counter;
					if($length_counter > $this->segment_length) {
						$length_counter = $this->segment_length;
					}
					$last_end_matches = 0;
					//while($length_counter > 2 * $this->minimum_overhead_length) { // roughly saying that we need at least two long enough instances to even consider working this piece
					while($length_counter > fractal_zip::maximum_substr_expression_length()) {
						$piece = substr($string, $counter, $length_counter);
						$matches = 0;
						foreach($strings as $string2) {
							$matches += substr_count($string2, $piece);
						}
						if($matches > 1 && $matches > $last_start_matches && $matches > $last_end_matches) {
							//print('$matches > 1; $matches, $last_start_matches, $last_end_matches, $piece: ');var_dump($matches, $last_start_matches, $last_end_matches, $piece);
							//$would_need_to_add_to_fractal_string = false;
							$position_in_fractal_string = strpos($this->fractal_string, $piece);
							if($position_in_fractal_string !== false) {
								$start_offset = $position_in_fractal_string;
							} else {
								$start_offset = strlen($this->fractal_string);
								//$would_need_to_add_to_fractal_string = true;
							}
							$end_offset = strlen($piece) + $start_offset - 1;
							$range_string = $this->left_fractal_zip_marker . $this->fractal_zipping_pass . $this->mid_fractal_zip_marker . $start_offset . '-' . $end_offset . $this->mid_fractal_zip_marker . $this->fractal_zipping_pass . $this->right_fractal_zip_marker;
							//if($matches * strlen($piece) / strlen($range_string) > $this->improvement_factor_threshold) {
							if($matches * strlen($piece) / strlen($range_string) > 2 * $this->improvement_factor_threshold) {
								//print('found an improvement on another fractal zipping pass<br>');
								//if($would_need_to_add_to_fractal_string) {
								//	$this->fractal_string .= $piece;
								//}
								//print('$piece, $range_string: ');var_dump($piece, $range_string);exit(0);
								$score = $matches * (strlen($piece) - strlen($range_string));
								$improving_replaces[$piece] = $score;
								$last_pass_made_an_improvement = true;
							}
						}
						$last_end_matches = $matches;
						$length_counter--;
					}
					$last_start_matches = $matches;
					$counter++;
				}
			}
			arsort($improving_replaces); // sort by association in reverse order
			//print('$improving_replaces: ');var_dump($improving_replaces);exit(0);
			foreach($improving_replaces as $search => $score) {
				$would_need_to_add_to_fractal_string = false;
				$position_in_fractal_string = strpos($this->fractal_string, $search);
				if($position_in_fractal_string !== false) {
					$start_offset = $position_in_fractal_string;
				} else {
					$start_offset = strlen($this->fractal_string);
					$would_need_to_add_to_fractal_string = true;
				}
				$end_offset = strlen($piece) + $start_offset - 1;
				$range_string = $this->left_fractal_zip_marker . $this->fractal_zipping_pass . $this->mid_fractal_zip_marker . $start_offset . '-' . $end_offset . $this->mid_fractal_zip_marker . $this->fractal_zipping_pass . $this->right_fractal_zip_marker;
				if($would_need_to_add_to_fractal_string) {
					$this->fractal_string .= $piece;
				}
				foreach($this->equivalences as $equivalence_index => $equivalence) {
					$this->equivalences[$equivalence_index][2] = str_replace($search, $range_string, $this->equivalences[$equivalence_index][2]);			
				}
			}
		}
	}
	
	// save the necessary arrays as a fractal_zip file
	if($debug) {
		print('$this->fractal_string, $this->equivalences, $this->branch_counter after zipping all files: ');var_dump($this->fractal_string, $this->equivalences, $this->branch_counter);
	}
//	print('debug stop before serialization');exit(0);
//	fractal_zip::clean_arrays_before_serialization();
	$this->array_fractal_zipped_strings_of_files = array();
	//print('$this->equivalences: ');var_dump($this->equivalences);
	foreach($this->equivalences as $equivalence) {
		//print('um34749<br>');
		$this->array_fractal_zipped_strings_of_files[$equivalence[1]] = $equivalence[2];
	}
	//print('$this->array_fractal_zipped_strings_of_files: ');var_dump($this->array_fractal_zipped_strings_of_files);
	//$fzc_contents = serialize(array($this->array_fractal_zipped_strings_of_files, $this->fractal_strings));
	/*$fzc_contents = serialize(array(
	$this->array_fractal_zipped_strings_of_files, 
	$this->multipass, 
	$this->branch_counter, 
	$this->left_fractal_zip_marker, 
	$this->mid_fractal_zip_marker, 
	$this->right_fractal_zip_marker, 
	$this->fractal_string
	));*/
	$fzc_contents = serialize(array($this->array_fractal_zipped_strings_of_files, $this->fractal_string));
	if($debug) {
		print('$this->array_fractal_zipped_strings_of_files: ');fractal_zip::var_dump_full($this->array_fractal_zipped_strings_of_files);
		print('$fzc_contents before compression: ');fractal_zip::var_dump_full($fzc_contents);
	}
	$lazy_array_fractal_zipped_strings_of_files = array();
	foreach($this->lazy_equivalences as $equivalence) {
		$lazy_array_fractal_zipped_strings_of_files[$equivalence[1]] = $equivalence[2];
	}
	//$lazy_fzc_contents = serialize(array($lazy_array_fractal_zipped_strings_of_files, $this->lazy_fractal_strings));
	/*$lazy_fzc_contents = serialize(array(
	$lazy_array_fractal_zipped_strings_of_files, 
	$this->multipass, 
	$this->branch_counter, 
	$this->left_fractal_zip_marker, 
	$this->mid_fractal_zip_marker, 
	$this->right_fractal_zip_marker, 
	$this->lazy_fractal_string
	));*/
	$lazy_fzc_contents = serialize(array($lazy_array_fractal_zipped_strings_of_files, $this->lazy_fractal_string));
	if($debug) {
		print('$lazy_array_fractal_zipped_strings_of_files: ');fractal_zip::var_dump_full($lazy_array_fractal_zipped_strings_of_files);
		print('$lazy_fzc_contents before compression: ');fractal_zip::var_dump_full($lazy_fzc_contents);
	}
	// gzip or LZMA?
	// http://us.php.net/manual/en/function.gzdeflate.php
	/* gzcompress produces longer data because it embeds information about the encoding onto the string. If you are compressing data that will only ever be handled on one machine, then you don't need 
	to worry about which of these functions you use. However, if you are passing data compressed with these functions to a different machine you should use gzcompress.	*/
	//$fzc_contents = gzcompress($fzc_contents, 9);
	//$fzc_contents = gzcompress($fzc_contents);
	//$fzc_contents = gzencode($fzc_contents, 9);
	//$fzc_contents = gzdeflate($fzc_contents, 9);
	//$fzc_contents = bzcompress($fzc_contents);
	//$lazy_fzc_contents = gzcompress($lazy_fzc_contents, 9);
	//$lazy_fzc_contents = gzcompress($lazy_fzc_contents);
	//file_put_contents('php_info_fractal_zip_contents.txt', $fzc_contents);
	$fzc_contents = fractal_zip::adaptive_compress($fzc_contents);
	$lazy_fzc_contents = fractal_zip::adaptive_compress($lazy_fzc_contents);
	if($debug) {
		print('$fzc_contents: ');var_dump($fzc_contents);
		print('$lazy_fzc_contents: ');var_dump($lazy_fzc_contents);
		//print('$this->fractal_zipping_pass: ');var_dump($this->fractal_zipping_pass);
	}
	//$last_folder_name = substr($dir, fractal_zip::strpos_last($dir, DS));
	//print('$dir, $last_folder_name: ');var_dump($dir, $last_folder_name);
	if(strlen($lazy_fzc_contents) <= strlen($fzc_contents)) {
		print('simply compressing the strings made the smallest file (' . strlen($fzc_contents) . ' &gt; ' . strlen($lazy_fzc_contents) . ') (' . strlen($fzc_contents) / strlen($lazy_fzc_contents) * 100 . '%)<br>');
		//file_put_contents($dir . DS . $last_folder_name . $this->fractal_zip_container_file_extension, $lazy_fzc_contents);
		//file_put_contents($last_folder_name . $this->fractal_zip_container_file_extension, $lazy_fzc_contents);
		file_put_contents($dir . $this->fractal_zip_container_file_extension, $lazy_fzc_contents);
	} else {
		print('<span style="color: green;">fractal zipping was actually useful (' . strlen($fzc_contents) . ' &#8804; ' . strlen($lazy_fzc_contents) . ') (' . strlen($fzc_contents) / strlen($lazy_fzc_contents) * 100 . '%)!</span><br>');
		//file_put_contents($dir . DS . $last_folder_name . $this->fractal_zip_container_file_extension, $fzc_contents);
		//file_put_contents($last_folder_name . $this->fractal_zip_container_file_extension, $fzc_contents);
		file_put_contents($dir . $this->fractal_zip_container_file_extension, $fzc_contents);
	}
	$micro_time_taken = microtime(true) - $this->initial_micro_time;
	print('Time taken zipping folder: ' . $micro_time_taken . ' seconds.<br>');
}

function add_to_fractal_zip($filename) {
	// would like to be able to add single files or folders to an existing fractal_zip
	// how to determine which .fzc to add to??
}

function remove_from_fractal_zip($filename) {
	// maybe this is actually useful?
}

function explore_fractal_zip($filename) {
	// would like to be able to explore the contents of a fractal_zip then subsequently extract individual or all files
	// this is done by open_file in fs if it tries to open a fractal_zip file
}

function open_container($filename, $debug = false) {
	print('Opening fractal zip container: ' . $filename . '<br>');
	$root_directory_of_container_file = substr($filename, 0, fractal_zip::strpos_last($filename, DS));
	$contents = file_get_contents($filename);
	// un-gzip or un-LZMA?
	//$contents = gzuncompress($contents);
	$contents = fractal_zip::adaptive_decompress($contents);
	//$contents = gzdecode($contents);
	//$contents = gzinflate($contents);
	//$contents = bzdecompress($contents);
	//$array_fractal_strings_and_equivalences = unserialize($contents);
	//$this->array_fractal_zipped_strings_of_files = $array_fractal_strings_and_equivalences[0];
	//$this->fractal_strings = $array_fractal_strings_and_equivalences[1];
	//print('$contents: ');var_dump($contents);
	$array_fractal_string_and_equivalences = unserialize($contents);
	//print('$array_fractal_string_and_equivalences: ');var_dump($array_fractal_string_and_equivalences);
	/*$this->array_fractal_zipped_strings_of_files = $array_fractal_string_and_equivalences[0];
	//print('$this->array_fractal_zipped_strings_of_files: ');var_dump($this->array_fractal_zipped_strings_of_files);
	$this->multipass = $array_fractal_string_and_equivalences[1];
	$this->branch_counter = $array_fractal_string_and_equivalences[2];
	$this->left_fractal_zip_marker = $array_fractal_string_and_equivalences[3];
	$this->mid_fractal_zip_marker = $array_fractal_string_and_equivalences[4];
	$this->right_fractal_zip_marker = $array_fractal_string_and_equivalences[5];
	$this->fractal_string = $array_fractal_string_and_equivalences[6];*/
	
	$this->array_fractal_zipped_strings_of_files = $array_fractal_string_and_equivalences[0];
	$this->fractal_string = $array_fractal_string_and_equivalences[1];
	
	foreach($this->array_fractal_zipped_strings_of_files as $index => $value) {
		fractal_zip::build_directory_structure_for($root_directory_of_container_file . DS . $index);
		$zipped_contents = $value;
		$unzipped_contents = fractal_zip::unzip($zipped_contents);
		file_put_contents($root_directory_of_container_file . DS . $index, $unzipped_contents);
		print('Extracted ' . $root_directory_of_container_file . DS . $index . '<br>');
		$this->files_counter++;
	}
	if($debug) {
		$micro_time_taken = microtime(true) - $this->initial_micro_time;
		print('Time taken opening container: ' . $micro_time_taken . ' seconds.<br>');
	}
}

function open_container_allowing_individual_extraction($filename, $debug = false) {
	print('Opening fractal zip container: ' . $filename . '<br>');
	$contents = file_get_contents($filename);
	//$contents = gzuncompress($contents);
	$contents = fractal_zip::adaptive_decompress($contents);
	$array_fractal_string_and_equivalences = unserialize($contents);
	/*$this->array_fractal_zipped_strings_of_files = $array_fractal_string_and_equivalences[0];
	//print('$this->array_fractal_zipped_strings_of_files: ');var_dump($this->array_fractal_zipped_strings_of_files);
	$this->multipass = $array_fractal_string_and_equivalences[1];
	$this->branch_counter = $array_fractal_string_and_equivalences[2];
	$this->left_fractal_zip_marker = $array_fractal_string_and_equivalences[3];
	$this->mid_fractal_zip_marker = $array_fractal_string_and_equivalences[4];
	$this->right_fractal_zip_marker = $array_fractal_string_and_equivalences[5];
	$this->fractal_string = $array_fractal_string_and_equivalences[6];*/
	
	$this->array_fractal_zipped_strings_of_files = $array_fractal_string_and_equivalences[0];
	$this->fractal_string = $array_fractal_string_and_equivalences[1];
	
	foreach($this->array_fractal_zipped_strings_of_files as $index => $value) {
		print('<a href="do.php?action=extract_file&path=' . fs::query_encode($filename) . '&file_to_extract=' . $index . '">Extract: ' . $index . '</a><br>');
	}
	//if($debug) {
		$micro_time_taken = microtime(true) - $this->initial_micro_time;
		print('Time taken opening container allowing individual extraction: ' . $micro_time_taken . ' seconds.<br>');
	//}
}

function extract_container($filename) {
	fractal_zip::open_container($filename);
	//$root_directory_of_container_file = substr($filename, 0, fractal_zip::strpos_last($filename, DS));
	foreach($this->array_fractal_zipped_strings_of_files as $index => $value) {
		//fractal_zip::build_directory_structure_for($root_directory_of_container_file . DS . $index);
		fractal_zip::build_directory_structure_for($index);
		$zipped_contents = $value;
		$unzipped_contents = fractal_zip::unzip($zipped_contents);
		//file_put_contents($root_directory_of_container_file . DS . $index, $unzipped_contents);
		//print('Extracted ' . $root_directory_of_container_file . DS . $index . '<br>');
		file_put_contents($index, $unzipped_contents);
		print('Extracted ' . $index . '<br>');
		$this->files_counter++;
	}
	$micro_time_taken = microtime(true) - $this->initial_micro_time;
	print('Time taken extracting files from fractal_zip container: ' . $micro_time_taken . ' seconds.<br>');
}

function extract_file_from_container($filename, $file) {
	fractal_zip::open_container($filename);
	//$root_directory_of_container_file = substr($filename, 0, fractal_zip::strpos_last($filename, DS));
	foreach($this->array_fractal_zipped_strings_of_files as $index => $value) {
		if($file === $index) {
			//fractal_zip::build_directory_structure_for($root_directory_of_container_file . DS . $index);
			fractal_zip::build_directory_structure_for($index);
			$zipped_contents = $value;
			$unzipped_contents = fractal_zip::unzip($zipped_contents);
			//file_put_contents($root_directory_of_container_file . DS . $index, $unzipped_contents);
			//print('Extracted ' . $root_directory_of_container_file . DS . $index . '<br>');
			file_put_contents($index, $unzipped_contents);
			print('Extracted ' . $index . '<br>');
			$this->files_counter++;
		}
	}
	$micro_time_taken = microtime(true) - $this->initial_micro_time;
	//print('Time taken extracting ' . $file . ' from fractal_zip container ' . $filename . ': ' . $micro_time_taken . ' seconds.<br>');
	print('Time taken extracting: ' . $micro_time_taken . ' seconds.<br>');
}

function build_directory_structure_for($filename) {
	//print('$filename: ');var_dump($filename);
	$folders = explode(DS, $filename);
	//print('$folders: ');var_dump($folders);
	$folder_string = '';
	foreach($folders as $index => $folder_name) {
		//print('$folder_string: ');var_dump($folder_string);
		if($index === sizeof($folders) - 1) {
			break;
		}
		$folder_string .= $folder_name . DS;
		if(!is_dir($folder_string)) {
			mkdir($folder_string);
		}
	}
}

function unzip($string, $fractal_string = false, $decode = true) {
	if($fractal_string === false) {
		$fractal_string = $this->fractal_string;
	}
	/*$found_equivalence = false;
	foreach($this->equivalences as $equivalence) {
		//$equivalence_string = $equivalence[0];
		//$equivalence_filename = $equivalence[1];
		$equivalence_fractal_zipped_expression = $equivalence[2];
		if($equivalence_fractal_zipped_expression === $string) {
			$found_equivalence = true;
			break;
		}
	}
	if(!$found_equivalence) {
		print('Equivalence not found.');exit(0);
	}*/
	/*$unzipped_string = '';
	$branch_ids = fractal_zip::branch_ids_from_zipped_string($string);
	foreach($branch_ids as $branch_id) {
		foreach($this->fractal_strings as $branch_id2 => $fractal_string) {
			//print('$branch_reference, $branch_id: ');var_dump($branch_reference, $branch_id);
			if($branch_id == $branch_id2) {
				$unzipped_string .= $fractal_string;
				break;
			}
		}
	}*/
	/*$unzipped_string = '';
	$character_ranges = explode(',', $string);
	foreach($character_ranges as $character_range) {
		if(strpos($character_range, '-') === false) {
			$start_offset = (int)$character_range;
			$unzipped_string .= $this->fractal_string[$start_offset];
		} else {
			$start_offset = (int)substr($character_range, 0, strpos($character_range, '-'));
			$end_offset = (int)substr($character_range, strpos($character_range, '-') + 1);
			$unzipped_string .= substr($this->fractal_string, $start_offset, $end_offset - $start_offset + 1);
		}
	}*/
	
	// true fractal unzip
	//print('$string, $fractal_string at the start of unzip: ');var_dump($string, $fractal_string);exit(0);
	//if($string === '<0"' . strlen($fractal_string) . '>') {
	//	fractal_zip::warning_once('this hack of allowing unencoded XML in lazy zipped string is probably untenable; especially when considering operations other than substring, such as replace, slide, warp, etc.');
	//	return $fractal_string;
	//}
	//if($string[0] === '<') {
	if(strpos($string, '<') !== false) {
		//print('fractally processing string<br>');
		$unzipped_string = fractal_zip::fractally_process_string($string, $fractal_string);
		//print('$string, $fractal_string, $unzipped_string from fractal_processing: ');var_dump($string, $fractal_string, $unzipped_string);
		if($decode) {
			$unzipped_string = htmlspecialchars_decode($unzipped_string);
		}
		return $unzipped_string;
	}
	
	//fractal_zip::warning_once('will have to write a parser rather than using regular expressions... probably faster and more accurate');
	preg_match_all('/' . fractal_zip::preg_escape($this->left_fractal_zip_marker) . $this->fractal_zipping_pass . fractal_zip::preg_escape($this->mid_fractal_zip_marker) . '([0-9]+)\-([0-9]+)' . fractal_zip::preg_escape($this->mid_fractal_zip_marker) . $this->fractal_zipping_pass . fractal_zip::preg_escape($this->right_fractal_zip_marker) . '/is', $string, $fractal_zipped_ranges);
	//print('$fractal_zipped_ranges: ');var_dump($fractal_zipped_ranges);
	foreach($fractal_zipped_ranges[0] as $index => $value) {
		$start_offset = $fractal_zipped_ranges[1][$index];
		$end_offset = $fractal_zipped_ranges[2][$index];
		//print('$value, substr($this->fractal_string, $start_offset, $end_offset - $start_offset + 1): ');var_dump($value, substr($this->fractal_string, $start_offset, $end_offset - $start_offset + 1));
		$string = str_replace($value, substr($this->fractal_string, $start_offset, $end_offset - $start_offset + 1), $string);
	}
	// parser
	$shorthand_counter = 1;
	$saved_shorthand = array();
	$counter = 0;
	$unzipped_string = '';
	//$string_fragment = '';
	$branch_counter = $this->branch_counter;
	while($branch_counter > -1) {
		while($counter < strlen($string)) {
			if(substr($string, $counter, strlen($this->left_fractal_zip_marker)) === $this->left_fractal_zip_marker) {
				$counter += strlen($this->left_fractal_zip_marker);
				if($this->multipass) {
					$string_fragment = $this->left_fractal_zip_marker;
					$left_branch_counter = '';
					while($counter < strlen($string)) {
						if(substr($string, $counter, strlen($this->mid_fractal_zip_marker)) === $this->mid_fractal_zip_marker) {
							$counter += strlen($this->mid_fractal_zip_marker);
							if($left_branch_counter == $this->branch_counter) {
								
							} else {
								$string_fragment .= $this->mid_fractal_zip_marker;
								$unzipped_string .= $string_fragment;
								continue 2;
							}
							$unzipped_string .= $saved_shorthand[$shorthand_number];
							if(substr($string, $counter, strlen($this->range_shorthand_marker)) === $this->range_shorthand_marker) { // short-hand range
								$counter += strlen($this->range_shorthand_marker);
								$string_fragment .= $this->range_shorthand_marker;
								$shorthand_number = '';
								while($counter < strlen($string)) {
									if(substr($string, $counter, strlen($this->mid_fractal_zip_marker)) === $this->mid_fractal_zip_marker) {
										$counter += strlen($this->mid_fractal_zip_marker);
										$string_fragment .= $this->mid_fractal_zip_marker;
										$right_branch_counter = '';
										while($counter < strlen($string)) {
											if(substr($string, $counter, strlen($this->right_fractal_zip_marker)) === $this->right_fractal_zip_marker) {
												$counter += strlen($this->right_fractal_zip_marker);
												$string_fragment .= $this->right_fractal_zip_marker;
												if($left_branch_counter == $right_branch_counter) {
													$unzipped_string .= $saved_shorthand[$shorthand_number];
													continue 4;
												} else {
													$string_fragment .= $this->right_fractal_zip_marker;
													$unzipped_string .= $string_fragment;
													continue 4;
												}
											} else {
												$right_branch_counter .= $string[$counter];
												$string_fragment .= $string[$counter];
											}
											$counter++;
										}
									} else {
										$shorthand_number .= $string[$counter];
										$string_fragment .= $string[$counter];
									}
									$counter++;
								}
							} else {
								$range_string = '';
								while($counter < strlen($string)) {
									if(substr($string, $counter, strlen($this->mid_fractal_zip_marker)) === $this->mid_fractal_zip_marker) {
										$counter += strlen($this->mid_fractal_zip_marker);
										$string_fragment .= $this->mid_fractal_zip_marker;
										$right_branch_counter = '';
										while($counter < strlen($string)) {
											if(substr($string, $counter, strlen($this->right_fractal_zip_marker)) === $this->right_fractal_zip_marker) {
												$counter += strlen($this->right_fractal_zip_marker);
												$string_fragment .= $this->right_fractal_zip_marker;
												if($left_branch_counter == $right_branch_counter) {
													$range_string_array = explode('-', $range_string);
													$start_offset = $range_string_array[0];
													$end_offset = $range_string_array[1];
													$unzipped_piece = substr($this->fractal_string, $start_offset, $end_offset - $start_offset + 1);
													$unzipped_string .= $unzipped_piece;
													$saved_shorthand[(string)$shorthand_counter] = $unzipped_piece;
													$shorthand_counter++;
													$counter += strlen($this->right_fractal_zip_marker);
													continue 4;
												} else {
													$string_fragment .= $this->right_fractal_zip_marker;
													$unzipped_string .= $string_fragment;
													continue 4;
												}
											} else {
												$right_branch_counter .= $string[$counter];
												$string_fragment .= $string[$counter];
											}
											$counter++;
										}
									} else {
										$range_string .= $string[$counter];
										$string_fragment .= $string[$counter];
									}
									$counter++;
								}
							}
						} else {
							$left_branch_counter .= $string[$counter];
							$string_fragment .= $string[$counter];
						}
						$counter++;
					}
					$unzipped_string .= $string_fragment;
				} else { // no multipass
					if(substr($string, $counter, strlen($this->range_shorthand_marker)) === $this->range_shorthand_marker) { // short-hand range
						$counter += strlen($this->range_shorthand_marker);
						$shorthand_number = '';
						while($counter < strlen($string)) {
							if(substr($string, $counter, strlen($this->right_fractal_zip_marker)) === $this->right_fractal_zip_marker) {
								//print('$saved_shorthand, $saved_shorthand[$shorthand_number], $shorthand_number: ');var_dump($saved_shorthand, $saved_shorthand[$shorthand_number], $shorthand_number);
								$shorthand_number = (int)$shorthand_number;
								$unzipped_string .= $saved_shorthand[$shorthand_number];
								$counter += strlen($this->right_fractal_zip_marker);
								continue 2;
							} else {
								$shorthand_number .= $string[$counter];
							}
							$counter++;
						}
					} else {
						$range_string = '';
						while($counter < strlen($string)) {
							if(substr($string, $counter, strlen($this->right_fractal_zip_marker)) === $this->right_fractal_zip_marker) {
								$range_string_array = explode('-', $range_string);
								$start_offset = $range_string_array[0];
								$end_offset = $range_string_array[1];
								$unzipped_piece = substr($this->fractal_string, $start_offset, $end_offset - $start_offset + 1);
								$unzipped_string .= $unzipped_piece;
								$saved_shorthand[$shorthand_counter] = $unzipped_piece;
								$shorthand_counter++;
								$counter += strlen($this->right_fractal_zip_marker);
								continue 2;
							} else {
								$range_string .= $string[$counter];
							}
							$counter++;
						}
					}
				}
			} else {
				$unzipped_string .= $string[$counter];
			}
			$counter++;
		}
		$branch_counter--;
	}
	if($decode) {
		$unzipped_string = htmlspecialchars_decode($unzipped_string);
	}
	return $unzipped_string;
	//return $string;
}

function create_fractal_file($filename, $equivalence_string, $fractal_string) {
	$fractally_processed_string = fractal_zip::fractally_process_string($equivalence_string, $fractal_string);
	fractal_zip::build_directory_structure_for($filename);
	file_put_contents($filename, $fractally_processed_string);
	print($filename . ' was created.<br>');
}

function recursive_substring_replace($equivalence_string, $fractal_string) {
	if(!fractal_zip::is_fractally_clean_for_unzip($equivalence_string)) {
		fractal_zip::warning('really bad! !fractal_zip::is_fractally_clean_for_unzip($equivalence_string) in fractally_process_string probably because a substring operator is busting into another substring operator! returning an empty string!');
		return '';
	}
	$initial_equivalence_string = $equivalence_string;
	$debug_counter = 0;
	while(preg_match('/<[0-9]/is', $equivalence_string) === 1) {
		//preg_match_all('/<([0-9]+)"([0-9]+)"*([0-9]*)\**([0-9]*)s*([0-9\.]*)>/is', $equivalence_string, $substring_operation_matches, PREG_OFFSET_CAPTURE);
		preg_match('/<([0-9]+)"([0-9]+)"*([0-9]*)\**([0-9]*)s*([0-9\.]*)>/is', $equivalence_string, $substring_operation_matches, PREG_OFFSET_CAPTURE);
		//print('$equivalence_string, $substring_operation_matches: ');var_dump($equivalence_string, $substring_operation_matches);exit(0);
		//$counter = sizeof($substring_operation_matches[0]) - 1;
		//while($counter > -1) {
			if(isset($this->substring_cache[$substring_operation_matches[0][0]])) {
				$substring = $this->substring_cache[$substring_operation_matches[0][0]];
			} else {
				$substring_offset = $substring_operation_matches[1][0];
				$substring_length = $substring_operation_matches[2][0];
				$substring_recursion_counter = $substring_operation_matches[3][0]; // what should be the order of the following markers?
				//print('$substring_offset, $substring_recursion_counter: ');var_dump($substring_offset, $substring_recursion_counter);
				$substring_tuple = $substring_operation_matches[4][0];
				$substring_scale = $substring_operation_matches[5][0];
				$substring = substr($fractal_string, $substring_offset, $substring_length);
				//print('$substring: ');var_dump($substring);
				if($substring_recursion_counter > 1) {
					//print('uhhh0003<br>');
					$substring = preg_replace('/<([0-9]+)"([0-9]+)>/is', '<$1"$2"' . ($substring_recursion_counter - 1) . '>', $substring);
					//print('$equivalence_string after processing a substring operation: ');var_dump($equivalence_string);
			//		$processed_a_subtring_operation = true;
				} else {
					//print('uhhh0004<br>');
					$substring = preg_replace('/<([0-9]+)"([0-9]+)>/is', '', $substring);
				}
				$single_substring = $substring;
				while($substring_tuple > 1) {
					$substring = $substring . $single_substring;
					$substring_tuple--;
				}
				if($substring_scale !== '') {
					$substring_by_scale = '';
					$offset = 0;
					$counter = 0;
					while($offset < strlen($substring)) {
						$counter += $substring_scale;
						while($counter > 0) {
							$substring_by_scale .= $substring[$offset];
							$counter -= 1;
						}
						$offset++;
					}
					$substring = $substring_by_scale;
				}
				$substring = fractal_zip::recursive_substring_replace($substring, $fractal_string);
				$this->substring_cache[$substring_operation_matches[0][0]] = $substring;
			}
			//$equivalence_string = substr($equivalence_string, 0, $substring_operation_matches[0][1]) . $substring . substr($equivalence_string, $substring_operation_matches[0][1] + strlen($substring_operation_matches[0][0]));
			$equivalence_string = str_replace($substring_operation_matches[0][0], $substring, $equivalence_string);
		//	continue 2; // funny
			//print('$equivalence_string after one substring operators processing loop: ');var_dump($equivalence_string);
			$debug_counter++;
			if($debug_counter > 620) {
				print('$initial_equivalence_string, $equivalence_string: ');var_dump($initial_equivalence_string, $equivalence_string);
				fractal_zip::fatal_error('$debug_counter > 620');
			}
		//	$counter--;
		//}
	}
	return $equivalence_string;
}

function fractally_process_string($equivalence_string, $fractal_string = false) {
	if($fractal_string === false) {
		$fractal_string = $this->fractal_string;
	}
	//print('$equivalence_string, $fractal_string in fractally_process_string: ');var_dump($equivalence_string, $fractal_string);
//	if(!include_once('..' . DS . 'LOM' . DS . 'O.php')) {
//		print('<a href="https://www.phpclasses.org/package/10594-PHP-Extract-information-from-XML-documents.html">LOM</a> is required');exit(0);
//	}
//	$O = new O($string);
	//$changed_something = true;
	// what is the correct way to process; continuing every time something is changed? breaking every time something is changed? doing all fractal operations every loop regardless of if something is changed?
	//while($changed_something) {
	// take care of substring operations first. is this correct?
	//print('$equivalence_string, $fractal_string before substring operations in fractally_process_string: ');var_dump($equivalence_string, $fractal_string);
	//$processed_a_subtring_operation = true;
	//while(strpos($equivalence_string, '<') !== false && $processed_a_subtring_operation) {	
	//	$processed_a_subtring_operation = false;
	//fractal_zip::warning_once('HACK');
	//$equivalence_string = str_replace('<2"5>', '<2"6>', $equivalence_string);
	//$debug_counter = 0;
	
	$this->substring_cache = array();
	$equivalence_string = fractal_zip::recursive_substring_replace($equivalence_string, $fractal_string);
	
	/*if(preg_match('/<[0-9]/is', $equivalence_string) === 1) { // do substring operations first
		$substring_offset = -1; // initialization
		//print('uhhh0001<br>');
		while(is_numeric($substring_offset)) {
			//print('uhhh0002<br>');
			if(!fractal_zip::is_fractally_clean_for_unzip($equivalence_string)) {
				fractal_zip::warning('really bad! !fractal_zip::is_fractally_clean_for_unzip($equivalence_string) in fractally_process_string probably because a substring operator is busting into another substring operator! returning an empty string!');
				return '';
			}
			preg_match('/<([0-9]+)"([0-9]+)"*([0-9]*)\**([0-9]*)s*([0-9\.]*)>/is', $equivalence_string, $substring_operation_matches, PREG_OFFSET_CAPTURE); // would a parser be faster? optimize later
			// this could also be recursive (fractal?) function
			//print('$equivalence_string, $substring_operation_matches: ');var_dump($equivalence_string, $substring_operation_matches);
			$substring_offset = $substring_operation_matches[1][0];
			$substring_length = $substring_operation_matches[2][0];
			$substring_recursion_counter = $substring_operation_matches[3][0]; // what should be the order of the following markers?
			//print('$substring_offset, $substring_recursion_counter: ');var_dump($substring_offset, $substring_recursion_counter);
			$substring_tuple = $substring_operation_matches[4][0];
			$substring_scale = $substring_operation_matches[5][0];
			$substring = substr($fractal_string, $substring_offset, $substring_length);
			//print('$substring: ');var_dump($substring);
			if($substring_recursion_counter > 1) {
				//print('uhhh0003<br>');
				$substring = preg_replace('/<([0-9]+)"([0-9]+)>/is', '<$1"$2"' . ($substring_recursion_counter - 1) . '>', $substring);
				//print('$equivalence_string after processing a substring operation: ');var_dump($equivalence_string);
		//		$processed_a_subtring_operation = true;
			} else {
				//print('uhhh0004<br>');
				$substring = preg_replace('/<([0-9]+)"([0-9]+)>/is', '', $substring);
			}
			$single_substring = $substring;
			while($substring_tuple > 1) {
				$substring = $substring . $single_substring;
				$substring_tuple--;
			}
			if($substring_scale !== '') {
				$substring_by_scale = '';
				$offset = 0;
				$counter = 0;
				while($offset < strlen($substring)) {
					$counter += $substring_scale;
					while($counter > 0) {
						$substring_by_scale .= $substring[$offset];
						$counter -= 1;
					}
					$offset++;
				}
				$substring = $substring_by_scale;
			}
			//$equivalence_string = substr($equivalence_string, 0, $substring_operation_matches[0][1]) . $substring . substr($equivalence_string, $substring_operation_matches[0][1] + strlen($substring_operation_matches[0][0]));
			$equivalence_string = str_replace($substring_operation_matches[0][0], $substring, $equivalence_string);
			//print('$equivalence_string after one substring operators processing loop: ');var_dump($equivalence_string);
			//$debug_counter++;
			//if($debug_counter > 500) {
			//	fractal_zip::fatal_error('$debug_counter > 500');
			//}
		}
	}*/
	//print('$equivalence_string, $fractal_string after substring operations in fractally_process_string: ');var_dump($equivalence_string, $fractal_string);
	//print('fractally_process_string001<br>');
	//while(strpos($equivalence_string, '<') !== false) {
	$debug_counter44 = 0;
	while(preg_match('/<[^r]/is', $equivalence_string) === 1) { // ignore replace operations at this step
		//print('fractally_process_string002<br>');
		$debug_counter44++;
		if($debug_counter44 > 48) {
			fractal_zip::fatal_error('$debug_counter44 > 48');
		}
		$new_string = '';
		//$changed_something = false;
		
		/*
		// <custom>akdls;faa;skdfkaslfjaldkfja;sldka;sldkffjdls;a;sasddsfsdfassdfdajkkl;jllj;kjkjllkdls;akfjdksla;fkdls;ajfkd;akdjslskdjf;aldkdjsls</custom>
		// even prior to generalized processing; just proving the possibility
		// fractal zipping was actually useful (185 ≤ 259)!
		// Time taken zipping folder: 0.0074958801269531 seconds.
		// 8 different tiles, 4x4 tiles, 16x16 map: 185 ≤ 259 71.4%
		// 4 different tiles, 4x4 tiles, 16x16 map: 136 ≤ 150 90.5ish%
		// 8 different tiles, 12x12 tiles, 16x16 map: 181 ≤ 371 48.8%
		// 8 different tiles, 4x4 tiles, 64x64 map: 193 ≤ 312 61.8% just quadrupled the first test which kind of skews...
		// there is more opportunity for compression with more variety, and bigger tiles, and bigger map in the structured content
		$custom = $O->_('custom');
		if(is_string($custom)) {
			$processed_string = '';
			$line = '';
			$counter = 0;
			$line_counter = 0;
			while($counter < strlen($custom)) {
				$line .= $custom[$counter] . $custom[$counter] . $custom[$counter] . $custom[$counter];
				//$line .= $custom[$counter] . $custom[$counter] . $custom[$counter] . $custom[$counter] . $custom[$counter] . $custom[$counter] . $custom[$counter] . $custom[$counter] . $custom[$counter] . $custom[$counter] . $custom[$counter] . $custom[$counter];
				$line_counter++;
				if($line_counter === 16) {
					$processed_string .= $line . $line . $line . $line;
					//$processed_string .= $line . $line . $line . $line . $line . $line . $line . $line . $line . $line . $line . $line;
					$line = '';
					$line_counter = 0;
				}
				$counter++;
			}
			return $processed_string;
		}
		// <repeat times="960">a</repeat>
		$repeats = $O->get_tagged('repeat');
		$counter = sizeof($repeats) - 1;
		while($counter > -1) {
			$string_to_repeat = $O->tagless($repeats[$counter]);
			$times_to_repeat = $O->get_attribute('times', $repeats[$counter]);
			$O->delete($repeats[$counter]);
			$new_string = '';
			$repeat_counter = 0;
			while($times_to_repeat > 0) {
				$new_string .= $string_to_repeat;
				$times_to_repeat--;
			}
			//print('$new_string, $O->code, $repeats, $O->context: ');var_dump($new_string, $O->code, $repeats, $O->context);
			//$O->new_($new_string, $repeats[$counter][1]);
			$O->code = O::str_insert($O->code, $new_string, $repeats[$counter][1]);
			$changed_something = true;
			$O->reset_context(); // hack
			//print('$new_string, $O->code, $repeats, $O->context after new_: ');var_dump($new_string, $O->code, $repeats, $O->context);
			$counter--;
		}
		// <replace string="abc" with="def" />
		$replaces = $O->get_tagged('replace');
		$counter = sizeof($replaces) - 1;
		while($counter > -1) {
			$string_to_replace = $O->get_attribute('string', $replaces[$counter]);
			$replacement = $O->get_attribute('with', $replaces[$counter]);
			$O->str_replace($string_to_replace, $replacement);
			$changed_something = true;
			$O->reset_context(); // hack
			$counter--;
		}
		// def<insert string="abc" />ghi
		
		*/
		
		//print('gradient1<br>');
		if(strpos($equivalence_string, '<g') !== false) { // gradient
			//print('gradient2<br>');
			fractal_zip::warning_once('excluding * from the end character is not general. maybe swap positions of end character and tuple');
			preg_match_all('/<g([^>]+)"([0-9]+)"([^\*>]+)\**([0-9]{0,})>/is', $equivalence_string, $gradient_operation_matches, PREG_OFFSET_CAPTURE);
			//print('$gradient_operation_matches: ');var_dump($gradient_operation_matches);
			$counter = sizeof($gradient_operation_matches[0]) - 1;
			while($counter > -1) {
				//print('gradient3<br>');
				$start_character = $gradient_operation_matches[1][$counter][0];
				$step = $gradient_operation_matches[2][$counter][0];
				$end_character = $gradient_operation_matches[3][$counter][0];
				$tuple = $gradient_operation_matches[4][$counter][0];
				$gradient_string = '';
				$character_counter = ord($start_character);
				while($character_counter <= ord($end_character)) {
					//print('gradient4<br>');
					//fractal_zip::warning_once('htmlentities usage will have to be generalized');
					//$gradient_string .= htmlentities(chr($character_counter));
					$gradient_string .= chr($character_counter);
					$character_counter += $step;
				}
				$single_gradient_string = $gradient_string;
				while($tuple > 1) {
					$gradient_string = $gradient_string . $single_gradient_string;
					$tuple--;
				}
				$equivalence_string = substr($equivalence_string, 0, $gradient_operation_matches[0][$counter][1]) . $gradient_string . substr($equivalence_string, $gradient_operation_matches[0][$counter][1] + strlen($gradient_operation_matches[0][$counter][0]));
				$counter--;
			}
		}
		//print('$equivalence_string after gradient: ');var_dump($equivalence_string);
		if(preg_match('/<l([0-9]+)>/is', $equivalence_string, $row_length_operation_matches)) {
			//fractal_zip::warning_once('forcing row length of 9');
			//$row_length = 9;
			$row_length = $row_length_operation_matches[1]; // dirty hack?
			//$found_row_length = false;
			$equivalence_string = substr($equivalence_string, strlen($row_length_operation_matches[0]));
			print('$row_length, $equivalence_string: ');var_dump($row_length, $equivalence_string);
		}
		//print('$equivalence_string after row length: ');var_dump($equivalence_string);
		//preg_match_all('/<[^r][^<>]+>/is', $equivalence_string, $operation_matches, PREG_OFFSET_CAPTURE);
		preg_match_all('/<[s][^<>]+>/is', $equivalence_string, $operation_matches, PREG_OFFSET_CAPTURE); // only skip; substring is handled above?
		//print('$operation_matches: ');var_dump($operation_matches);
		if(sizeof($operation_matches[0]) > 0) {
			$equivalence_offset = 0;
			//$skips_array = array();
			$delayed_strings = array();
			foreach($operation_matches[0] as $index => $value) {
				//print('fractally_process_string003<br>');
				$operation_string = $value[0];
				$operation_offset = $value[1];
				// add any straight text first
				/*if($operation_offset > $equivalence_offset) {
					$raw_text = substr($equivalence_string, $equivalence_offset, $operation_offset - $equivalence_offset);
					//if(isset($skips_array[$parser_offset])) { // ignoring the amount to skip may be ok?
					//if(isset($skips_array[$parser_offset])) {
					//	//unset($skips_array[$parser_offset]);
					//	if($skips_array[$parser_offset] !== true) {
					//		$skips_array[$parser_offset] = true;
					//	} else {
					//		$new_string .= $raw_text;
					//	}
					//} else {
						$new_string .= $raw_text;
					//}
					$equivalence_offset += strlen($raw_text);
					print('$new_string, $delayed_strings after adding text before the operation: ');var_dump($new_string, $delayed_strings);
				}*/
				if($operation_string[1] === 's') { // skip
					// take care of all skip operations then go back to checking for operations
					$equivalence_string = substr($equivalence_string, $equivalence_offset);
					//$equivalence_offset = 0; // would prefer to cleverly step the offset back; but is that possible?
					print('$new_string, $equivalence_string before skipping: ');var_dump($new_string, $equivalence_string);
					$rows = array();
					while(strlen($equivalence_string) > 0) { // is this correct?
						$position = 0;
						preg_match('/<s([0-9]+)>/is', $equivalence_string, $skip_operation_matches); // would a parser be faster? optimize later
						$skip_counter = $skip_operation_matches[1];
						$tile = '';
						while($position < strlen($equivalence_string) && $skip_counter > 0) {
							if($equivalence_string[$position] === '<') {
								$tile .= substr($equivalence_string, $position, strpos($equivalence_string, '>', $position) + 1 - $position);
								$position = strpos($equivalence_string, '>', $position);
							} else {
								$tile .= $equivalence_string[$position];
								$skip_counter--;
							}
							$position++;
						}
						// also take operations following
					//	if($equivalence_string[$offset] === '<') {
					//		$moved_text .= substr($equivalence_string, $offset, strpos($equivalence_string, '>', $offset) + 1 - $offset);
					//	}
						fractal_zip::warning_once('forcing two-dimensional rectangle for skipping in unzip');
						fractal_zip::warning_once('forcing only uniform skip operations in unzip');
						$tile_pieces = explode($skip_operation_matches[0], $tile);
						$row_index = 0;
						$found_a_row_with_space = false;
						foreach($rows as $row_index => $row) {
							if(strlen($row) < $row_length) {
								$found_a_row_with_space = true;
								break;
							}
						}
						if(!$found_a_row_with_space) {
							$row_index = sizeof($rows);
						}
						foreach($tile_pieces as $tile_piece) {
							$rows[$row_index] .= $tile_piece;
							$row_index++;
						}
						print('$rows: ');fractal_zip::var_dump_full($rows);
						$equivalence_string = substr($equivalence_string, $position);
					}
					$new_string .= implode('', $rows);
					print('$new_string at the end of skipping: ');var_dump($new_string);
					break;
				}/* else { // substring (coded as offset-length pairs) is the default operation
					preg_match('/<([0-9]+)"([0-9]+)"*([0-9]*)>/is', $operation_string, $substring_operation_matches); // would a parser be faster? optimize later
					$substring_offset = $substring_operation_matches[1];
					$substring_length = $substring_operation_matches[2];
					$substring_recursion_counter = $substring_operation_matches[3];
					$new_string .= substr($fractal_string, $substring_offset, $substring_length);
				}*/
				$equivalence_offset += strlen($operation_string);
				//$changed_something = true;
			}
			$equivalence_string = $new_string;
		}
		//print('$equivalence_string after one fractally process loop: ');var_dump($equivalence_string);
	}
	// seem to need to do spanning operations later than self-cloing operations
	if(strpos($equivalence_string, '<r') !== false) {
		preg_match_all('/<r([^"]+)"([^>]+)>(.*?)<\/r>/is', $equivalence_string, $replace_operation_matches, PREG_OFFSET_CAPTURE); // do we need LOM since there is nesting structure?
		$counter = sizeof($replace_operation_matches[0]) - 1;
		while($counter > -1) {
			$search = $replace_operation_matches[1][$counter][0];
			$replace = $replace_operation_matches[2][$counter][0];
			$subject = $replace_operation_matches[3][$counter][0];
			$replaced_string = str_replace($search, $replace, $subject);
			$equivalence_string = substr($equivalence_string, 0, $replace_operation_matches[0][$counter][1]) . $replaced_string . substr($equivalence_string, $replace_operation_matches[0][$counter][1] + strlen($replace_operation_matches[0][$counter][0]));
			$counter--;
		}
	}
	
	//return $O->code;
	//return $new_string;
	//fractal_zip::warning_once('html_entity_decode usage will have to be generalized');
	//$equivalence_string = html_entity_decode($equivalence_string);
	//$equivalence_string = htmlspecialchars_decode($equivalence_string);
	return $equivalence_string;
}
	
function zip($string, $entry_filename, $debug = false) {
	//print('$entry_filename: ');var_dump($entry_filename);
	$this->entry_filename = $entry_filename;
	print('zipping: ' . $entry_filename . '<br>');
	// attempt to section the file
	// if an AI could be called to notice patterns and section the string, here would be where to do it
	if($debug) {
		print('$string: ');var_dump($string);
	}
	//fractal_zip::warning_once('specific hack; test recursion = 7');
	//$this->fractal_string = $fractal_string;
	//$this->equivalences[] = array($string, $entry_filename, $zipped_string);
	//return true;
	
	/*$byte_lengths = array(1, 2, 4); // 8, 16 and 32-bit covers most file formats
	$byte_length_densities = array();
	foreach($byte_lengths as $byte_length) {
		$offset = 0;
		while($offset < $byte_length) {
			$characters = str_split(substr($string, $offset), $byte_length);
			$characters_counts = array();
			foreach($characters as $character) {
				if(isset($characters_counts[$character])) {
					$characters_counts[$character]++;
				} else {
					$characters_counts[$character] = 1;
				}
			}
			//print('$characters: ');var_dump($characters);
			$densities = array();
			foreach($characters_counts as $character => $count) {
				$densities[$character] = $count / strlen($string);
			}
			//print('$densities: ');var_dump($densities);
			if(isset($byte_length_densities[$byte_length])) {
				if(sizeof($densities) < sizeof($byte_length_densities[$byte_length])) {
					$byte_length_densities[$byte_length] = $densities;
				}
			} else {
				$byte_length_densities[$byte_length] = $densities;
			}
			$offset++;
		}
	}
	print('$byte_length_densities: ');var_dump($byte_length_densities);
	$significant_items = array();
	$major_item = false;
	foreach($byte_length_densities as $byte_length => $densities) {
		foreach($densities as $item => $density) {
			if($density > 0.5) {
				$significant_items[$item] = 'major';
				$major_item = $item;
			} elseif($density > 0.02) {
				$significant_items[$item] = 'minor';
			}
		}
	}
	print('$significant_items: ');var_dump($significant_items);
	if($major_item) {
		foreach($significant_items as $significant_item => $item_type) {
			if($item_type === 'minor' && substr_count($significant_item, $major_item) === strlen($significant_item)) {
				unset($significant_items[$significant_item]);
			}
		}
	}
	$significant_items = array_reverse($significant_items, true);
	print('modified $significant_items: ');var_dump($significant_items);
	$sections = array();
	$counter = 0;
	$section = '';
	$section_item_type = false;
	$minor_item = false;
	while($counter < strlen($string)) {
		if($section_item_type === false) {
			foreach($significant_items as $significant_item => $item_type) {
				if(substr($string, $counter, strlen($significant_item)) == $significant_item) {
					if($item_type === 'major') {
						$sections[] = array($section, false);
						$section = '';
						$section_item_type = 'major';
					}
					break;
				}
			}
		} elseif($section_item_type === 'major') {
			foreach($significant_items as $significant_item => $item_type) {
				if(substr($string, $counter, strlen($significant_item)) == $significant_item) {
					if($item_type === 'minor') {
						$sections[] = array($section, 'major');
						$section = $significant_item;
						$section_item_type = 'minor';
						$minor_item = $significant_item;
					} elseif($item_type === 'major') {
						$section .= $significant_item;
					}
					$counter += strlen($significant_item);
					continue 2;
				}
			}
			if($string[$counter] != $major_item) {
				$sections[] = array($section, 'major');
				$section = '';
				$section_item_type = false;
			}
		} elseif($section_item_type === 'minor') {
			foreach($significant_items as $significant_item => $item_type) {
				if(substr($string, $counter, strlen($significant_item)) == $significant_item) {
					if($item_type === 'minor') {
						$found_minor_item = $significant_item;
						if($found_minor_item == $minor_item) {
							$section .= $significant_item;
						} else {
							$sections[] = array($section, 'minor');
							$section = $significant_item;
							$minor_item = $found_minor_item;
						}
					} elseif($item_type === 'major') {
						$sections[] = array($section, 'minor');
						$section = $significant_item;
						$section_item_type = 'major';
					}
					$counter += strlen($significant_item);
					continue 2;
				}
			}
		}
		$section .= $string[$counter];
		$counter++;
	}
	$sections[] = array($section, $section_item_type);
	print('$sections: ');fractal_zip::var_dump_full($sections);*/
	
	// no significant difference in speed between these two
	//$characters_counts = count_chars($string, 1);
	/*$characters = str_split($string, 1);
	$characters_counts = array();
	foreach($characters as $character) {
		if(isset($characters_counts[$character])) {
			$characters_counts[$character]++;
		} else {
			$characters_counts[$character] = 1;
		}
	}*/
	//print('$characters_counts: ');fractal_zip::var_dump_full($characters_counts);
	// could theoretically create a handle on various filetypes this way...
	/*if(strpos($string, '<!DOCTYPE') !== false || strpos($string, '<html') !== false || strpos($string, '</p>') !== false) { // righteous hack
		print('treating this file as HTML<br>');
		$counter = 0;
		$offsets_to_split_at = array();
		while($counter < strlen($string)) {
			if($string[$counter] === '<') {
				$offsets_to_split_at[] = $counter;
			} elseif($string[$counter] === '>') {
				if($string[$counter + 1] === '<') {
					
				} else {
					$offsets_to_split_at[] = $counter + 1;
				}
			}
			$counter++;
		}
	} else {
		$limiters_sum = 0;
		foreach($this->common_limiters as $this->common_limiter) {
			$limiters_sum += substr_count($string, $this->common_limiter);
		}
		print('$limiters_sum, strlen($string): ');fractal_zip::var_dump_full($limiters_sum, strlen($string));
		if($limiters_sum / strlen($string) > 0.02 && $limiters_sum / strlen($string) < 0.2) {
			print('handling this file by breaking at the limiters<br>');
			$counter = 0;
			$offsets_to_split_at = array();
			while($counter < strlen($string)) {
				foreach($this->common_limiters as $this->common_limiter) {
					if($string[$counter] === $this->common_limiter) {
						$offsets_to_split_at[] = $counter;
					}
				}
				$counter++;
			}
		} else {
			$counter = 0;
			//$buffer = '';
			//$max_buffer_length = 10;
			$offsets_to_split_at = array();
			//$last_offset = 0;
			while($counter < strlen($string)) {
				//if(strlen($buffer) < $max_buffer_length) {
				//	$buffer .= $string[$counter];
				//} else {
					//if((strpos($buffer, $string[$counter]) === false ||
					//fractal_zip::density($string[$counter], $buffer) > 2 * fractal_zip::density($string[$counter], substr($string, 0, $max_buffer_length)) ||
					//fractal_zip::density($string[$counter], $buffer) < 0.5 * fractal_zip::density($string[$counter], substr($string, 0, $max_buffer_length)))
					//&& $counter - $last_offset > $max_buffer_length) {
					if(substr($string, $counter - 4, 4) === substr($string, $counter, 4)) {
						$counter += 4;
						continue;
					} elseif(substr($string, $counter, 4) === substr($string, $counter + 4, 4) && substr($string, $counter - 4, 4) != substr($string, $counter, 4)) {
						$offsets_to_split_at[] = $counter;
						$counter += 4;
						continue;
					} elseif(substr($string, $counter - 2, 2) === substr($string, $counter, 2)) {
						$counter += 2;
						continue;
					} elseif(substr($string, $counter, 2) === substr($string, $counter + 2, 2) && substr($string, $counter - 2, 2) != substr($string, $counter, 2)) {
						$offsets_to_split_at[] = $counter;
						$counter += 2;
						continue;
					} elseif(substr($string, $counter, 1) === substr($string, $counter + 1, 1) && substr($string, $counter - 1, 1) != $string[$counter]) {
						$offsets_to_split_at[] = $counter;
						//$last_offset = $counter;
					}
					//$buffer = substr($buffer, 1) . $string[$counter];
				//}
				$counter++;
			}
		}
	}
	$sections = array();
	$last_offset = 0;
	foreach($offsets_to_split_at as $offset) {
		$sections[] = array(substr($string, $last_offset, $offset - $last_offset), '?');
		$last_offset = $offset;
	}
	print('$sections: ');fractal_zip::var_dump_full($sections);*/
	//print('sizeof($sections): ');fractal_zip::var_dump_full(sizeof($sections));
	// the lazyiest possible fractal string processing; we'll assume that compression will handle the redundancy created rather than trying to craft the fractal string in such a way that useful pieces are prevalent
	
	// making containers containing containers becomes complicated when you consider escaping the data
	// also we have to prevent the trivial solution of referring to the unzipped version of a file being the simplest solution since that solution no longer works when that file is moved or deleted
	// which brings up the point that we'll have to ensure that this doesn't happen for any fractal_zipped files by creating a container (so that the pieces may not be manipulated)
	
	//$string = htmlspecialchars($string); // always; instead of trying to manage when
	//$zipped_string = htmlspecialchars($string); // always; instead of trying to manage when
	$zipped_string = htmlspecialchars($string, ENT_COMPAT, 'ISO-8859-1', true);
	//print('$string, strlen($string), $zipped_string, strlen($zipped_string): ');var_dump($string, strlen($string), $zipped_string, strlen($zipped_string));exit(0);
	// we could of course get fancy and do various things; including using higher base number to save characters and using replace operations under certain insertion and deletion string length conditions
	//$this->lazy_fractal_strings[$this->files_counter] = $string;
	//if(strlen($string) === 1) {
		//$lazy_fractal_zipped_string = strlen($this->lazy_fractal_string);
		//$lazy_fractal_zipped_string = $this->left_fractal_zip_marker . $this->fractal_zipping_pass . $this->mid_fractal_zip_marker . strlen($this->lazy_fractal_string) . $this->mid_fractal_zip_marker . $this->fractal_zipping_pass . $this->right_fractal_zip_marker;
	//	$lazy_fractal_zipped_string = fractal_zip::mark_range_string(strlen($this->lazy_fractal_string));
	//} else {		
		//$end_offset = strlen($this->lazy_fractal_string) + strlen($string) - 1;
		//$lazy_fractal_zipped_string = strlen($this->lazy_fractal_string) . '-' . $end_offset;
		//$lazy_fractal_zipped_string = $this->left_fractal_zip_marker . $this->fractal_zipping_pass . $this->mid_fractal_zip_marker . strlen($this->lazy_fractal_string) . '-' . $end_offset . $this->mid_fractal_zip_marker . $this->fractal_zipping_pass . $this->right_fractal_zip_marker;
		//$lazy_fractal_zipped_string = fractal_zip::mark_range_string(strlen($this->lazy_fractal_string) . '-' . $end_offset);
		//$lazy_fractal_zipped_string = '<' . strlen($this->lazy_fractal_string) . '"' . $end_offset . '>';
		//$lazy_fractal_zipped_string = '<' . strlen($this->lazy_fractal_string) . '"' . strlen($string) . '>';
		//$lazy_zipped_string = '<' . strlen($this->lazy_fractal_string) . '"' . strlen($string) . '>';
		$lazy_zipped_string = '<' . strlen($this->lazy_fractal_string) . '"' . strlen($zipped_string) . '>';
	//}
	//print('$lazy_fractal_zipped_string: ');var_dump($lazy_fractal_zipped_string);
	//$this->lazy_fractal_string .= $string;
	$this->lazy_fractal_string .= $zipped_string;
	//$this->lazy_equivalences[] = array($string, $entry_filename, $lazy_fractal_zipped_string);
	$this->lazy_equivalences[] = array($string, $entry_filename, $lazy_zipped_string);
	//if(sizeof($this->fractal_strings) === 2) {
	//	print('debug 1: more than 2 files "zipped"<br>');print('$this->fractal_strings, $this->equivalences, $this->branch_counter in zip: ');var_dump($this->fractal_strings, $this->equivalences, $this->branch_counter);exit(0);
	//}
	// straight adding the whole string to fractal_strings
	//$this->fractal_strings[] = array($this->branch_counter, $string); // branch_id, fractal_string
	//$this->equivalences[] = array($string, $entry_filename, $this->branch_counter); // filename, string, fractal zipped expression
	//$this->branch_counter++;
	
	// true fractal zip
	if(true) {
		print('forcing true fractal processing<br>');
		fractal_zip::warning_once('there\'s code above relating to different file formats and row length');
		fractal_zip::warning_once('$this->improvement_factor_threshold');
		
		/*fractal_zip::warning_once('hard-coded recursive fractal substring');
		$this->fractal_string .= 'a<12"17>aaaabb<0"12>b<0"12>bb';
		//$this->equivalences[] = array($string, $entry_filename, 'a<12"17"4>aaaa');
		$recursion_counter = 0;
		//$zipped_string = $string;
		$did_something = true;
		while($did_something) {
			$did_something = false;
			// hard-code the two options
			$fractal_substring = substr($this->fractal_string, 0, 12);
			if($recursion_counter === 0) {
				$fractal_substring = preg_replace('/<[0-9]+"[0-9]+>/is', '', $fractal_substring);
			}
			$zipped_string = str_replace($fractal_substring, '<0"12>', $zipped_string, $count);
			if($count > 0) {
				$did_something = true;
			}
			
			$fractal_substring = substr($this->fractal_string, 12, 17);
			if($recursion_counter === 0) {
				$fractal_substring = preg_replace('/<[0-9]+"[0-9]+>/is', '', $fractal_substring);
			}
			$zipped_string = str_replace($fractal_substring, '<12"17>', $zipped_string, $count);
			if($count > 0) {
				$did_something = true;
			}
			
			print('$this->fractal_string, $zipped_string, $recursion_counter: ');var_dump($this->fractal_string, $zipped_string, $recursion_counter);
			$recursion_counter++;
		}
		// really ugly; assuming that everything fits into a single expression
		$zipped_string = str_replace('>', '"' . $recursion_counter . '>', $zipped_string);
		print('$this->fractal_string, $string, $recursion_counter: ');var_dump($this->fractal_string, $string, $recursion_counter);*/
		
		//$zipped_string = $string;
		//$zipped_string = htmlspecialchars($string);
		//print('$zipped_string before gradient: ');var_dump($zipped_string);
		fractal_zip::warning_once('gradient disabled since it is a real hog! as the code stands, we cannot handle code of any signficant length!');
		/*
		// gradient
		//fractal_zip::warning_once('disabled gradient tuples to get substring tuples');
		//fractal_zip::warning_once('hard gradient hack');
		//$zipped_string = '<g0"1"~>';
		$gradient_expressions = array();
		$steps_array = array(1, 2, 3, 4, 8, 16, 32, 64); // roughly intended to correspond to byte index stepping values arising in common data structures (or a guess)
		$maximum_gradient_expression_length = strlen('<gA"64"B>'); // multibyte gradients?
		$offset = 0;
		while($offset < strlen($zipped_string)) {
			$step_counter = 0;
			while($step_counter < sizeof($steps_array)) {
				$sliding_offset = $offset + 1;
				while(ord($zipped_string[$sliding_offset - 1]) === ord($zipped_string[$sliding_offset]) - $steps_array[$step_counter] && $sliding_offset < strlen($zipped_string)) {
					$sliding_offset++;
				}
				if($sliding_offset - $offset > $maximum_gradient_expression_length) {
					$gradient_expression = '<g' . $zipped_string[$offset] . '"' . $steps_array[$step_counter] . '"' . $zipped_string[$sliding_offset - 1] . '>';
					$gradient_expressions[$gradient_expression] = true;
					$zipped_string = substr($zipped_string, 0, $offset) . $gradient_expression . substr($zipped_string, $sliding_offset);
					$offset += strlen($gradient_expression);
					continue 2;
				}
				$step_counter++;
			}
			$offset++;
		}*/
		//print('$zipped_string before simplifying: ');var_dump($zipped_string);exit(0);
		fractal_zip::warning_once('tuples disabled since not all code expects them, example: aaaaa<20"25"4>aaaaaaaa, aaaaa<20"25>aaaaaaaabbbbbb<0"20>b<0"20>bbbbbb');
	//	foreach($gradient_expressions as $gradient_expression => $true) {
	//		$zipped_string = fractal_zip::tuples($zipped_string, $gradient_expression);
	//	}		
		//print('$zipped_string after simplifying: ');var_dump($zipped_string);
		//if(strpos($zipped_string, '<g') !== false) {
		//	
		//}
		//$this->fractal_string = '';
		//$this->equivalences[] = array($string, $entry_filename, $zipped_string);
		//return true;
		//print('$this->fractal_string, $zipped_string after gradient: ');var_dump($this->fractal_string, $zipped_string);
		
		//fractal_zip::warning_once('forcing empty $this->fractal_string');
		//$this->fractal_string = '';
		//$fractal_substring_result = fractal_zip::fractal_substring($string);
		$fractal_substring_result = fractal_zip::fractal_substring($zipped_string);
	//	print('$fractal_substring_result: ');var_dump($fractal_substring_result);exit(0);
		$fractal_string = $fractal_substring_result[0];
		$zipped_string = $fractal_substring_result[1];
		//$this->fractal_string .= $fractal_string;
		$this->fractal_string = $fractal_string;
		//print('$fractal_string, $this->fractal_string, $zipped_string after fractal_substring: ');var_dump($fractal_string, $this->fractal_string, $zipped_string);
		
		// scale
		//fractal_zip::warning_once('enable scale zipping without hard-coded fractal_string');
		//fractal_zip::warning_once('hard scale hack. it would be complicated to incorporate scaling into the fractal substring code');
		//fractal_zip::warning_once('h4rd c0d|ng');
		//$this->fractal_string = 'aaabbccbbaaa';
		//$longest_continuous = 'aaaaaaaaaaaaaaaaaaaaaaaa';
		$repeat_chunks = array();
		$offset = 0;
		$did_first = false;
		$chunk = '';
		while($offset < strlen($zipped_string)) {
			if($did_first) {
				if($zipped_string[$offset] === $zipped_string[$offset - 1]) {
					
				} else {
					$repeat_chunks[] = $chunk;
					$chunk = '';
				}
			} else {
				$did_first = true;
			}
			$chunk .= $zipped_string[$offset];
			$offset++;
		}
		$repeat_chunks[] = $chunk;
		//print('$repeat_chunks: ');var_dump($repeat_chunks);
		$counter = 0;
		foreach($repeat_chunks as $chunk) {
			if(strlen($chunk) > $counter) {
				$counter = strlen($chunk);
			}
		}
		$best_counter = false;
		$best_sum = 0;
		while($counter > 0) {
			$sum = 0;
			foreach($repeat_chunks as $chunk) {
				if(strlen($chunk) % $counter === 0) {
					$sum += strlen($chunk);
				}
			}
			if($sum * sqrt($counter) > $best_sum * sqrt($best_counter)) { // pulling sqrt out of my ass
				$best_sum = $sum;
				$best_counter = $counter;
			}
			$counter--;
		}
		//print('$best_sum, $best_counter: ');var_dump($best_sum, $best_counter);
		/*$highest_common_factor = 0;
		$common_factor_counter = 0;
		while($common_factor_counter > -1) {
			$all_satisfied_by_this_factor = true;
			foreach($repeat_chunks as $chunk) {
				if(strlen($chunk) % $best_counter === 0) {
					if(strlen($chunk) % $common_factor_counter === 0) {
						
					} else {
						$all_satisfied_by_this_factor = false;
						break;
					}
				}
			}
			if($all_satisfied_by_this_factor) {
				$highest_common_factor = $common_factor_counter;
			}
			$common_factor_counter--;
		}*/
		$highest_common_factor = $best_counter;
		//print('$highest_common_factor: ');var_dump($highest_common_factor);
		if($highest_common_factor > 1) {
			$string_from_dividing_repeats_by_scale = '';
			foreach($repeat_chunks as $chunk) {
				$character_counter = floor(strlen($chunk) / $highest_common_factor);
				while($character_counter > 0) {
					$string_from_dividing_repeats_by_scale .= $chunk[0];
					$character_counter--;
				}
			}
			$this->fractal_string .= $string_from_dividing_repeats_by_scale;
			//print('$string_from_dividing_repeats_by_scale, $this->fractal_string: ');var_dump($string_from_dividing_repeats_by_scale, $this->fractal_string);
			//$zipped_string = '<0"12s0.25><0"12s0.5><0"12s2><0"12s8>';
			$offset = 0;
			//print('scale zip 1<br>');
			while($offset < strlen($zipped_string)) {
				//print('scale zip offset: ' . $offset . '<br>');
				//$scale = 1;
				$scaled_piece = '';
				$sliding_offset = $offset;
				$fractal_string_offset = 0; // bad
				while($zipped_string[$sliding_offset] === $this->fractal_string[$fractal_string_offset] && $sliding_offset < strlen($zipped_string) && $fractal_string_offset < strlen($this->fractal_string)) {
					//print('scale zip 3<br>');
					$scaled_piece .= $zipped_string[$sliding_offset];
					$sliding_offset++;
					$fractal_string_offset++;
				}
				if($sliding_offset === $offset) {
					//print('scale zip 3.9<br>');
					$offset++;
					continue;
				}
				if($zipped_string[$sliding_offset + 1] === $zipped_string[$sliding_offset] && $sliding_offset < strlen($zipped_string)) { // look to use a greater than 1 scale
					//print('scale zip 4<br>');
					while($zipped_string[$sliding_offset + 1] === $zipped_string[$sliding_offset] && $sliding_offset < strlen($zipped_string)) {
						//print('scale zip 5.5<br>');
						$sliding_offset++;
					}
					//print('$fractal_string_offset, $sliding_offset, $offset: ');var_dump($fractal_string_offset, $sliding_offset, $offset);
					//$scale = ($fractal_string_offset + 2) / ($sliding_offset - $offset);
				} else { // look to use a less than 1 scale (does not seem to work)
					//print('scale zip 5<br>');
					while($this->fractal_string[$fractal_string_offset + 1] === $this->fractal_string[$fractal_string_offset] && $fractal_string_offset < strlen($this->fractal_string)) {
						//print('scale zip 4.5<br>');
						$fractal_string_offset++;
					}
				}
				$scale = ($sliding_offset - $offset + 1) / $fractal_string_offset;
				$scaled_expression = '<0"' . strlen($this->fractal_string) . 's' . round($scale, 4) . '>';
				$scaled_piece = fractal_zip::fractally_process_string($scaled_expression); // bad
				//print('$scaled_expression, $scaled_piece before checking if they are good: ');var_dump($scaled_expression, $scaled_piece);
				if(substr($zipped_string, $offset, strlen($scaled_piece)) === $scaled_piece) {
					//print('scale zip 6<br>');
				} else {
					//print('scale zip 7<br>');
					$offset++;
					continue;
				}
				//print('scale zip 8<br>');
				//if(strlen($scaled_piece) > fractal_zip::maximum_scale_expression_length()) {
				if(strlen($scaled_piece) > strlen($scaled_expression)) {
					//print('scale zip 9<br>');
					$zipped_string = substr($zipped_string, 0, $offset) . $scaled_expression . substr($zipped_string, $offset + strlen($scaled_piece));
					$offset += strlen($scaled_expression);
					continue;
				}
				$offset++;
			}
		}
		//print('$this->fractal_string, $zipped_string after scale: ');var_dump($this->fractal_string, $zipped_string);
		//$this->fractal_string = '';
		//$this->equivalences[] = array($string, $entry_filename, $zipped_string);
		//return true;
		
		
		
		
		$this->equivalences[] = array($string, $entry_filename, $zipped_string);
		return true;
		
		//print('$string: ');var_dump($string);exit(0);
		$scores = array();
		//fractal_zip::warning_once('forcing row length of 9 in zip');
		//$row_length = 9;
		//$row_length = 1;
		//fractal_zip::warning_once('forcing tile width of 3 in zip');
		//$tile_width = 3;
		// need some cleverness...
	//	$debug_counter = 0;
		$tile_width = 2; // has to be more than one to be considered a tile
		while($tile_width < strlen($string)) { // really??
			//fractal_zip::warning_once('forcing tile height of 5 in zip');
			//$tile_height = 5;
			$tile_height = 2;
			while($tile_height < strlen($string)) { // really??
				if(strlen($string) === $tile_width * $tile_height) { // avoid the trivial solution of a single tile for the whole code
					$tile_width++;
					continue 2;
				}
				$row_length = $tile_width;
				//$row_length = $tile_width * $tile_height; // hacky?
				while($row_length < strlen($string)) {
					if($row_length < $tile_width) {
						continue;
					}
					//print('$row_length: ');var_dump($row_length);
					$rows = array();
					$offset = 0;
					$column = 0;
					$row = 0;
					while($offset < strlen($string)) {
						$rows[$row] .= $string[$offset];
						$offset++;
						$column++;
						if($column === $row_length) {
							$column = 0;
							$row++;
						}
					}
					fractal_zip::warning_once('forcing all rows to be the same length (which makes sense for the non-fractal dimension (2) that is forced) but there is no allowance for only analyzing part of the string for 2-dimensionality');
					//foreach($rows as $row_string) {
					//	if(strlen($row_string) !== $row_length) {
					//		$row_length += $tile_width;
					//		continue 2;
					//	}
					//}
					if(strlen($rows[sizeof($rows) - 1]) !== $row_length) {
						$row_length += $tile_width;
						continue;
					}
					if(sizeof($rows) % $tile_height !== 0) { // column height does not make sense with tile height
						$row_length += $tile_width;
						continue;
					}
					//print('$rows: ');fractal_zip::var_dump_full($rows);
					$skipping_string = '';
					$column = 0;
					$width = 0;
					$row = 0;
					$height = 0;
					//$skipping_commands = 0;
					//$debug_counter = 0;
					// need to measure how good row length, tile width, tile height choices are
					while($row < sizeof($rows)) {
						while($height < $tile_height) {
							while($width < $tile_width) {
								$skipping_string .= $rows[$row][$column];
								$column++;
								$width++;
							}
							$row++;
							$height++;
							if($height < $tile_height) {
								if($skipping_string[strlen($skipping_string) - 1] === '>') { // shouldn't have consecutive skip statements
									$row_length += $tile_width;
									continue 3;
								}
								$skipping_string .= '<s' . $tile_width * $tile_height . '>';
								//$skipping_commands++;
								$column -= $tile_width;
								$width = 0;
							} else {
								$height = 0;
								$width = 0;
								if($column === $row_length) {
									$column = 0;
									continue 2;
								}
								$row -= $tile_height;
							}
						}
					}
					//print('$skipping_string: ');fractal_zip::var_dump_full($skipping_string);
						/*fractal_zip::warning_once('forcing skipped tile strlen of 35 just as a simple hack');
						$fractal_string = '';
						$zipped_string = '<l' . $row_length . '>';
						$offset = 0;
						$counter = 0;
						$piece = '';
						while($offset < strlen($skipping_string)) {
							$piece .= $skipping_string[$offset];
							$counter++;
							if($counter === 35) {
								$position = strpos($fractal_string, $piece);
								if($position === false) {
									$position = strlen($fractal_string);
									$fractal_string .= $piece;
								}
								$zipped_string .= '<' . $position . '"35>';
								$counter = 0;
								$piece = '';
							}
							$offset++;
						}*/
					$fractal_substring_result = fractal_zip::fractal_substring($skipping_string);
					print('$rows, $tile_width, $tile_height, $row_length, $skipping_string, $fractal_substring_result: ');var_dump($rows, $tile_width, $tile_height, $row_length, $skipping_string, $fractal_substring_result);
				//	$debug_counter++;
				//	if($debug_counter > 10) {
				//		fractal_zip::fatal_error('$debug_counter > 10');
				//	}
					$fractal_string = $fractal_substring_result[0];
					$zipped_string = $fractal_substring_result[1];
					$zipped_string = '<l' . $row_length . '>' . $zipped_string;
					if(strlen($fractal_string) === 0) {
						
					} else {
						//print('strlen($skipping_string), strlen($fractal_string), strlen($zipped_string): ');var_dump(strlen($skipping_string), strlen($fractal_string), strlen($zipped_string));
						//$scores[(string)(strlen($skipping_string) / (strlen($fractal_string) + strlen($zipped_string)))] = array($row_length, $fractal_string, $zipped_string);
						$scores[(string)(strlen($string) / (strlen($fractal_string) - strlen($this->fractal_string) + strlen($zipped_string)))] = array($tile_width, $tile_height, $row_length, $fractal_string, $zipped_string);
						
						
						//print('zipping $scores ugh: ');var_dump($scores);
						//$row_length++;
					}
					$row_length += $tile_width;
					//break; // hacky
				}
				$tile_height++;
			}
			$tile_width++;
		}
		ksort($scores);
		$scores = array_reverse($scores, true);
		print('zipping $scores: ');var_dump($scores);exit(0);
		
		//$this->fractal_string .= $fractal_string;
		$this->fractal_string = $fractal_string;
		$this->equivalences[] = array($string, $entry_filename, $zipped_string);
		
		//$this->equivalences[] = array('aaaakkkkddddllllssss;;;;ffffaaaaaaaa;;;;sssskkkkddddffffkkkkaaaaaaaakkkkddddllllssss;;;;ffffaaaaaaaa;;;;sssskkkkddddffffkkkkaaaaaaaakkkkddddllllssss;;;;ffffaaaaaaaa;;;;sssskkkkddddffffkkkkaaaaaaaakkkkddddllllssss;;;;ffffaaaaaaaa;;;;sssskkkkddddffffkkkkaaaassssllllffffjjjjaaaallllddddkkkkffffjjjjaaaa;;;;ssssllllddddkkkkssssllllffffjjjjaaaallllddddkkkkffffjjjjaaaa;;;;ssssllllddddkkkkssssllllffffjjjjaaaallllddddkkkkffffjjjjaaaa;;;;ssssllllddddkkkkssssllllffffjjjjaaaallllddddkkkkffffjjjjaaaa;;;;ssssllllddddkkkkaaaa;;;;ssssllllddddkkkkffffffffjjjjddddllllssss;;;;aaaa;;;;ssssaaaa;;;;ssssllllddddkkkkffffffffjjjjddddllllssss;;;;aaaa;;;;ssssaaaa;;;;ssssllllddddkkkkffffffffjjjjddddllllssss;;;;aaaa;;;;ssssaaaa;;;;ssssllllddddkkkkffffffffjjjjddddllllssss;;;;aaaa;;;;ssssaaaassssddddddddssssffffssssddddffffaaaassssssssddddffffddddaaaaaaaassssddddddddssssffffssssddddffffaaaassssssssddddffffddddaaaaaaaassssddddddddssssffffssssddddffffaaaassssssssddddffffddddaaaaaaaassssddddddddssssffffssssddddffffaaaassssssssddddffffddddaaaajjjjkkkkkkkkllll;;;;jjjjlllllllljjjj;;;;kkkkjjjjkkkkjjjjlllllllljjjjkkkkkkkkllll;;;;jjjjlllllllljjjj;;;;kkkkjjjjkkkkjjjjlllllllljjjjkkkkkkkkllll;;;;jjjjlllllllljjjj;;;;kkkkjjjjkkkkjjjjlllllllljjjjkkkkkkkkllll;;;;jjjjlllllllljjjj;;;;kkkkjjjjkkkkjjjjllllllllkkkkddddllllssss;;;;aaaakkkkffffjjjjddddkkkkssssllllaaaa;;;;ffffkkkkddddllllssss;;;;aaaakkkkffffjjjjddddkkkkssssllllaaaa;;;;ffffkkkkddddllllssss;;;;aaaakkkkffffjjjjddddkkkkssssllllaaaa;;;;ffffkkkkddddllllssss;;;;aaaakkkkffffjjjjddddkkkkssssllllaaaa;;;;ffffkkkkddddllllssss;;;;aaaajjjjffffkkkkdddd;;;;aaaakkkkddddjjjjsssskkkkddddllllssss;;;;aaaajjjjffffkkkkdddd;;;;aaaakkkkddddjjjjsssskkkkddddllllssss;;;;aaaajjjjffffkkkkdddd;;;;aaaakkkkddddjjjjsssskkkkddddllllssss;;;;aaaajjjjffffkkkkdddd;;;;aaaakkkkddddjjjjssssllllsssskkkkddddjjjjffff;;;;aaaallllddddkkkkddddjjjjssssllllssssllllsssskkkkddddjjjjffff;;;;aaaallllddddkkkkddddjjjjssssllllssssllllsssskkkkddddjjjjffff;;;;aaaallllddddkkkkddddjjjjssssllllssssllllsssskkkkddddjjjjffff;;;;aaaallllddddkkkkddddjjjjssssllllssssaaaakkkkddddllllssss;;;;ffffaaaaaaaa;;;;sssskkkkddddffffkkkkaaaaaaaakkkkddddllllssss;;;;ffffaaaaaaaa;;;;sssskkkkddddffffkkkkaaaaaaaakkkkddddllllssss;;;;ffffaaaaaaaa;;;;sssskkkkddddffffkkkkaaaaaaaakkkkddddllllssss;;;;ffffaaaaaaaa;;;;sssskkkkddddffffkkkkaaaassssllllffffjjjjaaaallllddddkkkkffffjjjjaaaa;;;;ssssllllddddkkkkssssllllffffjjjjaaaallllddddkkkkffffjjjjaaaa;;;;ssssllllddddkkkkssssllllffffjjjjaaaallllddddkkkkffffjjjjaaaa;;;;ssssllllddddkkkkssssllllffffjjjjaaaallllddddkkkkffffjjjjaaaa;;;;ssssllllddddkkkkaaaa;;;;ssssllllddddkkkkffffffffjjjjddddllllssss;;;;aaaa;;;;ssssaaaa;;;;ssssllllddddkkkkffffffffjjjjddddllllssss;;;;aaaa;;;;ssssaaaa;;;;ssssllllddddkkkkffffffffjjjjddddllllssss;;;;aaaa;;;;ssssaaaa;;;;ssssllllddddkkkkffffffffjjjjddddllllssss;;;;aaaa;;;;ssssaaaassssddddddddssssffffssssddddffffaaaassssssssddddffffddddaaaaaaaassssddddddddssssffffssssddddffffaaaassssssssddddffffddddaaaaaaaassssddddddddssssffffssssddddffffaaaassssssssddddffffddddaaaaaaaassssddddddddssssffffssssddddffffaaaassssssssddddffffddddaaaajjjjkkkkkkkkllll;;;;jjjjlllllllljjjj;;;;kkkkjjjjkkkkjjjjlllllllljjjjkkkkkkkkllll;;;;jjjjlllllllljjjj;;;;kkkkjjjjkkkkjjjjlllllllljjjjkkkkkkkkllll;;;;jjjjlllllllljjjj;;;;kkkkjjjjkkkkjjjjlllllllljjjjkkkkkkkkllll;;;;jjjjlllllllljjjj;;;;kkkkjjjjkkkkjjjjllllllllkkkkddddllllssss;;;;aaaakkkkffffjjjjddddkkkkssssllllaaaa;;;;ffffkkkkddddllllssss;;;;aaaakkkkffffjjjjddddkkkkssssllllaaaa;;;;ffffkkkkddddllllssss;;;;aaaakkkkffffjjjjddddkkkkssssllllaaaa;;;;ffffkkkkddddllllssss;;;;aaaakkkkffffjjjjddddkkkkssssllllaaaa;;;;ffffkkkkddddllllssss;;;;aaaajjjjffffkkkkdddd;;;;aaaakkkkddddjjjjsssskkkkddddllllssss;;;;aaaajjjjffffkkkkdddd;;;;aaaakkkkddddjjjjsssskkkkddddllllssss;;;;aaaajjjjffffkkkkdddd;;;;aaaakkkkddddjjjjsssskkkkddddllllssss;;;;aaaajjjjffffkkkkdddd;;;;aaaakkkkddddjjjjssssllllsssskkkkddddjjjjffff;;;;aaaallllddddkkkkddddjjjjssssllllssssllllsssskkkkddddjjjjffff;;;;aaaallllddddkkkkddddjjjjssssllllssssllllsssskkkkddddjjjjffff;;;;aaaallllddddkkkkddddjjjjssssllllssssllllsssskkkkddddjjjjffff;;;;aaaallllddddkkkkddddjjjjssssllllssssaaaakkkkddddllllssss;;;;ffffaaaaaaaa;;;;sssskkkkddddffffkkkkaaaaaaaakkkkddddllllssss;;;;ffffaaaaaaaa;;;;sssskkkkddddffffkkkkaaaaaaaakkkkddddllllssss;;;;ffffaaaaaaaa;;;;sssskkkkddddffffkkkkaaaaaaaakkkkddddllllssss;;;;ffffaaaaaaaa;;;;sssskkkkddddffffkkkkaaaassssllllffffjjjjaaaallllddddkkkkffffjjjjaaaa;;;;ssssllllddddkkkkssssllllffffjjjjaaaallllddddkkkkffffjjjjaaaa;;;;ssssllllddddkkkkssssllllffffjjjjaaaallllddddkkkkffffjjjjaaaa;;;;ssssllllddddkkkkssssllllffffjjjjaaaallllddddkkkkffffjjjjaaaa;;;;ssssllllddddkkkkaaaa;;;;ssssllllddddkkkkffffffffjjjjddddllllssss;;;;aaaa;;;;ssssaaaa;;;;ssssllllddddkkkkffffffffjjjjddddllllssss;;;;aaaa;;;;ssssaaaa;;;;ssssllllddddkkkkffffffffjjjjddddllllssss;;;;aaaa;;;;ssssaaaa;;;;ssssllllddddkkkkffffffffjjjjddddllllssss;;;;aaaa;;;;ssssaaaassssddddddddssssffffssssddddffffaaaassssssssddddffffddddaaaaaaaassssddddddddssssffffssssddddffffaaaassssssssddddffffddddaaaaaaaassssddddddddssssffffssssddddffffaaaassssssssddddffffddddaaaaaaaassssddddddddssssffffssssddddffffaaaassssssssddddffffddddaaaajjjjkkkkkkkkllll;;;;jjjjlllllllljjjj;;;;kkkkjjjjkkkkjjjjlllllllljjjjkkkkkkkkllll;;;;jjjjlllllllljjjj;;;;kkkkjjjjkkkkjjjjlllllllljjjjkkkkkkkkllll;;;;jjjjlllllllljjjj;;;;kkkkjjjjkkkkjjjjlllllllljjjjkkkkkkkkllll;;;;jjjjlllllllljjjj;;;;kkkkjjjjkkkkjjjjllllllllkkkkddddllllssss;;;;aaaakkkkffffjjjjddddkkkkssssllllaaaa;;;;ffffkkkkddddllllssss;;;;aaaakkkkffffjjjjddddkkkkssssllllaaaa;;;;ffffkkkkddddllllssss;;;;aaaakkkkffffjjjjddddkkkkssssllllaaaa;;;;ffffkkkkddddllllssss;;;;aaaakkkkffffjjjjddddkkkkssssllllaaaa;;;;ffffkkkkddddllllssss;;;;aaaajjjjffffkkkkdddd;;;;aaaakkkkddddjjjjsssskkkkddddllllssss;;;;aaaajjjjffffkkkkdddd;;;;aaaakkkkddddjjjjsssskkkkddddllllssss;;;;aaaajjjjffffkkkkdddd;;;;aaaakkkkddddjjjjsssskkkkddddllllssss;;;;aaaajjjjffffkkkkdddd;;;;aaaakkkkddddjjjjssssllllsssskkkkddddjjjjffff;;;;aaaallllddddkkkkddddjjjjssssllllssssllllsssskkkkddddjjjjffff;;;;aaaallllddddkkkkddddjjjjssssllllssssllllsssskkkkddddjjjjffff;;;;aaaallllddddkkkkddddjjjjssssllllssssllllsssskkkkddddjjjjffff;;;;aaaallllddddkkkkddddjjjjssssllllssssaaaakkkkddddllllssss;;;;ffffaaaaaaaa;;;;sssskkkkddddffffkkkkaaaaaaaakkkkddddllllssss;;;;ffffaaaaaaaa;;;;sssskkkkddddffffkkkkaaaaaaaakkkkddddllllssss;;;;ffffaaaaaaaa;;;;sssskkkkddddffffkkkkaaaaaaaakkkkddddllllssss;;;;ffffaaaaaaaa;;;;sssskkkkddddffffkkkkaaaassssllllffffjjjjaaaallllddddkkkkffffjjjjaaaa;;;;ssssllllddddkkkkssssllllffffjjjjaaaallllddddkkkkffffjjjjaaaa;;;;ssssllllddddkkkkssssllllffffjjjjaaaallllddddkkkkffffjjjjaaaa;;;;ssssllllddddkkkkssssllllffffjjjjaaaallllddddkkkkffffjjjjaaaa;;;;ssssllllddddkkkkaaaa;;;;ssssllllddddkkkkffffffffjjjjddddllllssss;;;;aaaa;;;;ssssaaaa;;;;ssssllllddddkkkkffffffffjjjjddddllllssss;;;;aaaa;;;;ssssaaaa;;;;ssssllllddddkkkkffffffffjjjjddddllllssss;;;;aaaa;;;;ssssaaaa;;;;ssssllllddddkkkkffffffffjjjjddddllllssss;;;;aaaa;;;;ssssaaaassssddddddddssssffffssssddddffffaaaassssssssddddffffddddaaaaaaaassssddddddddssssffffssssddddffffaaaassssssssddddffffddddaaaaaaaassssddddddddssssffffssssddddffffaaaassssssssddddffffddddaaaaaaaassssddddddddssssffffssssddddffffaaaassssssssddddffffddddaaaajjjjkkkkkkkkllll;;;;jjjjlllllllljjjj;;;;kkkkjjjjkkkkjjjjlllllllljjjjkkkkkkkkllll;;;;jjjjlllllllljjjj;;;;kkkkjjjjkkkkjjjjlllllllljjjjkkkkkkkkllll;;;;jjjjlllllllljjjj;;;;kkkkjjjjkkkkjjjjlllllllljjjjkkkkkkkkllll;;;;jjjjlllllllljjjj;;;;kkkkjjjjkkkkjjjjllllllllkkkkddddllllssss;;;;aaaakkkkffffjjjjddddkkkkssssllllaaaa;;;;ffffkkkkddddllllssss;;;;aaaakkkkffffjjjjddddkkkkssssllllaaaa;;;;ffffkkkkddddllllssss;;;;aaaakkkkffffjjjjddddkkkkssssllllaaaa;;;;ffffkkkkddddllllssss;;;;aaaakkkkffffjjjjddddkkkkssssllllaaaa;;;;ffffkkkkddddllllssss;;;;aaaajjjjffffkkkkdddd;;;;aaaakkkkddddjjjjsssskkkkddddllllssss;;;;aaaajjjjffffkkkkdddd;;;;aaaakkkkddddjjjjsssskkkkddddllllssss;;;;aaaajjjjffffkkkkdddd;;;;aaaakkkkddddjjjjsssskkkkddddllllssss;;;;aaaajjjjffffkkkkdddd;;;;aaaakkkkddddjjjjssssllllsssskkkkddddjjjjffff;;;;aaaallllddddkkkkddddjjjjssssllllssssllllsssskkkkddddjjjjffff;;;;aaaallllddddkkkkddddjjjjssssllllssssllllsssskkkkddddjjjjffff;;;;aaaallllddddkkkkddddjjjjssssllllssssllllsssskkkkddddjjjjffff;;;;aaaallllddddkkkkddddjjjjssssllllssss', 
		//$entry_filename, '<custom>akdls;faa;skdfkaslfjaldkfja;sldka;sldkffjdls;a;sasddsfsdfassdfdajkkl;jllj;kjkjllkdls;akfjdksla;fkdls;ajfkd;akdjslskdjf;aldkdjslsakdls;faa;skdfkaslfjaldkfja;sldka;sldkffjdls;a;sasddsfsdfassdfdajkkl;jllj;kjkjllkdls;akfjdksla;fkdls;ajfkd;akdjslskdjf;aldkdjslsakdls;faa;skdfkaslfjaldkfja;sldka;sldkffjdls;a;sasddsfsdfassdfdajkkl;jllj;kjkjllkdls;akfjdksla;fkdls;ajfkd;akdjslskdjf;aldkdjslsakdls;faa;skdfkaslfjaldkfja;sldka;sldkffjdls;a;sasddsfsdfassdfdajkkl;jllj;kjkjllkdls;akfjdksla;fkdls;ajfkd;akdjslskdjf;aldkdjsls</custom>');
		
		return true;
	}
	
	
	if($debug) {
		print('$this->fractal_string, $this->equivalences, $this->branch_counter in zip: ');var_dump($this->fractal_string, $this->equivalences, $this->branch_counter);
	}
	return true;
}

function fractal_replace($search, $replace, $string, $offset = 0) {
	//print('$search, $replace, $string, $offset at the start of fractal_replace: ');var_dump($search, $replace, $string, $offset);
	//$this->fractal_replace_debug_counter++;
	//if($this->fractal_replace_debug_counter === 18) {
	//	fractal_zip::fatal_error('$this->fractal_replace_debug_counter === 18');
	//}
	if($search === $replace) {
		return $string;
	}
	$this->final_fractal_replace = $replace;
	//if(substr_count($replace, '<') > 1) {
	//	fractal_zip::fatal_error('substr_count($replace, \'<\') > 1');
	//}
	$initial_string = $string;
	$initial_offset = $offset;
	$initial_post_offset = $offset + strlen($search);
	preg_match_all('/<([0-9]+)"([0-9]+)"*([0-9]*)>/is', $replace, $tag_in_replace_matches, PREG_OFFSET_CAPTURE);
	// [4] is offset adjustment
	// [5] is length adjustment
	foreach($tag_in_replace_matches[0] as $index => $value) {
		$tag_in_replace_matches[4][$index] = 0;
		$tag_in_replace_matches[5][$index] = 0;
	}
	//print('$tag_in_replace_matches: ');var_dump($tag_in_replace_matches);
	$pre = substr($string, 0, $offset);
	//print('$string after replace in fractal_replace:');var_dump($string);
	preg_match_all('/<([0-9]+)"([0-9]+)"*([0-9]*)>/is', $pre, $tag_in_pre_matches, PREG_OFFSET_CAPTURE);
	//print('$tag_in_pre_matches: ');var_dump($tag_in_pre_matches);
	$counter = sizeof($tag_in_pre_matches[0]) - 1;
	while($counter > -1) { // reverse order
		$new_tag_in_pre_offset = $tag_in_pre_offset = (int)$tag_in_pre_matches[1][$counter][0];
		$new_tag_in_pre_length = $tag_in_pre_length = (int)$tag_in_pre_matches[2][$counter][0];
		if($tag_in_pre_offset >= $offset) {
			$new_tag_in_pre_length = $tag_in_pre_length + strlen($replace) - strlen($search);
		}
		//print('$new_tag_in_pre_offset, $tag_in_pre_offset, $new_tag_in_pre_length, $tag_in_pre_length: ');var_dump($new_tag_in_pre_offset, $tag_in_pre_offset, $new_tag_in_pre_length, $tag_in_pre_length);
		if($new_tag_in_pre_offset === $tag_in_pre_offset && $new_tag_in_pre_length === $tag_in_pre_length) {
			$counter--;
			continue;
		}
		$tag_in_pre_operation = $tag_in_pre_matches[0][$counter][0];
		$tag_in_pre_recursion = $tag_in_pre_matches[3][$counter][0];
		if($tag_in_pre_recursion !== '') {
			$new_tag_in_pre_operation = '<' . $new_tag_in_pre_offset . '"' . $new_tag_in_pre_length . '"' . $tag_in_pre_recursion . '>';
		} else {
			$new_tag_in_pre_operation = '<' . $new_tag_in_pre_offset . '"' . $new_tag_in_pre_length . '>';
		}
		foreach($tag_in_replace_matches[0] as $index => $value) {
			if($tag_in_replace_matches[1][$index][0] + $offset <= $tag_in_pre_matches[0][$counter][1] && $tag_in_replace_matches[2][$index][0] >= $tag_in_pre_matches[0][$counter][1] + strlen($tag_in_pre_operation)) {
				//print('beep000002<br>');
				$tag_in_replace_matches[5][$index] += strlen($new_tag_in_pre_operation) - strlen($tag_in_pre_operation);
			}
		}
		$string = substr($string, 0, $tag_in_pre_matches[0][$counter][1]) . $new_tag_in_pre_operation . substr($string, $tag_in_pre_matches[0][$counter][1] + strlen($tag_in_pre_operation));
		$counter--;
	}
	$offset = $offset + strlen($initial_string) - strlen($string);
	foreach($tag_in_replace_matches[0] as $index => $value) {
		if($tag_in_replace_matches[1][$index][0] + $offset <= $initial_offset) {
			//print('beep000001<br>');
			$tag_in_replace_matches[4][$index] += $offset - $initial_offset;
		}
	}
	$post_offset = $offset + strlen($replace);
	foreach($tag_in_replace_matches[0] as $index => $value) {
		if($tag_in_replace_matches[1][$index][0] + $offset >= $initial_post_offset) {
			//print('beep000003<br>');
			$tag_in_replace_matches[4][$index] += $post_offset - $initial_post_offset;
		}
	}
	$string_after_replace = $string = substr($string, 0, $offset) . $replace . substr($string, $offset + strlen($search));
	//print('$string_after_replace: ');var_dump($string_after_replace);
	preg_match_all('/<([0-9]+)"([0-9]+)"*([0-9]*)>/is', $string, $tag_in_post_matches, PREG_OFFSET_CAPTURE, $post_offset);
	//print('$tag_in_post_matches: ');var_dump($tag_in_post_matches);
	$counter = sizeof($tag_in_post_matches[0]) - 1;
	while($counter > -1) { // reverse order
		$new_tag_in_post_offset = $tag_in_post_offset = (int)$tag_in_post_matches[1][$counter][0];
		$new_tag_in_post_length = $tag_in_post_length = (int)$tag_in_post_matches[2][$counter][0];
		if($tag_in_post_offset >= $offset) {
			$new_tag_in_post_length = $tag_in_post_length + strlen($replace) - strlen($search);
		}
		//print('$new_tag_in_post_offset, $tag_in_post_offset, $new_tag_in_post_length, $tag_in_post_length: ');var_dump($new_tag_in_post_offset, $tag_in_post_offset, $new_tag_in_post_length, $tag_in_post_length);
		if($new_tag_in_post_offset === $tag_in_post_offset && $new_tag_in_post_length === $tag_in_post_length) {
			$counter--;
			continue;
		}
		$tag_in_post_operation = $tag_in_post_matches[0][$counter][0];
		$tag_in_post_recursion = $tag_in_post_matches[3][$counter][0];
		if($tag_in_post_recursion !== '') {
			$new_tag_in_post_operation = '<' . $new_tag_in_post_offset . '"' . $new_tag_in_post_length . '"' . $tag_in_post_recursion . '>';
		} else {
			$new_tag_in_post_operation = '<' . $new_tag_in_post_offset . '"' . $new_tag_in_post_length . '>';
		}
		foreach($tag_in_replace_matches[0] as $index => $value) {
			//print('$tag_in_replace_matches[1][$index][0] + $offset, $tag_in_post_matches[0][$counter][1], $tag_in_replace_matches[2][$index][0] + $post_offset, $tag_in_post_matches[0][$counter][1] + strlen($tag_in_post_operation): ');var_dump($tag_in_replace_matches[1][$index][0] + $offset, $tag_in_post_matches[0][$counter][1], $tag_in_replace_matches[2][$index][0] + $post_offset, $tag_in_post_matches[0][$counter][1] + strlen($tag_in_post_operation));
			if($tag_in_replace_matches[1][$index][0] + $offset <= $tag_in_post_matches[0][$counter][1] && $tag_in_replace_matches[2][$index][0] + $post_offset >= $tag_in_post_matches[0][$counter][1] + strlen($tag_in_post_operation)) {
				//print('beep000004<br>');
				$tag_in_replace_matches[5][$index] += strlen($new_tag_in_post_operation) - strlen($tag_in_post_operation);
			}
		}
		$string = substr($string, 0, $tag_in_post_matches[0][$counter][1]) . $new_tag_in_post_operation . substr($string, $tag_in_post_matches[0][$counter][1] + strlen($tag_in_post_operation));
		$counter--;
	}
	fractal_zip::warning_once('hackety; is fractal_replace working perfectly for more complex fractal_strings? need to test');
	//$search = substr($string, 0, strpos($string, 'b'));
	//$replace = 'a<' . strpos($string, 'b') . '"' . (strlen($string) - strpos($string, 'b') + 1) . '>aaaa';
	//$replace = 'a<' . strpos($string, 'b') . '"' . (strlen($string) - strpos($string, 'b')) . '>aaaa';
	//$offset = strpos($string, $search);
	
	//print('$offset, $post_offset: ');var_dump($offset, $post_offset);
	$search = substr($string, $offset, $post_offset - $offset); // can we not set search to replace?
	//$replace = 'a<' . $post_offset . '"' . (strlen($string) - $post_offset - $offset) . '>aaaa';
	//$replace = preg_replace('/<([0-9]+)"([0-9]+)"*([0-9]*)>/is', '<' . $post_offset . '"' . (strlen($string) - $post_offset - $offset) . '>', $search);
	//print('should be ' . htmlentities('<' . $post_offset . '"' . (strlen($string) - $post_offset - $offset) . '>') . '<br>');
	//print('$tag_in_replace_matches at the bottom: ');var_dump($tag_in_replace_matches);
	$counter = sizeof($tag_in_replace_matches[0]) - 1;
	while($counter > -1) { // reverse order
		$tag_in_replace_offset = (int)$tag_in_replace_matches[1][$counter][0];
		$tag_in_replace_length = (int)$tag_in_replace_matches[2][$counter][0];
		$new_tag_in_replace_offset = $tag_in_replace_offset + $tag_in_replace_matches[4][$counter];
		$new_tag_in_replace_length = $tag_in_replace_length + $tag_in_replace_matches[5][$counter];
		$tag_in_replace_operation = $tag_in_replace_matches[0][$counter][0];
		$tag_in_replace_recursion = $tag_in_replace_matches[3][$counter][0];
		if($tag_in_replace_recursion !== '') {
			$new_tag_in_replace_operation = '<' . $new_tag_in_replace_offset . '"' . $new_tag_in_replace_length . '"' . $tag_in_replace_recursion . '>';
		} else {
			$new_tag_in_replace_operation = '<' . $new_tag_in_replace_offset . '"' . $new_tag_in_replace_length . '>';
		}
		$replace = substr($replace, 0, $tag_in_replace_matches[0][$counter][1]) . $new_tag_in_replace_operation . substr($replace, $tag_in_replace_matches[0][$counter][1] + strlen($tag_in_replace_operation));
		$counter--;
	}
	//$offset = $offset;
	//print('new $search, $replace, $string, $offset at the end of the fractal_replace recursion: ');var_dump($search, $replace, $string, $offset);
	return fractal_zip::fractal_replace($search, $replace, $string, $offset);
}

function add_to_fractal_substrings_array($string, $fractal_substrings_array = false) {
	//print('$string in add_to_fractal_substrings_array: ');var_dump($string);
	if(strlen($string) === 1) {
		//print('afs0001<br>');
		if(isset($fractal_substrings_array[$string])) {
			//print('afs0001.3<br>');
			return array($string => $fractal_substrings_array[$string]);
		} else {
			//print('afs0001.6<br>');
			return array($string => array());
		}
	}
	if($fractal_substrings_array === false) {
		//print('afs0002<br>');
		//print('initial $string, $this->fractal_substrings_array: ');var_dump($string, $this->fractal_substrings_array);
		if(isset($this->fractal_substrings_array[$string[0]])) {
			//print('afs0003<br>');
			$this->fractal_substrings_array[$string[0]] = array_merge($this->fractal_substrings_array[$string[0]], fractal_zip::add_to_fractal_substrings_array(substr($string, 1), $this->fractal_substrings_array[$string[0]]));
		} else {
			//print('afs0004<br>');
			if(isset($this->fractal_substrings_array[$string[0]])) {
				//print('afs0004.3<br>');
				$this->fractal_substrings_array[$string[0]] = fractal_zip::add_to_fractal_substrings_array(substr($string, 1), $this->fractal_substrings_array[$string[0]]);
			} else {
				//print('afs0004.6<br>');
				$this->fractal_substrings_array[$string[0]] = fractal_zip::add_to_fractal_substrings_array(substr($string, 1), array());
			}
		}
		// something to consider: there may be some worth in the patterns that become apparent (to the eye (short stuttery lines or long sustained lines)) when dumping $this->fractal_substrings_array but it seems unlikely that a computer could "see" these patterns unfortunately
		//print('$string, $this->fractal_substrings_array: ');var_dump($string, $this->fractal_substrings_array);
	} else {
		//print('afs0005<br>');
		if(isset($fractal_substrings_array[$string[0]])) {
			//print('afs0006<br>');
			return array($string[0] => array_merge($fractal_substrings_array[$string[0]], fractal_zip::add_to_fractal_substrings_array(substr($string, 1), $fractal_substrings_array[$string[0]])));
		} else {
			//print('afs0007<br>');
			//print('$string, $fractal_substrings_array: ');var_dump($string, $fractal_substrings_array);
			if(isset($fractal_substrings_array[$string[0]])) {
				return array($string[0] => fractal_zip::add_to_fractal_substrings_array(substr($string, 1), $fractal_substrings_array[$string[0]]));
			} else {
				return array($string[0] => fractal_zip::add_to_fractal_substrings_array(substr($string, 1), array()));
			}
		}
	}
}

function minimally_new_substr($string) {
	$counter = 0;
	if(!isset($this->fractal_substrings_array[$string[0]])) {
		//return $string[0];
		$counter++;
	} else {
		$fractal_substrings_array = $this->fractal_substrings_array;
		//$minimally_new_substr = '';
		while($counter < strlen($string)) {
			if(isset($fractal_substrings_array[$string[$counter]])) {
				$fractal_substrings_array = $fractal_substrings_array[$string[$counter]];
			} else {
				break;
			}
			$counter++;
		}
		//print('substr($string, 0, 0): ');var_dump(substr($string, 0, 0));exit(0);
	}
	$minimally_new_substr = substr($string, 0, $counter);
	//print('$minimally_new_substr before checking: ');var_dump($minimally_new_substr);
	//print('mns0001<br>');
	while(!fractal_zip::is_fractally_clean($minimally_new_substr) && $counter < strlen($string)) {
		//print('mns0002<br>');exit(0);
		$minimally_new_substr .= $string[$counter];
		$counter++;
	}
	//print('mns0003<br>');
	//print('$minimally_new_substr after checking: ');var_dump($minimally_new_substr);
	return $minimally_new_substr;
}

/*function get_all_substrings($input, $delim = '') {
    if(strlen($delim) === 0) {
		$arr = str_split($input, 1);
	} else {
		$arr = explode($delim, $input);
	}
    $out = array();
    for ($i = 0; $i < count($arr); $i++) {
        for ($j = $i; $j < count($arr); $j++) {
            $out[] = implode($delim, array_slice($arr, $i, $j - $i + 1));
        }       
    }
    return $out;
}*/

//$subs = get_all_substrings("a b c", " ");
//print_r($subs);

function all_substrings_count($string, $minimum_count = 1) { // minimum_count is unused
	$counter = 0;
	$minimum_counter_skip = 1;
	$substr_records = array();
	$minimum_substr_length = fractal_zip::maximum_substr_expression_length();
	$length_string = (string)strlen($string);
	$multiple = 15 - strlen($length_string);
	if($multiple < 3) {
		$multiple = 3;
	}
	$maximum_substr_length = fractal_zip::maximum_substr_expression_length() * $multiple;
	//$highest_substr_count = 0;
	while($counter < strlen($string) - $minimum_substr_length) {
		if(sizeof($substr_records) > 25000000) { // to prevent from choking on large files
			break;
		}
		//$best_substr = false;
		$counter_skip = 1;
		$sliding_counter = $minimum_substr_length;
		//while(($sliding_counter < $this->segment_length || $string[$counter + $sliding_counter + 1] === $string[$counter + $sliding_counter]) && $counter + $sliding_counter < strlen($string)) {
		while(($sliding_counter < $maximum_substr_length || $string[$counter + $sliding_counter + 1] === $string[$counter + $sliding_counter]) && $counter + $sliding_counter < strlen($string)) {
			if($string[$counter + $sliding_counter + 1] === $string[$counter + $sliding_counter]) {
				
			} else {
				$substr = substr($string, $counter, $sliding_counter);
				$count = $substr_records[$substr];
				if(isset($count)) {
					//if($count > $highest_substr_count) {
					//	$highest_substr_count = $count;
					//}
					//$counter_skip++; // just crazy enough to work??
					$counter_skip = strlen($substr);
					//$counter_skip = strlen($substr) * $substr_records[$substr];
					//break; // preferring data at the start?
					//if($count === $highest_substr_count) {
					//	break;
					//}
				}
				$substr_records[$substr]++;
				//$best_substr = $substr;
			}
			$sliding_counter++;
			//$sliding_counter += $minimum_substr_length; // balls to the walls
		}
		$substr_records[substr($string, $counter, $sliding_counter)]++;
		//if($best_substr !== false) {
		//	$substr_records[$best_substr]++;
		//}
		fractal_zip::warning_once('I believe there is room at this counter_skip to sacrifice compression for speed');
		//fractal_zip::warning_once('counter_skip has a HUGE impact to prefer speed over compression');
		fractal_zip::warning_once('hacking counter_skip');
		//$counter_skip = $sliding_counter;
		if($counter_skip < $minimum_counter_skip) {
			$counter_skip = $minimum_counter_skip;
		}
		//print('$counter_skip: ');var_dump($counter_skip);
		$counter += $counter_skip;
	}
	//print('$substr_records after first order: ');var_dump($substr_records);exit(0);
	// effectively doing second order processing?
	/*$scored_substr_records = array();
	foreach($substr_records as $substr => $count) {
		$scored_substr_records[$substr] = strlen($substr) * $count;
	}
	asort($scored_substr_records, SORT_NUMERIC);
	$scored_substr_records = array_reverse($scored_substr_records);
	$scored_substr_records = array_slice($scored_substr_records, 0, 1); // only keep the top one
	//$string2 = $scored_substr_records[0];
	foreach($scored_substr_records as $string2 => $count2) { break; }
	//print('$scored_substr_records, $string2: ');var_dump($scored_substr_records, $string2);
	$counter = 0;
	$minimum_counter_skip = 1;
	$substr_records = array();
	$minimum_substr_length = fractal_zip::maximum_substr_expression_length();
	$maximum_substr_length = fractal_zip::maximum_substr_expression_length() * 10;
	//$highest_substr_count = 0;
	while($counter < strlen($string2) - $minimum_substr_length) {
		$counter_skip = 1;
		$sliding_counter = $minimum_substr_length;
		while($counter + $sliding_counter < strlen($string2)) {
			$substr = substr($string2, $counter, $sliding_counter);
			$substr_records[$substr] = substr_count($string, $substr);
			$sliding_counter++;
		}
		$counter++;
	}*/
	//$substr_records = fractal_zip::get_all_substrings($string);
	foreach($substr_records as $substr => $count) {
		if($count < 2 || !fractal_zip::is_fractally_clean($substr)) {
			unset($substr_records[$substr]);
		}
	}
	//print('$substr_records: ');var_dump($substr_records);
	fractal_zip::warning_once('sort the substrings according to the most promising and hopefully this in combination with only acting on better than average substrings will save some time');
	// could be smarter here!
	$scored_substr_records = array();
	foreach($substr_records as $substr => $count) {
		// weigh the length more heavily? but according to what formula?
		$scored_substr_records[$substr] = strlen($substr) * $count;
		//$scored_substr_records[$substr] = pow(strlen($substr), 2) * $count;
	}
	asort($scored_substr_records, SORT_NUMERIC);
	$scored_substr_records = array_reverse($scored_substr_records);
	//print('$scored_substr_records: ');var_dump($scored_substr_records);
	//$scored_substr_records = array_slice($scored_substr_records, 0, 3); // only keep the top 3
	$scored_substr_records = array_slice($scored_substr_records, 0, 1); // only keep the top one
	//$scored_substr_records = array_slice($scored_substr_records, 0, sizeof($segments_array)); // trying to balance for the fact that a very nice and long duplicated substring would be split of multiple segments
	//print('$scored_substr_records top1: ');var_dump($scored_substr_records);
	$sorted_substr_records = array();
	foreach($scored_substr_records as $substr => $score) {
		$sorted_substr_records[$substr] = $substr_records[$substr];
	}
	//print('$substr_records, $sorted_substr_records: ');var_dump($substr_records, $sorted_substr_records);exit(0);
	//return $substr_records;
	return $sorted_substr_records;
}

//function all_substrings_count($string, $minimum_count = 2) {
function all_substrings_count_old($string, $minimum_count = 1) { // tricky to consider the implications
	/*fractal_zip::warning_once('there is room for optimization here: later instances of substrings could be skipped to save time');
	fractal_zip::warning_once('important question: what is better? chunking strings here (saving lots of time if the chunking is done properly) or degreedying later? degreedying has the advantage of being able to stop 
	when you get a good result. of course, if chunking is generalizable it is preferable but I have my doubts that it is. what is the balance point?');*/
	// I suppose we also need to make this function fractal
	$this->fractal_substrings_array = array();
	//print('asc0001<br>');
	//print('$string in all_substrings_count: ');var_dump($string);
	$segments_array = str_split($string, $this->segment_length);
	//$segments_array = str_split($string, fractal_zip::maximum_substr_expression_length() * 10);
	$all_segments_substr_records = array();
	foreach($segments_array as $string) {
		$counter = 0;
		//$saved_substr_count = -1;
		$saved_substr = '';
		$substr_records = array();
		while($counter < strlen($string) - fractal_zip::maximum_substr_expression_length()) {
			//fractal_zip::warning_once('minimally_new_substr processing disabled since we may be creating terribly deep 1000+ dimensional arrays');
			//$minimally_new_substr = fractal_zip::minimally_new_substr(substr($string, $counter));
			//if($minimally_new_substr === '') {
			//	break;
			//}
			$minimum_counter_skip = 1;
			//if($piece[0] === '<') {
			//	$minimum_counter_skip = strpos($piece, '>') + 1;
			//}
			//print('asc0002<br>');
			$added_something_this_slide = false;
			$sliding_counter = $counter;
			$piece = substr($string, $sliding_counter, fractal_zip::maximum_substr_expression_length()); // not sure if this shortcut is useful now that we are recursing
			while(strlen(fractal_zip::tagless($piece)) < fractal_zip::maximum_substr_expression_length() && $sliding_counter < strlen($string)) {
				//print('asc0002.3<br>');exit(0);
				$piece .= $string[$sliding_counter];
				$sliding_counter++;
			}
			//print('$piece, $minimally_new_substr: ');var_dump($piece, $minimally_new_substr);
			//print('$piece: ');var_dump($piece);
			$sliding_counter += strlen($piece);
			//$did_first = false;
			// it may be a philosophical point: all this looking for patterns in the substring stage is appealing but in order to apply intelligence later, we need to be open to all possibilities
		//	$count = substr_count($string, $piece);
		//	if($count > 1) {
			//print('asc0002.4<br>');
			while(!fractal_zip::is_fractally_clean($piece) && $sliding_counter < strlen($string)) {
				//print('asc0002.5<br>');exit(0);
				$piece .= $string[$sliding_counter];
				$sliding_counter++;
			}
			//print('asc0002.6<br>');
			//$last_piece = $piece;
			//$last_count = substr_count($string, $piece);
			if(fractal_zip::is_fractally_clean($piece)) {
				$last_piece = $piece;
				$last_count = substr_count($string, $piece);
			} else {
				$last_piece = false;
				$last_count = false;
			}
			//print('first $piece: ');var_dump($piece);
			//if($last_count >= $minimum_count) {
				//fractal_zip::warning_once('only taking the first and last in all_substrings_count. is the rest of the fractal code flexible enough to handle this?');
				// have to meet the requirement according to $this->fractal_substrings_array for a new substring
				while($sliding_counter < strlen($string)) {
					//print('asc0003<br>');
					$count = substr_count($string, $piece);
					//if(strlen($minimally_new_substr) > $sliding_counter - $counter) {
					//	$substr_records[$piece] = $count;
					//	$piece .= $string[$sliding_counter];
					//	$sliding_counter++;
					//	continue;
					//}
					if($count >= $minimum_count) {
						//print('asc0003.1<br>');
						if(fractal_zip::is_fractally_clean($piece)) {
							//print('asc0003.2<br>');
							if($string[$sliding_counter] !== $string[$sliding_counter - 1]) {
								//print('adding $piece by different characters: ');var_dump($piece);
								$substr_records[$piece] = $count;
								//fractal_zip::add_to_fractal_substrings_array($piece);
								//$added_something_this_slide = true;
							}
							if($count !== $last_count) {
								//print('adding $last_piece by different count: ');var_dump($last_piece);
								if($last_piece !== false) {
									$substr_records[$last_piece] = $last_count;
									//fractal_zip::add_to_fractal_substrings_array($last_piece);
									//$added_something_this_slide = true;
									$last_piece = false;
									$last_count = false;
								}
							} else {
								$last_piece = $piece;
								$last_count = $count;
							}
						}
		//				if(!$did_first) {
		//					$substr_records[$piece] = $count;
		//					$did_first = true;
		//				} else {
		//					$last_piece = $piece;
		//					$last_count = $count;
		//				}
					} else {
						//print('asc0003.4<br>');
						//print('$substr_records when no count: ');var_dump($substr_records);exit(0);
						break;
					}
					$piece .= $string[$sliding_counter];
					$sliding_counter++;
					//print('lengthened $piece: ');var_dump($piece);
				}
			//}
			//print('asc0003.42<br>');
			//if($last_piece !== false && strlen($last_piece) >= strlen($minimally_new_substr) && $last_count >= $minimum_count) {
			if($last_piece !== false && $last_count >= $minimum_count) {
				//print('asc0003.5<br>');
				$substr_records[$last_piece] = $last_count;
				//fractal_zip::add_to_fractal_substrings_array($last_piece);
				//$added_something_this_slide = true;
			}
			//if(strlen($last_piece) >= strlen($minimally_new_substr) && fractal_zip::is_fractally_clean($piece)) {
			if(fractal_zip::is_fractally_clean($piece)) {
				$count = substr_count($string, $piece);
				if($count >= $minimum_count) {
					//print('asc0003.6<br>');
					$substr_records[$piece] = $count;
					//fractal_zip::add_to_fractal_substrings_array($piece);
					//$added_something_this_slide = true;
				}
			}
			//print('asc0003.62<br>');
			/*$saved_substr_count = $substr_count;
			while($sliding_counter < strlen($string) && $substr_count > 1) {
				$substr_count = substr_count($string, $piece . $string[$sliding_counter]);
				if($substr_count < $saved_substr_count) {
					//$saved_substr = $piece;
					foreach($substr_records as $last_piece => $last_count) {  }
					if($saved_substr_count === $last_count && substr($last_piece, strlen($last_piece) - strlen($piece)) === $piece) {
						$counter++;
						continue 2;
					} elseif(strpos(strrev($piece), '>') === 0) { // throw out strings ending with an operator (questionably)
						$counter++;
						continue 2;
					} elseif(substr_count($piece, '>') !== substr_count($piece, '<')) { // throw out partial operators (questionably)
						$counter++;
						continue 2;
					} else {
						$substr_records[$piece] = $saved_substr_count;
					}
					$saved_substr_count = $substr_count;
				}
				$piece .= $string[$sliding_counter];
				$sliding_counter++;
			}*/
			//$counter++;
			fractal_zip::warning_once('I believe there is room at this counter_skip to sacrifice compression for speed');
			$counter_skip = 1;
			//if($added_something_this_slide) {
			//	while($counter + $counter_skip < strlen($string) && $string[$counter] === $string[$counter + $counter_skip]) { // clumsily treating character changes as generally important (which they are in the test files)
			//		//print('asc0003.71<br>');	
			//		$counter_skip++;
			//	}
			//} else {
			//	// there may be a way to get counter_skip from $minimally_new_substr but I do not see it since we must both look for the substr_count changing while extending the piece from the start as well as 
			//	// look for substr_count changes while shortening the piece from the end. we would have to do something like enter an array's data from one of the outer points; something very strange to a computer program
			//	$counter_skip = strlen($minimally_new_substr) - 1;
			//}
			fractal_zip::warning_once('counter_skip has a HUGE impact to prefer speed over compression');
			fractal_zip::warning_once('hacking counter_skip');
			//break; // !!!
			//$counter_skip = strlen($piece) - 1;
			$left_angle_bracket_position = strpos($piece, '&lt;');
			if($left_angle_bracket_position === 0) {
				$right_angle_bracket_position = strpos($piece, '&gt;');
				$counter_skip = $right_angle_bracket_position + 1;
			} elseif($left_angle_bracket_position !== false) {
				$counter_skip = $left_angle_bracket_position;
			} else {
				$counter_skip = strlen($piece) - 1;
			}
			if($counter_skip < $minimum_counter_skip) {
				$counter_skip = $minimum_counter_skip;
			}
			//print('$counter_skip: ');var_dump($counter_skip);
			$counter += $counter_skip;
		}
		//print('$substr_records at end of segment: ');var_dump($substr_records);
		foreach($substr_records as $substr => $count) {
			if(isset($all_segments_substr_records[$substr])) {
				$all_segments_substr_records[$substr] += $count;
			} else {
				$all_segments_substr_records[$substr] = $count;
			}
		}
	}
	foreach($all_segments_substr_records as $substr => $count) {
		if($count < 2) {
			unset($all_segments_substr_records[$substr]);
		}
	}
	//print('$segments_array, $all_segments_substr_records: ');var_dump($segments_array, $all_segments_substr_records);
	$substr_records = $all_segments_substr_records;
	fractal_zip::warning_once('sort the substrings according to the most promising and hopefully this in combination with only acting on better than average substrings will save some time');
	// have to be smarter here!
	//print('end of all_substrings_count()<br>');
	//if(sizeof($substr_records) === 0) {
	//	$substr_records[$string] = 1; // should this be at the end or the start?
	//}
	$scored_substr_records = array();
	foreach($substr_records as $substr => $count) {
		//$scored_substr_records[$substr] = strlen($substr) * $count;
		// weigh the length more heavily... but according to what formula?
		$scored_substr_records[$substr] = pow(strlen($substr), 2) * $count;
	}
	asort($scored_substr_records, SORT_NUMERIC);
	$scored_substr_records = array_reverse($scored_substr_records);
	print('$scored_substr_records: ');var_dump($scored_substr_records);
	//$scored_substr_records = array_slice($scored_substr_records, 0, 3); // only keep the top 3
	$scored_substr_records = array_slice($scored_substr_records, 0, 1); // only keep the top one
	//$scored_substr_records = array_slice($scored_substr_records, 0, sizeof($segments_array)); // trying to balance for the fact that a very nice and long duplicated substring would be split of multiple segments
	print('$scored_substr_records top1: ');var_dump($scored_substr_records);exit(0);
	$sorted_substr_records = array();
	foreach($scored_substr_records as $substr => $score) {
		$sorted_substr_records[$substr] = $substr_records[$substr];
	}
	//print('$substr_records, $sorted_substr_records: ');var_dump($substr_records, $sorted_substr_records);exit(0);
	//return $substr_records;
	return $sorted_substr_records;
}

function fractal_substring($string) {
	//print('start of fractal_substring');exit(0);
	// adding pieces derived from comparison to fractal_strings
	//if(sizeof($this->fractal_strings) === 0) { // would probably prefer to not hard-code this
	// probably goes in the above function
//	if(strlen($this->fractal_string) === 0) { // would probably prefer to not hard-code this
//		//$this->equivalences[] = array($string, $entry_filename, fractal_zip::mark_range_string('0-' . (strlen($string) - 1)));
//		//$this->fractal_string = $string;
//		//$this->equivalences[] = array($string, $entry_filename, '<' . $start_offset . '"' . (strlen($string) - 1) . '>');
//		$fractal_string = $string;
//		$zipped_string = '<' . $start_offset . '"' . (strlen($string) - 1) . '>';
//	} else {
		// special case of an identical string already having been fractal zipped
//		foreach($this->equivalences as $equivalence) {
//			$equivalence_string = $equivalence[0];
//			if($string === $equivalence_string) {
//				$this->equivalences[] = array($string, $entry_filename, $equivalence[2]);
//				//print('$this->fractal_string, $this->equivalences, $this->branch_counter in zip: ');var_dump($this->fractal_string, $this->equivalences, $this->branch_counter);
//				if($debug) {
//					print('special case of an identical string already having been fractal zipped<br>');
//				}
//				return true;
//			}
//		}
		// what's faster, using strpos character-wise or doing a compare to get the piece that is the same?
		// compare takes longer and results in a bigger file even on short strings, so that would seem to be that.
		//$initial_string = $string;
		$fractal_string = $this->fractal_string;
		
		$this->improvement_factor_threshold = 1; // hack
		

		
		
//		$this->shorthand_counter = 1;
//		$this->saved_shorthand = array();
		// alter the scope according to filesize; approximating how the size of a fractal container determines how the scope of the viewer will start
		//$fractal_scope_factor = strlen($string) / 100000;
		//if($fractal_scope_factor < 1) {
		//	$fractal_scope_factor = 1;
		//}
	//$debug_counter = 0;
	//$degreedying_debug_counter = 0;
	// this probably shouldn't be a class variable when going into a recursive function but it may be non-impactful
	$this->recursive_fractal_substring_debug_counter = 0;
	//$this->walk_the_path_debug_counter = 0;
	//$recursion_counter = 0;
	$this->string = $string;
	$this->fractal_path_scores = array();
	$fractal_paths = fractal_zip::recursive_fractal_substring($string, $fractal_string);
	//print('final $this->fractal_path_scores, $fractal_paths, $recursion_counter: ');fractal_zip::var_dump_full($this->fractal_path_scores, $fractal_paths, $recursion_counter);
	//print('final $this->fractal_path_scores, $fractal_paths: ');fractal_zip::var_dump_full($this->fractal_path_scores, $fractal_paths);
	print('final $this->fractal_path_scores: ');fractal_zip::var_dump_full($this->fractal_path_scores);
	if(sizeof($fractal_paths) === 0) {
		return array($this->fractal_string . $string, '<' . strlen($this->fractal_string) . '"' . strlen($string) . '>'); // so that subsequent files can work from this file's data
	}
	fractal_zip::warning_once('zipping multiple files (which creates a non-empty fractal_string from the second file on) are not handled correctly here');
	asort($this->fractal_path_scores, SORT_NUMERIC);
	$this->fractal_path_scores = array_reverse($this->fractal_path_scores);
	
	//foreach($this->fractal_path_scores as $serialized_best_path => $best_score) { break; }
	
	//fractal_zip::warning_once('euurrkk! while theoretically useful yet never practically shown to be useful comparing compression-wise rather than linearly is a real hog? 14s > 14s on aaaaa<20"25"5>aaaaaaaa');
	// pretty cumbersome but maybe inevitable given that there are many factors to check at varying times
	//fractal_zip::good_news('we should consider using lazy string compared to other compressed results, not simply the best string-wise (linear compression) result');
	// actually now that it's working properly it is of use!!
	// I have not seen an instance where this is effective
	$lazy_fractal_string = $fractal_string . $string;
	$lazy_zipped_string = '<' . strlen($fractal_string) . '"' . strlen($string) . '>';
	$this->array_fractal_zipped_strings_of_files = array();
	//$this->equivalences[] = array($string, $entry_filename, $zipped_string);
	foreach($this->equivalences as $equivalence) {
		$this->array_fractal_zipped_strings_of_files[$equivalence[1]] = $equivalence[2];
	}
	$this->array_fractal_zipped_strings_of_files[$this->entry_filename] = $lazy_zipped_string;
	$lazy_fzc_contents = serialize(array($this->array_fractal_zipped_strings_of_files, $lazy_fractal_string));
	//$lazy_fzc_contents = gzcompress($lazy_fzc_contents, 9);
	$lazy_fzc_contents = fractal_zip::adaptive_compress($lazy_fzc_contents);
	$best_score = 0;
	$serialized_best_path = false;
	foreach($this->fractal_path_scores as $serialized_potential_path => $potential_linear_score) {
		$walk_result = fractal_zip::walk_the_path(unserialize($serialized_potential_path), $fractal_paths);
		$potential_fractal_string = $walk_result[2];
		$potential_zipped_string = $walk_result[1];
		$this->array_fractal_zipped_strings_of_files = array();
		//$this->equivalences[] = array($string, $entry_filename, $zipped_string);
		foreach($this->equivalences as $equivalence) {
			$this->array_fractal_zipped_strings_of_files[$equivalence[1]] = $equivalence[2];
		}
		$this->array_fractal_zipped_strings_of_files[$this->entry_filename] = $potential_zipped_string;
		$potential_fzc_contents = serialize(array($this->array_fractal_zipped_strings_of_files, $potential_fractal_string));
		//$potential_fzc_contents = gzcompress($potential_fzc_contents, 9);
		$potential_fzc_contents = fractal_zip::adaptive_compress($potential_fzc_contents);
		$compressed_score = strlen($lazy_fzc_contents) / strlen($potential_fzc_contents);
		//print('$this->array_fractal_zipped_strings_of_files, $potential_fractal_string, $lazy_fzc_contents, $potential_fzc_contents, $compressed_score: ');var_dump($this->array_fractal_zipped_strings_of_files, $potential_fractal_string, $lazy_fzc_contents, $potential_fzc_contents, $compressed_score);
		if($compressed_score > $best_score) {
			$best_score = $compressed_score;
			$serialized_best_path = $serialized_potential_path;
		}
	}
	print('$serialized_best_path, $best_score: ');var_dump($serialized_best_path, $best_score);
	
	//fractal_zip::warning_once('end hack');
	//print('$this->fractal_paths[\'aaaaa\']: ');fractal_zip::var_dump_full($this->fractal_paths['aaaaa']);
	//return array('a<12"17>aaaabb<0"12>b<0"12>bb', '<0"12"5>');
	if($best_score < 1) { // this is logical. only reason not to do it would be tiny gains on tiny files (which do not need to be compresed)
		//return array($fractal_string, $string);
		//return array($fractal_string . $string, '<' . strlen($fractal_string) . '"' . strlen($string) . '>'); // so that subsequent files can work from this file's data
		return array($fractal_string . $string, '<' . strlen($fractal_string) . '"' . strlen($string) . '>'); // so that subsequent files can work from this file's data
	} else {
		$best_path = unserialize($serialized_best_path);
		//fractal_zip::warning_once('another deadly hack: instead of fixing some code somewhere we are just pruning the last step in the best path!');
		//$best_path = array_slice($best_path, 1);
		//print('$fractal_paths[\'aaaaabbbbbbbbbbbbbaaaaaaaa\'][\'aaaaabbbbbb<0"38>b<0"38>bbbbbbaaaaaaaa\'][\'aaaaabbbbbb<0"38>b<0"38>bbbbbbaaaaaaaa\'][\'aaaaabbbbbb<0"38>b<0"38>bbbbbbaaaaaaaa\'][\'bbbbbb<0"38>b<0"38>bbbbbbaaaaaaaab\']: ');var_dump($fractal_paths['aaaaabbbbbbbbbbbbbaaaaaaaa']['aaaaabbbbbb<0"38>b<0"38>bbbbbbaaaaaaaa']['aaaaabbbbbb<0"38>b<0"38>bbbbbbaaaaaaaa']['aaaaabbbbbb<0"38>b<0"38>bbbbbbaaaaaaaa']['bbbbbb<0"38>b<0"38>bbbbbbaaaaaaaab']);
		//print('$fractal_paths[\'aaaaabbbbbbbbbbbbbaaaaaaaa\'][\'aaaaabbbbbb<0"38>b<0"38>bbbbbbaaaaaaaa\'][\'aaaaabbbbbb<0"38>b<0"38>bbbbbbaaaaaaaa\']: ');var_dump($fractal_paths['aaaaabbbbbbbbbbbbbaaaaaaaa']['aaaaabbbbbb<0"38>b<0"38>bbbbbbaaaaaaaa']['aaaaabbbbbb<0"38>b<0"38>bbbbbbaaaaaaaa']);
		//print('$fractal_paths: ');var_dump($fractal_paths);
		$walk_result = fractal_zip::walk_the_path($best_path, $fractal_paths);
		//print('$walk_result: ');var_dump($walk_result);exit(0);
		return array($walk_result[2], $walk_result[1]);
	}
	
	//print('$string, $this->fractal_string, $fractal_string, $zipped_string at the end of fractal_substring(): ');var_dump($string, $this->fractal_string, $fractal_string, $zipped_string);
	//if(strlen($string) / (strlen($fractal_string) - strlen($this->fractal_string) + strlen($zipped_string)) > $this->improvement_factor_threshold) {
	//	return array($fractal_string, $zipped_string);
	//} else {
	//	return array($fractal_string, $initial_string);
	//}	
}

function walk_the_path($path, $fractal_paths) {
	//print('$path, $fractal_paths in walk_the_path: ');var_dump($path, $fractal_paths);
	//$this->walk_the_path_debug_counter++;
	//if($this->walk_the_path_debug_counter > 1) {
	//	fractal_zip::fatal_error('$this->walk_the_path_debug_counter > 1');
	//}
	if(sizeof($path) > 1) {
		return fractal_zip::walk_the_path(array_slice($path, 1), $fractal_paths[$path[0]][0]);
	} else {
		return $fractal_paths[$path[0]];
	}
}

function recursive_fractal_substring($string, $fractal_string, $fractal_paths = array(), $path = array(), $recursion_counter = 0, $last_score = false) {
	if($last_score === false) {
		//$last_score = $this->fractal_path_branch_trimming_score;
		//$last_score = 1 / $this->fractal_path_branch_trimming_multiplier; // (identity)
		$last_score = 1; // (identity)
	}
	$best_score = $last_score;
	//print('$string, $fractal_string, $fractal_paths, $path at the start of recursive_fractal_substring: ');var_dump($string, $fractal_string, $fractal_paths, $path);
	//print('rfs0001<br>');exit(0);
	//$did_something = true;
	//while($did_something) {
	//	$did_something = false;	
		//$zipped_string = '';
		$initial_string = $string;
		$initial_fractal_string = $fractal_string;
		$initial_path = $path;
		
		
		
		// looking to add to fractal_string
		fractal_zip::warning_once('only looking to add to fractal_string of recursion_counter < 2. consider limiting substr record entries to something smaller than the default segment_length (20000). maybe 140?? probably too small? consider stepping forward as good compression results and back when we get stuck. this sort of fractal traversal is probably inevitable to truly achieve fractal compression. you cannot know the whole fractal formula for the compression of code until you fully do; approximating based on only part of the fractal is insufficient.');
		//print('here26475480<br>');
	//	if($recursion_counter < 2) {
	//	if($recursion_counter === 1) {
	//		fractal_zip::warning_once('forcing path step at $recursion_counter === 1');
	//		$substr_records['bbbbbb<0"13>b<0"13>bbbbbb'] = 777;
	//	} else {
	//		$substr_records = array();
	//	}
		$substr_records = fractal_zip::all_substrings_count($string);
		//$substr_records = array();
		/*$substr_records = array();
		print('$recursion_counter, $recursion_counter % 2: ');var_dump($recursion_counter, $recursion_counter % 2);
		if($recursion_counter === 0) {
			$substr_records['aaaaaaaaaaaaa'] = 888;
		} elseif($recursion_counter === 1) {
			$substr_records['bbbbbb<0"13>b<0"13>bbbbbb'] = 888;
		} elseif($recursion_counter === 2) {
			$substr_records['aaaaa<13"25"2>aaaaaaaa'] = 888;
		} elseif($recursion_counter % 2 === 0) { // even
			$substr_records['aaaaa<20"25"' . $recursion_counter . '>aaaaaaaa'] = 888;
		} elseif($recursion_counter % 2 !== 0) { // odd
			$substr_records['bbbbbb<0"20"' . $recursion_counter . '>b<0"20"' . $recursion_counter . '>bbbbbb'] = 888;
		}
		fractal_zip::warning_once('hacked $substr_records to show off');*/
	//	$substr_records = array_merge($substr_records, fractal_zip::all_substrings_count($string)); // debug
		//print('$string, $fractal_string, $fractal_paths, $path, $substr_records in recursive_fractal_substring: ');var_dump($string, $fractal_string, $fractal_paths, $path, $substr_records);
		//print('$string, $substr_records, $this->fractal_substrings_array: ');var_dump($string, $substr_records, $this->fractal_substrings_array);
		//print('rfs0002<br>');
	//$this->recursive_fractal_substring_debug_counter++;
	//if($this->recursive_fractal_substring_debug_counter > 10) {
	//	fractal_zip::fatal_error('$this->recursive_fractal_substring_debug_counter > 10');
	//}
	//$micro_time_taken = microtime(true) - $this->initial_micro_time;
	//if($micro_time_taken > 10) {
	//	print('$this->fractal_path_scores: ');fractal_zip::var_dump_full($this->fractal_path_scores);
	//	print('$string, $fractal_string, $fractal_paths, $path: ');var_dump($string, $fractal_string, $fractal_paths, $path);
	//	fractal_zip::fatal_error('$micro_time_taken > 10 in recursive_fractal_substring');
	//}
	if($recursion_counter > 30) {
		print('$this->fractal_path_scores: ');fractal_zip::var_dump_full($this->fractal_path_scores);
		print('$string, $fractal_string, $fractal_paths, $path: ');var_dump($string, $fractal_string, $fractal_paths, $path);
		fractal_zip::fatal_error('$recursion_counter > 30');
	}
		//fractal_zip::warning_once('for now, do not attempt any sorting of $substr_records. it is an open question whether any sort of optimization is possible here. is there anything more fundamental to reality to use than fractality?');
		//print('$string, $fractal_string, $substr_records: ');var_dump($string, $fractal_string, $substr_records);
		/*print('$substr_records: ');var_dump($substr_records);
		//fractal_zip::warning_once('doing exactly second order of substring_count seems arbitrary');
		fractal_zip::warning_once('quite cumbersome!');
		while(sizeof($substr_records) > 1) {
			$new_substr_records = array();
			foreach($substr_records as $substr => $count) {
				$found_current_substr = false;
				foreach($substr_records as $substr2 => $count2) {
					if(!$found_current_substr) {
						$found_current_substr = true;
					} else {
						// bleh; not using the same function
						// ignore the counts in substr records?
						$counter = 0;
						while($counter < strlen($substr)) {
							$piece = '';
							$counter2 = 0;
							$counter3 = 0;
							while($counter2 < strlen($substr2)) {
								while($substr[$counter + $counter3] === $substr2[$counter2 + $counter3] && $counter2 + $counter3 < strlen($substr2)) {
									$piece .= $substr[$counter + $counter3];
									$counter3++;
									//print('$piece: ');var_dump($piece);
								}
								if(strlen($piece) > fractal_zip::maximum_substr_expression_length()) {
									if(isset($new_substr_records[$piece])) {
										$new_substr_records[$piece]++;
									} else {
										$new_substr_records[$piece] = 1;
									}
								}
								$piece = '';
								//$counter3 = 0;
								$counter2++;
							}
							$counter++;
						}
					}
				}
			}
			//$substr_records = array();
			//foreach($new_substr_records as $substr => $count) {
			//	if($count > 1) {
			//		$substr_records[$substr] = $count;
			//	}
			//}
			//foreach subterr count as index => value
			//foreach(yomentum count towards infinity) {
			//	choose the most upwards
			//	filter as needed by littlest number
			//	go under the hood
			//	blast from the past until you are done
			//	
			//}
			//foreach($new_substr_records as $substr => $count) {
			//	foreach($new_substr_records as $substr2 => $count2) {
			//		if($substr === $substr2) {
			//			
			//		} elseif(strpos($substr, $substr2) !== false && $count2 > $count) {
			//			$substr_records[$substr2] *= $count;
			//			continue 2;
			//		}
			//	}
			//	$substr_records[$substr] = $count;
			//}
			//print('$new_substr_records: ');var_dump($new_substr_records);
			//fractal_zip::warning_once('beyond understanding');
			//$new_new_substr_records = array();
			//foreach($new_substr_records as $substr => $count) {
			//	foreach($new_substr_records as $substr2 => $count2) {
			//		$substr_count1 = substr_count($substr, $substr2);
			//		$substr_count2 = substr_count($substr2, $substr);
			//		if(isset($new_new_substr_records[$substr])) {
			//			$new_new_substr_records[$substr] += $substr_count1 + $substr_count2;
			//		} else {
			//			$new_new_substr_records[$substr] = $substr_count1 + $substr_count2;
			//		}
			//	}
			//}
			//print('$new_new_substr_records: ');var_dump($new_new_substr_records);
			//$array1 = array();
			//foreach($new_substr_records as $substr => $count) {
			//	$array1[$substr] = $new_new_substr_records[$substr] / $count;
			//}
			//print('$array1: ');var_dump($array1);
			$substr_records = $new_substr_records;
			//$substr_records = array();
			//foreach($array1 as $substr => $count) {
			//	if($count > 1) {
			//		$substr_records[$substr] = 1;
			//	}
			//}
			print('$substr_records after distillation step: ');var_dump($substr_records);
		}
		print('$substr_records: ');var_dump($substr_records);exit(0);
		if(sizeof($substr_records) > 0) {
			$best_score = -1;
			$best_piece = '';
			foreach($substr_records as $piece => $substr_count) {
				//print('$piece, strlen($piece): ');var_dump($piece, strlen($piece));
				//if(strlen($piece) * $substr_count > $best_score) { // this would seem to be the natural approach... but it denies higher order structure and (from a tiny amount of testing) doesn't help compression
				if($substr_count > $best_score) { // this would seem to be the natural approach... but it denies higher order structure and (from a tiny amount of testing) doesn't help compression
					$best_score = $substr_count;
					$best_piece = $piece;
				}
			}
			print('$best_score: ');var_dump($best_score);
			fractal_zip::warning_once('does this best piece selection have ANY sort of general applicability?');
			//foreach($substr_records as $piece => $substr_count) {
			//	//print('looking for one after best score<br>');
			//	if($substr_count === $best_score) {
			//		$best_piece = $piece;
			//		break;
			//	}
			//}
			//fractal_zip::warning_once('forcing hard-coded substr on best_piece');
			*/
			/*$best_piece = false;
			if($recursion_counter === 0) {
				//$best_piece = substr($best_piece, 1);
				$best_piece = 'aaaaa';
				fractal_zip::warning_once('deepening the hack');
				if(strpos($string, $best_piece) === false) {
					$best_piece = 'bbbbb';
				}
			} elseif($recursion_counter === 1) {
				//$best_piece = substr($best_piece, 1, strlen($best_piece) - 6);
				$best_piece = 'bb<0"5>b<0"5>bb';
				if(strpos($string, $best_piece) === false) {
					$best_piece = 'a<0"5>aaaa';
				}
			}
			if($best_piece !== false) {
				print('$best_piece: ');var_dump($best_piece);
				$marked_range_string = '<' . strlen($fractal_string) . '"' . strlen($best_piece) . '>';
				$string = str_replace($best_piece, $marked_range_string, $string);
				$fractal_string .= $best_piece;
				$did_something = true;
			}*/
	//	}
	//	}
		
		
		
		//if($recursion_counter === 3) {
		//	print('$string, $substr_records: ');var_dump($string, $substr_records);exit(0);
		//}
		//print('rfs0003 $recursion_counter: ');var_dump($recursion_counter);
		fractal_zip::warning_once('will need to limit how far into the string fractal_zip looks given the manifold ways we are looking at the string if we want reasonable speed. probably something like fractal_zip::maximum_substr_expression_length() * 100 would be greedy enough when attempting potential compression');
		// looking to match what's already in the fractal_string
		//$scores = array(0); // dummy entry for initialization
		//$scores = array($this->fractal_path_branch_trimming_score * pow($this->fractal_path_branch_trimming_multiplier, $recursion_counter)); // requirements progressively increase, but whether this is a good function to increase by is unknown		
		$scores = array($last_score); // requirements are based on the last step rather than a function
		$substr_records_debug_counter = 0;
		
		//if($recursion_counter === 2) {
		//	fractal_zip::warning_once('forcing path step at $recursion_counter === 2');
		//	$substr_records['aaaaa<13"25"2>aaaaaaaa'] = 888;
		//}
		
		if(strpos($string, '<"') !== false) { // debug
			print('$string: ');var_dump($string);
			fractal_zip::fatal_error('strpos($string, \'<"\') !== false 1');
		}
		
		//print('$string, $fractal_string, $substr_records: ');var_dump($string, $fractal_string, $substr_records);
		foreach($substr_records as $piece => $piece_count) {
			//print('$substr_records_debug_counter, $piece: ');var_dump($substr_records_debug_counter, $piece);
			//print('$piece: ');var_dump($piece);
			//$substr_records_debug_counter++;
			//if($substr_records_debug_counter > 4) {
			//	print('$piece, $fractal_paths, $string, $fractal_string, $path1: ');var_dump($piece, $fractal_paths, $string, $fractal_string, $path);
			//	fractal_zip::fatal_error('$substr_records_debug_counter > 4');
			//}
			//print('rfs0003.1<br>');
			
			//if($piece === 'bbbbbb<0"13>b<0"13>bbbbbb') {
			//	fractal_zip::warning_once('$piece === bbbbbb<0"13>b<0"13>bbbbbb');
			//	$fractal_paths[$piece] = array(fractal_zip::recursive_fractal_substring($string, $fractal_string, array(), $path, $recursion_counter + 1), $string, $fractal_string);
			//	$this->fractal_path_scores[serialize($path)] = $score;
			//	$scores[] = $score;
			//	continue;
			//}
			
			/*fractal_zip::warning_once('forcing path steps hack debugging hack');
			if($recursion_counter === 0) {
				//$best_piece = substr($best_piece, 1);
			//	$best_piece = 'aaaaa';
			//	fractal_zip::warning_once('deepening the hack');
			//	if(strpos($string, $best_piece) === false) {
			//		$best_piece = 'bbbbb';
			//	}
				//if($piece !== 'bbbbb') {
				//if($piece !== 'aaaaa') {
				//if($piece !== 'aaaaaaaaaaaaa') {
				if($piece !== 'bbbbbbaaaaaaaaaaaaabaaaaaaaaaaaaabbbbbb') { // fix improving fractal_string
					continue;
				}
			} elseif($recursion_counter === 1) {
				//$best_piece = substr($best_piece, 1, strlen($best_piece) - 6);
			//	$best_piece = 'bb<0"5>b<0"5>bb';
			//	if(strpos($string, $best_piece) === false) {
			//		$best_piece = 'a<0"5>aaaa';
			//	}
				//if($piece !== 'a<0"5>aaaa') {
				//if($piece !== 'bb<0"5>b<0"5>bb') {
				//if($piece !== 'bbbbbb<0"13>b<0"13>bbbbbb') {
				//if($piece !== 'bbbbbbaaaaa<0"39>aaaaaaaabaaaaa<0"39>aaaaaaaabbbbbb') { // fix improving fractal_string
				//if($piece !== 'bbbbbbaaaaa<0"45>aaaaaaaabaaaaa<0"45>aaaaaaaabbbbbb') { // fix improving fractal_string
				if($piece !== 'bbbbbbaaaaa<0"51>aaaaaaaabaaaaa<0"51>aaaaaaaabbbbbb') { // fix improving fractal_string
					continue;
				}
			} elseif($recursion_counter === 2) {
				//if($piece !== 'a<5"15>aaaa') {
				//if($piece !== 'aaaaa<13"25"2>aaaaaaaa') {
				if($piece !== 'aaaaa<0"59"2>aaaaaaaa') {
					continue;
				}
			} elseif($recursion_counter === 3) {
				//if($piece !== 'bb<0"12>b<0"12>bb') {
				if($piece !== 'bbbbbb<0"20>b<0"20>bbbbbb') {
					continue;
				}
			}*/
			
			//print('rfs0003.2 $piece, $string, $fractal_string, $path1: ');var_dump($piece, $string, $fractal_string, $path);
			$string = $initial_string;
			$fractal_string = $initial_fractal_string;
			$path = $initial_path;
			
			//$recursion_markerless_piece = preg_replace('/<([0-9]+)"([0-9]+)"*([0-9]*)>/is', '<$1"$2>', $piece); // remove recursion markers when comparing to fractal_string
			$recursion_markerless_piece = fractal_zip::recursion_markerless($piece); // remove recursion markers when comparing to fractal_string
			
			// looking to improve the fractal_string
		
			//fractal_zip::warning_once('hack of only looking to improve the fractal_string when $recursion_counter === 1');
			//fractal_zip::warning_once('hack of only looking to improve the whole initial_fractal_string');
			//fractal_zip::warning_once('hack of only looking to improve the fractal_string using the first fractal_path step');
			/*fractal_zip::warning_once('improving the fractal_string is not working properly at least as evidenced by when we are getting 
			$this->fractal_string = bbbbbbaaaaaaaaaaaaabaaaaaaaaaaaaabbbbbb
			$zipped_string = <6"13"3>
			which is supposed to resolve to aaaaabbbbbbaaaaabbbbbbaaaaaaaaaaaaabaaaaaaaaaaaaabbbbbbaaaaaaaabaaaaabbbbbbaaaaaaaaaaaaabaaaaaaaaaaaaabbbbbbaaaaaaaabbbbbbaaaaaaaa from the dehacking_fractal_substring.txt with recursion of 4
			this can be gotten by not forcing the path step at recursion_counter = 0');*/
			//if($recursion_counter === 1) {
				//fractal_zip::warning_once('REAL hurly');
				//print('$fractal_string, $string before improving the fractal_string: ');var_dump($fractal_string, $string);
				//$fractal_string = 'a<12"17>aaaabb<0"12>b<0"12>bb';
				//$string = 'abba<12"17>aaaaba<12"17>aaaabbaaaa';
				//$search = 'aaaaa';
				//$search = $initial_fractal_string;
				//foreach($fractal_paths as $search => $the_rest_of_the_path) { break; }
				//$search = $this->first_fractal_path_step;
				//$string_match_position = fractal_zip::strpos_ignoring_operations($string, $search);
				//$string_match_position = fractal_zip::strpos_ignoring_operations($fractal_string, $piece);
				//print('$fractal_string before looking to improve the fractal_string: ');var_dump($fractal_string);
				if($recursion_counter === 0) {
					//print('adding $piece to $fractal_string 1<br>');
					$fractal_string .= $piece; // piece should never have any recusion markers (or operators) when $recursion_counter === 0 ?
					//$fractal_string .= $recursion_markerless_piece;
				} else {
					//$string_match_position = fractal_zip::strpos_ignoring_operations($string, $this->first_fractal_path_step);
					$match_position = fractal_zip::strpos_ignoring_operations($fractal_string, $piece);
					//print('$this->first_fractal_path_step, $string, $string_match_position, $fractal_paths when looking to improve the fractal_string : ');var_dump($this->first_fractal_path_step, $string, $string_match_position, $fractal_paths);
					//print('$fractal_string, $piece, $match_position when looking to improve the fractal_string : ');var_dump($fractal_string, $piece, $match_position);
					if($match_position !== false) {
						//print('improving $fractal_string<br>');
						//$replace = 'a<5"15>aaaa';
						$search = fractal_zip::tagless($piece);
						//$replace = substr($string, $match_position, $this->length_including_operations);
						//$replace = $piece;
						//$replace = preg_replace('/<([0-9]+)"([0-9]+)"*([0-9]*)>/is', '<$1"$2>', $piece); // remove recursion markers when comparing to fractal_string
						$replace = $recursion_markerless_piece;
						//print('$this->first_fractal_path_step, $replace when looking to improve the fractal_string : ');var_dump($this->first_fractal_path_step, $replace);exit(0);
						//$offset = 0;
						//$offset = strpos($fractal_string, $this->first_fractal_path_step);
						//$this->fractal_replace_debug_counter = 0;
						$this->final_fractal_replace = $replace; // initialization
						//$fractal_string = fractal_zip::fractal_replace($this->first_fractal_path_step, $replace, $fractal_string, $offset);
						//print('$search, $replace, $fractal_string, $match_position before fractal_replace: ');var_dump($search, $replace, $fractal_string, $match_position);
						$fractal_string = fractal_zip::fractal_replace($search, $replace, $fractal_string, $match_position);
						// should be:			
						//print('$fractal_string, $string should be: ');var_dump('a<12"17>aaaabb<0"12>b<0"12>bb', 'abba<12"17>aaaaba<12"17>aaaabbaaaa');
						//print('$piece, $this->final_fractal_replace when improving the whole initial_fractal_string: ');var_dump($piece, $this->final_fractal_replace);
						//print('$string 00: ');var_dump($string);
						$string = str_replace($piece, $this->final_fractal_replace, $string);
						fractal_zip::warning_once('tuples disabled since not all code expects them, example: aaaaa<20"25"4>aaaaaaaa, aaaaa<20"25>aaaaaaaabbbbbb<0"20>b<0"20>bbbbbb');
					//	$string = fractal_zip::tuples($string, $this->final_fractal_replace);
						//print('$string 99: ');var_dump($string);
						$piece = $this->final_fractal_replace;
						fractal_zip::warning_once('when improving the fractal string, the piece that ends up in $fractal_paths is not recursion marked; maybe unimportant');
						//print('$fractal_string, $string after improving the fractal_string: ');var_dump($fractal_string, $string);
						//$string = $zipped_string;
						//$did_something = true;
				//		$recursion_counter++;
				//		continue;
					} else {
						//print('$fractal_string, $piece before adding $piece to $fractal_string: ');var_dump($fractal_string, $piece);
						//$fractal_string .= $piece;
						$fractal_string .= $recursion_markerless_piece;
						//print('$fractal_string, $piece after adding $piece to $fractal_string: ');var_dump($fractal_string, $piece);
					}
				}
			//}
			
			
			//if(strpos($fractal_string, $piece) === false) {
			//	$fractal_string .= $piece;
			//}
			
			
			
			
			//print('rfs0003.3<br>');
			if($recursion_counter === 0) {
				//print('rfs0003.4<br>');
				//$match_position = strpos(preg_replace('/<[0-9]+"[0-9]+>/is', '', $fractal_string), $piece . $string[$sliding_counter]);
				//$match_position = fractal_zip::strpos_ignoring_operations($fractal_string, $piece);
				$this->first_fractal_path_step = $piece;
			}// else {
				//print('rfs0003.5<br>');
				//$match_position = strpos($fractal_string, $piece);
				//$this->length_including_operations = strlen($piece);
			//}
			//print('rfs0003.51<br>');
			$match_position = fractal_zip::strpos_ignoring_operations($fractal_string, $recursion_markerless_piece);
			//print('$fractal_string, $piece, $match_position2: ');var_dump($fractal_string, $piece, $match_position);
			//print('rfs0003.6<br>');exit(0);
			//$substr_records_debug_counter++;
			//if($substr_records_debug_counter > 18) {
			//	print('$piece, $fractal_paths, $string, $fractal_string, $path1: ');var_dump($piece, $fractal_paths, $string, $fractal_string, $path);
			//	fractal_zip::fatal_error('$substr_records_debug_counter > 18');
			//}
			//print('$fractal_string, $match_position of $piece in $fractal_string: ');var_dump($fractal_string, $match_position);
			if($match_position !== false) {
				//print('found in fractal_string<br>');
				fractal_zip::warning_once('still need to write the code to ensure that marked recursion_counters do not end up in the fractal_string (fractal_string maintains its flexibility and string fundamentally uses that flexibility');
				if($recursion_counter > 0) {
					$marked_range_string = '<' . $match_position . '"' . $this->length_including_operations . '"' . ($recursion_counter + 1) . '>';
				} else {
					$marked_range_string = '<' . $match_position . '"' . $this->length_including_operations . '>';
				}
				//print('$match_position, $this->length_including_operations, $marked_range_string: ');var_dump($match_position, $this->length_including_operations, $marked_range_string);
				//$zipped_string .= $marked_range_string;
				//print('$string 11: ');var_dump($string);
				$string = str_replace($piece, $marked_range_string, $string);
				fractal_zip::warning_once('tuples disabled since not all code expects them, example: aaaaa<20"25"4>aaaaaaaa, aaaaa<20"25>aaaaaaaabbbbbb<0"20>b<0"20>bbbbbb');
			//	$string = fractal_zip::tuples($string, $marked_range_string);
				//print('$string 22: ');var_dump($string);
				//$fractal_paths[$piece] = array(array(), $string, $fractal_string);
				$path[] = $piece;
				//print('$piece, $fractal_paths, $string, $fractal_string, $path2: ');var_dump($piece, $fractal_paths, $string, $fractal_string, $path);exit(0);
			//if($substr_records_debug_counter > 28) {
			//	print('$piece, $fractal_paths, $string, $fractal_string, $path2: ');var_dump($piece, $fractal_paths, $string, $fractal_string, $path);
			//	fractal_zip::fatal_error('$substr_records_debug_counter > 28');
			//}
				$score = strlen($this->string) / (strlen($string) + strlen($fractal_string)); // crude?
				fractal_zip::warning_once('need to tune this fractal_path branch trimming score1 can we progressively determine the fractal dimension?');
				//print('rfs0003.71<br>');exit(0);
				//print('$score, fractal_zip::average($scores), fractal_zip::silent_validate($string, $fractal_string, $this->string): ');var_dump($score, fractal_zip::average($scores), fractal_zip::silent_validate($string, $fractal_string, $this->string));
				//print('$string, $fractal_string, $this->string: ');var_dump($string, $fractal_string, $this->string);
				//print('$initial_string, $string, fractal_zip::maximum_substr_expression_length(), $score, fractal_zip::average($scores), fractal_zip::silent_validate($string, $fractal_string, $this->string): ');var_dump($initial_string, $string, fractal_zip::maximum_substr_expression_length(), $score, fractal_zip::average($scores), fractal_zip::silent_validate($string, $fractal_string, $this->string));exit(0);
				//print('rfs0003.72<br>');exit(0);
				//print('$this->string: ');var_dump($this->string);
				//if($score >= $this->fractal_path_branch_trimming_score) {
				//if($score >= $this->fractal_path_branch_trimming_score * pow($this->fractal_path_branch_trimming_multiplier, $recursion_counter)) {
				//if($score > fractal_zip::average($scores)) {
				//if($score > fractal_zip::average($scores) && fractal_zip::silent_validate($string, $fractal_string, $this->string)) {
				//if($score > fractal_zip::average($scores) * $this->fractal_path_branch_trimming_multiplier && fractal_zip::silent_validate($string, $fractal_string, $this->string)) {
				//if($score > fractal_zip::average($scores) * 1.05 && fractal_zip::silent_validate($string, $fractal_string, $this->string)) {
				//if($score > $last_score && fractal_zip::silent_validate($string, $fractal_string, $this->string)) {
				//if($score > $recursion_counter && fractal_zip::silent_validate($string, $fractal_string, $this->string)) {
				//if($score > $last_score && $score > fractal_zip::average($scores) && fractal_zip::silent_validate($string, $fractal_string, $this->string)) {
				//if($score > $last_score * $this->fractal_path_branch_trimming_multiplier && fractal_zip::silent_validate($string, $fractal_string, $this->string)) {
				//if($score > $recursion_counter && $score > fractal_zip::average($scores) && fractal_zip::silent_validate($string, $fractal_string, $this->string)) {
				//if($score > $recursion_counter && $score > $last_score && fractal_zip::silent_validate($string, $fractal_string, $this->string)) {
				//if($score > $recursion_counter && $score > $best_score && fractal_zip::silent_validate($string, $fractal_string, $this->string)) {
				//if($score > $last_score && fractal_zip::silent_validate($string, $fractal_string, $this->string)) {
				if(strlen($initial_string) - strlen($string) > fractal_zip::maximum_substr_expression_length() && $score > fractal_zip::average($scores) && fractal_zip::silent_validate($string, $fractal_string, $this->string)) {
					//print('adding to fractal_paths<br>');
					//$string = preg_replace('/<([0-9]+)"([0-9]+)"1(\**[0-9]*)(s*[0-9]*)>/is', '<$1"$2$3$4>', $string); // unmarked recursion_counter is understood by later code as recursion_counter = 1
					//$fractal_paths[$piece] = array(fractal_zip::recursive_fractal_substring($string, $fractal_string, array(), $path, $recursion_counter + 1, $score * $this->fractal_path_branch_trimming_multiplier), $string, $fractal_string);
					$fractal_paths[$piece] = array(fractal_zip::recursive_fractal_substring($string, $fractal_string, array(), $path, $recursion_counter + 1, $score), $string, $fractal_string);
					//print('rfs0003.73<br>');exit(0);
					//fractal_zip::warning_once('it is worth considering whether we could simply use the best score at each recursion level (ignoring $marked_range_string length) to save much time. ');
					$this->fractal_path_scores[serialize($path)] = $score;
					$scores[] = $score;
					$best_score = $score;
				}
			} else {
				//print('not found in fractal_string<br>');
				//$string = preg_replace('/<([0-9]+)"([0-9]+)"1(\**[0-9]*)(s*[0-9]*)>/is', '<$1"$2$3$4>', $string); // unmarked recursion_counter is understood by later code as recursion_counter = 1
				$fractal_paths[$piece] = array(false, $string, $fractal_string);
			}
		}
		//print('rfs0003.9<br>');exit(0);
		
		// looking to use a substring of the fractal_string
		//$string = $initial_string;
		$fractal_string = $initial_fractal_string;
		//print('$string, $fractal_string before looking to use a substring of the fractal_string: ');var_dump($string, $fractal_string);
		//$path = $initial_path;
		$fractal_substr_records = fractal_zip::all_substrings_count($fractal_string, 1);
		/*$fractal_substr_records = array();
		if($recursion_counter === 0) {
			$fractal_substr_records['aaaaaaaaaaaaa'] = 888;
		} elseif($recursion_counter === 1) {
			$fractal_substr_records['bbbbbb<0"13>b<0"13>bbbbbb'] = 888;
		} elseif($recursion_counter === 2) {
			$substr_records['aaaaa<13"25"2>aaaaaaaa'] = 888;
		} elseif($recursion_counter % 2 === 0) { // even
			$fractal_substr_records['aaaaa<20"25>aaaaaaaa'] = 888;
		} elseif($recursion_counter % 2 !== 0) { // odd
			$fractal_substr_records['bbbbbb<0"20>b<0"20>bbbbbb'] = 888;
		}
		fractal_zip::warning_once('hacked $fractal_substr_records to show off');*/
		//print('$fractal_string, $fractal_substr_records: ');var_dump($fractal_string, $fractal_substr_records);
		//$recursion_markerless_string = preg_replace('/<([0-9]+)"([0-9]+)"*([0-9]*)>/is', '<$1"$2>', $string); // remove recursion markers when comparing to fractal_string
		//print('$recursion_markerless_string: ');var_dump($recursion_markerless_string);
		$debug_counter45 = 0;
		foreach($fractal_substr_records as $piece => $piece_count) {
			$string = $initial_string;
			$fractal_string = $initial_fractal_string;
			//print('$string, $fractal_string before looking to use a substring of the fractal_string: ');var_dump($string, $fractal_string);
			$path = $initial_path;
			/*fractal_zip::warning_once('hack4729');
			if($recursion_counter === 1) {
				if($piece !== 'bbbbbbaaaaaaaaaaaaabaaaaaaaaaaaaabbbbbb') { // fix improving fractal_string
					continue;
				}
			} else*//*if($recursion_counter === 2) {
				if($piece !== 'bbbbbbaaaaa<0"51>aaaaaaaabaaaaa<0"51>aaaaaaaabbbbbb') { // fix improving fractal_string
					continue;
				}
			} else*//*if($recursion_counter === 3) {
				//if($piece !== 'bbbbbb<0"20>b<0"20>bbbbbb') {
				if($piece !== 'aaaaa<0"59>aaaaaaaa') {
					continue;
				}
			}*//* elseif($recursion_counter === 4) {
				if($piece !== 'aaaaa<20"25>aaaaaaaa') {
					continue;
				}
			}*/
			
			
			// looking to improve the string (and fractal_string?)
			if(strpos($string, '<"') !== false) { // debug
				fractal_zip::fatal_error('strpos($string, \'<"\') !== false');
			}
			$string_match_position = fractal_zip::strpos_ignoring_operations($string, $piece);
			$recursion_marked_piece = substr($string, $string_match_position, $this->length_including_operations);
			$recursion_markerless_piece = fractal_zip::recursion_markerless($piece);
			//$match_position = strpos($fractal_string, $recursion_markerless_piece);
			$match_position = fractal_zip::strpos_ignoring_operations($fractal_string, $recursion_markerless_piece);
			//$match_positions = array();
			//$recursion_markerless_piece = preg_replace('/<([0-9]+)"([0-9]+)"*([0-9]*)>/is', '<$1"$2>', $piece); // remove recursion markers when comparing to fractal_string
			//print('$string, $piece, $fractal_string before checking if fractal_string should be improved: ');var_dump($string, $piece, $fractal_string);
			if(strpos(fractal_zip::recursion_markerless($string), $piece) === false) { // if we directly find it in the string then we do not have to improve the fractal_string
				//print('$piece, $string, $fractal_string, $recursion_counter before looking to improve the string2: ');var_dump($piece, $string, $fractal_string, $recursion_counter);
			//if($recursion_counter === 0) {
			//	print('adding $piece to $string 3<br>');
			//	//$fractal_string .= $piece;
			//} else {
				//$string_match_position = fractal_zip::strpos_ignoring_operations($string, $this->first_fractal_path_step);
				//$string_match_position = fractal_zip::strpos_ignoring_operations($string, $piece);
				//print('$this->first_fractal_path_step, $string, $string_match_position, $fractal_paths when looking to improve the fractal_string : ');var_dump($this->first_fractal_path_step, $string, $string_match_position, $fractal_paths);
				//print('$string, $piece, $string_match_position when looking to improve the string : ');var_dump($string, $piece, $string_match_position);
				if($string_match_position !== false) {
					//print('improving $string2<br>');
					$search = fractal_zip::tagless($piece);
					//$replace = $recursion_markerless_piece;
					//$replace = fractal_zip::tagless($piece);
					$replace = $recursion_marked_piece;
					//$replace = $piece;
					//print('$search, $replace, $recursion_markerless_piece, $match_position, $fractal_string: ');var_dump($search, $replace, $recursion_markerless_piece, $match_position, $fractal_string);
					//fractal_zip::warning_once('hack3498501');
					//$replace = fractal_zip::fractal_replace('<0"39>', '<0"51>', $replace, 11); // ??
					$did_something = true;
					$last_replace = $replace;
					//$debug_counter38 = 0;
					$replace_offset = 0;
					//print('$string, $piece, $fractal_string before looking to improve the string (and fractal_string?): ');var_dump($string, $piece, $fractal_string);
					while($did_something) {
						//print('$search, $replace, $debug_counter38: ');var_dump($search, $replace, $debug_counter38);
						//$debug_counter38++;
						//if($debug_counter38 > 11) {
						//	print('$search, $replace, $string, $piece, $fractal_string: ');var_dump($search, $replace, $string, $piece, $fractal_string);
						//	fractal_zip::fatal_error('$debug_counter38 > 11');
						//}
						$did_something = false;
						if(preg_match('/<([0-9]+)"([0-9]+)"*([0-9]*)\**([0-9]*)s*([0-9\.]*)>/is', $replace, $matches, PREG_OFFSET_CAPTURE, $replace_offset)) { // would a parser be faster? optimize later
							$recursion_markerless_replace = fractal_zip::recursion_markerless($replace);
							//print('$matches: ');var_dump($matches);
						//preg_match_all('/<([0-9]+)"' . strlen($piece) . '>/is', $piece, $matches, PREG_OFFSET_CAPTURE); // ??
						//$counter = sizeof($matches) - 1;
						//while($counter > -1) {
						//foreach($matches[0] as $index => $value) {
							//$new4756 = '<' . $matches[1][$counter][0] . '"' . strlen($replace) . '>';
							//$new4756 = '<' . $matches[1][$counter][0] . '"' . strlen($replace + $matches[0][$counter][0]) . '>';
							//print('$matches[0][$counter][0], $new4756: ');var_dump($matches[0][$counter][0], $new4756);
							//$replace_string_part_after_this_operator = substr($replace, $matches[0][1] + strlen($matches[0][0]));
							//$replace_without_this_operator = substr($replace, 0, $matches[0][1]) . $replace_string_part_after_this_operator;
							//print('$matches[0][0], $replace_without_this_operator, $matches[0][1]: ');var_dump($matches[0][0], $replace_without_this_operator, $matches[0][1]);
							//$replace = fractal_zip::fractal_replace($replace_string_part_after_this_operator, $matches[0][0] . $replace_string_part_after_this_operator, $replace_without_this_operator, $matches[0][1]); // kind of ugly but probably effective
							$new_offset = $matches[1][0]; // hack; maybe okay since we fractal_replace?
							//print('$matches[1][0], $matches[2][0], $match_position: ');var_dump($matches[1][0], $matches[2][0], $match_position);
							//if($matches[1][0] + $matches[2][0] <= $match_position) {
							//$match_positions[] = 
							//if($match_position !== false) {
							$new_length = $matches[2][0];
							if(strlen($recursion_markerless_replace) > $this->length_including_operations) {
							//if(strlen($recursion_markerless_replace) === $this->length_including_operations && $recursion_markerless_replace === $recursion_markerless_piece) {
							//	
							//} else {
								//$new_length = $matches[2][0] + strlen($matches[0][0]); // only if it refers to itself?
								//$new_length = $matches[2][0] + strlen($replace) - strlen($search); // only if it refers to itself?
								$new_length = $matches[2][0] + strlen($recursion_markerless_replace) - $this->length_including_operations; // only if it refers to itself?
							}
							//print('$recursion_markerless_piece, $match_position, $this->length_including_operations, $new_length: ');var_dump($recursion_markerless_piece, $match_position, $this->length_including_operations, $new_length);
							//$substring_offset = $matches[1][0];
							//$substring_length = $matches[2][0];
							$substring_recursion_counter = $matches[3][0]; // what should be the order of the following markers?
							$substring_tuple = $matches[4][0];
							$substring_scale = $matches[5][0];
							if($substring_recursion_counter > 1) {
								$recursion_part = '"' . $substring_recursion_counter;
							} else {
								$recursion_part = '';
							}
							if($substring_tuple > 1) {
								$tuple_part = '*' . $substring_tuple;
							} else {
								$tuple_part = '';
							}
							if($substring_scale > 1) {
								$scale_part = 's' . $substring_scale;
							} else {
								$scale_part = '';
							}
							
							//print('$new_offset, $new_length, $recursion_part, $tuple_part, $scale_part: ');var_dump($new_offset, $new_length, $recursion_part, $tuple_part, $scale_part);
							$new_operation = '<' . $new_offset . '"' . $new_length . $recursion_part . $tuple_part . $scale_part . '>';
							//$replace = fractal_zip::fractal_replace($matches[0][0], $new_operation, $replace_without_this_operator, $matches[0][1]); // kind of ugly but probably effective
							//$replace = fractal_zip::fractal_replace('', $new_operation, $replace_without_this_operator, $matches[0][1]); // kind of ugly but probably effective
							$replace = fractal_zip::fractal_replace($matches[0][0], $new_operation, $last_replace, $matches[0][1]); // kind of ugly but probably effective
							//$replace = substr($replace, 0, $matches[0][$counter][1]) . $new4756 . substr($replace, $matches[0][$counter][1]);
							//$counter--;
						//}
							//print('$search, $fractal_string, $last_replace, $replace: ');var_dump($search, $fractal_string, $last_replace, $replace);
							if($last_replace !== $replace) {
								$did_something = true;
								$replace_offset = $matches[0][1] + strlen($matches[0][0]);
								$last_replace = $replace;
							}
						}
					}
					if(strpos($replace, '<"') !== false) { // debug
						print('$fractal_string, $piece, $search, $recursion_marked_piece, $replace: ');var_dump($fractal_string, $piece, $search, $recursion_marked_piece, $replace);
						fractal_zip::fatal_error('strpos($replace, \'<"\') !== false');
					}
					//$replace = fractal_zip::fractal_replace($search, $replace, $replace); // ??
					//$match_position = strpos($fractal_string, $recursion_markerless_piece);
					//print('$match_position when looking to improve the string : ');var_dump($match_position);
					$this->final_fractal_replace = $replace; // initialization
					//print('$search, $recursion_marked_piece, $replace, fractal_zip::recursion_markerless($replace), $fractal_string, $piece, $match_position before improving the whole string2: ');var_dump($search, $recursion_marked_piece, $replace, fractal_zip::recursion_markerless($replace), $fractal_string, $piece, $match_position);
					//$fractal_string = fractal_zip::fractal_replace($search, $replace, $fractal_string, $match_position);
					//print('$fractal_string before fractal_replace: ');var_dump($fractal_string);
					$fractal_string = fractal_zip::fractal_replace($search, fractal_zip::recursion_markerless($replace), $fractal_string, $match_position);
					//$fractal_string = str_replace($search, fractal_zip::recursion_markerless($replace), $fractal_string);
					/*$last_fractal_string = $fractal_string;
					$fractal_string_offset = 0;
					$did_something = true;
					$debug_counter39 = 0;
					while($did_something) {
						$debug_counter39++;
						if($debug_counter39 > 10) {
							fractal_zip::fatal_error('$debug_counter39 > 10');
						}
						$did_something = false;
						//$match_position = strpos($fractal_string, $recursion_markerless_piece);
						$fractal_string_match_position = strpos($fractal_string, $recursion_markerless_piece, $fractal_string_offset);
						$fractal_string = fractal_zip::fractal_replace($search, fractal_zip::recursion_markerless($replace), $fractal_string, $fractal_string_match_position);
						print('$last_fractal_string, $fractal_string: ');var_dump($last_fractal_string, $fractal_string);
						if($last_fractal_string !== $fractal_string) {
							$did_something = true;
							$fractal_string_offset = $fractal_string_match_position + strlen($recursion_markerless_piece);
							$last_fractal_string = $fractal_string;
						}
					}*/
					//print('$search, $replace, $fractal_string, $piece, $match_position after improving the whole string2: ');var_dump($search, $replace, $fractal_string, $piece, $match_position);
					if(preg_match('/<([0-9]+)"([0-9]+)"([0-9])/is', $fractal_string, $recursion_marker_in_fractal_string_matches)) { // debug
						print('$fractal_string: ');var_dump($fractal_string);
						fractal_zip::fatal_error('recursion marker in fractal_string found');
					}
					//print('$string before improving replace: ');var_dump($string);
					$string = str_replace($recursion_marked_piece, $this->final_fractal_replace, $string);
					fractal_zip::warning_once('tuples disabled since not all code expects them, example: aaaaa<20"25"4>aaaaaaaa, aaaaa<20"25>aaaaaaaabbbbbb<0"20>b<0"20>bbbbbb');
				//	$string = fractal_zip::tuples($string, $this->final_fractal_replace);
					//print('$string, $this->final_fractal_replace after improving replace: ');var_dump($string, $this->final_fractal_replace);
					//print('$recursion_markerless_piece, $this->final_fractal_replace, fractal_zip::recursion_markerless($this->final_fractal_replace), $fractal_string before improving replace: ');var_dump($recursion_markerless_piece, $this->final_fractal_replace, fractal_zip::recursion_markerless($this->final_fractal_replace), $fractal_string);
					//$fractal_string = str_replace($recursion_markerless_piece, fractal_zip::recursion_markerless($this->final_fractal_replace), $fractal_string); // ??
					//$fractal_string = str_replace($piece, fractal_zip::recursion_markerless($this->final_fractal_replace), $fractal_string); // ??
					//print('$recursion_markerless_piece, fractal_zip::recursion_markerless($this->final_fractal_replace), $fractal_string after improving replace: ');var_dump($recursion_markerless_piece, fractal_zip::recursion_markerless($this->final_fractal_replace), $fractal_string);
					$recursion_marked_piece = $piece = $this->final_fractal_replace;
					//print('$piece, $recursion_marked_piece, $string, $fractal_string after improving the whole string2: ');var_dump($piece, $recursion_marked_piece, $string, $fractal_string);
					fractal_zip::warning_once('when improving the fractal the piece that ends up in $fractal_paths is not recursion marked; maybe unimportant2');
				}// else {
				//	print('adding $piece to $fractal_string 4<br>');
				//	//$fractal_string .= $piece;
				//}
			} else {
				$piece = $recursion_marked_piece;
			}
			
			//print('$search, $replace, $fractal_string, $piece, $match_position after improving the whole string3: ');var_dump($search, $replace, $fractal_string, $piece, $match_position);
			//$string_match_position = strpos($recursion_markerless_string, $piece);
			//$string_match_position = fractal_zip::strpos_ignoring_operations($string, $piece);
			//$string_match_position = fractal_zip::strpos_ignoring_operations($string, $recursion_markerless_piece);
			//print('fractal_substr $piece, $string_match_position: ');var_dump($piece, $string_match_position);
			if($string_match_position !== false) {
				
				//print('$fractal_string, $recursion_markerless_piece, $match_position: ');var_dump($fractal_string, $recursion_markerless_piece, $match_position);
				if($match_position !== false) {
					if($recursion_counter > 0) {
						$marked_range_string = '<' . $match_position . '"' . strlen($piece) . '"' . ($recursion_counter + 1) . '>';
					} else {
						$marked_range_string = '<' . $match_position . '"' . strlen($piece) . '>';
					}
					//$real_string_match_position = fractal_zip::strpos_ignoring_operations($string, $piece);
					//$recursion_marked_piece = substr($string, $string_match_position, $this->length_including_operations);
					//print('$recursion_marked_piece, $marked_range_string, $string 33: ');var_dump($recursion_marked_piece, $marked_range_string, $string);
					$string = str_replace($recursion_marked_piece, $marked_range_string, $string);
					fractal_zip::warning_once('tuples disabled since not all code expects them, example: aaaaa<20"25"4>aaaaaaaa, aaaaa<20"25>aaaaaaaabbbbbb<0"20>b<0"20>bbbbbb');
				//	$string = fractal_zip::tuples($string, $marked_range_string);
					//print('$string 44: ');var_dump($string);
					$path[] = $piece;
					$score = strlen($this->string) / (strlen($string) + strlen($fractal_string)); // crude?
					//print('$string, $fractal_string, $this->string for silent_validate when looking to improve the string (and fractal_string?): ');var_dump($string, $fractal_string, $this->string);
					//$debug_counter45++;
					//if($debug_counter45 > 20) {
					//	fractal_zip::fatal_error('$debug_counter45 > 20');
					//}
					//if($score >= $this->fractal_path_branch_trimming_score) {
					//if($score >= $this->fractal_path_branch_trimming_score * pow($this->fractal_path_branch_trimming_multiplier, $recursion_counter)) {
					//if($score > fractal_zip::average($scores)) {
					//if($score > fractal_zip::average($scores) && fractal_zip::silent_validate($string, $fractal_string, $this->string)) {
					//if($score > fractal_zip::average($scores) * $this->fractal_path_branch_trimming_multiplier && fractal_zip::silent_validate($string, $fractal_string, $this->string)) {
					//if($score > fractal_zip::average($scores) * 1.14 && fractal_zip::silent_validate($string, $fractal_string, $this->string)) {
					//if($score > $last_score && fractal_zip::silent_validate($string, $fractal_string, $this->string)) {
					//if($score > $recursion_counter && fractal_zip::silent_validate($string, $fractal_string, $this->string)) {
					//if($score > $last_score && $score > fractal_zip::average($scores) && fractal_zip::silent_validate($string, $fractal_string, $this->string)) {
					//if($score > $last_score * $this->fractal_path_branch_trimming_multiplier && fractal_zip::silent_validate($string, $fractal_string, $this->string)) {
					//if($score > $recursion_counter && $score > fractal_zip::average($scores) && fractal_zip::silent_validate($string, $fractal_string, $this->string)) {
					//if($score > $recursion_counter && $score > $last_score && fractal_zip::silent_validate($string, $fractal_string, $this->string)) {
					//if($score > $recursion_counter && $score > $best_score && fractal_zip::silent_validate($string, $fractal_string, $this->string)) {
					//if($score > $last_score && fractal_zip::silent_validate($string, $fractal_string, $this->string)) {
					if(strlen($initial_string) - strlen($string) > fractal_zip::maximum_substr_expression_length() && $score > fractal_zip::average($scores) && fractal_zip::silent_validate($string, $fractal_string, $this->string)) {
					// recursion_counter clumsily acts for speed? but only for the test_files
						//$fractal_paths[$piece] = array(fractal_zip::recursive_fractal_substring($string, $fractal_string, array(), $path, $recursion_counter + 1, $score * $this->fractal_path_branch_trimming_multiplier), $string, $fractal_string);
						$fractal_paths[$piece] = array(fractal_zip::recursive_fractal_substring($string, $fractal_string, array(), $path, $recursion_counter + 1, $score), $string, $fractal_string);
						$this->fractal_path_scores[serialize($path)] = $score;
						$scores[] = $score;
						$best_score = $score;
					}
				}
			} else {
				$fractal_paths[$piece] = array(false, $string, $fractal_string);
			}
		}
		
		//print('rfs0004<br>');exit(0);
		/*
		// looking to use a substring of the fractal_string
		$string = $initial_string;
		$fractal_string = $initial_fractal_string;
		//print('$string, $fractal_string before looking to use a substring of the fractal_string: ');var_dump($string, $fractal_string);
		$path = $initial_path;
		$counter = 0;
		while($counter < strlen($string) - fractal_zip::maximum_substr_expression_length()) {
			// optimizable? or do we have to try every substr?
			$piece = substr($string, $counter, fractal_zip::maximum_substr_expression_length()); // not sure if this shortcut is useful now that we are recursing
			//print('$counter, fractal_zip::maximum_substr_expression_length(), $string, $piece initially: ');var_dump($counter, fractal_zip::maximum_substr_expression_length(), $string, $piece);
			if(isset($fractal_paths[$piece])) {
				$counter++;
				continue;
			}
			//if($recursion_counter === 0) {
			//	//$match_position = strpos(preg_replace('/<[0-9]+"[0-9]+>/is', '', $fractal_string), $piece . $string[$sliding_counter]);
			//	$match_position = fractal_zip::strpos_ignoring_operations($fractal_string, $piece);
			//} else {
			//	$match_position = strpos($fractal_string, $piece);
			//	$this->length_including_operations = strlen($piece);
			//}
			print('$match_position of $piece in $fractal_string while looking to use a substring of the fractal_string: ');var_dump($match_position);
			$match_position = fractal_zip::strpos_ignoring_operations($fractal_string, $piece);
			if($match_position === false) {
				$counter++;
				continue;
			}
			$sliding_counter = $counter + strlen($piece);
			//print('$piece, $match_position, $sliding_counter, $string, $fractal_string when looking to use a substring of the fractal_string: ');var_dump($piece, $match_position, $sliding_counter, $string, $fractal_string);
			//print(' when found a match: ');var_dump($piece);
			//while($sliding_counter < strlen($string) && $string[$sliding_counter] === $fractal_string[$match_position + strlen($piece)] && strlen($piece) < $this->segment_length) {
			fractal_zip::warning_once('probably need chunking here like in all_substrings');
			while($sliding_counter - 1 < strlen($string) && $match_position + strlen($piece) - 1 < strlen($fractal_string) && $string[$sliding_counter - 1] === $fractal_string[$match_position + strlen($piece) - 1] && strlen($piece) < $this->segment_length) {
				if($recursion_counter !== 0) {
					$this->length_including_operations = strlen($piece);
				}
				//fractal_zip::warning_once('atrocious hack');
				//if($piece === 'bb<0"12>b<0"12>bb') {
				if(fractal_zip::is_fractally_clean($piece)) {
					//print('found the magical piece!');exit(0);
					if($recursion_counter > 0) {
						$marked_range_string = '<' . $match_position . '"' . $this->length_including_operations . '"' . ($recursion_counter + 1) . '>';
					} else {
						$marked_range_string = '<' . $match_position . '"' . $this->length_including_operations . '>';
					}
					$string = str_replace($piece, $marked_range_string, $string);
					fractal_zip::warning_once('tuples disabled since not all code expects them, example: aaaaa<20"25"4>aaaaaaaa, aaaaa<20"25>aaaaaaaabbbbbb<0"20>b<0"20>bbbbbb');
					$string = fractal_zip::tuples($string, $marked_range_string);
					//$string = str_replace($piece, 'XXX9o9placeholder9o9XXX', $string);
					//fractal_zip::warning_once('probably needs more general applicability');
					//$string = str_replace('>', '"' . $recursion_counter . '>', $string);
					//$string = str_replace('XXX9o9placeholder9o9XXX', $marked_range_string, $string);
					fractal_zip::warning_once('assuming that everything fits into a single expression');
				//	$string = str_replace('>', '"' . ($recursion_counter + 1) . '>', $string);
					$path[] = $piece;
					$score = strlen($this->string) / (strlen($string) + strlen($fractal_string)); // crude?
					fractal_zip::warning_once('need to tune this fractal_path branch trimming score2 can we progressively determine the fractal dimension?');
					//if($score >= $this->fractal_path_branch_trimming_score) {
					//if($score >= $this->fractal_path_branch_trimming_score * pow($this->fractal_path_branch_trimming_multiplier, $recursion_counter)) {
					print('$score, fractal_zip::average($scores) while looking to use a substring of the fractal_string: ');var_dump($score, fractal_zip::average($scores));
					if($score > fractal_zip::average($scores)) {
						//$string = preg_replace('/<([0-9]+)"([0-9]+)"1(\**[0-9]*)(s*[0-9]*)>/is', '<$1"$2$3$4>', $string); // unmarked recursion_counter is understood by later code as recursion_counter = 1
						$fractal_paths[$piece] = array(fractal_zip::recursive_fractal_substring($string, $fractal_string, array(), $path, $recursion_counter + 1), $string, $fractal_string);
						$this->fractal_path_scores[serialize($path)] = $score;
						$scores[] = $score;
					}
				}
				$string = $initial_string;
				$path = $initial_path;
				$sliding_counter++;
				//print('$piece, $sliding_counter, $string, $fractal_string before lengthening piece: ');var_dump($piece, $sliding_counter, $string, $fractal_string);
				$piece .= $string[$sliding_counter - 1];
			}
			$counter++;
		}
		*/
		
		/*$counter = 0;
		//$saved_piece = '';
		while($counter < strlen($string) - fractal_zip::maximum_substr_expression_length()) {
			$sliding_counter = $counter;
			$piece = substr($string, $sliding_counter, fractal_zip::maximum_substr_expression_length()); // not sure if this shortcut is useful now that we are recursing
			$sliding_counter += strlen($piece);
			if($recursion_counter === 0) {
				//$match_position = strpos(preg_replace('/<[0-9]+"[0-9]+>/is', '', $fractal_string), $piece . $string[$sliding_counter]);
				$match_position = fractal_zip::strpos_ignoring_operations($fractal_string, $piece);
			} else {
				$match_position = strpos($fractal_string, $piece);
				$this->length_including_operations = strlen($piece);
			}
			//print('$match_position: ');var_dump($match_position);
			$found_a_better_match = true;
			if($match_position !== false) {
				while($found_a_better_match) {
					//print('$found_a_better_match<br>');
					$found_a_better_match = false;
					//print('$piece when found a match: ');var_dump($piece);
					while($sliding_counter < strlen($string) && $string[$sliding_counter] === $fractal_string[$match_position + strlen($piece)] && strlen($piece) < $this->segment_length) {
						//print('lengthening match<br>');
						$piece .= $string[$sliding_counter];
						$sliding_counter++;
						//$start_offset = $match_position;
						//$matched_piece_exists = true;
					}
					//print('$piece after finding full match: ');var_dump($piece);
					if($recursion_counter !== 0) {
						$this->length_including_operations = strlen($piece);
					}
					if(strlen($piece) === $this->segment_length || $sliding_counter === strlen($string)) {
						$marked_range_string = '<' . $match_position . '"' . $this->length_including_operations . '>';
						if(strlen($piece) / strlen($marked_range_string) > 1) {
							//$zipped_string = fractal_zip::shorthand_add($zipped_string, $range_string);
							//print('$piece, $marked_range_string when adding 1: ');var_dump($piece, $marked_range_string);
							$zipped_string .= $marked_range_string;
							$did_something = true;
						} else {
							// hack since other operators will end with >?
				//			$piece = str_replace('>', '"' . $recursion_counter . '>', $piece);
							//print('$piece when adding: ');var_dump($piece);
							$zipped_string .= $piece;
						}
						$counter = $sliding_counter;
						continue 2;
					}
					// look for a better match
					if($recursion_counter === 0) {
						$next_match_position = fractal_zip::strpos_ignoring_operations($fractal_string, $piece, $match_position + 1);
					} else {
						$next_match_position = strpos($fractal_string, $piece, $match_position + 1);
						$this->length_including_operations = strlen($piece);
					}
					while($next_match_position !== false) {
						//print('$next_match_position !== false<br>');
						if($string[$sliding_counter] === $fractal_string[$next_match_position + strlen($piece)]) {
							$piece .= $string[$sliding_counter];
							$sliding_counter++;
							$start_offset = $match_position = $next_match_position;
							$found_a_better_match = true;
							break;
						}
						if($recursion_counter === 0) {
							$next_match_position = fractal_zip::strpos_ignoring_operations($fractal_string, $piece, $next_match_position + 1);
						} else {
							$next_match_position = strpos($fractal_string, $piece, $next_match_position + 1);
							$this->length_including_operations = strlen($piece);
						}
						//print('$fractal_string, $piece, $match_position + 1, $next_match_position __: ');var_dump($fractal_string, $piece, $match_position + 1, $next_match_position);
					}
				}
				//print('$piece, $zipped_string, $next_match_position: ');var_dump($piece, $zipped_string, $next_match_position);exit(0);
			} else {
				$zipped_string .= $string[$counter];
				$counter++;
				continue;
			}
			//print('$match_position, $piece: ');var_dump($match_position, $piece);
			//if($matched_piece_exists) {
			//if($match_position !== false) {
			//if($match_position !== false && $this->length_including_operations > 10) { // hack for debugging
			if($match_position !== false && $this->length_including_operations > fractal_zip::maximum_substr_expression_length()) {
				$zipped_string .= '<' . $match_position . '"' . $this->length_including_operations . '>';
				$did_something = true;
				$counter += strlen($piece);
				//print('$piece, $zipped_string, $next_match_position: ');var_dump($piece, $zipped_string, $next_match_position);
			} else {
				$zipped_string .= $string[$counter];
				$counter++;
			}

		}
		
		//print('$string, $counter after looking for substrings: ');var_dump($string, $counter);
		if($counter < strlen($string)) {
			$zipped_string .= substr($string, $counter);
		}
		*/
		
		/*fractal_zip::warning_once('probably do not have to degreedy anything since piece is provided as is and it\'s usefulness is scored rather than trying to work with a bad piece');
		//break; // hack
		if(!$did_something) {
			//fractal_zip::fatal_error('here is where we would attempt to de-greedy the fractal?');
			//fractal_zip::warning_once('de-greedying fractal substring is currently lame; it only degreedies in one direction "forward" and only works when all substring operators in the string are the same');
			fractal_zip::warning_once('de-greedying fractal substring is currently lame; it forces use of substring operations preexisting in the fractal_string. this may be idealistically appealing but is certainly a constraint on the possible operators...');
			preg_match_all('/<([0-9]+)"([0-9]+)"*([0-9]*)>/is', $zipped_string, $tag_matches, PREG_OFFSET_CAPTURE);
			foreach($tag_matches[0] as $index => $value) {
				$counter = $index + 1;
				$unset_something = false;
				$size = sizeof($tag_matches[0]);
				while($counter < $size) {
					if($value[0] === $tag_matches[0][$counter][0]) {
						unset($tag_matches[0][$counter]);
						unset($tag_matches[1][$counter]);
						unset($tag_matches[2][$counter]);
						unset($tag_matches[3][$counter]);
						$unset_something = true;
					}
					$counter++;
				}
				if($unset_something) {
					$tag_matches[0] = array_values($tag_matches[0]);
					$tag_matches[1] = array_values($tag_matches[1]);
					$tag_matches[2] = array_values($tag_matches[2]);
					$tag_matches[3] = array_values($tag_matches[3]);
				}
			}
			//print('$tag_matches: ');var_dump($tag_matches);
			//foreach($tag_matches[0] as $index => $value) {
			//	if($tag_matches[0][0][0] === $value[0]) {
			//		
			//	} else {
			//		fractal_zip::fatal_error('how to de-greedy with different tags in the string is not coded yet');
			//	}
			//}
			//preg_match('/<([0-9]+)"([0-9]+)"*([0-9]*)>/is', $tag_matches[0][0][0], $first_tag_matches);
			preg_match_all('/<([0-9]+)"([0-9]+)"*([0-9]*)>/is', $fractal_string, $fractal_string_substring_operators);
			//print('$fractal_string_substring_operators: ');var_dump($fractal_string_substring_operators);
			$fractal_string_operator_parameters = array();
			foreach($fractal_string_substring_operators[0] as $index => $value) {
				$fractal_string_operator_parameters[] = array($fractal_string_substring_operators[1][$index], $fractal_string_substring_operators[2][$index], $fractal_string_substring_operators[3][$index]);
			}
			foreach($fractal_string_operator_parameters as $index => $value) {
				$counter = $index + 1;
				$unset_something = false;
				$size = sizeof($fractal_string_operator_parameters);
				while($counter < $size) {
					if($fractal_string_operator_parameters[$index][0] === $fractal_string_operator_parameters[$counter][0] && $fractal_string_operator_parameters[$index][1] === $fractal_string_operator_parameters[$counter][1] && $fractal_string_operator_parameters[$index][2] === $fractal_string_operator_parameters[$counter][2]) {
						unset($fractal_string_operator_parameters[$counter]);
						$unset_something = true;
					}
					$counter++;
				}
				if($unset_something) {
					$fractal_string_operator_parameters = array_values($fractal_string_operator_parameters);
				}
			}
		//	$fractal_string_operator_parameters = array_unique($fractal_string_operator_parameters);
		//	$fractal_string_operator_parameters = array_values($fractal_string_operator_parameters);
			//print('$fractal_string_operator_parameters: ');var_dump($fractal_string_operator_parameters);
			$recursion_marked_something = false;
			$degreedied_something = false;
			foreach($tag_matches[0] as $tag_index => $tag) {
				//print('$tag, $zipped_string: ');var_dump($tag, $zipped_string);
				if($tag_matches[3][$tag_index][0] !== '') {
					//print('$tag_matches[3][$tag_index][0]: ');var_dump($tag_matches[3][$tag_index][0]);
					fractal_zip::fatal_error('how to handle recursion counters on fractal substring operators is not coded yet');
				} elseif($tag_matches[2][$tag_index][0] < fractal_zip::maximum_substr_expression_length()) { // then give up
					
				} elseif($tag[0] === $zipped_string) {
					$zipped_string = str_replace('>', '"' . $recursion_counter . '>', $zipped_string);
					$recursion_marked_something = true;
					continue;
				} else {
					print('degreedying<br>');
					$degreedied_backwards = false;
					foreach($fractal_string_operator_parameters as $index => $value) {
						if($value[0] === $tag_matches[1][$tag_index][0]) {
							$degreedied_string = '<' . $value[0] . '"' . $value[1] . '>' . substr($fractal_string, $value[0] + $value[1], $tag_matches[2][$tag_index][0] - $value[1]);
							$degreedied_backwards = true;
							break;
						}
					}
					//print('$degreedied_backwards: ');var_dump($degreedied_backwards);
					if(!$degreedied_backwards) { // take only a preexisting tag
						$counter = 1;
						//$degreedied_string = $fractal_string[$tag_matches[1][$tag_index][0]] . '<' . ($tag_matches[1][$tag_index][0] + 1) . '"' . ($tag_matches[2][$tag_index][0] - 1) . '>';
						//print('um001<br>');
						while(true) {
							//print('um002<br>');
							if($tag_matches[2][$tag_index][0] - $counter < fractal_zip::maximum_substr_expression_length()) { // then give up
								//print('um003<br>');
								continue 2;
							}
							foreach($fractal_string_operator_parameters as $index => $value) {
								//print('um004<br>');
								//print('$value[0], $tag_matches[1][$tag_index][0] + $counter, $value[1], $tag_matches[2][$tag_index][0] - $counter: ');var_dump($value[0], $tag_matches[1][$tag_index][0] + $counter, $value[1], $tag_matches[2][$tag_index][0] - $counter);
								if($value[0] == $tag_matches[1][$tag_index][0] + $counter && $value[1] == $tag_matches[2][$tag_index][0] - $counter) {
									//print('um005<br>');
									$degreedied_string = substr($fractal_string, $tag_matches[1][$tag_index][0], $counter) . '<' . $value[0] . '"' . $value[1] . '>';
									break 2;
								}
							}
							$counter++;
						}
					}
					//print('$tag[0], $degreedied_string, $zipped_string: ');var_dump($tag[0], $degreedied_string, $zipped_string);
					if($tag[0] === $degreedied_string) {
						//fractal_zip::warning_once('questionable whether this code has general utility or just works for the test case');
						$zipped_string = str_replace($tag[0], str_replace('>', '"' . $recursion_counter . '>', $degreedied_string), $zipped_string);
						$recursion_marked_something = true;
					} else {
						//print('$tag[0], $degreedied_string, $zipped_string, $recursion_counter before degreedying: ');var_dump($tag[0], $degreedied_string, $zipped_string, $recursion_counter);
						$zipped_string = str_replace($tag[0], $degreedied_string, $zipped_string);
						//print('$tag[0], $degreedied_string, $zipped_string, $recursion_counter after degreedying: ');var_dump($tag[0], $degreedied_string, $zipped_string, $recursion_counter);
						$degreedied_something = true;
					}
				}
			}
			//$degreedying_debug_counter++;
			//if($degreedying_debug_counter > 20) {
			//	fractal_zip::fatal_error('$degreedying_debug_counter > 20');
			//}
			if($recursion_marked_something) {
				$string = $zipped_string;
				continue;
			}
			if($degreedied_something) {
				$string = $zipped_string;
				$did_something = true;
				continue;
			}
		}

		
		print('$fractal_string, $zipped_string, $recursion_counter: ');var_dump($fractal_string, $zipped_string, $recursion_counter);
		$string = $zipped_string;*/
	//	$recursion_counter++;
		//if($recursion_counter > 4) {
		//	fractal_zip::fatal_error('debug break $recursion_counter > 4');
		//}
	//}
	
	//print('$this->fractal_path_scores, $fractal_paths at the end of recursive_fractal_substring: ');var_dump($this->fractal_path_scores, $fractal_paths);
	//print('end of recursion loop<br>');exit(0);
	return $fractal_paths;
	//return fractal_zip::recursive_fractal_substring($string, $fractal_string, $fractal_paths = array(), $path = array());
}

function simple_substring_expressions_only($string) {
	preg_match_all('/<([0-9]+)"([0-9]+)"*([0-9]*)\**([0-9]*)s*([0-9\.]*)>/is', $string, $matches, PREG_OFFSET_CAPTURE); // would a parser be faster? optimize later
	//print('$matches in simple_substring_expressions_only: ');var_dump($matches);
	$counter = sizeof($matches[0]) - 1;
	while($counter > -1) {
		$substring_offset = $matches[1][$counter][0];
		$substring_length = $matches[2][$counter][0];
		//$substring_recursion_counter = $matches[3][0]; // what should be the order of the following markers?
		//$substring_tuple = $matches[4][0];
		//$substring_scale = $matches[5][0];
		$string = substr($string, 0, $matches[0][$counter][1]) . '<' . $substring_offset . '"' . $substring_length . '>' . substr($string, $matches[0][$counter][1] + strlen($matches[0][$counter][0]));
		$counter--;
	}
	return $string;
}

function recursion_markerless($string) {
	return fractal_zip::simple_substring_expressions_only($string);
}

function simplify($string, $operation) {
	return fractal_zip::tuples($string, $operation);
}

function multiples($string, $operation) {
	return fractal_zip::tuples($string, $operation);
}

function tuples($string, $operation) {
	preg_match_all('/' . $operation . '/is', $string, $matches, PREG_OFFSET_CAPTURE);
	//print('$matches in tuples: ');var_dump($matches);
	$counter = sizeof($matches[0]) - 1;
	$tuple = 1;
	//print('tuple1<br>');
	while($counter > -1) {
		//print('tuple2<br>');
		if($matches[0][$counter - 1][1] === $matches[0][$counter][1] - strlen($operation)) {
			//print('tuple3<br>');
			$tuple++;
		} elseif($tuple > 1) {
			//print('tuple4<br>');
			$tupled_operation = str_replace('>', '*' . $tuple . '>', $operation);
			$string = substr($string, 0, $matches[0][$counter][1]) . $tupled_operation . substr($string, $matches[0][$counter][1] + ($tuple * strlen($operation)));
			$tuple = 1;
		}
		$counter--;
	}
	//print('$tuple after loop: ');var_dump($tuple);
//	if($tuple > 1) {
//		$counter++;
//		$tupled_operation = str_replace('>', '*' . $tuple . '>', $operation);
//		$string = substr($string, 0, $matches[0][$counter][1]) . $tupled_operation . substr($string, $matches[0][$counter][1] + ($tuple * strlen($operation)));
//	}
	return $string;
}

function is_fractally_clean($piece) { // pretty hacky
	if(strpos($piece, '>') < strpos($piece, '<')) { // questionably throw out partial operators
		return false;
	} elseif(fractal_zip::strpos_last($piece, '>') < fractal_zip::strpos_last($piece, '<')) {
		return false;
	} elseif(substr_count($piece, '>') !== substr_count($piece, '<')) { // questionably throw out partial operators
		return false;
	} elseif(substr_count($piece, '>') === 1 && substr_count($piece, '<') === 1 && strpos($piece, '<') === 0 && strpos(strrev($piece), '>') === 0) { // questionably throw out pieces that are only a single operator
		return false;
	}/* elseif(strpos($piece, '<<') !== false || strpos($piece, '>>') !== false) { // ugly but probably effective until something smarter is done
		return false;	
	} elseif(strlen(fractal_zip::tagless($piece)) < fractal_zip::maximum_substr_expression_length()) {
		fractal_zip::warning_once('again, questionable whether this has general applicability');
		return false;
	}*/
	// parse looking for consecutive same bracket
	$offset = 0;
	$current_bracket = false;
	//print('ifc0001<br>');
	while($offset < strlen($piece)) {
		//print('ifc0002<br>');
		if($piece[$offset] === '<') {
			//print('ifc0003<br>');exit(0);
			if($current_bracket === '<') {
				//print('ifc0004<br>');exit(0);
				return false;
			} else {
				//print('ifc0005<br>');exit(0);
				$current_bracket = '<';
			}
		} elseif($piece[$offset] === '>') {
			//print('ifc0006<br>');exit(0);
			if($current_bracket === '>') {
				//print('ifc0007<br>');exit(0);
				return false;
			} else {
				//print('ifc0008<br>');exit(0);
				$current_bracket = '>';
			}
		}
		$offset++;
	}
	//print('ifc0009<br>');
	/*preg_match('/[0-9"]+/is', $piece, $matches);
	print('$matches, $piece: ');var_dump($matches, $piece);
	if($matches[0] === $piece) {
		return false;	
	}*/
	return true;
}

function is_fractally_clean_for_unzip($piece) { // pretty hacky
	// duplication of functions.....
	if(strpos($piece, '>') < strpos($piece, '<')) {
		return false;
	} elseif(fractal_zip::strpos_last($piece, '>') < fractal_zip::strpos_last($piece, '<')) {
		return false;
	} elseif(substr_count($piece, '>') !== substr_count($piece, '<')) {
		return false;
	}/* elseif(substr_count($piece, '>') === 1 && substr_count($piece, '<') === 1 && strpos($piece, '<') === 0 && strpos(strrev($piece), '>') === 0) { // questionably throw out pieces that are only a single operator
		return false;
	} else*//*if(strpos($piece, '<<') !== false || strpos($piece, '>>') !== false) { // ugly but probably effective until something smarter is done
		return false;	
	}*//* elseif(strlen(fractal_zip::tagless($piece)) < fractal_zip::maximum_substr_expression_length()) {
		fractal_zip::warning_once('again, questionable whether this has general applicability');
		return false;
	}*/
	/*preg_match('/[0-9"]+/is', $piece, $matches);
	if($matches[0] === $piece) {
		return false;	
	}*/
	// parse looking for consecutive same bracket
	$offset = 0;
	$current_bracket = false;
	while($offset < strlen($piece)) {
		if($piece[$offset] === '<') {
			if($current_bracket === '<') {
				return false;
			} else {
				$current_bracket = '<';
			}
		} elseif($piece[$offset] === '>') {
			if($current_bracket === '>') {
				return false;
			} else {
				$current_bracket = '>';
			}
		}
		$offset++;
	}
	return true;
}

function strpos_ignoring_operations($haystack, $needle, $offset = 0) {
	//print('$haystack, $needle, $offset in strpos_ignoring_operations: ');var_dump($haystack, $needle, $offset);
	$strpos = strpos($haystack, $needle, $offset);
	if($strpos !== false) {
		$this->length_including_operations = strlen($needle);
		return $strpos;
	}
	$needle = fractal_zip::tagless($needle);
	$needle_offset = 0;
	while($offset < strlen($haystack)) {
		$haystack_offset = $offset;
		$in_operation = false;
		//print('sp001<br>');
		while($haystack_offset < strlen($haystack) && ($in_operation || $haystack[$haystack_offset] === '<' || ($needle_offset < strlen($needle) && $haystack[$haystack_offset] === $needle[$needle_offset]))) {
			//print('sp002<br>');
			if($in_operation) {
				//print('sp003<br>');
				if($haystack[$haystack_offset] === '>') {
					//print('sp004<br>');
					$in_operation = false;
				} 
			} elseif($haystack[$haystack_offset] === '<') {
				//print('sp005<br>');
				$in_operation = true;
			} else {
				//print('sp006<br>');
				$needle_offset++;
				if($needle_offset === strlen($needle)) {
					//print('sp007<br>');
					$this->length_including_operations = $haystack_offset - $offset + 1;
					return $offset;
				}
			}
			$haystack_offset++;
		}
		$needle_offset = 0;
		$offset++;
	}
	return false;
}

function shorthand_add($fractal_zipped_string, $range_string) {
	$shorthand_exists = false;
	foreach($this->saved_shorthand as $saved_shorthand_index => $saved_shorthand) {
		if($saved_shorthand === $range_string) {
			$marked_range_string = fractal_zip::mark_range_string($this->range_shorthand_marker . $saved_shorthand_index);
			$fractal_zipped_string .= $marked_range_string;
			$shorthand_exists = true;
			break;
		}
	}
	if(!$shorthand_exists) {
		$this->saved_shorthand[$this->shorthand_counter] = $range_string;
		$this->shorthand_counter++;
		$marked_range_string = fractal_zip::mark_range_string($range_string);
		$fractal_zipped_string .= $marked_range_string;
	}
	return $fractal_zipped_string;
}

function fractal_substring_operator($start_offset, $end_offset) {
	return '<' . $start_offset . '"' . $end_offset . '>';
}

function mark_range_string($range_string) {
	if($this->multipass) {
		$range_string = $this->left_fractal_zip_marker . $this->branch_counter . $this->mid_fractal_zip_marker . $range_string . $this->mid_fractal_zip_marker . $this->branch_counter . $this->right_fractal_zip_marker;
	} else {
		$range_string = $this->left_fractal_zip_marker . $range_string . $this->right_fractal_zip_marker;
	}
	return $range_string;
}

function add_fractal_string_if($string) {
	foreach($this->fractal_strings as $branch_id => $fractal_string) {
		if($string === $fractal_string) {
			return $branch_id;
		}
	}
	$this->fractal_strings[$this->branch_counter] = $string;
	$this->branch_counter++;
	return $this->branch_counter - 1;
}

function clean_fractal_strings() {
	print('$this->fractal_strings, $this->equivalences at start of clean_fractal_strings: ');var_dump($this->fractal_strings, $this->equivalences);
	// use a more complex fractal zipped string expression to free up a fractal string and take less net length
	/*foreach($this->fractal_strings as $branch_id => $fractal_string) {
		// try to build this fractal string from other fractal strings
		$offset = 0;
		$fractal_string_length = strlen($fractal_string);
		$built_string = '';
		$built_fractal_expression = '';
		foreach($this->fractal_strings as $branch_id2 => $fractal_string2) {
			if($branch_id2 <= $branch_id) {
				continue;
			}
			$fractal_string2_length = strlen($fractal_string2);
			if(substr($fractal_string, $offset, $fractal_string2_length) === $fractal_string2) {
				$offset += $fractal_string2_length;
				$built_string .= $fractal_string2;
				$built_fractal_expression .= $branch_id2 . ',';
				if($offset === $fractal_string_length) {
					$built_fractal_expression = fractal_zip::clean_ending_comma($built_fractal_expression);
					print('built a fractal string from fractal strings: <br>');
					print('fractal expression ' . $branch_id . ' is equivalent to fractal expression ' . $built_fractal_expression . '<br>');
					// now, if this expression saves us space, then use it
					$space_saved = $fractal_string_length;
					$space_added = 0;
					foreach($this->equivalences as $equivalence) {
						$existing_branch_ids = fractal_zip::branch_ids_from_zipped_string($equivalence[2]);
						foreach($existing_branch_ids as $existing_branch_id) {
							if($existing_branch_id == $branch_id) {
								$space_added += strlen($built_fractal_expression) - strlen($branch_id);
							}
						}
					}
					if($space_saved > $space_added) {
						unset($this->fractal_strings[$branch_id]);
						$branch_ids_to_add = fractal_zip::branch_ids_from_zipped_string($built_fractal_expression);
						//print('$branch_ids_to_add: ');var_dump($branch_ids_to_add);
						foreach($this->equivalences as $equivalence_index => $equivalence) {
							$existing_branch_ids = fractal_zip::branch_ids_from_zipped_string($equivalence[2]);
							$new_branch_ids = array();
							foreach($existing_branch_ids as $existing_branch_id) {
								//print('$existing_branch_id, $branch_id: ');var_dump($existing_branch_id, $branch_id);
								if($existing_branch_id == $branch_id) {
									foreach($branch_ids_to_add as $branch_id_to_add) {
										$new_branch_ids[] = $branch_id_to_add;
									}
								} else {
									$new_branch_ids[] = $existing_branch_id;
								}
							}
							//print('$existing_branch_ids, $new_branch_ids: ');var_dump($existing_branch_ids, $new_branch_ids);
							$this->equivalences[$equivalence_index][2] = fractal_zip::zipped_string_from_branch_ids($new_branch_ids);
						}
						print('this fractionation saves space!<br>');
						print('$space_saved, $space_added: ');var_dump($space_saved, $space_added);
						print('$this->fractal_strings, $this->equivalences to test saving space: ');var_dump($this->fractal_strings, $this->equivalences);
					}
					break;
				}
			}
		}
	}*/
	// should we also do the reverse (check if a fractal expression portion is used frequently enough to merit its agglomeration)? yes:
	
	
	// seems to be no final filesize advantage to using this on short patterned or random strings even though it's more efficient with the fractal strings
	// what about large files?
	/*fractal_zip::remove_duplicated_fractal_strings();
	//print('$this->fractal_strings after remove_duplicated_fractal_strings: ');var_dump($this->fractal_strings);
	// break fractal expressions of same length into pieces
	foreach($this->fractal_strings as $branch_id => $fractal_string) {
		$offset = 0;
		$fractal_string_length = strlen($fractal_string);
		$built_string = '';
		$built_fractal_expression = '';
		foreach($this->fractal_strings as $branch_id2 => $fractal_string2) {
			if($branch_id2 <= $branch_id) {
				continue;
			}
			$fractal_string2_length = strlen($fractal_string2);
			if($fractal_string_length === $fractal_string2_length) {
				//print('$branch_id, $fractal_string, $branch_id2, $fractal_string2: ');var_dump($branch_id, $fractal_string, $branch_id2, $fractal_string2);
				$fractal_strings_to_potentially_add = array();
				$diff_array = Diff::compare($fractal_string, $fractal_string2, true);
				//print('$diff_array: ');var_dump($diff_array);
				//$diff_table = Diff::get_colored_comparison_table_string($diff_array);
				//print($diff_table);
				//unset($this->fractal_strings[$index]);
				$fractal_string_to_add = '';
				$fractal_zipped_string1 = '';
				$fractal_zipped_string2 = '';
				$diff_mode = false;
				$space_saved = 0;
				$space_added = 0;
				foreach($diff_array as $diff_index => $diff_value) {
					if($diff_mode === false) {
						$diff_mode = $diff_value[1];
					}
					$fractal_string_to_add .= $diff_value[0];
					if($diff_mode !== $diff_array[$diff_index + 1][1]) {
						if($diff_mode === 0) {
							$fractal_zipped_string1 .= $this->branch_counter . ',';
							$fractal_zipped_string2 .= $this->branch_counter . ',';
							$space_saved += strlen($fractal_string_to_add);
						} elseif($diff_mode === 1) {
							$fractal_zipped_string2 .= $this->branch_counter . ',';
						} elseif($diff_mode === 2) {
							$fractal_zipped_string1 .= $this->branch_counter . ',';
						} else {
							print('should never get here23658970981-');exit(0);
						}
						$fractal_strings_to_potentially_add[$this->branch_counter] = $fractal_string_to_add;
						$fractal_string_to_add = '';
						$this->branch_counter++;
						$diff_mode = $diff_array[$diff_index + 1][1];
					}
				}
				$fractal_zipped_string1 = fractal_zip::clean_ending_comma($fractal_zipped_string1);
				$fractal_zipped_string2 = fractal_zip::clean_ending_comma($fractal_zipped_string2);
				foreach($this->equivalences as $equivalence) {
					$existing_branch_ids = fractal_zip::branch_ids_from_zipped_string($equivalence[2]);
					foreach($existing_branch_ids as $existing_branch_id) {
						if($existing_branch_id == $branch_id) {
							$space_added += strlen($fractal_zipped_string1) - strlen($branch_id);
						}
						if($existing_branch_id == $branch_id) {
							$space_added += strlen($fractal_zipped_string2) - strlen($branch_id2);
						}
					}
				}
				//print('$space_saved, $space_added: ');var_dump($space_saved, $space_added);
				if($space_saved > $space_added) {
					foreach($fractal_strings_to_potentially_add as $fractal_string_to_potentially_add_branch_id => $fractal_string_to_add) {
						$this->fractal_strings[$fractal_string_to_potentially_add_branch_id] = $fractal_string_to_add;
					}					
					//print('$branch_id, $branch_id2, $fractal_zipped_string1, $fractal_zipped_string2, $this->fractal_strings: ');var_dump($branch_id, $branch_id2, $fractal_zipped_string1, $fractal_zipped_string2, $this->fractal_strings);
					unset($this->fractal_strings[$branch_id]);
					unset($this->fractal_strings[$branch_id2]);
					// why are we crossing these over...?
					$branch_ids_to_add = fractal_zip::branch_ids_from_zipped_string($fractal_zipped_string2);
					$branch_ids_to_add2 = fractal_zip::branch_ids_from_zipped_string($fractal_zipped_string1);
					foreach($this->equivalences as $equivalence_index => $equivalence) {
						$existing_branch_ids = fractal_zip::branch_ids_from_zipped_string($equivalence[2]);
						$new_branch_ids = array();
						foreach($existing_branch_ids as $existing_branch_id) {
							if($existing_branch_id == $branch_id) {
								foreach($branch_ids_to_add as $branch_id_to_add) {
									$new_branch_ids[] = $branch_id_to_add;
								}
							} elseif($existing_branch_id == $branch_id2) {
								foreach($branch_ids_to_add2 as $branch_id_to_add2) {
									$new_branch_ids[] = $branch_id_to_add2;
								}
							} else {
								$new_branch_ids[] = $existing_branch_id;
							}
						}
						$this->equivalences[$equivalence_index][2] = fractal_zip::zipped_string_from_branch_ids($new_branch_ids);
					}
					print('this fractal string intercomparison of ' . $branch_id . ' and ' . $branch_id2 . ' saves space!<br>');
				}
			}
		}
	}*/
	fractal_zip::remove_duplicated_fractal_strings();
	// remove unused strings; is there a situation where we wouldn't want to remove unused strings? or can we safely say that the most useful strings will be generated when needed?
	$used_strings = array();
	foreach($this->equivalences as $equivalence_index => $equivalence) {
		$fractal_zipped_string = $equivalence[2];
		$branch_ids = fractal_zip::branch_ids_from_zipped_string($fractal_zipped_string);
		foreach($branch_ids as $branch_id) {
			$used_strings[$branch_id] = true;
		}
	}
	foreach($this->fractal_strings as $index => $fractal_string) {
		if(isset($used_strings[$index])) {
			
		} else {
			unset($this->fractal_strings[$index]);
		}
	}
}

function remove_duplicated_fractal_strings() {
	$array_branch_id_reassignments = array();
	foreach($this->fractal_strings as $index => $fractal_string) {
		//$index2 = $index + 1;
		//while($index2 < sizeof($this->fractal_strings)) { // can't use indices since cleaning leaves gaps
		foreach($this->fractal_strings as $index2 => $fractal_string2) {
			if($index2 <= $index) {
				continue;
			}
			if($fractal_string === $fractal_string2) {
				unset($this->fractal_strings[$index2]);
				if(isset($array_branch_id_reassignments[$index2])) {
					
				} else {
					$array_branch_id_reassignments[$index2] = $index;
				}
			}
			//$index2++;
		}
	}
	//print('$array_branch_id_reassignments in clean_fractal_strings: ');var_dump($array_branch_id_reassignments);
	foreach($this->equivalences as $equivalence_index => $equivalence) {
		$fractal_zipped_string = $equivalence[2];
		$branch_ids = fractal_zip::branch_ids_from_zipped_string($fractal_zipped_string);
		//print('$branch_ids in clean_fractal_strings: ');var_dump($branch_ids);
		foreach($array_branch_id_reassignments as $from_id => $to_id) {
			foreach($branch_ids as $branch_id_index => $branch_id) {
				if($branch_id == $from_id) {
					$branch_ids[$branch_id_index] = $to_id;
				}
			}
		}
		$this->equivalences[$equivalence_index][2] = fractal_zip::branch_ids_to_zipped_string($branch_ids);
		//print('$this->equivalences in clean_fractal_strings: ');var_dump($this->equivalences);
	}
}

function minimize_branch_ids() {
	$array_branch_id_reassignments = array();
	$counter = 0;
	$new_fractal_strings = array();
	foreach($this->fractal_strings as $index => $fractal_string) {
		$array_branch_id_reassignments[$index] = $counter;
		$new_fractal_strings[$counter] = $fractal_string;
		$counter++;
	}
	$this->fractal_strings = $new_fractal_strings;
	foreach($this->equivalences as $equivalence_index => $equivalence) {
		$fractal_zipped_string = $equivalence[2];
		$branch_ids = fractal_zip::branch_ids_from_zipped_string($fractal_zipped_string);
		foreach($array_branch_id_reassignments as $from_id => $to_id) {
			foreach($branch_ids as $branch_id_index => $branch_id) {
				if($branch_id == $from_id) {
					$branch_ids[$branch_id_index] = $to_id;
				}
			}
		}
		$this->equivalences[$equivalence_index][2] = fractal_zip::branch_ids_to_zipped_string($branch_ids);
	}
}

function validate_fractal_zip($entry_filename) {
	//print('FORCING Valid fractal_zip.<br>');
	//return true;
	print('$entry_filename, $this->fractal_string, $this->equivalences: ');fractal_zip::var_dump_full($entry_filename, $this->fractal_string, $this->equivalences);
	foreach($this->equivalences as $equivalence) {
		$equivalence_filename = $equivalence[1];
		if($equivalence_filename === $entry_filename) {
			$equivalence_string = $equivalence[0];
			$equivalence_fractal_zipped_expression = $equivalence[2];
			print('$equivalence_filename, $equivalence_string, $equivalence_fractal_zipped_expression: ');fractal_zip::var_dump_full($equivalence_filename, $equivalence_string, $equivalence_fractal_zipped_expression);
			if(fractal_zip::unzip($equivalence_fractal_zipped_expression) === $equivalence_string) {
				print('Valid fractal_zip.<br>');
				return true;
			} else {
				break;
			}
		}
	}
	print('Invalid fractal_zip.<br>');
	//print('$equivalence_filename, $entry_filename, $equivalence_fractal_zipped_expression, fractal_zip::unzip($equivalence_fractal_zipped_expression), $equivalence_string, $this->fractal_string: ');fractal_zip::var_dump_full($equivalence_filename, $entry_filename, $equivalence_fractal_zipped_expression, fractal_zip::unzip($equivalence_fractal_zipped_expression), $equivalence_string, $this->fractal_string);
	print('$this->fractal_string: ');var_dump($this->fractal_string);
	print('$equivalence_fractal_zipped_expression, fractal_zip::unzip($equivalence_fractal_zipped_expression), $equivalence_string, $this->fractal_string: ');fractal_zip::var_dump_full($equivalence_fractal_zipped_expression, fractal_zip::unzip($equivalence_fractal_zipped_expression), $equivalence_string, $this->fractal_string);
	exit(0);
	return false;
}

function silent_validate($string, $fractal_string, $equivalence_string) {
	fractal_zip::warning_once('silent_validate was created to weed out bad results instead of fixing the code that creates these bad results, example: aaaaa<20"25"6>aaaaaaaa, aaaaa<20"25>aaaaaaaabbbbbb<0"20>b<0"20>bbbbbb ... definate hack but seems to make us go faster since we are eliminating problems. is slower when there are no problems due to buggy code.');
	$result = false;
	//$initial_fractal_string = $this->fractal_string;
	//$this->fractal_string = $fractal_string;
	//print('$string, $fractal_string, fractal_zip::unzip($string, $fractal_string), $equivalence_string in silent_validate: ');var_dump($string, $fractal_string, fractal_zip::unzip($string, $fractal_string), $equivalence_string);
	if(fractal_zip::unzip($string, $fractal_string, false) === $equivalence_string) {
		$result = true;
	}
	//$this->fractal_string = $initial_fractal_string;
	return $result;
}

function clean_ending_comma($string) {
	if(substr($string, strlen($string) - 1) === ',') {
		$string = substr($string, 0, strlen($string) - 1);
	}
	return $string;
}

function branch_ids_to_zipped_string($branch_ids) {
	return implode(',', $branch_ids);
}

function zipped_string_from_branch_ids($branch_ids) {
	return fractal_zip::branch_ids_to_zipped_string($branch_ids);
}

function branch_ids_from_zipped_string($string) {
	if(strpos($string, ',') !== false) {
		$branches = explode(',', $string);
	} else {
		$branches = array($string);
	}
	$new_branches = array();
	foreach($branches as $branch) {
		if(strpos($branch, '-') === false) {
			$new_branches[] = $branch;
		} else {
			$branch_range_start = substr($branch, 0, strpos($branch, '-'));
			$branch_range_end = substr($branch, strpos($branch, '-') + 1);
			$counter = $branch_range_start;
			while($counter <= $branch_range_end) {
				$new_branches[] = $counter;
				$counter++;
			}
		}
	}
	return $new_branches;
}

function zipped_string_to_branch_ids($string) {
	return fractal_zip::branch_ids_from_zipped_string($string);
}

function clean_arrays_before_serialization() {
	fractal_zip::minimize_branch_ids();
	fractal_zip::clean_fractal_zipped_strings();
}

function clean_fractal_zipped_strings() {
	foreach($this->equivalences as $equivalence_index => $equivalence) {
		$fractal_zipped_string = $equivalence[2];
		$fractal_zipped_string = fractal_zip::clean_fractal_zipped_string($fractal_zipped_string);
		$this->equivalences[$equivalence_index][2] = $fractal_zipped_string;
	}
}

function clean_fractal_zipped_string($string) {
	$string = fractal_zip::clean_ending_comma($string);
	// what's better; having a range over 2 sequential branch IDs or a comma between them?
	$new_branches = array();
	$last_branch = -2;
	//$branch_range_start_id = false;
	$branch_range_end_id = false;
	$branches = fractal_zip::branch_ids_from_zipped_string($string);
	foreach($branches as $branch) {
		if($branch == $last_branch + 1) {
			//if($branch_range_start_id === false) {
			//	$branch_range_start_id = $last_branch;
			//}
			$branch_range_end_id = $branch;
		} else {
			if($branch_range_end_id !== false) {
				$new_branches[sizeof($new_branches) - 1] .= '-' . $branch_range_end_id;
				$branch_range_end_id = false;
			}
			$new_branches[] = $branch;
		}
		$last_branch = $branch;
	}
	if($branch_range_end_id !== false) {
		$new_branches[sizeof($new_branches) - 1] .= '-' . $branch_range_end_id;
		$branch_range_end_id = false;
	}
	$string = fractal_zip::branch_ids_to_zipped_string($new_branches);
	return $string;
}

function adaptive_compress($string) {
	if(file_exists('C:\Program Files\7-Zip\7z.exe')) {
		fractal_zip::message_once('7-Zip executable found at C:\Program Files\7-Zip\7z.exe. Using 7-zip.');
		$temp_file_path = $this->program_path . DS . 'temp' . $this->fractal_zip_file_extension;
		file_put_contents($temp_file_path, $string);
		$temp_container_path = $this->program_path . DS . 'temp' . $this->fractal_zip_container_file_extension;
		exec('C:\Progra~1\7-Zip\7z a -t7z ' . $temp_container_path . ' ' . $temp_file_path, $output, $return);
		if($return !== 0) {
			print('$output, $return: ');var_dump($output, $return);
			fractal_zip::message_once('7-Zip did not work. Using gzip instead.');
		} else {
			$container_file_contents = file_get_contents($temp_container_path);
			unlink($temp_file_path);
			unlink($temp_container_path);
			return $container_file_contents;
		}
	} else {
		fractal_zip::message_once('7-Zip executable not found at C:\Program Files\7-Zip\7z.exe. Using gzip instead.');
	}
	// using worse compression seems to take longer??
	return gzcompress($string, 9);
}

function adaptive_decompress($string) {
	if($string[0] === '7' && $string[1] === 'z') {
		if(file_exists('C:\Program Files\7-Zip\7z.exe')) {
			$temp_container_path = $this->program_path . DS . 'temp' . $this->fractal_zip_container_file_extension;
			file_put_contents($temp_container_path, $string);
			exec('C:\Progra~1\7-Zip\7z x ' . $temp_container_path . ' -aoa -o' . $this->program_path, $output, $return);
			if($return !== 0) {
				print('$output, $return: ');var_dump($output, $return);exit(0);
			}
			$temp_file_path = $this->program_path . DS . 'temp' . $this->fractal_zip_file_extension;
			$temp_file_contents = file_get_contents($temp_file_path);
			unlink($temp_file_path);
			unlink($temp_container_path);
			return $temp_file_contents;
		} else {
			print('$string: ');var_dump($string);
			fractal_zip::fatal_error('string to decompress was identified as having 7-Zip format but 7-Zip executable was not found at C:\Program Files\7-Zip\7z.exe.');
		}
	} else {
		return gzuncompress($string);
	}
}

function escape($string) {
	$string = str_replace('{', 'XXX9o9left9o9XXX', $string);
	$string = str_replace('}', 'XXX9o9right9o9XXX', $string);
	$string = str_replace('|', 'XXX9o9mid9o9XXX', $string); // not currently necessary
	return $string;
}

function unescape($string) {
	$string = str_replace('XXX9o9left9o9XXX', '{', $string);
	$string = str_replace('XXX9o9right9o9XXX', '}', $string);
	$string = str_replace('XXX9o9mid9o9XXX', '|', $string); // not currently necessary
	return $string;
}

function filename_minus_extension($string) {
	return substr($string, 0, fractal_zip::strpos_last($string, '.'));
}

function file_extension($string) {
	return substr($string, fractal_zip::strpos_last($string, '.'));
}

function strpos_last($haystack, $needle) {
	//print('$haystack, $needle: ');var_dump($haystack, $needle);
	if(strlen($needle) === 0) {
		return false;
	}
	$len_haystack = strlen($haystack);
	$len_needle = strlen($needle);		
	$pos = strpos(strrev($haystack), strrev($needle));
	if($pos === false) {
		return false;
	}
	return $len_haystack - $pos - $len_needle;
}

function tagless($variable) {
	if(is_array($variable)) {
		if(fractal_zip::all_entries_are_arrays($variable)) {
			$tagless_array = array();
			foreach($variable as $index => $value) {
				$tagless_array[] = fractal_zip::tagless($value[0]);
			}
			if(sizeof($tagless_array) === 1) {
				return $tagless_array[0];
			}
			return $tagless_array;
		} else {
			return fractal_zip::tagless($variable[0]);
		}
		//fractal_zip::fatal_error('tagless() expects string input');
	}
	return preg_replace('/<[^<>]*>/is', '', $variable);
}

function var_dump_full() {
	$arguments_array = func_get_args();
	foreach($arguments_array as $index => $value) {
		$data_type = gettype($value);
		if($data_type == 'array') {
			$biggest_array_size = fractal_zip::get_biggest_sizeof($value);
			if($biggest_array_size > 2000) {
				ini_set('xdebug.var_display_max_children', '2000');
			} elseif($biggest_array_size > ini_get('xdebug.var_display_max_children')) {
				ini_set('xdebug.var_display_max_children', $biggest_array_size);
			}
		} elseif($data_type == 'string') {
			$biggest_string_size = strlen($value);
			if($biggest_string_size > 2000) {
				ini_set('xdebug.var_display_max_data', '10000');
			} elseif($biggest_string_size > ini_get('xdebug.var_display_max_data')) {
				ini_set('xdebug.var_display_max_data', $biggest_string_size);
			}
		} elseif($data_type == 'integer' || $data_type == 'float' || $data_type == 'chr' || $data_type == 'boolean' || $data_type == 'NULL') {
			// these are already compact enough
		} else {
			print('<span style="color: orange;">Unhandled data type in var_dump_full: ' . gettype($value) . '</span><br>');
		}
		var_dump($value);
	}
}

function get_biggest_sizeof($array, $biggest = 0) {
	if(sizeof($array) > $biggest) {
		$biggest = sizeof($array);
	}
	foreach($array as $index => $value) {
		if(is_array($value)) {
			$biggest = fractal_zip::get_biggest_sizeof($value, $biggest);
		}
	}
	return $biggest;
}

function density($substring, $string) {
	return substr_count($string, $substring);
}

function average($array) {
	$sum = 0;
	foreach($array as $index => $value) {
		$sum += $value;
	}
	return $sum / sizeof($array);
}

function preg_escape($string) {
	return str_replace('/', '\/', preg_quote($string));
}

function fatal_error($message) { 
	print('<span style="color: red;">' . $message . '</span>');exit(0);
}

function fatal_error_once($string) {
	if(!isset($this->printed_strings[$string])) {
		print('<span style="color: red;">' . $string . '</span>');exit(0);
		$this->printed_strings[$string] = true;
	}
	return true;
}

function warning($message) { 
	print('<span style="color: orange;">' . $message . '</span><br>');
}

function warning_if($string, $count) {
	if($count > 1) {
		fractal_zip::warning($string);
	}
}

function warning_once($string) {
	if(!isset($this->printed_strings[$string])) {
		print('<span style="color: orange;">' . $string . '</span><br>');
		$this->printed_strings[$string] = true;
	}
	return true;
}

function message($message) { 
	print('<span>' . $message . '</span><br>');
}

function message_if($string, $count) {
	if($count > 1) {
		fractal_zip::message($string);
	}
}

function message_once($string) {
	if(!isset($this->printed_strings[$string])) {
		print('<span>' . $string . '</span><br>');
		$this->printed_strings[$string] = true;
	}
	return true;
}

function good_news($message) { 
	print('<span style="color: green;">' . $message . '</span><br>');
}

function good_news_if($string, $count) {
	if($count > 1) {
		fractal_zip::good_news($string);
	}
}

function good_news_once($string) {
	if(!isset($this->printed_strings[$string])) {
		print('<span style="color: green;">' . $string . '</span><br>');
		$this->printed_strings[$string] = true;
	}
	return true;
}

}

?>