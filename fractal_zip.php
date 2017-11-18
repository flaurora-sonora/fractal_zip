<?php

class fractal_zip {

function __construct($improvement_factor_threshold = 10, $segment_length = 20000, $multipass = false) {
	$this->initial_time = time();
	$this->initial_micro_time = microtime(true);
	//include('..' . DIRECTORY_SEPARATOR . 'diff' . DIRECTORY_SEPARATOR . 'class.Diff.php');
	$this->fractal_zip_marker = 'FZ';
	$this->fractal_zip_file_extension = '.fractalzip';
	$this->fractal_zip_container_file_extension = '.fzc';
	$this->fractal_strings = array();
	$this->fractal_string = '';
	$this->equivalences = array();
	$this->branch_counter = 0;
	//$this->improvement_factor_threshold = 2; // requiring an improvement of twice as good is stringent
	//$this->improvement_factor_threshold = 10;
	//$this->improvement_factor_threshold = 1;
	//$this->improvement_factor_threshold = 0.5; // good joke
	$this->improvement_factor_threshold = $improvement_factor_threshold;
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
	$this->left_fractal_zip_marker = 'XXX9o9left9o9XXX';
	$this->mid_fractal_zip_marker = 'XXX9o9mid9o9XXX';
	$this->right_fractal_zip_marker = 'XXX9o9right9o9XXX';
	$this->range_shorthand_marker = '*';
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
			
		} elseif(is_dir($dir . DIRECTORY_SEPARATOR . $entry)) {
			fractal_zip::recursive_zip_folder($dir . DIRECTORY_SEPARATOR . $entry, $debug = false);
		} else {
			$entry_filename = $dir . DIRECTORY_SEPARATOR . $entry;
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
			
		} elseif(is_dir($dir . DIRECTORY_SEPARATOR . $entry)) {
			fractal_zip::recursive_get_strings_for_fractal_zip_markers($dir . DIRECTORY_SEPARATOR . $entry);
		} else {
			$entry_filename = $dir . DIRECTORY_SEPARATOR . $entry;
			$contents = file_get_contents($entry_filename);
			$this->strings_for_fractal_zip_markers[] = $contents;
		}
	}
	closedir($handle);
}

