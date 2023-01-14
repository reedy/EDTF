<?php

declare( strict_types = 1 );

namespace EDTF\PackagePrivate\Humanizer;

use EDTF\EdtfValue;
use EDTF\Humanizer;
use EDTF\Model\ExtDate;
use EDTF\Model\ExtDateTime;
use EDTF\Model\Interval;
use EDTF\Model\Qualification;
use EDTF\Model\Season;
use EDTF\Model\UnspecifiedDigit;
use EDTF\PackagePrivate\Humanizer\Internationalization\MessageBuilder;
use EDTF\PackagePrivate\Humanizer\Strategy\LanguageStrategy;

class InternationalizedHumanizer implements Humanizer {

	private const SEASON_MAP = [
		21 => 'edtf-spring',
		22 => 'edtf-summer',
		23 => 'edtf-autumn',
		24 => 'edtf-winter',
		25 => 'edtf-spring-north',
		26 => 'edtf-summer-north',
		27 => 'edtf-autumn-north',
		28 => 'edtf-winter-north',
		29 => 'edtf-spring-south',
		30 => 'edtf-summer-south',
		31 => 'edtf-autumn-south',
		32 => 'edtf-winter-south',
		33 => 'edtf-quarter-1',
		34 => 'edtf-quarter-2',
		35 => 'edtf-quarter-3',
		36 => 'edtf-quarter-4',
		37 => 'edtf-quadrimester-1',
		38 => 'edtf-quadrimester-2',
		39 => 'edtf-quadrimester-3',
		40 => 'edtf-semester-1',
		41 => 'edtf-semester-2',
	];

	private const MONTH_MAP = [
		1 => 'edtf-january',
		2 => 'edtf-february',
		3 => 'edtf-march',
		4 => 'edtf-april',
		5 => 'edtf-may',
		6 => 'edtf-june',
		7 => 'edtf-july',
		8 => 'edtf-august',
		9 => 'edtf-september',
		10 => 'edtf-october',
		11 => 'edtf-november',
		12 => 'edtf-december',
	];

	private MessageBuilder $messageBuilder;
	private LanguageStrategy $languageStrategy;

	public function __construct( MessageBuilder $messageBuilder, LanguageStrategy $languageStrategy ) {
		$this->messageBuilder = $messageBuilder;
		$this->languageStrategy = $languageStrategy;
	}

	public function humanize( EdtfValue $edtf ): string {
		if ( $edtf instanceof ExtDate ) {
			return $this->humanizeDate( $edtf );
		}

		if ( $edtf instanceof ExtDateTime ) {
			return $this->humanizeDateTime( $edtf );
		}

		if ( $edtf instanceof Season ) {
			return $this->humanizeSeason( $edtf );
		}

		if ( $edtf instanceof Interval ) {
			return $this->humanizeInterval( $edtf );
		}

		return '';
	}

	private function humanizeSeason( Season $season ): string {
		return $this->message(
			'edtf-season-and-year',
			$this->message( self::SEASON_MAP[$season->getSeason()] ),
			(string)$season->getYear()
		);
	}

	/**
	 * @param string[] $parts
	 */
	private function humanSeparator( array $parts ) : string {
		if ( !count( $parts ) ) {
			return "";
		}

		if ( count( $parts ) === 1 ) {
			return current( $parts );
		}

		$last = array_pop( $parts );
		return implode( ', ', $parts ) . $this->message( 'edtf-and' ) . $last;
	}

	private function humanizedDateByMessage( string $humanizedDate, string $msgKey ) : string {
		if ( $this->languageStrategy->monthUppercaseFirst()
			|| strpos( $this->message( $msgKey ), '$' ) === 0 ) {
			return $humanizedDate;
		}
		
		return strtolower( $humanizedDate );
	}

	private function composeMessage( ExtDate $date, string $humanizedDate ) : string {
		$qualification = $date->getQualification();

		$parts = [
			'uncertain' => $qualification->getUncertainParts(),
			'approximate' => $qualification->getApproximateParts(),
			'uncertain-and-approximate' => $qualification->getUncertainAndApproximateParts(),
		];

		// this data-structure, together with the loop below
		// cannot be moved to the Qualification class since
		// Qualification::UNDEFINED does NOT return the parts
		// of the date that are NULL. So where this is not
		// suitable to the Qualification class (i.e. it does
		// not fit the purpose of that class) some of the logic
		// in this method could be moved to the ExtDate class itself
			
		$undefinedParts = array_filter( [
			$date->getDay() === NULL,
			$date->getMonth() === NULL,
			$date->getYear() === NULL
		] );

		// check if whole date is uncertain, approximate, or uncertain and approximate
		foreach ( $parts as $msgKey => $uncertainty ) {
			if ( count( $undefinedParts ) + count( $uncertainty ) === 3 ) {
				return $this->message( 'edtf-' . $msgKey, $this->humanizedDateByMessage( $humanizedDate, 'edtf-' . $msgKey ) )
					. $this->message( 'edtf-date-' . $msgKey, $humanizedDate );
			}
		}

		// 'edtf-day', 'edtf-month','edtf-year'
		$partToMsg = function( string $value ) : string {
			return $this->message( 'edtf-' . $value );
		};

		$outerMsg = '';
		$portions = [];
		foreach ( $parts as $msgKey => $parts ) {
			if ( count( $parts ) ) {
				$portions[] = $this->humanSeparator( array_map( $partToMsg, $parts ) )
					. $this->message( 'edtf-parts-' . $msgKey, (string)count( $parts ) );
				$outerMsg = $msgKey;
			}
		}

		return $this->message( 'edtf-' . $outerMsg, $this->humanizedDateByMessage( $humanizedDate, 'edtf-' . $outerMsg ) )
			. ' (' . $this->humanSeparator( $portions ) . ')';
	}

