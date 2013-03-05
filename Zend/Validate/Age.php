<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of General_Validate_Url
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
	private $__date_ref;
	protected $_messageTemplates = array(
		self::MSG_INVALID_FORMAT => "'%value%' n'est pas reconnu comme une date valide.",
		self::MSG_NOT_BORN => "La personne n'est pas née.",
		self::MSG_NOT_EQUAL => "Le %dateref%, la personne née le '%value%' n'a pas %age% an(s).",
		self::MSG_OLDER_THAN => "Le %dateref%, la personne née le '%value%' a plus de %age% an(s).",
		self::MSG_OLDER_OR_EQUAL_THAN => "Le %dateref%, la personne née le '%value%' n'a pas moins de %age% an(s).",
		self::MSG_YOUNGER_THAN => "Le %dateref%, la personne née le '%value%' a moins de %age% an(s).",
		self::MSG_YOUNGER_OR_EQUAL_THAN => "Le %dateref%, la personne née le '%value%' n'a pas plus de de %age% an(s).",
	);

	/**
	 * Validate that a personn is older/younger/equal to an age at the date_ref given
	 * Usage:<br>
	 * $validate = new General_Validate_Age(13, General_Validate_Age::OLDER_OR_EQUAL);
	 * $validate->isValid('15/02/1974');
	 * 
	 * @param int $age Default = 18
	 * @param int $type Default = EQUAL
	 * @param string $date_ref Default = today Must be a Zend_Date compatible date
	 */
	public function General_Validate_Age($age = 18, $type = self::EQUAL, $date_ref = null) {
		$this->__compare = (int) $type;
		$this->__age_ref = (int) $age;
		$this->__date_ref = new Zend_Date($date_ref);
		foreach ($this->_messageTemplates as $key => $msg) {
			$this->_messageTemplates[$key] = preg_replace('/%age%/', $this->__age_ref, $msg);
		}
	}

	/**
	 * Vlaidate that the birthdate is more than 18ans year ago
	 * @param string $value Must be a Zend_Date compatible date
	 * @return boolean
	 */
	public function isValid($value) {
		if (!Zend_Date::isDate($value)) {
			$this->_error(self::MSG_INVALID_FORMAT, $value);
			return false;
		}

		//To be sure calcul are right
		Zend_Date::setOptions(array('format_type' => 'php'));

		$today = $this->__date_ref;
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
