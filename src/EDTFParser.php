<?php

namespace EDTF;

use EDTF\EDTFDateTime as EDTFDateTime;

class EDTFParser
{
	
	public static string
	$regexPattern = "/(?x) # Turns on free spacing mode for easier readability					
					
					# Year start
						(?<year>					
							(?<yearnum>[+-]?(?:\d+e\d+|[0-9u][0-9ux]*))
							(?>S # Literal S letter. It is for the significant digit indicator
							(?<yearprecision>\d+))?
							(?<yearend>\)?[~?]{0,2})
						)
					# Year end
					
					(?>- # Literal - (hyphen)
					
					# Month start										
						(?<month>						
							(?<monthopenparens>\(+)?							
							(?<monthnum>
								(?>1[0-9u]|[0u][0-9u]|2[1-4])
							)								
							(?>\^							
								(?<seasonqualifier>[\P{L}\P{N}\P{M}:.-]+)
							)?								
						)
												
						(?<monthend>(?:\)?[~?]{0,2}){0,2})						
					# Month end
					
					(?>- # Literal - (hyphen)
					
					# Day start
						(?<day>
						(?<dayopenparens>\(+)?
						(?<daynum>(?>[012u][0-9u]|3[01u])))
						(?<dayend>[)~?]*)
					# Day end
					
					# Others start #
						(?>T # Literal T
						(?<hour>2[0-3]|[01][0-9]):					
						(?<minute>[0-5][0-9]):					
						(?<second>[0-5][0-9])(?>					
						(?<tzutc>Z)|					
						(?<tzsign>[+-])					
						(?<tzhour>[01][0-9]):					
						(?<tzminute>[0-5][0-9]))?)?)?)?$
					# Others end #
					/";
	
	public static bool $isItDatePair = FALSE;
	public static array $parsedEDTFArray;
	public static array $startDateArray;
	public static array $endDateArray;
	
	
	public static array $onlyDateArray = array(
		'second' => '',
		'minute' => '',
		'hour' => '',
		'daynum' => '',
		'monthnum' => '',
		'yearnum' => ''		
	);
	
	
	public static function parseEDTFDate(string $dateStr): void
	{		
		$dateDelimiter = "/";
		$pos = strpos($dateStr, $dateDelimiter);	
		if ($pos === false) {
			preg_match( static::$regexPattern, $dateStr, $singleDateArr );
			static::$isItDatePair = FALSE;				
			static::$parsedEDTFArray = array_map('strval', $singleDateArr);
			static::$onlyDateArray = array_map('strval', $singleDateArr);
		} else {			
			$startDateStr = substr( $dateStr, 0, strrpos( $dateStr, '/' ) );
			$endDateStr   = substr( $dateStr, strrpos( $dateStr, '/' ) + 1 );			
			preg_match( static::$regexPattern, $startDateStr, $startDateArr );
			preg_match( static::$regexPattern, $endDateStr, $endDateArr );
			static::$isItDatePair = TRUE;
			static::$parsedEDTFArray = array( $startDateArr , $endDateArr);
			static::$startDateArray = $startDateArr;
			static::$endDateArray = $endDateArr;
		}
	}

	public static function getStartDate(): EDTFDateTime {

		if ( EDTFParser::$isItDatePair ) {
			$dateTime = new EDTFDateTime( 
					static::$startDateArray['second'],
					static::$startDateArray['minute'],
					static::$startDateArray['hour'],
					static::$startDateArray['daynum'],
					static::$startDateArray['monthnum'],
					static::$startDateArray['yearnum']
				);			
			return $dateTime;
		} else {
			$dateTime = new EDTFDateTime( 
				'',
				'',
				'',
				'',
				'',
				''
			);			
		}
		return $dateTime;		
	}	

	public static function getEndDate(): EDTFDateTime {

		if ( EDTFParser::$isItDatePair ) {
			$dateTime = new EDTFDateTime( 
					static::$endDateArray['second'],
					static::$endDateArray['minute'],
					static::$endDateArray['hour'],
					static::$endDateArray['daynum'],
					static::$endDateArray['monthnum'],
					static::$endDateArray['yearnum']
				);			
			return $dateTime;
		} else {
			$dateTime = new EDTFDateTime( 
				'',
				'',
				'',
				'',
				'',
				''
			);			
		}
		return $dateTime;		
	}
	
	public static function getOnlyDate(): EDTFDateTime {

		if ( !EDTFParser::$isItDatePair ) {			

			$second   = array_key_exists( 'second',   static::$onlyDateArray ) ? static::$onlyDateArray['second'] : '';
			$minute   = array_key_exists( 'minute',   static::$onlyDateArray ) ? static::$onlyDateArray['minute'] : '';
			$hour 	  = array_key_exists( 'hour',     static::$onlyDateArray ) ? static::$onlyDateArray['hour'] : '';
			$daynum   = array_key_exists( 'daynum',   static::$onlyDateArray ) ? static::$onlyDateArray['daynum'] : '';
			$monthnum = array_key_exists( 'monthnum', static::$onlyDateArray ) ? static::$onlyDateArray['monthnum'] : '';
			$yearnum  = array_key_exists( 'yearnum',  static::$onlyDateArray ) ? static::$onlyDateArray['yearnum'] : '';			
			$tzhour   = array_key_exists( 'tzhour',   static::$onlyDateArray ) ? static::$onlyDateArray['tzhour'] : '';
			$tzminute = array_key_exists( 'tzminute', static::$onlyDateArray ) ? static::$onlyDateArray['tzminute'] : '';
			$tzutc    = array_key_exists( 'tzutc',    static::$onlyDateArray ) ? static::$onlyDateArray['tzutc'] : '';
			
			$dateTime = new EDTFDateTime(
					$tzutc,
					$tzminute,
					$tzhour,
					$second,
					$minute,
					$hour,
					$daynum,
					$monthnum,
					$yearnum
				);			
			return $dateTime;
		} else {
			$dateTime = new EDTFDateTime( 
				'',
				'',
				'',
				'',
				'',
				''
			);
		}
		return $dateTime;		
	}
}