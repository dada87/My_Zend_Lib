<?php
/**
 * Description of Zend_Validate_Age
 *
 * @author ddattee
 */
class Zend_Validate_Age extends Zend_Validate_Abstract {

  const EQUAL = 0;
	const OLDER_EXCLUSIF = 1;
	const OLDER_OR_EQUAL = 2;
	const YOUNGER_EXCLUSIF = -1;
	const YOUNGER_OR_EQUAL = -2;
	const MSG_INVALID_FORMAT = 'invalidFormat';
	const MSG_NOT_BORN = 'notBorn';
	const MSG_NOT_EQUAL = 'notEqual';
	const MSG_OLDER_THAN = 'holderThanAge';
	const MSG_OLDER_OR_EQUAL_THAN = 'olderOrEqualThanAge';
	const MSG_YOUNGER_THAN = 'youngerThanAge';
	const MSG_YOUNGER_OR_EQUAL_THAN = 'youngerOrEqualThanAge';

	private $__age_ref;
	private $__compare;
	protected $_messageTemplates = array(
		self::MSG_INVALID_FORMAT => "'%value%' is not a valid date format.",
		self::MSG_NOT_BORN => "The personn is not born.",
		self::MSG_NOT_EQUAL => "The personn born on the '%value%' is not %age% year(s) old.",
		self::MSG_OLDER_THAN => "The personn born on the '%value%' is older than %age% year(s) old.",
		self::MSG_OLDER_OR_EQUAL_THAN => "The personn born on the '%value%' is no younger than %age% year(s) old.",
		self::MSG_YOUNGER_THAN => "The personn born on the '%value%' is younger than %age% year(s) old.",
		self::MSG_YOUNGER_OR_EQUAL_THAN => "The personn born on the '%value%' is no older than %age% year(s) old.",
	);

	/**
	 * Validate that a personn is older/younger/equal to an age
	 * Usage:<br>
	 * $validate = new General_Validate_Age(13, General_Validate_Age::OLDER_OR_EQUAL);
	 * $validate->isValid('15/02/1974');
	 * 
	 * @param int $age Default = 18
	 * @param int $type Default = EQUAL
	 */
	public function General_Validate_Age($age = 18, $type = self::EQUAL) {
		$this->__age_ref = (int) $age;
		$this->__compare = (int) $type;
		foreach ($this->_messageTemplates as $key => $msg) {
			$this->_messageTemplates[$key] = preg_replace('/%age%/', $this->__age_ref, $msg);
		}
	}

	/**
	 * Vlaidate that the birthdate is more than 18ans year ago
	 * @param string $value Must be a date detectable by zend date.
	 * @return boolean
	 */
	public function isValid($value) {
		if (!Zend_Date::isDate($value)) {
			$this->_error(self::INVALID_FORMAT, $value);
			return false;
		}

		//To be sure calcul are right
		Zend_Date::setOptions(array('format_type' => 'php'));

		$today = new Zend_Date();
		$birthdate = new Zend_Date($value);
		//Deduce the number of year
		$age = $today->toString('Y') - $birthdate->toString('Y');
		// Ajust age for leap year
		$adjust = strcmp($today->toString('L'), $birthdate->toString('L'));
		if ($birthdate->toString('m') > 2) {
			$birthdate->addDay($adjust);
		}

		if ($today->toString('z') < $birthdate->toString('z')) {
			$age--;
		}
		//Reset the format to it's default
		Zend_Date::setOptions(array('format_type' => 'iso'));

		if ($age < 0) {
			$this->_error(self::MSG_NOT_BORN);
			return false;
		} else {
			switch ($this->__compare) {
				case self::OLDER_EXCLUSIF:
					//If he is younger or equale to the age
					if ($age <= $this->__age_ref) {
						$this->_error(self::MSG_YOUNGER_OR_EQUAL_THAN, $value);
						return false;
					}
					break;
				case self::OLDER_OR_EQUAL:
					//If he is younger than the age
					if ($age < $this->__age_ref) {
						$this->_error(self::MSG_YOUNGER_THAN, $value);
						return false;
					}
					break;
				case self::YOUNGER_EXCLUSIF:
					//If he is older or equal to the age
					if ($age >= $this->__age_ref) {
						$this->_error(self::MSG_OLDER_OR_EQUAL_THAN, $value);
						return false;
					}
					break;
				case self::YOUNGER_OR_EQUAL:
					//If he is older than the age
					if ($age > $this->__age_ref) {
						$this->_error(self::MSG_OLDER_THAN, $value);
						return false;
					}
					break;
				case self::EQUAL:
				default:
					//If he older or younger than the right age
					if ($age != $this->__age_ref) {
						$this->_error(self::MSG_NOT_EQUAL, $value);
						return false;
					}
					break;
			}
		}

		return true;
	}

}
