<?php

$file = __DIR__.'/ex.html';

$read = file_get_contents($file, true);

$explode = explode('</tr>', $read);

$providers = ['chart', 'apple', 'twitter', 'one', 'google', 'samsung', 'wind', 'gmail' ,'sb', 'dcm', 'kddi'];

$response = [];

foreach($explode as $tr){
	$count = 0;
	$name = null;
	$lineExplode = explode(PHP_EOL, $tr);
	
	if(contains($tr, '<td class="name">')){
		foreach($lineExplode as $line){
			if(contains($line, '<td class="name">')){
				$name = str_replace(' ', '_', str_replace('</td>', '', str_replace('<td class="name">', '', $line)));
				$name = str_replace(',', '', mb_strtoupper($name, 'UTF-8'));
				break;
			}
		}
	}
	
	foreach($lineExplode as $line){
		if(contains($line, '<td class="code"><a href="')){
			//$code = preg_match_all('@name="">(.+?)</a></td>@', $line);
			$code = preg_match_all('~">U(.+?)<\/a><\/td>~', $line, $match);
			//$code = preg_match_all('/">U(.+?)<\/a><\/td>/', $line, $match);
			$code = 'U'.$match[1][0];
			if(contains($code, ' ')){
				$dualCode = explode(' ', $code);
				foreach($dualCode as $key => $value){
					$response[$name]['code'.($key === 0 ? '' : $key)] = $value;
				}
			}else{
				$response[$name]['code'] = $code;
			}
		}elseif(contains($line, '<td class="andr')){
			$src = preg_match_all('~src="(.+?)"><\/td>~', $line, $match);
			$response[$name][$providers[$count]] = $match[1][0];
			$count++;
		}
		
		if($count > 10){
			$count = 0;
			break;
		}
	}
}

function contains($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if ($needle != '' && mb_strpos($haystack, $needle) !== false) {
                return true;
            }
        }

        return false;
    }
	
$json = __DIR__.'/emoji.json';

file_put_contents($json, str_replace('\\', '', json_encode($response, 128)));

print 'done';