	private function humanizeDate( ExtDate $date ): string {
		$humanizedDate = $this->humanizeDateWithoutUncertainty( $date );
		
		if ( !$date->getQualification()->isFullyKnown() ) {
			return $this->composeMessage( $date, $humanizedDate );
		}

		return $humanizedDate;
	}

	private function message( string $key, string ...$parameters ): string {
		return $this->messageBuilder->buildMessage( $key, ...$parameters );
	}

	private function humanizeDateWithoutUncertainty( ExtDate $date ): string {
		$year = $date->getYear();
		$month = $date->getMonth();
		$day = $date->getDay();

		if ( $year !== null ) {
			$year = $this->humanizeYear(
				$year,
				$date->getUnspecifiedDigit()
			);
		}

		if ( $month !== null ) {
			$month = $this->message( self::MONTH_MAP[$month] );
			if ( $day === null ) {
				$month = ucfirst( $month );
			}
		}

		if ( $day !== null ) {
			$day = $this->languageStrategy->applyOrdinalEnding( $day );
		}

		return $this->humanizeYearMonthDay( $year, $month, $day );
	}

	private function humanizeYearMonthDay( ?string $year, ?string $month, ?string $day ): string {
		if ( $year !== null && $month !== null && $day !== null ) {
			return $this->message( 'edtf-full-date', $year, $month, $day );
		}

		if ( $year !== null && $month === null && $day !== null ) {
			return $this->message( 'edtf-day-and-year', $day, $year );
		}

		return implode(
			' ',
			array_filter( [ $month, $day, $year ], 'is_string' )
		);
	}

	private function humanizeYear( int $year, UnspecifiedDigit $unspecifiedDigit ): string {
		$yearStr = (string)abs( $year );

		if ( $year <= -1000 ) {
			return $this->message( 'edtf-bc-year', $yearStr );
		}

		if ( $year < 0 ) {
			return $this->message( 'edtf-bc-year-short', $yearStr );
		}

		$endingChar = $this->needsYearEndingChar( $unspecifiedDigit ) ? 's' : '';

		if ( $year < 1000 ) {
			return $this->message( 'edtf-year-short', $yearStr . $endingChar );
		}

		return $yearStr . $endingChar;
	}

	/**
	 * Check, do we need to add 's' char to humanized year representation
	 * This can be applicable to unspecified years i.e. 197X or 19XX
	 */
	private function needsYearEndingChar( UnspecifiedDigit $unspecifiedDigit ): bool {
		return $unspecifiedDigit->century() || $unspecifiedDigit->decade();
	}

	private function humanizeInterval( Interval $interval ): string {
		if ( $interval->isNormalInterval() ) {
			$humanizedStartDate = $this->humanize( $interval->getStartDate() );
			$humanizedEndDate = $this->humanize( $interval->getEndDate() );

			return $this->message(
				'edtf-interval-normal',
				!$this->languageStrategy->monthUppercaseFirst() ? strtolower(
					$humanizedStartDate
				) : $humanizedStartDate,
				!$this->languageStrategy->monthUppercaseFirst() ? strtolower( $humanizedEndDate ) : $humanizedEndDate
			);
		}

		if ( $interval->hasOpenEnd() ) {
			return $this->message(
				'edtf-interval-open-end',
				$this->humanize( $interval->getStartDate() )
			);
		}

		if ( $interval->hasOpenStart() ) {
			return $this->message(
				'edtf-interval-open-start',
				$this->humanize( $interval->getEndDate() )
			);
		}

		if ( $interval->hasUnknownEnd() ) {
			return $this->message(
				'edtf-interval-unknown-end',
				$this->humanize( $interval->getStartDate() )
			);
		}

		if ( $interval->hasUnknownStart() ) {
			return $this->message(
				'edtf-interval-unknown-start',
				$this->humanize( $interval->getEndDate() )
			);
		}

		return '';
	}

	private function humanizeDateTime( ExtDateTime $dateTime ): string {
		return sprintf( "%02d:%02d:%02d", $dateTime->getHour(), $dateTime->getMinute(), $dateTime->getSecond() )
			. ' ' . $this->humanizeTimeZoneOffset( $dateTime->getTimezoneOffset() )
			. ' ' . $this->humanizeDate( $dateTime->getDate() );
	}

	private function humanizeTimeZoneOffset( ?int $offsetInMinutes ): string {
		if ( $offsetInMinutes === null ) {
			return '(' . $this->message( 'edtf-local-time' ) . ')';
		}

		if ( $offsetInMinutes === 0 ) {
			return 'UTC';
		}

		return 'UTC'
			. ( $offsetInMinutes < 0 ? '-' : '+' )
			. (string)floor( abs( $offsetInMinutes ) / 60 )
			. ( $offsetInMinutes % 60 === 0 ? '' : sprintf( ":%02d", abs( $offsetInMinutes ) % 60 ) );
	}
}