function create_fractal_zip_markers($dir, $debug = false) {
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
					while($length_counter > 2 * $this->minimum_overhead_length) { // roughly saying that we need at least two long enough instances to even consider working this piece
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
	$fzc_contents = serialize(array(
	$this->array_fractal_zipped_strings_of_files, 
	$this->multipass, 
	$this->branch_counter, 
	$this->left_fractal_zip_marker, 
	$this->mid_fractal_zip_marker, 
	$this->right_fractal_zip_marker, 
	$this->fractal_string
	));
	if($debug) {
		print('$this->array_fractal_zipped_strings_of_files: ');fractal_zip::var_dump_full($this->array_fractal_zipped_strings_of_files);
		print('$fzc_contents before compression: ');fractal_zip::var_dump_full($fzc_contents);
	}
	$lazy_array_fractal_zipped_strings_of_files = array();
	foreach($this->lazy_equivalences as $equivalence) {
		$lazy_array_fractal_zipped_strings_of_files[$equivalence[1]] = $equivalence[2];
	}
	//$lazy_fzc_contents = serialize(array($lazy_array_fractal_zipped_strings_of_files, $this->lazy_fractal_strings));
	$lazy_fzc_contents = serialize(array(
	$lazy_array_fractal_zipped_strings_of_files, 
	$this->multipass, 
	$this->branch_counter, 
	$this->left_fractal_zip_marker, 
	$this->mid_fractal_zip_marker, 
	$this->right_fractal_zip_marker, 
	$this->lazy_fractal_string
	));
	if($debug) {
		print('$lazy_array_fractal_zipped_strings_of_files: ');fractal_zip::var_dump_full($lazy_array_fractal_zipped_strings_of_files);
		print('$lazy_fzc_contents before compression: ');fractal_zip::var_dump_full($lazy_fzc_contents);
	}
	// gzip or LZMA?
	// http://us.php.net/manual/en/function.gzdeflate.php
	/* gzcompress produces longer data because it embeds information about the encoding onto the string. If you are compressing data that will only ever be handled on one machine, then you don't need 
	to worry about which of these functions you use. However, if you are passing data compressed with these functions to a different machine you should use gzcompress.	*/
	$fzc_contents = gzcompress($fzc_contents, 9);
	//$fzc_contents = gzcompress($fzc_contents);
	//$fzc_contents = gzencode($fzc_contents, 9);
	//$fzc_contents = gzdeflate($fzc_contents, 9);
	//$fzc_contents = bzcompress($fzc_contents);
	$lazy_fzc_contents = gzcompress($lazy_fzc_contents, 9);
	//$lazy_fzc_contents = gzcompress($lazy_fzc_contents);
	if($debug) {
		print('$fzc_contents: ');var_dump($fzc_contents);
		print('$lazy_fzc_contents: ');var_dump($lazy_fzc_contents);
		print('$this->fractal_zipping_pass: ');var_dump($this->fractal_zipping_pass);
	}
	//$last_folder_name = substr($dir, fractal_zip::strpos_last($dir, DIRECTORY_SEPARATOR));
	//print('$dir, $last_folder_name: ');var_dump($dir, $last_folder_name);
	if(strlen($lazy_fzc_contents) < strlen($fzc_contents)) {
		print('simply compressing the strings made the smaller file (' . strlen($fzc_contents) . ' &gt; ' . strlen($lazy_fzc_contents) . ')<br>');
		//file_put_contents($dir . DIRECTORY_SEPARATOR . $last_folder_name . $this->fractal_zip_container_file_extension, $lazy_fzc_contents);
		//file_put_contents($last_folder_name . $this->fractal_zip_container_file_extension, $lazy_fzc_contents);
		file_put_contents($dir . $this->fractal_zip_container_file_extension, $lazy_fzc_contents);
	} else {
		print('<span style="color: green;">fractal zipping was actually useful (' . strlen($fzc_contents) . ' &#8804; ' . strlen($lazy_fzc_contents) . ')!</span><br>');
		//file_put_contents($dir . DIRECTORY_SEPARATOR . $last_folder_name . $this->fractal_zip_container_file_extension, $fzc_contents);
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
	$root_directory_of_container_file = substr($filename, 0, fractal_zip::strpos_last($filename, DIRECTORY_SEPARATOR));
	$contents = file_get_contents($filename);
	// un-gzip or un-LZMA?
	$contents = gzuncompress($contents);
	//$contents = gzdecode($contents);
	//$contents = gzinflate($contents);
	//$contents = bzdecompress($contents);
	//$array_fractal_strings_and_equivalences = unserialize($contents);
	//$this->array_fractal_zipped_strings_of_files = $array_fractal_strings_and_equivalences[0];
	//$this->fractal_strings = $array_fractal_strings_and_equivalences[1];
	//print('$contents: ');var_dump($contents);
	$array_fractal_string_and_equivalences = unserialize($contents);
	//print('$array_fractal_string_and_equivalences: ');var_dump($array_fractal_string_and_equivalences);
	$this->array_fractal_zipped_strings_of_files = $array_fractal_string_and_equivalences[0];
	//print('$this->array_fractal_zipped_strings_of_files: ');var_dump($this->array_fractal_zipped_strings_of_files);
	$this->multipass = $array_fractal_string_and_equivalences[1];
	$this->branch_counter = $array_fractal_string_and_equivalences[2];
	$this->left_fractal_zip_marker = $array_fractal_string_and_equivalences[3];
	$this->mid_fractal_zip_marker = $array_fractal_string_and_equivalences[4];
	$this->right_fractal_zip_marker = $array_fractal_string_and_equivalences[5];
	$this->fractal_string = $array_fractal_string_and_equivalences[6];
	/*foreach($this->array_fractal_zipped_strings_of_files as $index => $value) {
		fractal_zip::build_directory_structure_for($root_directory_of_container_file . DIRECTORY_SEPARATOR . $index);
		$zipped_contents = $value;
		$unzipped_contents = fractal_zip::unzip($zipped_contents);
		file_put_contents($root_directory_of_container_file . DIRECTORY_SEPARATOR . $index, $unzipped_contents);
		print('Extracted ' . $root_directory_of_container_file . DIRECTORY_SEPARATOR . $index . '<br>');
		$this->files_counter++;
	}*/
	if($debug) {
		$micro_time_taken = microtime(true) - $this->initial_micro_time;
		print('Time taken opening container: ' . $micro_time_taken . ' seconds.<br>');
	}
}

function open_container_allowing_individual_extraction($filename, $debug = false) {
	print('Opening fractal zip container: ' . $filename . '<br>');
	$contents = file_get_contents($filename);
	$contents = gzuncompress($contents);
	$array_fractal_string_and_equivalences = unserialize($contents);
	$this->array_fractal_zipped_strings_of_files = $array_fractal_string_and_equivalences[0];
	//print('$this->array_fractal_zipped_strings_of_files: ');var_dump($this->array_fractal_zipped_strings_of_files);
	$this->multipass = $array_fractal_string_and_equivalences[1];
	$this->branch_counter = $array_fractal_string_and_equivalences[2];
	$this->left_fractal_zip_marker = $array_fractal_string_and_equivalences[3];
	$this->mid_fractal_zip_marker = $array_fractal_string_and_equivalences[4];
	$this->right_fractal_zip_marker = $array_fractal_string_and_equivalences[5];
	$this->fractal_string = $array_fractal_string_and_equivalences[6];
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
	//$root_directory_of_container_file = substr($filename, 0, fractal_zip::strpos_last($filename, DIRECTORY_SEPARATOR));
	foreach($this->array_fractal_zipped_strings_of_files as $index => $value) {
		//fractal_zip::build_directory_structure_for($root_directory_of_container_file . DIRECTORY_SEPARATOR . $index);
		fractal_zip::build_directory_structure_for($index);
		$zipped_contents = $value;
		$unzipped_contents = fractal_zip::unzip($zipped_contents);
		//file_put_contents($root_directory_of_container_file . DIRECTORY_SEPARATOR . $index, $unzipped_contents);
		//print('Extracted ' . $root_directory_of_container_file . DIRECTORY_SEPARATOR . $index . '<br>');
		file_put_contents($index, $unzipped_contents);
		print('Extracted ' . $index . '<br>');
		$this->files_counter++;
	}
	$micro_time_taken = microtime(true) - $this->initial_micro_time;
	print('Time taken extracting files from fractal_zip container: ' . $micro_time_taken . ' seconds.<br>');
}

function extract_file_from_container($filename, $file) {
	fractal_zip::open_container($filename);
	//$root_directory_of_container_file = substr($filename, 0, fractal_zip::strpos_last($filename, DIRECTORY_SEPARATOR));
	foreach($this->array_fractal_zipped_strings_of_files as $index => $value) {
		if($file === $index) {
			//fractal_zip::build_directory_structure_for($root_directory_of_container_file . DIRECTORY_SEPARATOR . $index);
			fractal_zip::build_directory_structure_for($index);
			$zipped_contents = $value;
			$unzipped_contents = fractal_zip::unzip($zipped_contents);
			//file_put_contents($root_directory_of_container_file . DIRECTORY_SEPARATOR . $index, $unzipped_contents);
			//print('Extracted ' . $root_directory_of_container_file . DIRECTORY_SEPARATOR . $index . '<br>');
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
	$folders = explode(DIRECTORY_SEPARATOR, $filename);
	//print('$folders: ');var_dump($folders);
	$folder_string = '';
	foreach($folders as $index => $folder_name) {
		//print('$folder_string: ');var_dump($folder_string);
		if($index === sizeof($folders) - 1) {
			break;
		}
		$folder_string .= $folder_name . DIRECTORY_SEPARATOR;
		if(!is_dir($folder_string)) {
			mkdir($folder_string);
		}
	}
}

function zip($string, $entry_filename, $debug = false) {
	//print('$entry_filename: ');var_dump($entry_filename);
	print('zipping: ' . $entry_filename . '<br>');
	// attempt to section the file
	// if an AI could be called to notice patterns and section the string, here would be where to do it
	if($debug) {
		print('$string: ');var_dump($string);
	}
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
	
	// we could of course get fancy and do various things; including using higher base number to save characters and using replace operations under certain insertion and deletion string length conditions
	//$this->lazy_fractal_strings[$this->files_counter] = $string;
	if(strlen($string) === 1) {
		//$lazy_fractal_zipped_string = strlen($this->lazy_fractal_string);
		//$lazy_fractal_zipped_string = $this->left_fractal_zip_marker . $this->fractal_zipping_pass . $this->mid_fractal_zip_marker . strlen($this->lazy_fractal_string) . $this->mid_fractal_zip_marker . $this->fractal_zipping_pass . $this->right_fractal_zip_marker;
		$lazy_fractal_zipped_string = fractal_zip::mark_range_string(strlen($this->lazy_fractal_string));
	} else {		
		$end_offset = strlen($this->lazy_fractal_string) + strlen($string) - 1;
		//$lazy_fractal_zipped_string = strlen($this->lazy_fractal_string) . '-' . $end_offset;
		//$lazy_fractal_zipped_string = $this->left_fractal_zip_marker . $this->fractal_zipping_pass . $this->mid_fractal_zip_marker . strlen($this->lazy_fractal_string) . '-' . $end_offset . $this->mid_fractal_zip_marker . $this->fractal_zipping_pass . $this->right_fractal_zip_marker;
		$lazy_fractal_zipped_string = fractal_zip::mark_range_string(strlen($this->lazy_fractal_string) . '-' . $end_offset);
	}
	//print('$lazy_fractal_zipped_string: ');var_dump($lazy_fractal_zipped_string);
	$this->lazy_fractal_string .= $string;
	$this->lazy_equivalences[] = array($string, $entry_filename, $lazy_fractal_zipped_string);
	//if(sizeof($this->fractal_strings) === 2) {
	//	print('debug 1: more than 2 files "zipped"<br>');print('$this->fractal_strings, $this->equivalences, $this->branch_counter in zip: ');var_dump($this->fractal_strings, $this->equivalences, $this->branch_counter);exit(0);
	//}
	// straight adding the whole string to fractal_strings
	//$this->fractal_strings[] = array($this->branch_counter, $string); // branch_id, fractal_string
	//$this->equivalences[] = array($string, $entry_filename, $this->branch_counter); // filename, string, fractal zipped expression
	//$this->branch_counter++;
	
	
	
	// adding pieces derived from comparison to fractal_strings
	//if(sizeof($this->fractal_strings) === 0) { // would probably prefer to not hard-code this
	if(strlen($this->fractal_string) === 0) { // would probably prefer to not hard-code this
		/*
		//$this->fractal_strings[] = array($this->branch_counter, $string); // branch_id, fractal_string
		$fractal_zipped_string = '';
		foreach($sections as $section) {
			if($section[1] === false) {
				$segments = str_split($section[0], $this->segment_length);
				foreach($segments as $segment) {
					$id = fractal_zip::add_fractal_string_if($segment);
					$fractal_zipped_string .= $id . ',';
					$this->branch_counter++;
				}
			} else {
				$id = fractal_zip::add_fractal_string_if($section[0]);
				$fractal_zipped_string .= $id . ',';
				$this->branch_counter++;
			}
		}
		print('$string, $entry_filename, $this->branch_counter in zip: ');var_dump($string, $entry_filename, $this->branch_counter);
		$this->equivalences[] = array($string, $entry_filename, fractal_zip::clean_ending_comma($fractal_zipped_string)); // filename, string, fractal zipped expression
		print('$this->fractal_strings, $this->equivalences: ');fractal_zip::var_dump_full($this->fractal_strings, $this->equivalences);
		$micro_time_taken = microtime(true) - $this->initial_micro_time;
		print('Time taken parsing the first file: ' . $micro_time_taken . ' seconds.<br>');
		exit(0);return;*/
		
		$this->fractal_string = $string;
		//if(strlen($string) === 1) {
		//	//$this->equivalences[] = array($string, $entry_filename, '0');
		//	$this->equivalences[] = array($string, $entry_filename, $this->left_fractal_zip_marker . '0' . $this->left_fractal_zip_marker); // this is an extremely special case and not accounted for elsewhere in the code
		//} else {
			$strlen_string_minus_one = strlen($string) - 1; // fucking php...
			//$this->equivalences[] = array($string, $entry_filename, '0-' . $strlen_string_minus_one);
			//$this->equivalences[] = array($string, $entry_filename, $this->left_fractal_zip_marker . $this->fractal_zipping_pass . $this->mid_fractal_zip_marker . '0-' . $strlen_string_minus_one . $this->mid_fractal_zip_marker . $this->fractal_zipping_pass . $this->right_fractal_zip_marker);
			$this->equivalences[] = array($string, $entry_filename, fractal_zip::mark_range_string('0-' . $strlen_string_minus_one));
		//}
		//print('$this->fractal_string, $this->equivalences: ');fractal_zip::var_dump_full($this->fractal_string, $this->equivalences);
		//$micro_time_taken = microtime(true) - $this->initial_micro_time;
		//print('Time taken parsing the first file: ' . $micro_time_taken . ' seconds.<br>');
		//exit(0);
	} else {
		/*$modified_fractal_string = '';
		$fractal_strings_to_potentially_add = array();
		$string_pieces = array();
		foreach($sections as $section) {
			if($section[1] === false) {
				$segments = str_split($section[0], $this->segment_length);
				foreach($segments as $segment) {
					$string_pieces[] = $segment;
				}
			} else {
				$string_pieces[] = $section[0];
			}
		}
		foreach($string_pieces as $string_piece) {
			foreach($this->fractal_strings as $branch_id => $fractal_string) {
				if($fractal_string[0] === $string[0]) { // simplistic filter to find useful fractal strings. something more sophisticated would, of course, be better
					$diff_array = Diff::compare($fractal_string, substr($string_piece, strlen($modified_fractal_string)), true);
					//print('$diff_array: ');var_dump($diff_array);
					//$diff_table = Diff::get_colored_comparison_table_string($diff_array);
					//print($diff_table);
					//$modified_fractal_string .= $this->fractal_strings[$index][1];
					//unset($this->fractal_strings[$index]);
					$fractal_string_to_add = '';
					$fractal_zipped_string1 = '';
					//$fractal_zipped_string2 = '';
					$diff_mode = false;
					foreach($diff_array as $diff_index => $diff_value) {
						if($diff_mode === false) {
							$diff_mode = $diff_value[1];
						}
						$fractal_string_to_add .= $diff_value[0];
						if($diff_mode !== $diff_array[$diff_index + 1][1]) {
							if($diff_mode === 0) {
								$fractal_zipped_string1 .= $this->branch_counter . ',';
								//$fractal_zipped_string2 .= $this->branch_counter . ',';
								$fractal_strings_to_potentially_add[$this->branch_counter] = $fractal_string_to_add;
								$this->branch_counter++;
							} elseif($diff_mode === 1) {
								//$fractal_zipped_string2 .= $this->branch_counter . ',';
							} elseif($diff_mode === 2) {
								$fractal_zipped_string1 .= $this->branch_counter . ',';
								$fractal_strings_to_potentially_add[$this->branch_counter] = $fractal_string_to_add;
								$this->branch_counter++;
							} else {
								print('should never get here23658970980-');exit(0);
							}
							//$this->fractal_strings[$this->branch_counter] = $fractal_string_to_add; // branch_id, fractal_string
							//$modified_fractal_string .= $fractal_string_to_add;
							$fractal_string_to_add = '';
							$diff_mode = $diff_array[$diff_index + 1][1];
						}
					}
					//if(strlen($modified_fractal_string) === strlen($string)) {
					//	break 2;
					//}
				}
			}
		}
		$total_length_of_fractal_strings_to_add = 0;
		foreach($fractal_strings_to_potentially_add as $branch_id => $fractal_string_to_add) {
			$total_length_of_fractal_strings_to_add += strlen($fractal_string_to_add);
		}
		//print('$fractal_strings_to_potentially_add, $fractal_zipped_string, $fractal_zipped_string1: ');var_dump($fractal_strings_to_potentially_add, $fractal_zipped_string, $fractal_zipped_string1);
		//if($total_length_of_fractal_strings_to_add + strlen($fractal_zipped_string1) > strlen($string) + strlen($this->branch_counter)) { // then fractalizing it is not worth it
		//foreach($fractal_strings_to_potentially_add as $branch_id => $fractal_string_to_add) {
		//}
		//print('$total_length_of_fractal_strings_to_add, strlen($fractal_zipped_string1), strlen($string), strlen($this->branch_counter): ');var_dump($total_length_of_fractal_strings_to_add, strlen($fractal_zipped_string1), strlen($string), strlen($this->branch_counter));
		// sqrt is an approximation for how likely it is for the fractionation to be useful, rather than calculating what the end result on filesize will be (which would be difficult). natural logarithm could be superior
		//if(($total_length_of_fractal_strings_to_add + strlen($fractal_zipped_string1)) > 4 * (strlen($string) + strlen($this->branch_counter))) { // then fractalizing it is not worth it
		//if(sqrt(sizeof($fractal_strings_to_potentially_add)) * ($total_length_of_fractal_strings_to_add + strlen($fractal_zipped_string1)) > sqrt(sizeof($this->equivalences)) * (strlen($string) + strlen($this->branch_counter))) { // then fractalizing it is not worth it
		if(($total_length_of_fractal_strings_to_add + strlen($fractal_zipped_string1)) > log(sizeof($this->equivalences)) * (strlen($string) + strlen($this->branch_counter))) { // then fractalizing it is not worth it
			// is this ever worthwhile? have to keep the faith... what sort of fanciness is required to overcome the bit/byte level zipping's results? can already compressed data be inhabited or do we have to uncompress it to make any use of it?
			print('$string, $entry_filename, $this->branch_counter in zip2: ');var_dump($string, $entry_filename, $this->branch_counter);
			$fractal_zipped_string = '';
			foreach($sections as $section) {
				if($section[1] === false) {
					$segments = str_split($section[0], $this->segment_length);
					foreach($segments as $segment) {
						$id = fractal_zip::add_fractal_string_if($segment);
						$fractal_zipped_string .= $id . ',';
						$this->branch_counter++;
					}
				} else {
					$id = fractal_zip::add_fractal_string_if($section[0]);
					$fractal_zipped_string .= $id . ',';
					$this->branch_counter++;
				}
			}
		} else {
			foreach($fractal_strings_to_potentially_add as $branch_id => $fractal_string_to_add) {
				$this->fractal_strings[$branch_id] = $fractal_string_to_add; // branch_id, fractal_string
			}
			$fractal_zipped_string = $fractal_zipped_string1;
			//$this->equivalences[] = array($string, $entry_filename, fractal_zip::clean_ending_comma($fractal_zipped_string1)); // filename, string, fractal zipped expression
			// some sort of cleanup...
			//foreach($this->equivalences as $equivalence_index => $equivalence) {
			//	$equivalence_string = $equivalence[0];
			//	$equivalence_filename = $equivalence[1];
			//	//$equivalence_fractal_zipped_string = $equivalence[2];
			//	if($equivalence_string === $modified_fractal_string) {
			//		$this->equivalences[$equivalence_index] = array($equivalence_string, $equivalence_filename, fractal_zip::clean_ending_comma($fractal_zipped_string2));
			//	}
			//}
		}*/
		
		// special case of an identical string already having been fractal zipped
		foreach($this->equivalences as $equivalence) {
			$equivalence_string = $equivalence[0];
			if($string === $equivalence_string) {
				$this->equivalences[] = array($string, $entry_filename, $equivalence[2]);
				//print('$this->fractal_string, $this->equivalences, $this->branch_counter in zip: ');var_dump($this->fractal_string, $this->equivalences, $this->branch_counter);
				if($debug) {
					print('special case of an identical string already having been fractal zipped<br>');
				}
				return true;
			}
		}
		// what's faster, using strpos character-wise or doing a compare to get the piece that is the same?
		// compare takes longer and results in a bigger file even on short strings, so that would seem to be that.
		$fractal_zipped_string = '';
		
		/*$diff_array = Diff::compare($this->fractal_string, $string, true);
		//print('$diff_array: ');var_dump($diff_array);
		//$diff_table = Diff::get_colored_comparison_table_string($diff_array);
		//print($diff_table);
		$fractal_string_to_add = '';
		$start_index = 0;
		$deleted_count = 0;
		$inserted_count = 0;
		foreach($diff_array as $diff_index => $diff_value) {
			$fractal_string_to_add .= $diff_value[0];
			if($diff_value[1] !== $diff_array[$diff_index + 1][1]) {
				if($diff_value[1] === 0) {
					if(strlen($fractal_string_to_add) === 1) {
						$fractal_zipped_string .= $start_index . ',';
					} else {
						$end_index = $diff_index - $inserted_count;
						$fractal_zipped_string .= $start_index . '-' . $end_index . ',';
					}
				} elseif($diff_value[1] === 1) {
					$deleted_count += strlen($fractal_string_to_add);
				} elseif($diff_value[1] === 2) {
					$inserted_count += strlen($fractal_string_to_add);
					$start_index = strlen($this->fractal_string);
					$this->fractal_string .= $fractal_string_to_add;
					if(strlen($fractal_string_to_add) === 1) {
						$fractal_zipped_string .= $start_index . ',';
					} else {
						$end_index = strlen($this->fractal_string) - 1 - $deleted_count;
						$fractal_zipped_string .= $start_index . '-' . $end_index . ',';
					}
				} else {
					print('should never get here23658970982-');exit(0);
				}
				$start_index = $diff_index - $inserted_count + 1;
				$fractal_string_to_add = '';
			}
		}*/
		
		$this->shorthand_counter = 1;
		$this->saved_shorthand = array();
		$counter = 0;
		$saved_piece = '';
		// alter the scope according to filesize; approximating how the size of a fractal container determines how the scope of the viewer will start
		//$fractal_scope_factor = strlen($string) / 100000;
		//if($fractal_scope_factor < 1) {
		//	$fractal_scope_factor = 1;
		//}
		while($counter < strlen($string)) {
			//print('here3758596070<br>');
			$sliding_counter = $counter;
			if(strlen($saved_piece) > 0) {
				//print('here3758596070.1<br>');
				//$piece = $saved_piece;
				$substring = substr($string, $sliding_counter, (2 * $this->minimum_overhead_length) - strlen($saved_piece)); // roughly saying that we need at least two long enough instances to even consider working this piece
				$sliding_counter += strlen($substring);
				$piece = $saved_piece . $substring;
			} else {
				//print('here3758596070.2<br>');
				//$piece = '';
				$substring = substr($string, $sliding_counter, 2 * $this->minimum_overhead_length); // roughly saying that we need at least two long enough instances to even consider working this piece
				$sliding_counter += strlen($substring);
				$piece = $substring;
			}
			//print('$piece after substring: ');var_dump($piece);
			$matched_piece_exists = false;
			$match_position = strpos($this->fractal_string, $piece . $string[$sliding_counter]);
			$found_a_better_match = true;
			if($match_position !== false) {
				while($found_a_better_match) {
					//print('here3758596071<br>');
					$found_a_better_match = false;
					while($sliding_counter < strlen($string) && $string[$sliding_counter] === $this->fractal_string[$match_position + strlen($piece)] && strlen($piece) < $this->segment_length) {
						//print('here3758596072<br>');
						$piece .= $string[$sliding_counter];
						$sliding_counter++;
						$start_offset = $match_position;
						$matched_piece_exists = true;
					}
					//print('$piece after matching: ');var_dump($piece);
					if(strlen($piece) === $this->segment_length || $sliding_counter === strlen($string)) {
						//print('here3758596072.1<br>');
						$end_offset = strlen($piece) + $start_offset - 1;
						//$range_string = $this->left_fractal_zip_marker . $this->fractal_zipping_pass . $this->mid_fractal_zip_marker . $start_offset . '-' . $end_offset . $this->mid_fractal_zip_marker . $this->fractal_zipping_pass . $this->right_fractal_zip_marker;
						$range_string = $start_offset . '-' . $end_offset;
						$marked_range_string = fractal_zip::mark_range_string($range_string);
						if(strlen($piece) / strlen($marked_range_string) > $this->improvement_factor_threshold) {
							//print('here3758596072.2<br>');
							//print('adding $range_string (' . $range_string . ') to $fractal_zipped_string.<br>');
							//$fractal_zipped_string .= $range_string;
							$fractal_zipped_string = fractal_zip::shorthand_add($fractal_zipped_string, $range_string);
						} else {
							//print('here3758596072.3<br>');
							//print('$counter: ');var_dump($counter);
							//print('$sliding_counter: ');var_dump($sliding_counter);
							//print('$saved_piece: ');var_dump($saved_piece);
							//print('adding $piece (' . $piece . ') to $fractal_zipped_string.<br>');
							$fractal_zipped_string .= $piece;
						}
						$counter = $sliding_counter;
						//print('$counter after segment: ');var_dump($counter);
						//if(strlen($piece) === $this->segment_length && $sliding_counter === strlen($string)) { // um
						//	print('adding $string[strlen($string) - 1] (' . $string[strlen($string) - 1] . ') to $fractal_zipped_string.<br>');
						//	$fractal_zipped_string .= $string[strlen($string) - 1];
						//}
						continue 2;
					}
					// look for a better match
					$next_match_position = strpos($this->fractal_string, $piece, $match_position + 1);
					while($next_match_position !== false) {
						//print('here3758596073<br>');
						if($string[$sliding_counter] === $this->fractal_string[$next_match_position + strlen($piece)]) {
							$piece .= $string[$sliding_counter];
							$sliding_counter++;
							$start_offset = $match_position = $next_match_position;
							$found_a_better_match = true;
							break;
						}
						$next_match_position = strpos($this->fractal_string, $piece, $next_match_position + 1);
					}
				}
			} else {
				if(strlen($saved_piece) > 0) {
					//print('here3758596073.12<br>');
					//print('$saved_piece: ');var_dump($saved_piece);
					//print('adding $saved_piece (' . $saved_piece . ') to $fractal_zipped_string.<br>');
					$fractal_zipped_string .= $saved_piece;
					//$counter += strlen($saved_piece);
					$saved_piece = '';
				} else {
					//print('here3758596073.13<br>');
					//print('$saved_piece: ');var_dump($saved_piece);
					//print('adding $string[$counter] (' . $string[$counter] . ') to $fractal_zipped_string.<br>');
					$fractal_zipped_string .= $string[$counter];
					$counter++;
				}
				continue;
			}
			if($matched_piece_exists) {
				//print('here3758596073.2<br>');
				/*$end_offset = strlen($piece) + $start_offset - 1;
				$saved_range_string = $this->left_fractal_zip_marker . $this->fractal_zipping_pass . $this->mid_fractal_zip_marker . $start_offset . '-' . $end_offset . $this->mid_fractal_zip_marker . $this->fractal_zipping_pass . $this->right_fractal_zip_marker;
				if(strlen($piece) / strlen($range_string) > $this->improvement_factor_threshold) {
					$fractal_zipped_string .= $range_string;
				} else {
					$fractal_zipped_string .= $piece;
				}*/
				if(strlen($piece) > $this->minimum_overhead_length) {
					//print('here3758596073.3 piece: <br>');var_dump($piece);
					if(strlen($saved_piece) === 0) {
						//print('here3758596073.31<br>');
						$saved_piece = $piece;
					} else {
						//print('here3758596073.32<br>');
						$start_offset = strlen($this->fractal_string);
					}
					$end_offset = strlen($saved_piece) + $start_offset - 1;
					//$range_string = $this->left_fractal_zip_marker . $this->fractal_zipping_pass . $this->mid_fractal_zip_marker . $start_offset . '-' . $end_offset . $this->mid_fractal_zip_marker . $this->fractal_zipping_pass . $this->right_fractal_zip_marker;
					$range_string = $start_offset . '-' . $end_offset;
					$marked_range_string = fractal_zip::mark_range_string($range_string);
					//print('here3758596073.4 $range_string:<br>');var_dump($range_string);
					if(strlen($saved_piece) / strlen($marked_range_string) > $this->improvement_factor_threshold) {
						//print('here3758596073.41<br>');
						//print('adding $range_string (' . $range_string . ') to $fractal_zipped_string.<br>');
						//$fractal_zipped_string .= $range_string;
						$fractal_zipped_string = fractal_zip::shorthand_add($fractal_zipped_string, $range_string);
						//$this->fractal_string .= $saved_piece;
					} else {
						//print('here3758596073.42<br>');
						//print('adding $saved_piece (' . $saved_piece . ') to $fractal_zipped_string.<br>');
						$fractal_zipped_string .= $saved_piece;
						//$this->fractal_string .= $saved_piece;
					}
					//print('here3758596073.5<br>');
					$saved_piece = '';
				} else {
					//print('here3758596073.51<br>');
					//print('adding $piece (' . $piece . ') to $fractal_zipped_string.<br>');
					$fractal_zipped_string .= $piece;
				}
				//print('here3758596073.52<br>');
				//$piece = '';
				if(strlen($saved_piece) > 0) {
					//print('here3758596073.6<br>');
					//print('adding $piece (' . $piece . ') to $saved_piece.<br>');
					$saved_piece .= $piece;
				}// else {
				//	print('here3758596073.7<br>');
				//	$saved_piece = $piece;
				//}
				$counter += strlen($piece);
			}
			$unmatched_piece_exists = false;
			while($counter < strlen($string) && strpos($this->fractal_string, $string[$counter]) === false) {
				//print('here3758596074<br>');
				$unmatched_piece_exists = true;
				$initial_character = $piece = $string[$counter];
				//$initial_character = $string[$counter];
				//$piece .= $initial_character;
				while($string[$counter + 1] === $initial_character) {
					//print('here3758596075<br>');
					$piece .= $initial_character;
					$counter++;
				}
				if(strlen($saved_piece) > 0) {
					//print('here3758596076<br>');
					//print('adding $piece (' . $piece . ') to $saved_piece.<br>');
					$saved_piece .= $piece;
				} else {
					//print('here3758596077<br>');
					if(strlen($piece) > $this->minimum_overhead_length) {
						//print('here3758596077.1<br>');
						$start_offset = strlen($this->fractal_string);
						$end_offset = strlen($piece) + $start_offset - 1;
						//$range_string = $this->left_fractal_zip_marker . $this->fractal_zipping_pass . $this->mid_fractal_zip_marker . $start_offset . '-' . $end_offset . $this->mid_fractal_zip_marker . $this->fractal_zipping_pass . $this->right_fractal_zip_marker;
						$range_string = $start_offset . '-' . $end_offset;
						$marked_range_string = fractal_zip::mark_range_string($range_string);
						if(strlen($piece) / strlen($marked_range_string) > $this->improvement_factor_threshold) {
							//print('here3758596077.11<br>');
							//$fractal_zipped_string .= $range_string;
							$fractal_zipped_string = fractal_zip::shorthand_add($fractal_zipped_string, $range_string);
						} else {
							//print('here3758596077.12<br>');
							//print('adding $piece (' . $piece . ') to $fractal_zipped_string.<br>');
							$fractal_zipped_string .= $piece;
						}
						//print('adding $piece (' . $piece . ') to $this->fractal_string.<br>');
						$this->fractal_string .= $piece;
					} else {
						//print('here3758596077.2<br>');
						//print('adding $piece (' . $piece . ') to $saved_piece.<br>');
						$saved_piece .= $piece;
					}
				}
				$counter++;
			}
			if(!$matched_piece_exists && !$unmatched_piece_exists) {
				//print('here3758596078<br>');
				//print('$piece, $saved_piece, $counter, $sliding_counter: ');var_dump($piece, $saved_piece, $counter, $sliding_counter);
				//if(strlen($saved_piece) > 0) {
					//print('here3758596079.0<br>');
					$saved_piece .= $string[$counter];
					//$counter++;
				//}
				if(strlen($saved_piece) > 0) {
					$counter++;
				}
			}
		}
		//print('here3758596080<br>');
		//print('$counter: ');var_dump($counter);
		if(strlen($saved_piece) > 0) {
			//print('here3758596081<br>');
			$start_offset = strlen($this->fractal_string);
			$end_offset = strlen($saved_piece) + $start_offset - 1;
			//$range_string = $this->left_fractal_zip_marker . $this->fractal_zipping_pass . $this->mid_fractal_zip_marker . $start_offset . '-' . $end_offset . $this->mid_fractal_zip_marker . $this->fractal_zipping_pass . $this->right_fractal_zip_marker;
			$range_string = $start_offset . '-' . $end_offset;
			$marked_range_string = fractal_zip::mark_range_string($range_string);
			if(strlen($saved_piece) / strlen($marked_range_string) > $this->improvement_factor_threshold) {
				//print('here3758596082<br>');
				//$fractal_zipped_string .= $range_string;
				$fractal_zipped_string = fractal_zip::shorthand_add($fractal_zipped_string, $range_string);
				$this->fractal_string .= $saved_piece;
			} else {
				//print('here3758596083<br>');
				$fractal_zipped_string .= $saved_piece;
			}
		}
		
		//$this->equivalences[] = array($string, $entry_filename, fractal_zip::clean_ending_comma($fractal_zipped_string));
		$this->equivalences[] = array($string, $entry_filename, $fractal_zipped_string);
		//print('$fractal_zipped_string: ');fractal_zip::var_dump_full($fractal_zipped_string);
		//print('$this->fractal_string, $this->equivalences, $counter in zip: ');var_dump($this->fractal_string, $this->equivalences, $counter);
		//exit(0);
	}
//	fractal_zip::clean_fractal_strings();
	if($debug) {
		print('$this->fractal_string, $this->equivalences, $this->branch_counter in zip: ');var_dump($this->fractal_string, $this->equivalences, $this->branch_counter);
	}
	return true;
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

function unzip($string) {
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
	return $unzipped_string;
	//return $string;
}

function validate_fractal_zip($entry_filename) {
	print('$entry_filename, $this->equivalences: ');fractal_zip::var_dump_full($entry_filename, $this->equivalences);
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
	print('$equivalence_fractal_zipped_expression, fractal_zip::unzip($equivalence_fractal_zipped_expression), $equivalence_string, $this->fractal_string: ');fractal_zip::var_dump_full($equivalence_fractal_zipped_expression, fractal_zip::unzip($equivalence_fractal_zipped_expression), $equivalence_string, $this->fractal_string);
	exit(0);
	return false;
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

function preg_escape($string) {
	return str_replace('/', '\/', preg_quote($string));
}

function fatal_error($message) { 
	print('<span style="color: red;">' . $message . '</span>');exit(0);
}

function warning($message) { 
	print('<span style="color: orange;">' . $message . '</span><br>');
}

function fatal_error_once($string) {
	if(!isset($this->printed_strings[$string])) {
		print('<span style="color: red;">' . $string . '</span>');exit(0);
		$this->printed_strings[$string] = true;
	}
	return true;
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

}

?>